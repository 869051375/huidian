<?php

namespace common\models;

/**
 * This is the model class for table "funds_record".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $virtual_order_id
 * @property integer $pay_record_id
 * @property integer $receipt_id
 * @property integer $refund_record_id
 * @property string $virtual_sn
 * @property integer $order_id
 * @property string $order_sn_list
 * @property string $sn
 * @property integer $orientation
 * @property integer $pay_platform
 * @property integer $pay_method
 * @property string $amount
 * @property string $trade_no
 * @property integer $trade_time
 *
 * @property User $user
 * @property Order $order
 * @property Order[] $orders
 * @property Receipt $receipt
 * @property VirtualOrder $virtualOrder
 */
class FundsRecord extends \yii\db\ActiveRecord
{
    const PAY_MONEY = 0;  //出账
    const MONEY_COLLECTION = 1;  //进账
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'funds_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'virtual_order_id', 'order_id', 'orientation', 'pay_platform', 'trade_time','pay_method'], 'integer'],
            [['order_sn_list'], 'string'],
            [['amount'], 'number'],
            [['virtual_sn', 'sn'], 'string', 'max' => 17],
            [['trade_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'virtual_order_id' => 'Virtual Order ID',
            'virtual_sn' => 'Virtual Sn',
            'order_id' => 'Order ID',
            'order_sn_list' => 'Order Sn List',
            'sn' => 'Sn',
            'orientation' => 'Orientation',
            'pay_platform' => 'Pay Platform',
            'amount' => 'Amount',
            'trade_no' => 'Trade No',
            'trade_time' => 'Trade Time',
        ];
    }

    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrder()
    {
        return self::hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getVirtualOrder()
    {
        return self::hasOne(VirtualOrder::className(), ['id' => 'virtual_order_id']);
    }

    public function getReceipt()
    {
        return self::hasOne(Receipt::className(), ['id' => 'receipt_id']);
    }

    public function getOrders()
    {
        return self::hasMany(Order::className(), ['virtual_order_id' => 'id'])->via('virtualOrder');
    }

    public function getPayPlatformName()
    {
        if($this->pay_platform == PayRecord::PAY_PLATFORM_WX)
        {
            return '微信支付';
        }
        else if($this->pay_platform == PayRecord::PAY_PLATFORM_ALIPAY)
        {
            return '支付宝';
        }
        else if($this->pay_platform == PayRecord::PAY_PLATFORM_UNIONPAY)
        {
            return '银联支付';
        }
        else if($this->pay_platform == PayRecord::PAY_PLATFORM_CASH)
        {
            return '线下支付';
        }
        return null;
    }

    public function getPayMethodName()
    {
        if($this->pay_platform == PayRecord::PAY_PLATFORM_CASH && $this->pay_method != 0)
        {
            $pay_name = PayRecord::getPayMethod();
            return $pay_name[$this->pay_method];
        }
        return null;
    }

    public function getOrientation()
    {
        if($this->orientation==self::PAY_MONEY)
        {
            return '退款';
        }
        else if($this->orientation==self::MONEY_COLLECTION)
        {
            return '付款';
        }
        return null;
    }

    public function getOrderSnList()
    {
        $order_sn_list = trim($this->order_sn_list, ',');
        if(empty($order_sn_list)){
            return [];
        }
        return explode(',', trim($order_sn_list, ','));
    }

    public function setOrderSnList($orderSnList)
    {
        if(is_array($orderSnList))
            $this->order_sn_list = implode(',', $orderSnList);
    }

    public function setOrderIdList($orderSnList)
    {
        if(is_array($orderSnList))
            $this->order_id = implode(',', $orderSnList);
    }
}
