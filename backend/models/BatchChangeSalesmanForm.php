<?php
namespace backend\models;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\CrmCustomerCombine;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderTeam;
use Yii;
use yii\base\Model;


class BatchChangeSalesmanForm extends Model
{
    public $order_id;
    public $company_id;
    public $administrator_id;

    /**
     * @var Order[]
     */
    public $orders;

    /** @var  Administrator */
    public $administrator;

    public function rules()
    {
        return [
            [['order_id'], 'required','message' => '请至少选择一个子订单！'],
            [['company_id','administrator_id'], 'required'],
            ['order_id', 'validateOrderId'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateOrderId()
    {
        $order_ids = explode(',',rtrim($this->order_id,','));
        $this->orders = Order::find()->where(['in','id',$order_ids])->all();
        if(count($order_ids) != count($this->orders))
        {
            $this->addError('order_id','多选中有无效的订单id存在');
        }
        else
        {
            foreach($this->orders as $order)
            {
                $orderTeam = OrderTeam::find()->select('id')
                    ->where(['order_id' => $order->id,'administrator_id' => $this->administrator_id])
                    ->limit(1)->one();
                if($orderTeam)
                {
                    $this->addError('order_id', '订单共享人不能作为订单负责人！');
                }
            }
        }
    }

    public function validateAdministratorId()
    {
       $this->administrator = Administrator::findOne($this->administrator_id);
       if(null == $this->administrator)
       {
           $this->addError('administrator_id','业务员不存在！');
       }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => '所属公司',
            'administrator_id' => '所属业务员',
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            foreach($this->orders as $order)
            {
                $order->salesman_aid = $this->administrator->id;
                $order->salesman_name = $this->administrator->name;
                $order->salesman_department_id = $this->administrator->department_id;
                $order->company_id = $this->administrator->company_id;
                $order->save(false);
                $order->sign();
                if(!(isset($order->customer_service_id) && $order->customer_service_id > 0))
                {
                    $order->autoAssignCustomerService($this->administrator->company_id);//自动分配客服
                }
                $company_name = $this->administrator->company ? $this->administrator->company->name.'的' : '';
                OrderRecord::create($order->id, '批量替换订单负责人', '订单负责人更新为：'.$company_name.$this->administrator->name,$admin, 0, OrderRecord::INTERNAL_ACTIVE,0);
                if(!$order->user->is_vest)
                {
                    CrmCustomerCombine::addTeam($this->administrator, $order->user->customer);
                }
                //新增后台操作日志
                AdministratorLog::logChangeSalesman($order);
            }
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}
