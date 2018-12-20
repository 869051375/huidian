<?php
namespace backend\models;

use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\CrmCustomerCombine;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderTeam;
use Yii;
use yii\base\Model;
use yii\db\Expression;

class ChangeSalesmanForm extends Model
{
    public $order_id;
    public $salesman_id;//业务人员（administrator表id）
    public $user_id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Administrator
     */
    public $administrator;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['salesman_id', 'order_id','user_id'], 'required'],
            ['user_id', 'validateUserId'],
            ['order_id', 'validateOrderId'],
            ['salesman_id', 'validateSalesmanId'],
        ];
    }

    public function validateUserId()
    {
        $this->order = Order::find()
            ->where(['user_id'=>$this->user_id,'id'=>$this->order_id])
            ->one();
        if(null == $this->order)
        {
            $this->addError('salesman_id', '订单不存在。');
            return ;
        }
        if(empty($this->order->user))
        {
            $this->addError('salesman_id', '该客户不存在。');
        }
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);

        if(null == $this->order)
        {
            $this->addError('salesman_id', '订单不存在。');
            return ;
        }
        $orderTeam = OrderTeam::find()->select('id')
            ->where(['order_id' => $this->order->id,'administrator_id' => $this->salesman_id])
            ->limit(1)->one();
        if($orderTeam)
        {
            $this->addError('order_id', '订单共享人不能作为订单负责人！');
        }
    }

    public function validateSalesmanId()
    {
        $this->administrator = Administrator::findOne($this->salesman_id);
        if(null == $this->administrator)
        {
            $this->addError('salesman_id', '业务人员不存在。');
            return ;
        }
        if(!$this->administrator->isActive())
        {
            $this->addError('salesman_id', '该业务人员未开通服务。');
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'salesman_id' => '业务人员',
        ];
    }



    public function save()
    {

        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            $department_name = $this->administrator->department ? $this->administrator->department->name : '';
            $old_department_name = $this->order->salesmanDepartment ? $this->order->salesmanDepartment->name : '';
            $remark = $this->order->salesman_aid ? $old_department_name.$this->order->salesman_name.'更换为'.$department_name.$this->administrator->name : '新增订单负责人为'.$department_name.$this->administrator->name;


            $this->order->salesman_aid = $this->administrator->id;
            $this->order->salesman_department_id = $this->administrator->department_id;
            $this->order->salesman_name = $this->administrator->name;
            $this->order->company_id = $this->administrator->company_id;
            $this->order->save(false);
            if(!(isset($this->order->customer_service_id) && $this->order->customer_service_id > 0))
            {
                $this->order->autoAssignCustomerService($this->administrator->company_id);//自动分配客服
            }
            $this->order->sign();
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            OrderRecord::create($this->order->id, '修改负责人', $remark,$admin, 0, OrderRecord::INTERNAL_ACTIVE,0);
            if(!$this->order->user->is_vest)
            {
                CrmCustomerCombine::addTeam($this->administrator, $this->order->user->customer);
            }
            //新增后台操作日志
            AdministratorLog::logChangeSalesman($this->order);
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