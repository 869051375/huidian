<?php
namespace backend\models;
use common\models\Administrator;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderRecord;
use Yii;
use yii\base\Model;


class BatchOrderSettlementMonthForm extends Model
{
    public $settlement_month;
    public $order_id;

    /**
     * @var Order[]
     */
    public $orders;

    public function rules()
    {
        return [
            [['settlement_month', 'order_id'], 'trim'],
            [['order_id'], 'required','message' => '请至少选择一个子订单！'],
            [['settlement_month'], 'required'],
            ['order_id', 'validateOrderId'],
            ['settlement_month','date', 'format' => 'yyyyMM','message' => '订单业绩提点所属月份格式不正确'],
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
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'settlement_month' => '订单业绩提点所属月份',
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->orders as $order)
            {
                $order->settlement_month = $this->settlement_month;
                OrderRecord::create($order->id, '批量编辑订单业绩提点月', '批量编辑订单业绩提点月更新为：'.$this->settlement_month, $admin,0, OrderRecord::INTERNAL_ACTIVE,0);
                $order->save(false);
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
