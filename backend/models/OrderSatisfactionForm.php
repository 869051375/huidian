<?php
namespace backend\models;

use common\models\Order;
use yii\base\Model;

/**
 * Class OrderSatisfactionForm
 * @package backend\models
 *
 */
class OrderSatisfactionForm extends Model
{
    public $order_id;
    public $is_satisfaction;

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
            [['order_id', 'is_satisfaction'], 'required'],
            [['order_id', 'is_satisfaction'], 'integer'],
            [['order_id'], 'validateOrderId'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id', '订单不存在！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'is_satisfaction' => '客户满意度',
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->order->is_satisfaction = $this->is_satisfaction;
        if($this->order->save(false))
        {
            return true;
        }
        return false;
    }
}
