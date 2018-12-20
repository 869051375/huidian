<?php
namespace backend\models;
use common\models\Coupon;
use common\models\Product;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class CouponForm extends Model
{
    /**
     * @var Coupon
     */
    public $coupon;

    public $id;
    public $coupon_id;
    public $name;
    public $remark;
    public $begin_release_time;
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
    public $discount;
    public $order_total_amount;
    public $is_release_evaluate;
    public $is_release_register;
    public $take_limit;
    public $can_return;
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

            [['mode', 'generate_status', 'qty', 'qty_received', 'qty_used', 'scope', 'type', 'code_type', 'is_confirm', 'status', 'discount', 'is_release_evaluate', 'is_release_register', 'take_limit', 'can_return', 'creator_id', 'created_at'], 'integer', 'on' => 'update'],
            [['product_ids'], 'string', 'on' => 'update'],
            [['remit_amount'], 'number', 'max'=> 9999999999999.99,'tooBig'=>'满减金额的值必须不大于9,9999,9999,9999.99。', 'on' => 'update'],
            [['order_total_amount'], 'number', 'max'=> 9999999999999.99, 'tooBig'=>'订单满的值必须不大于9,9999,9999,9999.99。','on' => 'update'],
            [['discount'], 'number', 'max'=> 99, 'on' => 'update'],
            [['coupon_code'], 'string', 'max' => 30, 'on' => 'update'],

            [['begin_release_time', 'end_release_time', 'begin_effect_time', 'end_effect_time'], 'required', 'on' => 'update'],
            [['begin_release_time', 'end_release_time', 'begin_effect_time', 'end_effect_time'], 'date', 'format' => 'yyyy-MM-dd', 'on' => 'update'],
            ['begin_release_time', 'string', 'max' => 10 , 'on' => 'update'],
            ['end_release_time', 'string', 'max' => 10, 'on' => 'update'],
            ['begin_effect_time', 'string', 'max' => 10, 'on' => 'update'],
            ['end_effect_time', 'string', 'max' => 10, 'on' => 'update'],
            ['begin_release_time', 'validateReleaseTimes', 'on' => 'update'],
            [['coupon_id', 'qty', 'take_limit', 'scope', 'type'], 'required', 'on' => 'update'],

            [['id'], 'required', 'on' => 'addProduct'],
            ['id', 'validateId','on'=>'addProduct'],
            ['product_id','required','on'=>'addProduct'],
            [['product_id'], 'validateProductId','on'=>'addProduct'],

            [['coupon'], 'validateCoupon', 'on' => 'confirm'],
        ];
    }

    public function validateCoupon()
    {
        $model = Coupon::findOne( $this->coupon->id);
        if($this->coupon->isConfirmed())
        {
            $this->addError('coupon', '优惠券已经确认提交了，无法操作!');
        }
        if($model->isApplyScope())
        {
            if(empty($model->getProductIds()))
            {
                $this->addError('coupon', '没有添加应用商品，无法操作!');
            }
        }
        if($model->begin_effect_time <= 0 || $model->end_effect_time <= 0)
        {
            $this->addError('coupon', '没有添加使用有效期，无法操作!');
        }
    }

    public function validateReleaseTimes($attribute, $params)
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
            if($this->begin_release_time < $create_at)
            {
                $this->addError($attribute, '发放/领取开始时间不能小于创建时间！');
            }
            elseif($this->begin_release_time > $this->end_release_time )
            {
                $this->addError($attribute, '发放/领取开始时间不能大于发放/领取截止时间！');
            }
            elseif($this->begin_effect_time > $this->end_effect_time)
            {
                $this->addError($attribute, '生效起始日期需不能大于生效截止日期！');
            }
            elseif($this->begin_effect_time < $this->begin_release_time)
            {
                $this->addError($attribute, '生效起始日期需大于或等于发放/领取起始日期！');
            }
            elseif($this->end_effect_time < $this->end_release_time)
            {
                $this->addError($attribute, '生效截止日期需大于或等于发放/领取截止日期！');
            }
        }
    }

    public function validateId()
    {
        $this->couponProduct = Coupon::findOne($this->id);
        if(null == $this->couponProduct)
        {
            $this->addError('id', '您的操作有误！');
        }
    }

    public function validateProductId()
    {
        if(null != $this->couponProduct)
        {
            $ids = $this->couponProduct->getProductIds();
            if(in_array($this->product_id, $ids))
            {
                $this->addError('product_id', '您已添加过此商品！');
            }
        }
        $product = Product::findOne($this->product_id);
        if(null == $product)
        {
            $this->addError('product_id', '您的操作有误！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '优惠券名称',
            'remark' => '优惠券说明',
            'begin_release_time' => '发放/领取日期(开始)',
            'end_release_time' => '发放/领取日期(截止)',
            'begin_effect_time' => '使用有效期(开始)',
            'end_effect_time' => '使用有效期(截止)',
            'qty' => '发行数量',
            'product_id' => '添加商品',
            'scope' => '应用范围',
            'type' => '优惠券类型',
            'remit_amount' => '满减金额',
            'discount' => '折扣力度',
            'order_total_amount' => '订单满',
            'is_release_evaluate' => '评价成功系统自动发放',
            'is_release_register' => '注册成功系统自动发放',
            'take_limit' => '单个用户限领张数',
            'can_return' => '订单取消退回账户',
        ];
    }

    /**
     * @param Coupon $coupon
     * @return bool
     */
    public function confirm($coupon)
    {
        if(!$coupon->save()) return false;
        return $this->coupon->confirmed();
    }

    public function save()
    {
        if(!$this->validate()) return false;

        $coupon = new Coupon();
        $coupon->name = $this->name;
        $coupon->mode = Coupon::MODE_COUPON;
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
        $begin_release_time = strtotime($this->begin_release_time. '00:00:00');
        $end_release_time = strtotime($this->end_release_time. '23:59:59');
        $begin_effect_time = strtotime($this->begin_effect_time. '00:00:00');
        $end_effect_time = strtotime($this->end_effect_time. '23:59:59');
        $coupon->begin_release_time = $begin_release_time > 0 ? $begin_release_time : 0;
        $coupon->end_release_time = $end_release_time > 0 ? $end_release_time : 0;
        $coupon->begin_effect_time = $begin_effect_time > 0 ? $begin_effect_time : 0;
        $coupon->end_effect_time = $end_effect_time > 0 ? $end_effect_time : 0;
        $coupon->remark = $this->remark;
        $coupon->scope = $this->scope;
        $coupon->type = $this->type;
        if($this->type == Coupon::TYPE_REDUCTION)
        {
            $coupon->remit_amount = $this->remit_amount;
        }
        else
        {
            $coupon->discount = $this->discount;
        }
        $coupon->order_total_amount = $this->order_total_amount;
        $coupon->qty = $this->qty;
        $coupon->take_limit = $this->take_limit;
        $coupon->is_release_evaluate = $this->is_release_evaluate;
        $coupon->is_release_register = $this->is_release_register;
        $coupon->can_return = $this->can_return;
        $coupon->mode = Coupon::MODE_COUPON;
        $coupon->is_confirm = 1;

        if(!$coupon->update(false))return null;
        return $coupon;
    }

    /**
     * @return bool
     */
    public function saveCouponProduct()
    {
        if(!$this->validate()) return false;
        $ids = $this->couponProduct->getProductIds();
        $product_id = [];
        $product_id[] = $this->product_id;
        $this->couponProduct->setProductIds(ArrayHelper::merge($ids, $product_id));
        return $this->couponProduct->save(false);
    }
}
