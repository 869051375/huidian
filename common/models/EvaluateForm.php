<?php

namespace common\models;

use Yii;
use yii\base\Model;


class EvaluateForm extends Model
{
    public $pro_score;
    public $efficiency_score;
    public $attitude_score;
    public $complex_score;
    public $tag;
    public $user_id;
    public $order_id;
    public $product_id;
    public $evaluate_content;
    public $evaluate_id;
    public $customer_service_id;

    private $tags;

    private $customer_service;
    /**
     * @var Order
     */
    private $order;

    public function rules()
    {
        return [
            [['user_id', 'order_id', 'pro_score', 'efficiency_score','product_id','customer_service_id','attitude_score'], 'integer'],
            [['order_id', 'pro_score', 'efficiency_score','product_id','customer_service_id','attitude_score'], 'required'],
            [['efficiency_score','pro_score','attitude_score'], 'match','pattern'=>'/^[1-5]$/','message'=>'数据错误，请修改'],
            ['tag', 'string', 'max' => 72],
            ['tag', 'validateTag'],
            ['evaluate_content', 'string', 'max' => 80],
            ['order_id', 'validateOrderId'],
            ['customer_service_id', 'validateCustomerServiceId'],
        ];
    }

    public function validateCustomerServiceId()
    {
        $this->customer_service = CustomerService::findOne($this->customer_service_id);
        if($this->customer_service == null)
        {
            $this->addError('customer_service_id','客服不存在！');
        }
    }

    public function validateOrderId()
    {
        $this->order = Order::find()->where(['id' => $this->order_id, 'user_id' => \Yii::$app->user->id])->one();
        if(null == $this->order)
        {
            $this->addError('evaluate_content', '订单不存在哦~');
        }
    }

    public function getTagList()
    {
        if(empty($this->tag)) return [];
        return explode(',', $this->tag);
    }

    public function validateTag()
    {
        $this->tags = explode(',', $this->tag);
    }

    public function save()
    {
        if($this->validate())
        {
            $model = new OrderEvaluate();
            $model->pro_score = !empty($this->pro_score)?$this->pro_score:0;
            $model->efficiency_score = !empty($this->efficiency_score)?$this->efficiency_score:0;
            $model->attitude_score = !empty($this->attitude_score)?$this->attitude_score:0;
            $model->updateComplexScore();
            $model->setTagList($this->tags);
            $model->user_id = Yii::$app->user->id;
            $model->product_id = $this->product_id;
            $model->order_id = $this->order_id;
            $model->evaluate_content = $this->evaluate_content;
            $model->customer_service_id = $this->customer_service_id;
            $model->customer_service_name = $this->customer_service->name;

            $model->package_id = 0;
            if($this->order->virtualOrder->package_id > 0)
            {
                $model->package_id = $this->order->virtualOrder->package_id;
            }
            $model->save(false);
            if(isset($this->tags))
            {
                foreach($this->tags as $tag)
                {
                    CustomerServiceTag::addTag($model->customer_service_id, $tag);
                    ProductTag::addTag($model->product_id, $tag);
                    if($model->package_id > 0)
                    {
                        ProductTag::addTag($model->package_id, $tag);
                    }
                }
            }

            // 订单评价成功时系统自动发放优惠券
            $coupon = new Coupon();
            $coupons = $coupon->getEffectiveCoupons('evaluate');
            if(null != $coupons)
            {
                foreach ($coupons as $v)
                {
                    $form = new ReleaseCoupon();
                    $form->coupon_id = $v->id;
                    $form->user = Yii::$app->user->identity;
                    $form->source = CouponUser::SOURCE_REGISTER;
                    $form->pushToQueue(true, false);
                }
            }

            return $model;
        }
        return null;
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
            'product_id' => 'Product ID',
            'pro_score' => '专业程度',
            'efficiency_score' => '服务效率',
            'attitude_score' => '服务态度',
            'complex_score' => 'Complex Score',
            'tag' => 'Tag',
            'is_reply' => 'Is Reply',
            'is_audit' => 'Is Audit',
            'evaluate_content' => '评价内容',
            'reply_content' => 'Reply Content',
            'customer_service_id' => 'Customer Service ID',
            'customer_service_name' => 'Customer Service Name',
            'modify_time' => 'Modify Time',
            'reply_time' => 'Reply Time',
            'created_at' => 'Created At',
        ];
    }
}
