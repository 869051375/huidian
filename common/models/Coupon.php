<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%coupon}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $coupon_code
 * @property string $remark
 * @property integer $mode
 * @property integer $generate_status
 * @property integer $qty
 * @property integer $qty_received
 * @property integer $qty_used
 * @property string $product_ids
 * @property integer $scope
 * @property integer $type
 * @property integer $code_type
 * @property integer $is_confirm
 * @property integer $status
 * @property string $remit_amount
 * @property integer $discount
 * @property string $order_total_amount
 * @property integer $is_release_evaluate
 * @property integer $is_release_register
 * @property integer $take_limit
 * @property integer $can_return
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $begin_release_time
 * @property integer $end_release_time
 * @property integer $begin_effect_time
 * @property integer $end_effect_time
 * @property integer $created_at
 *
 * @property CouponUser[] $couponUsers
 */
class Coupon extends ActiveRecord
{
    const TYPE_REDUCTION = 1; //1.满减券
    const TYPE_DISCOUNT = 2; //2.折扣券

    const MODE_COUPON = 1; //优惠券
    const MODE_COUPON_CODE = 2; //优惠码

    const CODE_TYPE_FIXED = 1; //固定码
    const CODE_TYPE_RANDOM = 2; //随机码

    const STATUS_ACTIVE = 0; //未作废
    const STATUS_OBSOLETED = 1; //已作废

    const CONFIRM_DISABLED = 0;//未确认发布
    const CONFIRM_ACTIVE = 1;//确认发布

    const CAN_RETURN_DISABLED = 0;//不可退
    const CAN_RETURN_ACTIVE = 1;//可退

    //是否评价成功发放
    const RELEASE_EVALUATE_DISABLED = 0; //否
    const RELEASE_EVALUATE_ACTIVE = 1; //是

    //是否注册成功发放
    const RELEASE_REGISTER_DISABLED = 0; //否
    const RELEASE_REGISTER_ACTIVE = 1; //是

