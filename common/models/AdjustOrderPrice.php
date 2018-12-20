<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%adjust_order_price}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $virtual_order_id
 * @property string $adjust_price
 * @property string $adjust_price_reason
 * @property integer $status
 * @property string $status_reason
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $confirm_time
 * @property integer $confirm_id
 * @property string $confirm_name
 */
class AdjustOrderPrice extends ActiveRecord
{
    const STATUS_NOT_ADJUST = 0;
    const STATUS_PENDING = 1;
    const STATUS_PASS = 2;
    const STATUS_REJECT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%adjust_order_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'virtual_order_id', 'status', 'creator_id', 'created_at', 'confirm_time', 'confirm_id'], 'integer'],
            [['adjust_price'], 'number'],
            [['adjust_price_reason', 'status_reason'], 'string', 'max' => 80],
            [['creator_name', 'confirm_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'order_id' => '订单id',
            'virtual_order_id' => '虚拟订单id',
            'adjust_price' => '调整金额，保留小数点后2位',
            'adjust_price_reason' => '修改说明',
            'status' => '价格修改状态，申请中:0,申请通过:1,申请未通过:2',
            'status_reason' => '审核说明',
            'creator_id' => '操作人id',
            'creator_name' => '操作人姓名',
            'created_at' => '创建时间',
            'confirm_time' => '审核确认时间',
            'confirm_id' => '审核人id',
            'confirm_name' => '审核人姓名',
        ];
    }

    public static function createAdjustPrice($order_id,$virtual_order_id,$status,$administrator,$adjust_price,$adjust_price_reason)
    {
        $model = new AdjustOrderPrice();
        $model->order_id = $order_id;
        $model->virtual_order_id = $virtual_order_id;
        $model->status = $status;
        $model->creator_id = $administrator->id;
        $model->creator_name = $administrator->name;
        $model->created_at = time();
        $model->adjust_price = $adjust_price;
        $model->adjust_price_reason = $adjust_price_reason;
        $model->save(false);
    }
}
