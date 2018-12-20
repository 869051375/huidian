<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "experience_apply".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $product_name
 * @property integer $product_id
 * @property integer $district_id
 * @property integer $industry_id
 * @property string $address_name
 * @property integer $address_id
 * @property integer $status
 * @property integer $customer_service_id
 * @property integer $created_at
 * @property District $district
 * @property User $user
 * @property Product $product
 * @property CustomerService $customerService
 */
class ExperienceApply extends \yii\db\ActiveRecord
{
    const ALREADY_DEAL = 1;
    const UNTREATED = 0;
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%experience_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'product_id', 'district_id', 'industry_id', 'customer_service_id', 'address_id', 'status', 'created_at'], 'integer'],
            ['product_id', 'required'],
            [['product_name', 'address_name'], 'string', 'max' => 20],
            ['product_id', 'validateProductId'],
            ['industry_id', 'validateIndustryId'],
        ];
    }

    public function validateProductId()
    {
        $product = Product::find()->where(['id'=>$this->product_id,'status'=>1])->one();
        if($product == null)
        {
            $this->addError('product_id', '商品不存在！');
        }
    }

    public function validateIndustryId()
    {
        if(empty($this->industry_id)) return ;
        $industry = Industry::find()->where(['id'=>$this->industry_id])->one();
        if($industry == null)
        {
            $this->addError('industry_id', '行业不存在！');
        }
    }

    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDistrict()
    {
        return self::hasMany(District::className(), ['id' => 'district_id']);
    }

    public function getProduct()
    {
        return self::hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getCustomerService()
    {
        return static::hasOne(CustomerService::className(), ['id' => 'customer_service_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'product_name' => 'Product Name',
            'product_id' => 'Product ID',
            'district_id' => 'District ID',
            'industry_id' => 'Industry ID',
            'address_name' => 'Address Name',
            'address_id' => 'Address ID',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    /*
    * 自动分配客服
    */
    public function autoAssignCustomerService($customer_service_id = null)
    {
        $cs = null;
        if($this->user->customer_service_id)
        {
            /** @var CustomerService $cs */
            $cs = CustomerService::findOne($this->user->customer_service_id);
        }
        else
        {
            /** @var CustomerService $cs */
            if($customer_service_id)
            {
                $cs = CustomerService::find()->where(['id' => $customer_service_id, 'status' => CustomerService::STATUS_ACTIVE])->one();
            }
            if(null == $cs)
            {
                $cs = CustomerService::find()->orderBy(['assign_count' => SORT_ASC])->where(['status' => CustomerService::STATUS_ACTIVE])->one();
            }
        }
        if(null != $cs)
        {
            $this->assignCustomerService($cs);
        }
    }

    /**
     * 分配客服
     * @param CustomerService $customerService
     */
    public function assignCustomerService($customerService)
    {
        if($this->user->customer_service_id != $customerService->id)
        {
            if($this->customerService && $this->customerService->assign_count > 0)
            {
                $this->customerService->assign_count-=1;
                $this->customerService->save();
            }
            $this->user->customer_service_id = $customerService->id;
            $customerService->assign_count+=1;
            $customerService->service_number+=1;
            $customerService->save(false);
            $this->user->save(false);
        }
        $this->customer_service_id = $customerService->id;
        $this->save(false);
    }
}
