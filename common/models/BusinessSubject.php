<?php

namespace common\models;

use common\validators\TelPhoneValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\redis\Cache;

/**
 * This is the model class for table "business_subject".
 *
 * @property integer $id
 * @property integer $user_id
 *
 * @property integer $customer_id
 * @property integer $register_status
 * @property integer $tax_type
 * @property string  $credit_code
 * @property string  $register_unit
 * @property string  $filing_tel
 * @property string  $filing_email
 * @property string  $official_website
 * @property string  $company_remark
 *
 * @property integer $order_id
 * @property integer $subject_type
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $district_id
 * @property integer $industry_id
 * @property string  $id_no
 * @property string  $position
 * @property integer $enterprise_type
 *
 * @property integer $operating_period_begin
 * @property integer $operating_period_end
 * @property string $name
 * @property string $region
 * @property string $feature
 * @property string $organization_form
 * @property string $company_name
 * @property string $province_name
 * @property string $city_name
 * @property string $district_name
 * @property string $registered_capital
 * @property string $address
 * @property string $legal_person_name
 * @property string $industry_name
 * @property string $image
 * @property string $scope
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $creator_id
 * @property integer $company_type_id
 * @property string  $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property string $customer_number
 *
 * @property Order[] $order
 * @property CrmCustomer $customer
 */
class BusinessSubject extends \yii\db\ActiveRecord
{
    const SUBJECT_TYPE_ACTIVE = 1;//自然人
    const SUBJECT_TYPE_DISABLED = 0;//企业或者其他组织

