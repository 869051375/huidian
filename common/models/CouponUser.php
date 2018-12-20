<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%coupon_user}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $coupon_id
 * @property integer $source
 * @property integer $status
 * @property integer $take_time
 *
 * @property Coupon $coupon
 */
class CouponUser extends ActiveRecord
{
    const STATUS_ACTIVE = 0; //未使用
    const STATUS_USED = 1; //已使用

    const SOURCE_TAKE     = 0; // 0: 用户领取
    const SOURCE_REGISTER = 1; // 1: 注册成功系统发放
    const SOURCE_ORDER_EVALUATE = 2; // 2: 订单评价成功系统发放

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%coupon_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'coupon_id', 'source', 'status', 'take_time'], 'integer'],
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
            'coupon_id' => 'Coupon ID',
            'source' => 'Source',
            'status' => 'Status',
            'take_time' => 'Take Time',
        ];
    }

    //已使用
    public function isUsed()
    {
        return $this->status == self::STATUS_USED;
    }

    //未使用
    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    //新到（24小时之内）
    public function isNewCoupon()
    {
        $time = time();
        return $this->take_time >= ($time - 86400) && $time >= $this->take_time;
    }

    public function getCoupon()
    {
        return self::hasOne(Coupon::className(), ['id' => 'coupon_id'])->orderBy(['end_effect_time' => SORT_ASC]);
    }
}