    //应用范围
    const SCOPE_REMOVE = 0; //排除商品
    const SCOPE_APPLY = 1; //应用商品

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%coupon}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mode', 'generate_status', 'qty', 'qty_received', 'qty_used', 'scope', 'type', 'code_type', 'is_confirm', 'status', 'discount', 'is_release_evaluate', 'is_release_register', 'take_limit', 'can_return', 'creator_id', 'begin_release_time', 'end_release_time', 'begin_effect_time', 'end_effect_time', 'created_at'], 'integer'],
            [['product_ids'], 'string'],
            [['remit_amount', 'order_total_amount'], 'number'],
            [['name'], 'string', 'max' => 15],
            [['coupon_code'], 'string', 'max' => 30],
            [['remark'], 'string', 'max' => 50],
            [['creator_name'], 'string', 'max' => 22],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],

            ],
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert){
                /** @var Administrator $user */
                $user = \Yii::$app->user->identity;
                $this->creator_id = $user->id;
                $this->creator_name = $user->getTypeName(). ':'.$user->name;
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'coupon_code' => 'Coupon Code',
            'remark' => 'Remark',
            'mode' => 'Mode',
            'generate_status' => 'Generate Status',
            'qty' => 'Qty',
            'qty_received' => 'Qty Received',
            'qty_used' => 'Qty Used',
            'product_ids' => 'Product Ids',
            'scope' => 'Scope',
            'type' => 'Type',
            'code_type' => 'Code Type',
            'is_confirm' => 'Is Confirm',
            'status' => 'Status',
            'remit_amount' => 'Remit Amount',
            'discount' => 'Discount',
            'order_total_amount' => 'Order Total Amount',
            'is_release_evaluate' => 'Is Release Evaluate',
            'is_release_register' => 'Is Release Register',
            'take_limit' => 'Take Limit',
            'can_return' => 'Can Return',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'begin_release_time' => 'Begin Release Time',
            'end_release_time' => 'End Release Time',
            'begin_effect_time' => 'Begin Effect Time',
            'end_effect_time' => 'End Effect Time',
            'created_at' => 'Created At',
        ];
    }

    //评价成功发放
    public function isReleaseEvaluate()
    {
        return $this->is_release_evaluate == self::RELEASE_EVALUATE_ACTIVE;
    }

    //注册成功发放
    public function isReleaseRegister()
    {
        return $this->is_release_register == self::RELEASE_REGISTER_ACTIVE;
    }

    //优惠券
    public function isModeCoupon()
    {
        return $this->mode == self::MODE_COUPON;
    }

    //优惠码
    public function isModeCouponCode()
    {
        return $this->mode == self::MODE_COUPON_CODE;
    }

    //应用于商品
    public function isApplyScope()
    {
        return $this->scope == self::SCOPE_APPLY;
    }

    //排除商品
    public function isRemoveScope()
    {
        return $this->scope == self::SCOPE_REMOVE;
    }

    //固定码
    public function isCodeFixed()
    {
        return $this->code_type == self::CODE_TYPE_FIXED;
    }

    //随机码
    public function isCodeRandom()
    {
        return $this->code_type == self::CODE_TYPE_RANDOM;
    }

    //折扣券
    public function isTypeDiscount()
    {
        return $this->type == self::TYPE_DISCOUNT;
    }

    //满减券
    public function isTypeReduction()
    {
        return $this->type == self::TYPE_REDUCTION;
    }

    public function getTypeName()
    {
        $statusList = static::getTypeList();
        return $statusList[$this->type];
    }

    public static function getTypeList()
    {
        return [
            self::TYPE_REDUCTION => '满减券',
            self::TYPE_DISCOUNT => '折扣券',
        ];
    }

    public function getCodeTypeName()
    {
        $statusList = static::getCodeTypeList();
        return $statusList[$this->code_type];
    }

    public static function getCodeTypeList()
    {
        return [
            self::CODE_TYPE_FIXED => '固定码',
            self::CODE_TYPE_RANDOM => '随机码',
        ];
    }

    public function getStatusName()
    {
        $statusList = static::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => '未作废',
            self::STATUS_OBSOLETED => '已作废',
        ];
    }

    //未生效
    public function isNotEffect()
    {
        return $this->begin_effect_time > time();
    }

    //正常生效使用中
    public function isNormal()
    {
        $time = time();
        return $this->begin_effect_time <= $time && $this->end_effect_time >= $time;
    }

    // 未到领取时间
    public function isPendingRelease()
    {
        return time() < $this->begin_release_time;
    }

    // 过了领取时间
    public function isExpiredRelease()
    {
        return time() > $this->end_release_time;
    }

    // 是否在领取时间
    public function isReleaseExpired()
    {
        $time = time();
        return $time < $this->begin_release_time || $time > $this->end_release_time;
    }

    // 是否在为可领取状态
    public function isReleaseStatus()
    {
        return $this->status === Coupon::STATUS_ACTIVE && $this->is_confirm === Coupon::CONFIRM_ACTIVE;
    }

    //未到使用期
    public function isisPendingEffect()
    {
        return time() < $this->begin_effect_time;
    }

    //已过期
    public function isExpired()
    {
        return $this->end_effect_time < time();
    }

    //即将过期（7天之内）
    public function isWillExpire()
    {
        $time = time();
        return $this->end_effect_time <= ($time + 7*86400) && $time <= $this->end_effect_time;
    }

    //已作废
    public function isObsoleted()
    {
        return $this->status == self::STATUS_OBSOLETED;
    }

    //已确认
    public function isConfirmed()
    {
        return $this->is_confirm == self::CONFIRM_ACTIVE;
    }

    //优惠券可退
    public function canReturn()
    {
        return $this->can_return == self::CAN_RETURN_ACTIVE;
    }


    //确认优惠券
    public function confirmed()
    {
        $this->is_confirm = self::CONFIRM_ACTIVE;
        return $this->save(false);
    }

    //未使用数量
    public static function couponActiveCounts()
    {
        $query = self::couponCountQuery();
        $time = time();
        $query->andWhere(['and', ['>=', 'c.end_effect_time', $time], ['cu.status' => CouponUser::STATUS_ACTIVE]]);
//        $query->andWhere(['cu.status' => CouponUser::STATUS_ACTIVE]);
//        $query->andWhere(['and', ['<=', 'c.begin_effect_time', $time],
//            ['>=', 'c.end_effect_time', $time]]);
        $count = $query->count();
        return $count ? $count : 0 ;
    }

    //已使用数量
    public static function couponUsedCounts()
    {
        $query = self::couponCountQuery();
        $query->andWhere(['cu.status' => CouponUser::STATUS_USED]);
        $count = $query->count();
        return $count ? $count : 0 ;
    }

    //已过期数量
    public static function couponInvalidCounts()
    {
        $query = self::couponCountQuery();
        $query->andWhere(['and', ['<', 'c.end_effect_time', time()], ['cu.status' => CouponUser::STATUS_ACTIVE]]);
        $count = $query->count();
        return $count ? $count : 0 ;
    }

    //获取可用的优惠券
    public static function getEffectCoupons()
    {
        $query = self::couponCountQuery();
        $time = time();
        $query->andWhere(['cu.status' => CouponUser::STATUS_ACTIVE]);
        $query->andWhere(['and', ['<=', 'c.begin_effect_time', $time],
            ['>=', 'c.end_effect_time', $time]]);
        return $query->all();
    }

    private static function couponCountQuery()
    {
        $query = CouponUser::find()->alias('cu');
        $query -> innerJoinWith('coupon c');
        $query->andWhere(['cu.user_id' => \Yii::$app->user->id]);
        $query -> andWhere(['c.mode' => Coupon::MODE_COUPON]);
        return $query;
    }

    public function getProductIds()
    {
        $ids = trim($this->product_ids, ',');
        if(empty($ids)){
            return [];
        }
        return explode(',', trim($ids, ','));
    }

    public function addProductId($id)
    {
        $ids = $this->getProductIds();
        $ids[] = $id;
        $this->setProductIds($ids);
    }

    public function setProductIds($product_ids)
    {
        if(!empty($product_ids)){
            $product_ids = array_unique($product_ids);
            $this->product_ids = ','.implode(',', $product_ids).',';
        }
        else
        {
            $this->product_ids = '';
        }
    }

    /**
     * @param $product_id
     * @return bool 是否成功
     */
    public function removeProduct($product_id)
    {
        $ids = $this->getProductIds();
        ArrayHelper::removeValue($ids, $product_id);
        $this->setProductIds($ids);
        return $this->save(false);
    }

    /**
     * @return Product[]
     */
    public function getProductList()
    {
        $ids = $this->getProductIds();
        if(empty($ids)) return [];
        return Product::find()->where(['in', 'id', $this->getProductIds()])->all();
    }
    public function getCouponUsers()
    {
        return self::hasMany(CouponUser::className(), ['coupon_id' => 'id']);
    }

    public function getEffectiveCoupons($type = null)
    {
        $currentTime = time();
        $query = Coupon::find()
            ->andWhere([
            'mode' => Coupon::MODE_COUPON,
            'status' => Coupon::STATUS_ACTIVE,
            'is_confirm' => Coupon::CONFIRM_ACTIVE,])
            ->andWhere(['and',
                ['<', 'begin_release_time', $currentTime],
                ['>', 'end_release_time', $currentTime]]);
        if($this->qty > 0)
        {
            $query->andWhere(['>', 'qty', 'qty_received']);
        }

        if($type === 'register')
        {
            $query->andWhere(['is_release_register' => Coupon::RELEASE_REGISTER_ACTIVE]);
        }

        if($type === 'evaluate')
        {
            $query->andWhere(['is_release_evaluate' => Coupon::RELEASE_EVALUATE_ACTIVE]);
        }

        return $query->all();
    }

    public function isEmpty()
    {
        return $this->qty > 0 && $this->qty <= $this->qty_received;
    }
}