    const TAX_NORMAL_PERSON = 1; //一般人
    const TAX_SMALL_SCALE = 2;   //小规模
//
//    public $status;
//    public $creator_name;
//    public $creator_id;
//    public $updated_at;
//    public $updater_id;
//    public $updater_name;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%business_subject}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            if($insert) // 如果是新增
            {
                $this->creator_id = $user->id;
                $this->creator_name = $user->name;
                $this->updater_id = $user->id;
                $this->updater_name = $user->name;
            } else {
                $this->updater_id = $user->id;
                $this->updater_name = $user->name;
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject_type','registered_capital','customer_id','province_id', 'city_id', 'district_id', 'industry_id','tax_type','operating_period_begin','operating_period_end'],'integer'],
            ['company_name','string','max'=>255],
            ['address','string','max'=>255],
            ['province_name','string','max'=>15],
            ['city_name','string','max'=>15],
            ['district_name','string','max'=>15],
            ['credit_code','string','max'=>64],
            ['register_unit','string','max'=>50],
            ['industry_name', 'string', 'max' => 30],
            ['filing_tel', TelPhoneValidator::className(), 'telOnly' => true, 'message' => '备案电话格式错误'],
            ['filing_tel','string','max'=>15],
            ['register_status','string','max'=>10],
            ['enterprise_type','string','max'=>40],
            ['filing_email','email'],
            ['filing_email','string','max'=>64],
            ['legal_person_name','string','max'=>15],
            [['official_website','company_remark','scope'],'string'],
            ['name','string','max'=>50],
            ['region','string','max'=>20],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'=>'',
            'province_name' => '',
            'region' => '',
            'company_name' => '公司名称',
            'feature' => '行业特点',
            'organization_form' => '组织形式',
            'city_name' => '',
            'district_name' => '服务项目',
            'district_id' => '注册地址',
            'address' => '地址',
            'legal_person_name' => '法定代表人',
            'operating_period_begin' => '成立日期',
            'operating_period_end' => '到期日期',
            'registered_capital' => '注册资本',
            'industry_id' => '行业类型',
            'industry_name' => '',
            'image' => '上传营业执照',
            'order_id' => '',
            'in_do' => '',
            'scope' => '经营范围',
            'enterprise_type' => '公司类型',
            'credit_code' => '信用代码',
            'register_status' => '登记状态',
            'register_unit' => '登记机关',
            'filing_tel' => '备案电话',
            'filing_email' => '备案邮箱',
            'tax_type' => '税务类型',
            'official_website' => '官网地址',
            'company_remark' => '备注描述',
            'updater_id' => '',
            'updater_name' => '',
            'updated_at' => '',
        ];
    }

    public function getPersonAttribute()
    {
        return [
            'region' => '姓名',
            'name'=>'身份证',
            'scope' => '户籍地址',
            'province_name' => '',
            'company_name' => '',
            'feature' => '',
            'organization_form' => '',
            'city_name' => '',
            'district_name' => '',
            'district_id' => '',
            'address' => '',
            'legal_person_name' => '',
            'operating_period_begin' => '',
            'operating_period_end' => '',
            'registered_capital' => '',
            'industry_id' => '',
            'industry_name' => '',
            'image' => '',
            'order_id' => '',
            'in_do' => '',
            'credit_code' => '',
            'register_status' => '',
            'register_unit' => '',
            'filing_tel' => '',
            'filing_email' => '',
            'tax_type' => '',
            'official_website' => '',
            'company_remark' => '',
            'updater_id' => '',
            'updater_name' => '',
            'updated_at' => '',
        ];
    }

    public function attributeHints()
    {
        return [
            'registered_capital'=>'万元',
            'industry_name'=>'请输入行业名称',
//            'operating_period_begin'=>'开始时间',
//            'operating_period_end'=>'结束时间',
        ];
    }

    public function getOrder()
    {
        return self::hasMany(Order::className(), ['business_subject_id' => 'id']);
    }

    public function getCustomer()
    {
        return self::hasOne(CrmCustomer::className(), ['id' => 'customer_id']);
    }

    /**
     * @param User $user
     * @param integer $subject_id
     * @param $isProxy
     * @return array|null|\yii\db\ActiveRecord|static
     */
    public static function getSubject($user, $subject_id = 0, $isProxy)
    {
        //后台下单未选业务主体直接返回
        if($isProxy && empty($subject_id))
        {
            return null;
        }
        if($subject_id)
        {
            $model = self::findOne($subject_id);
        }
        else
        {
            //todo 之前的数据可能会出现问题
            if($user->customer)
            {
                $model = self::find()->where(['customer_id'=>$user->customer->id])->orderBy(['created_at' => SORT_DESC])->one();
            }
        }
        return empty($model)? null : $model;
    }

    public function subScope()
    {
        if(mb_strlen($this->scope) > 10)
        {
            return mb_substr($this->scope,0,10).'......';
        }
        return $this->scope;
    }

    public static function getRegisterStatus()
    {
        return [
            '在业' => '在业',
            '注销' => '注销',
            '存续' => '存续',
            '迁入' => '迁入',
            '吊销' => '吊销',
            '迁出' => '迁出',
            '停业' => '停业',
            '清算' => '清算',
        ];
    }

    public static function getTaxType()
    {
        return [
            0 => '请选择税务类型',
            self::TAX_NORMAL_PERSON => '一般人',
            self::TAX_SMALL_SCALE => '小规模',
        ];
    }

    public function getTxtName()
    {
        $tax = self::getTaxType();
        if($this->tax_type)
        {
            return $tax[$this->tax_type];
        }
        return null;
    }

    public function getCompanyName()
    {
        if($this->subject_type)
        {
            return $this->region;
        }
        return $this->company_name;
    }

    //已付款订单(已付款订单，包含未付清和已付全款)
    public function getAlreadyPayCount()
    {
        $key = 'order-already-pay-count-id-'.$this->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = Order::getPaidQueryBySubjectId($this->id)->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    //未付订单
    public function getUnpaidCount()
    {
        $key = 'order-unpaid-count-id-'.$this->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = Order::getPendingPayQueryBySubjectId($this->id)->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    //未成交商机数量
    public function getOpportunityNotDealCounts()
    {
        $key = 'opportunity-notdeal-counts-id-'.$this->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = CrmOpportunity::find()->where(['business_subject_id' => $this->id])->andWhere(['not in','status',CrmOpportunity::STATUS_DEAL])->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count ? $count : 0;
    }

    //已成交商机数量
    public function getOpportunityDealCounts()
    {
        $key = 'opportunity-deal-counts-id-'.$this->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = CrmOpportunity::find()->where(['status' => CrmOpportunity::STATUS_DEAL, 'business_subject_id' => $this->id])->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count ? $count : 0;
    }

    /**
     * @param BusinessSubject[] $businessSubject
     * @param $status
     * @return int
     */
    public static function getSubjectCount($businessSubject,$status)
    {
        $i = 0;
        foreach($businessSubject as $item)
        {
            if($item->subject_type == $status)
            {
                $i += 1;
            }
        }
        return $i;
    }



}