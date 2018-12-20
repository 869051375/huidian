<?php
namespace backend\models;
use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\ExpectedProfitSettlementDetail;
use common\models\Order;
use common\utils\BC;
use Yii;
use yii\base\Model;


class BatchKnotExpectedProfitForm extends Model
{
    public $order_id;
    public $company_id;
    public $department_id;
    public $rate;

    /**
     * @var Order[]
     */
    public $orders;

    /** @var  Administrator */
    public $administrator;

    /** @var  CrmDepartment */
    public $department;

    public function rules()
    {
        return [
            [['order_id'], 'required','message' => '请至少选择一个子订单！'],
            [['company_id','department_id','rate'], 'required'],
            ['rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
            ['order_id', 'validateOrderId'],
            ['department_id', 'validateDepartmentId'],
        ];
    }

    public function validateDepartmentId()
    {
        $this->department = CrmDepartment::find()->where(['id' => $this->department_id,'company_id' => $this->company_id])->limit(1)->one();
        if(null == $this->department)
        {
            $this->addError('department_id','公司部门不存在');
        }
    }

    public function validateOrderId()
    {
        $order_ids = explode(',',rtrim($this->order_id,','));
        $this->orders = Order::find()->where(['in','id',$order_ids])->all();
        if(count($order_ids) != count($this->orders))
        {
            $this->addError('order_id','多选中有无效的订单id存在');
        }
//        else
//        {
//            foreach($this->orders as $order)
//            {
//                if(floatval($order->getSurplusProfit()) == 0)
//                {
//                    $this->addError('order_id','当前子订单中没有可计算的预计利润！');
//                }
//            }
//        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => '结转对象所属公司',
            'department_id' => '结转对象所属部门',
            'rate' => '结转金额百分比',
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
            $data = [];
            foreach($this->orders as $i => $order)
            {
                $time = time();
                $year = date('Y', $time);
                $month = date('m', $time);
                $data[] = [
                    'year' => $year,
                    'month' => $month,
                    'order_id' => $order->id,
                    'virtual_order_id' => $order->virtual_order_id,
                    'sn' => $order->sn,
                    'v_sn' => $order->virtualOrder->sn,
                    'type' => ExpectedProfitSettlementDetail::TYPE_KNOT,
                    'company_id' => $this->department->company_id,
                    'company_name' => $this->department->company->name,
                    'title' => '预计利润结转',
                    'remark' => '',
                    'administrator_id' => 0,
                    'administrator_name' => '',
                    'department_id' => $this->department->id,
                    'department_name' => $this->department->name,
                    'department_path' => $this->department->path,
                    'expected_profit' => BC::div(BC::mul($order->getSurplusProfit(), $this->rate), 100, 2),
                    'created_at' => $time,
                    'creator_name' => $admin->name,
                    'creator_id' => $admin->id
                ];
            }
            \Yii::$app->db->createCommand()->batchInsert(ExpectedProfitSettlementDetail::tableName(), [
                'year',
                'month',
                'order_id',
                'virtual_order_id',
                'sn',
                'v_sn',
                'type',
                'company_id',
                'company_name',
                'title',
                'remark',
                'administrator_id',
                'administrator_name',
                'department_id',
                'department_name',
                'department_path',
                'expected_profit',
                'created_at',
                'creator_name',
                'creator_id',
            ], $data)->execute();
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
