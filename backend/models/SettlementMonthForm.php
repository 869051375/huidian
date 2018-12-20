<?php
namespace backend\models;

use common\models\MonthProfitRecord;
use common\models\Order;
use yii\base\Model;

/**
 * Class OrderTotalCostForm
 * @package backend\models
 *
 */
class SettlementMonthForm extends Model
{
    public $order_id;
    public $settlement_month;

    /**
     * @var Order
     */
    public $order;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['settlement_month', 'order_id'], 'required'],
            ['order_id', 'integer'],
            ['order_id', 'validateOrderId'],
            ['settlement_month', 'date', 'format' => 'yyyyMM', 'message' => '订单业绩提点所属月份格式不正确'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if($this->order == null)
        {
            $this->addError('order_id','你要找的订单不存在');
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

    public function save()
    {
        if(!$this->validate())return false;
        $this->order->settlement_month = $this->settlement_month;
        if($this->order->save())
        {
            return $this->order;
        }
        return false;
    }
}
