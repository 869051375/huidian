<?php
namespace common\models;

use yii\base\Model;

/**
 * Class CancelOrder
 * @package backend\models
 */
class CancelOrderForm extends Model
{
    public $virtual_order_id;

    /**
     * @var VirtualOrder
     */
    private $vo;

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['virtual_order_id', 'validateVirtualOrderId'],
        ];
    }

    public function validateVirtualOrderId()
    {
        $this->vo = VirtualOrder::find()->where(['id' => $this->virtual_order_id, 'user_id' => \Yii::$app->user->id])->one();
        if(!$this->vo)
        {
            $this->addError('virtual_order_id', '找不到订单。');
        }
        else
        {
            if($this->vo->isAlreadyPayment())
            {
                $this->addError('virtual_order_id', '该订单状态无法直接取消。');
            }
            foreach($this->vo->orders as $order)
            {
                if($order->isPayAfterService() && !$order->isPendingAllot())
                {
                    $this->addError('virtual_order_id', '该订单当前状态已经派单，不能取消。');
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'virtual_order_id' => '订单',
        ];
    }

    /**
     * 需要验证取消订单的一系列状态，服务终止，两种情况，1.已经付款的，需要生成退款记录，并且虚拟订单需要取消.2.未付款的，直接取消
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->vo->cancel(Order::BREAK_REASON_USER_CANCEL);
        $this->vo->refund();
        return true;
    }
}