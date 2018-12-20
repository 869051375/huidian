<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%coupon_code}}".
 *
 * @property string $random_code
 * @property integer $coupon_id
 * @property integer $user_id
 * @property integer $status
 *
 * @property User $user
 * @property Coupon $coupon
 */
class CouponCode extends ActiveRecord
{
    const STATUS_UNUSED = 0; //未使用
    const STATUS_USED = 1; //已使用
    const STATUS_OBSOLETED = 2; //已作废

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%coupon_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['random_code'], 'required'],
            [['coupon_id', 'user_id', 'status'], 'integer'],
            [['random_code'], 'string', 'max' => 8],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'random_code' => 'Random Code',
            'coupon_id' => 'Coupon ID',
            'user_id' => 'User ID',
            'status' => 'Status',
        ];
    }

    public function isStatusUsed()
    {
        return $this->status == self::STATUS_USED;
    }

    public function isStatusUnused()
    {
        return $this->status == self::STATUS_UNUSED;
    }

    public function isStatusObsoleted()
    {
        return $this->status == self::STATUS_OBSOLETED;
    }

    public function getStatusName()
    {
        $statusList = static::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_UNUSED => '未使用',
            self::STATUS_USED => '已使用',
            self::STATUS_OBSOLETED => '已作废',
        ];
    }

    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCoupon()
    {
        return self::hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }
}
