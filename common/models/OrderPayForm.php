<?php
namespace common\models;

use yii\base\Model;

class OrderPayForm extends Model
{
    public $virtual_order_id;
    public $is_split_pay;
    public $pay_platform;
    public $payment;

    /**
     * @var VirtualOrder
     */
    public $vo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['virtual_order_id', 'pay_platform', 'is_split_pay'], 'required'],
            ['virtual_order_id', 'validateVirtualOrderId'],
            ['is_split_pay', 'boolean'],
            ['payment', 'number'],
            ['is_split_pay', 'validateIsSplitPay'],
        ];
    }

    public function validateVirtualOrderId()
    {
        $this->vo = VirtualOrder::findByUserId($this->virtual_order_id, \Yii::$app->user->id);
        if(null == $this->vo)
        {
            $this->addError('virtual_order_id', '订单不存在。');
            return;
        }
        if($this->vo->getPendingPayAmount() <= 0)
        {
            $this->addError('virtual_order_id', '该订单不可支付。');
        }
        if($this->vo->isOrderPayTimeout())
        {
            $this->addError('virtual_order_id', '该订单的支付时间已超时，请重新下单。');
        }

        //判断是否有库存限制商品库存量为0的订单，有就取消所有订单
        foreach($this->vo->orders as $order)
        {
            if($order->product->isInventoryLimit() && $order->product->inventory_qty <= 0)
            {
                $this->addError('virtual_order_id', '该订单存在库存不足的商品，请重新下单。');
                return;
            }
        }
    }

    public function validateIsSplitPay()
    {
        if($this->is_split_pay)
        {
            if($this->payment < 0.01)
            {
                $this->addError('payment', '支付金额必须大于0.01。');
            }
            else if($this->payment > $this->vo->getPendingPayAmount())
            {
                $this->addError('payment', '支付金额不能超出订单待支付金额。');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'username' => '账号',
            'password' => '密码',
            'auto_login' => '自动登录',
            'payment' => '支付金额',
        ];
    }

    /**
     * @return PayRecord|null
     */
    public function save()
    {
        if(!$this->validate()) return null;
        $pr = PayRecord::createPayRecord($this->vo, $this->is_split_pay ? $this->payment : $this->vo->getPendingPayAmount());
        if(null != $pr)
        {
            return $pr;
        }
        return null;
    }
}
