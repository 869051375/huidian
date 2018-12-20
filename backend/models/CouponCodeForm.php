<?php
namespace backend\models;
use common\models\Coupon;
use common\models\CouponCode;
use yii\base\Model;

class CouponCodeForm extends Model
{
    /**
     * @var Coupon
     */
    public $coupon;

    public $coupon_id;
    public $name;
    public $remark;
//    public $begin_release_time;
    public $end_release_time;
    public $begin_effect_time;
    public $end_effect_time;
    public $qty;

    public $coupon_code;
    public $mode;
    public $generate_status;
    public $qty_received;
    public $qty_used;
    public $product_ids;
    public $scope;
    public $type;
    public $code_type;
    public $is_confirm;
    public $status;
    public $remit_amount;
    public $order_total_amount;
    public $creator_id;
    public $creator_name;
    public $created_at;

    public $product_id;

    /**
     * @var Coupon
     */
    public $couponProduct;

    public function rules()
    {
        return [
            [['name'], 'required', 'on' => ['insert', 'update']],
            ['name', 'string', 'max' => 15 , 'on' => 'insert'],

            [['remark'], 'required', 'on' => 'update'],
            [['remark'], 'string', 'max' => 50, 'on' => 'update'],

            [['mode', 'generate_status', 'qty', 'qty_received', 'qty_used', 'scope', 'type', 'code_type', 'is_confirm', 'status', 'creator_id', 'created_at'], 'integer', 'on' => 'update'],
            [['product_ids'], 'string', 'on' => 'update'],
            [['remit_amount'], 'number', 'max'=> 9999.99, 'on' => 'update'],
            [['order_total_amount'], 'number', 'max'=> 9999.99, 'on' => 'update'],
            [['coupon_code'], 'string', 'max' => 30, 'on' => 'update'],

            [['begin_effect_time', 'end_effect_time'], 'required', 'on' => 'update'],
            [['begin_effect_time', 'end_effect_time'], 'date', 'format' => 'yyyy-MM-dd', 'on' => 'update'],
            ['begin_effect_time', 'string', 'max' => 10, 'on' => 'update'],
            ['end_effect_time', 'string', 'max' => 10, 'on' => 'update'],

            ['begin_effect_time', 'validateEffectTimes', 'on' => 'update'],

            [['coupon_id', 'qty', 'scope', 'code_type'], 'required', 'on' => 'update'],

            [['coupon_code', 'code_type'], 'validateCouponCode', 'skipOnEmpty' => false, 'skipOnError' => false],

        ];
    }

    public function validateCouponCode()
    {
        if(!empty($this->code_type) && $this->code_type == Coupon::CODE_TYPE_FIXED)
        {
            if(empty($this->coupon_code))
            {
                $this->addError('coupon_code', '固定码不能为空。');
            }
            else
            {
                $coupon = Coupon::find()->where(['coupon_code' => $this->coupon_code])->one();
                if(null != $coupon)
                {
                    $this->addError('coupon_code', '固定码不能相同。');
                }
            }
        }
    }

    public function validateEffectTimes($attribute, $params)
    {
        $coupon = Coupon::findOne($this->coupon_id);
        if(null == $coupon)
        {
            $this->addError($attribute, '您的操作有误！');
        }
        else
        {
            if($coupon->created_at <= 0)
            {
                $this->addError($attribute, '您的操作有误！');
            }

            $create_at = \Yii::$app->formatter->asDate($coupon->created_at);
            if($this->begin_effect_time < $create_at)
            {
                $this->addError($attribute, '生效起始日期不能小于创建时间！');
            }
            elseif($this->begin_effect_time > $this->end_effect_time)
            {
                $this->addError($attribute, '生效起始日期需不能大于生效截止日期！');
            }
        }
    }

    public function validateId()
    {
        $this->couponProduct = Coupon::findOne($this->coupon_id);
        if(null == $this->couponProduct)
        {
            $this->addError('id', '您的操作有误！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '优惠码名称',
            'coupon_code' => '优惠码',
            'remark' => '优惠码说明',
            'begin_effect_time' => '使用有效期(开始)',
            'end_effect_time' => '使用有效期(截止)',
            'qty' => '发行数量',
            'product_id' => '添加商品',
            'scope' => '应用范围',
            'code_type' => '优惠码类型',
            'remit_amount' => '满减金额',
            'order_total_amount' => '订单满',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;

        $coupon = new Coupon();
        $coupon->name = $this->name;
        $coupon->mode = Coupon::MODE_COUPON_CODE;
        if(!$coupon->save(false))
        {
            return null;
        }
        return $coupon;
    }

    /**
     * @param Coupon $coupon
     * @return null
     */
    public function update($coupon)
    {
        if(!$this->validate()) return null;
        $begin_effect_time = strtotime($this->begin_effect_time. '00:00:00');
        $end_effect_time = strtotime($this->end_effect_time. '23:59:59');
        $coupon->begin_effect_time = $begin_effect_time > 0 ? $begin_effect_time : 0;
        $coupon->end_effect_time = $end_effect_time > 0 ? $end_effect_time : 0;
        $coupon->remark = $this->remark;
        $coupon->remit_amount = $this->remit_amount;
        $coupon->scope = $this->scope;
        $coupon->coupon_code = $this->coupon_code;
        $coupon->code_type = $this->code_type;
        $coupon->generate_status = 0;
        $coupon->order_total_amount = $this->order_total_amount;
        $coupon->qty = $this->qty;
        $coupon->mode = Coupon::MODE_COUPON_CODE;
        $coupon->is_confirm = 1;
        if(!$coupon->update(false))return null;
        return $coupon;
    }
}
