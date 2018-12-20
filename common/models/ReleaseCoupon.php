<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/8/23
 * Time: 下午3:32
 */

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\redis\Connection;

class ReleaseCoupon extends Model
{
    const TAKE_COUPON_QUEUE_KEY = 'take-coupon-queue';

    public $coupon_id;

    /**
     * @var User
     */
    public $user;

    public $source;

    /**
     * @var Coupon
     */
    private $coupon;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['coupon_id'], 'required'],
            [['coupon_id'], 'validateCoupon'],
        ];
    }

    public function validateCoupon()
    {
        $this->coupon = Coupon::findOne($this->coupon_id);
        if(null == $this->coupon)
        {
            $this->addError('coupon_id', '7');
        }
        else
        {
            if(!in_array($this->source, [CouponUser::SOURCE_TAKE, CouponUser::SOURCE_REGISTER, CouponUser::SOURCE_ORDER_EVALUATE]))
            {
                $this->addError('coupon_id', '4');
            }
            // 优惠券类型是否属于优惠券
            if($this->coupon->mode != Coupon::MODE_COUPON)
            {
                $this->addError('coupon_id', '7');
            }
            // 用户是否已经领取超出数量
            if($this->source === CouponUser::SOURCE_TAKE)
            {
                $count = CouponUser::find()->where(['user_id' => $this->user->id, 'coupon_id' => $this->coupon->id, 'source' => CouponUser::SOURCE_TAKE])->count();
                if($this->coupon->take_limit > 0 && $count >= $this->coupon->take_limit)
                {
                    $this->addError('coupon_id', '1'); // 已经领过了
                }
            }
            if($this->coupon->isEmpty())
            {
                $this->addError('coupon_id', '9'); // 被抢光了
            }
            // 是否在有效期范围内 并且 状态是否正常有效
//            if($this->coupon->isReleaseExpired())
//            {
//                $this->addError('coupon_id', '8'); // 已经失效
//            }
            if(!$this->coupon->isReleaseStatus())
            {
                $this->addError('coupon_id', '8'); // 已经失效
            }
            //过了领取时间
            if($this->coupon->isExpiredRelease())
            {
                $this->addError('coupon_id', '8'); // 已经失效
            }
            //未到领取时间
            if($this->coupon->isPendingRelease())
            {
                $this->addError('coupon_id', '10'); // 等生效
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'coupon_id' => '优惠券'
        ];
    }

    public function pushToQueue($isReturnKey = false, $isValidate = true)
    {
        if($isValidate && !$this->validate())
        {
            return false;
        }
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        $key = null;
        if($isReturnKey)
        {
            $key = md5($this->user->id.$this->coupon_id.rand(10000, 99999));
            $redis->set($key, null);
        }
        $redis->lpush(self::TAKE_COUPON_QUEUE_KEY, serialize(['user_id' => $this->user->id, 'coupon_id' => $this->coupon_id, 'source' => $this->source, 'key' => $key]));
        return $key;
    }

    /**
     * @return bool
     * @throws
     */
    public function release()
    {
        if(!$this->validate())
        {
            return false;
        }
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            $take = new CouponUser();
            $take->source = $this->source;
            $take->user_id = $this->user->id;
            $take->coupon_id = $this->coupon->id;
            $take->status = CouponUser::STATUS_ACTIVE;
            $take->take_time = time();
            $take->save(false);

            $count = Coupon::updateAll([
                'qty_received' => new Expression('qty_received+1'),
                ],
                [
                    'and',
                    ['id' => $this->coupon->id],
                    ['or',
                        ['>=', 'qty', new Expression('qty_received+1')],
                        ['<=', 'qty', 0]
                    ]
                ]);
            if($count === 1)
            {
                $t->commit();
                return true;
            }
            else
            {
                $this->addError('coupon_id', '9'); // 被抢光了
                $t->rollBack();
            }
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }

        return false;
    }
}