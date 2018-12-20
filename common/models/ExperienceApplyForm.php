<?php
namespace common\models;

use common\jobs\SendSmsJob;
use shmilyzxt\queue\base\Queue;
use yii\base\Model;

/**
 * ExperienceApplyForm form
 */
class ExperienceApplyForm extends Model
{
    public $product_id;
    public $industry_id = 0;
    public $user_id;
    public $address_id = 0;
    public $district_id = 0;

    /**
     * @var Product
     */
    private $product;
    /**
     * @var Product
     */
    private $address;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'district_id', 'industry_id', 'address_id'], 'integer'],
            ['product_id', 'required'],
            ['product_id', 'validateProductId'],
            ['industry_id', 'validateIndustryId'],
            ['address_id', 'validateAddressId'],
        ];
    }

    public function validateProductId()
    {
        $this->product = Product::find()->where(['id'=>$this->product_id, 'status'=>Product::STATUS_ONLINE])->one();
        if($this->product == null)
        {
            $this->addError('product_id', '商品不存在！');
        }
    }

    public function validateAddressId()
    {
        $addressIds = $this->product->getAddressIds();
        if(!empty($addressIds) && in_array($this->address_id, $addressIds))
        {
            $this->address = Product::find()->where(['id' => $this->address_id, 'status' => Product::STATUS_ONLINE])->one();
            if($this->address == null)
            {
                $this->addError('address_id', '地址不存在！');
            }
        }
    }

    public function validateIndustryId()
    {
        $industry = Industry::find()->where(['id'=>$this->industry_id])->one();
        if($industry == null)
        {
            $this->addError('industry_id', '行业不存在！');
        }
    }

    public function save()
    {
        if(!$this->validate()) return null;

        $query = ExperienceApply::find()->where([
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'status'=>ExperienceApply::UNTREATED
        ]);
        $apply = $query->one();
        if(null != $apply) return null;

        $model = new ExperienceApply();
        $model->loadDefaultValues();
        $model->product_id = $this->product_id;
        $model->product_name = $this->product->name;
        $model->industry_id = empty($this->industry_id) ? 0 : $this->industry_id;
        $model->user_id = $this->user_id;
        $model->address_id = empty($this->address_id) ? 0 : $this->address_id;
        $model->district_id = empty($this->district_id) ? 0 : $this->district_id;
        $model->address_name = $this->address ? $this->address->name : '';
        if($model->save())
        {
            $model->autoAssignCustomerService();
            //体验商品申请时发送给客服
            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            $assign_customer_service_sms_id = Property::get('assign_customer_service_sms_id');
            if($queue && $assign_customer_service_sms_id)
            {
                //todo 待确定模板类型，此功能暂时不使用
                //客户昵称：{1}；客户手机号：{2}；所购商品：{3}
                $queue->pushOn(new SendSmsJob(),['phone' => $model->customerService->phone,
                    'sms_id' =>$assign_customer_service_sms_id, 'data' => [
                        $model->user->name, $model->user->phone, $model->product->name,
                    ] ], 'sms');
            }
            return $model;
        }
        return null;
    }
}
