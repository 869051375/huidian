<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\base\Exception;

/**
 * This is the model class for table "{{%order_record}}".
 *
 * @property integer $id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $order_id
 * @property integer $receipt_id
 * @property integer $order_flow_record_id
 * @property integer $file_id
 * @property string $title
 * @property string $remark
 * @property integer $is_internal
 * @property integer $created_at
 * @property OrderFlowRecord[] $flowRecords
 * @property OrderFlowRecord $flowRecord
 * @property OrderFile $orderFile
 * @property Receipt $receipt
 *
 * @property Order $order
 */
class OrderRecord extends ActiveRecord
{
    const INTERNAL_ACTIVE = 1;//仅内部后台查看
    const INTERNAL_DISABLED = 0;//仅前台查看
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creator_id', 'order_id', 'receipt_id', 'order_flow_record_id', 'file_id', 'created_at'], 'integer'],
            [['remark'], 'string'],
            [['creator_name'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 80],
            ['is_internal', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'order_id' => 'Order ID',
            'receipt_id' => 'Receipt ID',
            'order_flow_record_id' => 'Order Flow Record ID',
            'file_id' => 'File ID',
            'title' => 'Title',
            'remark' => 'Remark',
            'is_internal' => 'Is Internal',
            'created_at' => 'Created At',
        ];
    }

    public function getOrder()
    {
        return static::hasOne(Order::className(), ['id' => 'order_id']);

    }

    public function getFlowRecords()
    {
        return static::hasMany(OrderFlowRecord::className(), ['id' => 'order_flow_record_id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getFlowRecord()
    {
        return static::hasOne(OrderFlowRecord::className(), ['id' => 'order_flow_record_id']);
    }

    public function getOrderFile()
    {
        return static::hasOne(OrderFile::className(), ['id' => 'file_id']);
    }
    /**
     * 添加信息到订单记录表
     * @param string $title
     * @param string $remark
     * @param int $order_id
     * @param int $receipt_id
     * @param int $order_flow_record_id
     * @param int $file_id
     * @param int $is_internal
     * @param Administrator $admin
     * @return bool
     * @throws Exception
     */
    public static function create($order_id, $title= '', $remark = '', $admin = null, $order_flow_record_id = 0, $is_internal = 0, $file_id = 0, $receipt_id = 0)
    {
        $orderRecord = new OrderRecord();
        $orderRecord->order_id = $order_id;
        $orderRecord->title = $title;
        $orderRecord->remark = $remark;
        $orderRecord->order_flow_record_id = $order_flow_record_id;
        $orderRecord->file_id = $file_id;
        $orderRecord->is_internal = $is_internal;
        $orderRecord->receipt_id = $receipt_id;
        if(null != $admin)
        {
            $orderRecord->creator_id = $admin->id;
            $orderRecord->creator_name = $admin->name;
        }
        $orderRecord->created_at = time();
        if(!$orderRecord->save(false)) return false;
        return true;
    }

    public function getOperator()
    {
        if($this->creator_id==0)
        {
            return '客户';
        }
        return $this->creator_name;
    }

    public function getReceipt()
    {
        return self::hasOne(Receipt::className(), ['id' => 'receipt_id']);
    }
}
