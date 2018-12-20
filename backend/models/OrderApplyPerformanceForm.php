<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\models\OrderBalanceRecord;
use Yii;
use yii\base\Model;

/**
 * Class OrderSatisfactionForm
 * @package backend\models
 *
 */
class OrderApplyPerformanceForm extends Model
{
    public $order_id;

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
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
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
            'order_id' => '订单id',
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->order->is_apply = Order::APPLY_ACTIVE;
        if($this->order->save(false))
        {
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            OrderBalanceRecord::createRecord('订单申请计算业绩提成',OrderBalanceRecord::STATUS_APPLY,$this->order->id,$admin->name.'申请计算业绩提成');
            return true;
        }
        return false;
    }
}
