<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%invoice}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property string $order_sn
 * @property integer $virtual_order_id
 * @property string $invoice_amount
 * @property string $invoice_title
 * @property string $tax_number
 * @property string $addressee
 * @property string $phone
 * @property string $address
 * @property integer $status
 * @property string $express
 * @property string $express_no
 * @property integer $created_at
 * @property integer $confirm_time
 * @property integer $invoice_time
 * @property integer $send_time
 *
 * @property Order $order
 * @property User $user
 */
class Invoice extends ActiveRecord
{
    const STATUS_SUBMITTED = 0;//发票已提交申请，后台待确认
    const STATUS_CONFIRMED = 1;//后台已确认
    const STATUS_INVOICED = 2;//发票已开具
    const STATUS_SEND = 3;//发票已寄送
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'virtual_order_id', 'status', 'created_at', 'confirm_time', 'invoice_time', 'send_time'], 'integer'],
            [['invoice_amount'], 'number'],
            [['order_sn'], 'string', 'max' => 16],
            [['invoice_title', 'express'], 'string', 'max' => 100],
            [['addressee'], 'string', 'max' => 10],
            [['phone'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 255],
            [['express_no'], 'string', 'max' => 24],
            [['tax_number'], 'string', 'max' => 18],
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
            'order_id' => 'Order ID',
            'order_sn' => 'Order Sn',
            'virtual_order_id' => 'Virtual Order ID',
            'invoice_amount' => 'Invoice Amount',
            'invoice_title' => 'Invoice Title',
            'addressee' => 'Addressee',
            'phone' => 'Phone',
            'address' => 'Address',
            'status' => 'Status',
            'express' => 'Express',
            'express_no' => 'Express No',
            'created_at' => 'Created At',
            'confirm_time' => 'Confirm Time',
            'invoice_time' => 'Invoice Time',
            'send_time' => 'Send Time',
            'tax_number' => 'Tax Number',
        ];
    }

    //发票已提交申请，后台待确认
    public function isSubmitted()
    {
        return $this->status == self::STATUS_SUBMITTED;
    }

    //后台已确认
    public function isConfirmed()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }

    //发票已开具
    public function isInvoiced()
    {
        return $this->status == self::STATUS_INVOICED;
    }

    //发票已寄出
    public function isSend()
    {
        return $this->status == self::STATUS_SEND;
    }

    public function getOrder()
    {
        return static::hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getUser()
    {
        return static::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getStatusName()
    {
        $statusList = static::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_SUBMITTED => '待确认',
            self::STATUS_CONFIRMED => '待开具',
            self::STATUS_INVOICED => '待寄送',
            self::STATUS_SEND => '已寄送'
        ];
    }
}
