<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\City;
use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use common\models\District;
use common\models\Industry;
use common\models\Province;
use common\validators\TelPhoneValidator;
use Yii;
use yii\base\Model;

class BusinessSubjectForm extends Model
{
    public $id = 0;
    public $subject_type = 0;
    public $customer_id = 0;
    public $company_name = '';
    public $register_status = '';
    public $enterprise_type = '';
    public $legal_person_name = '';
    public $tax_type = 0;
    public $user_id = 0;
    public $province_id = 0;
    public $province_name = '';
    public $city_id = 0;
    public $city_name = '';
    public $district_id = 0;
    public $district_name = '';
    public $name = '';
    public $region = '';
    public $address = '';
    public $operating_period_begin = '';
    public $operating_period_end = '';
    public $organization_form = '';
    public $filing_tel = '';
    public $credit_code = '';
    public $register_unit = '';
    public $filing_email = '';
    public $official_website = '';
    public $company_remark = '';
    public $industry_id = 0;
    public $industry_name = '';
    public $scope;
    public $registered_capital = 0;
    public $personal_address;

    /***
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //业务主体必填字段 todo 必填字段暂定这个几个，后边再添加
            [['company_name','address','credit_code','register_unit','industry_name','filing_tel','filing_tel','register_status','enterprise_type','filing_email','legal_person_name','province_name','city_name','district_name','official_website','company_remark','scope','operating_period_begin','operating_period_end','region','name'],'trim'],
            //[['company_name','customer_id','legal_person_name','operating_period_begin','province_id', 'city_id' ,'district_id'],'required','on'=>'business'],
            [['company_name','customer_id','legal_person_name'],'required','on'=>'business'],
            [['id','subject_type','registered_capital','customer_id','province_id', 'city_id', 'district_id', 'industry_id','tax_type'],'integer'],
            ['customer_id','validateCustomerId'],
            [['province_id', 'city_id' ,'district_id'],'validateArea'],
            ['industry_id', 'validateIndustryId','on'=>'business'],
            ['company_name','string','max'=>255],
            ['address','string','max'=>255],
            ['credit_code','string','max'=>64],
            ['register_unit','string','max'=>50],
            ['industry_name', 'string', 'max' => 30],
            ['filing_tel', TelPhoneValidator::className(), 'telOnly' => true, 'message' => '备案电话格式错误；正确格式，如：010-88888888'],
            ['filing_tel','string','max'=>15],
            ['register_status','string','max'=>10],
            ['enterprise_type','string','max'=>40,'on'=>'business'],
            ['filing_email','email'],
            ['filing_email','string','max'=>64],
            ['legal_person_name','string','max'=>15],
            ['province_name','string','max'=>15],
            ['city_name','string','max'=>15],
            ['district_name','string','max'=>15],
            [['official_website','company_remark','scope'],'string'],
            ['operating_period_begin', 'string', 'max' => 10],
            ['operating_period_end', 'string', 'max' => 10],
            ['operating_period_begin', 'validateTimes','on'=>'business'],
            ['operating_period_end', 'validateEndTime','on'=>'business'],

            //自然人主体信息必填字段
            [['region','scope'],'required','on'=>'personal'],
            ['name','match','pattern'=>'/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[0-2])(0[1-9]|[1-2]\d|3[0-1])\d{3}(\d|X)$/i','message'=>'请输入有效的身份证号码','on'=>'personal'],
            [['scope'],'string'],
            ['region','string','max'=>20],

            [['province_id', 'city_id' ,'district_id'],'default', 'value'=>'0'],
            [['operating_period_begin', 'operating_period_end'],'default', 'value' => 0],
        ];
    }

    public function validateCustomerId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer = CrmCustomer::findOne($this->customer_id);
        if(null == $this->customer)
        {
            $this->addError('customer_id', '客户不存在');
        }
        else if(
            !$this->customer->isPrincipal($administrator) &&
            !$this->customer->isCombine($administrator) &&
            !$this->customer->isSubFor($administrator) &&
            !Yii::$app->user->can('business-subject/create') && !Yii::$app->user->can('business-subject/update'))
        {
            $this->addError('customer_id', '您没有对该客户操作的权限');
        }
    }

    public function validateTimes()
    {
        $this->setTime();
        if(!empty($this->operating_period_begin))
        {
            if($this->operating_period_begin > $this->operating_period_end && $this->operating_period_end)
            {
                $this->addError('operating_period_begin', '成立时间不能大于到期时间！');
            }
            elseif ($this->operating_period_begin < 0)
            {
                $this->addError('operating_period_begin', '请选择正确的成立时间！');
            }
        }
        else
        {
            $this->operating_period_begin = 0;
        }
    }

    public function setTime()
    {
        if(isset($this->operating_period_begin))
        {
            $this->operating_period_begin = strtotime($this->operating_period_begin);
        }
        if(isset($this->operating_period_end))
        {
            $this->operating_period_end = strtotime($this->operating_period_end);
        }
    }

    public function validateEndTime()
    {
        if(!empty($this->operating_period_end))
        {
            if ($this->operating_period_end < 0)
            {
                $this->addError('operating_period_end', '请选择正确的到期时间！');
            }
            else if ($this->operating_period_end > 9999999999)
            {
                $this->operating_period_end = 0;
            }
        }
        else
        {
            $this->operating_period_end = 0;
        }
    }

    public function validateArea()
    {
        if($this->province_id || $this->city_id || $this->district_id)
        {
            if($this->province_id && $this->city_id && $this->district_id)
            {
                $this->validateProvinceId();
                $this->validateCityId();
                $this->validateDistrictId();
                return true;
            }
            $this->addError('district_id','地址不能为空！');
        }
        return true;
    }

    public function validateProvinceId()
    {
        $model = Province::findOne($this->province_id);
        if(empty($model))
        {
            $this->addError('district_id','省份不存在！');
        }
        $this->province_name = $model->name;
    }

    public function validateCityId()
    {
        $model = City::findOne($this->city_id);
        if(empty($model))
        {
            $this->addError('district_id','城市不存在！');
        }
        $this->city_name = $model->name;
    }

    public function validateDistrictId()
    {
        $model = District::findOne($this->district_id);
        if(empty($model))
        {
            $this->addError('district_id','区县不存在！');
        }
        $this->district_name = $model->name;
    }

    public function validateIndustryId()
    {
        if($this->industry_id!=0 && $this->industry_id!=999)
        {
            $model = Industry::findOne($this->industry_id);
            if(empty($model))
            {
                $this->addError('industry_id','行业不存在！');
            }
            $this->industry_name = $model->name;
        }
        if($this->industry_id==999)
        {
            if(empty($this->industry_name) && $this->industry_id!=0)
            {
                $this->addError('industry_name','行业名称不能为空！');
            }
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'=>'',
            'province_name' => '',
            'company_name' => '',
            'region' => '',
            'feature' => '行业特点',
            'organization_form' => '组织形式',
            'city_name' => '',
            'district_name' => '服务项目',
            'district_id' => '地址',
            'address' => '',
            'legal_person_name' => '',
            'operating_period_begin' => '成立日期',
            'operating_period_end' => '结束日期',
            'registered_capital' => '注册资本',
            'industry_name' => '',
            'image' => '上传营业执照',
            'enterprise_type' => '公司类型',
            'order_id' => '',
            'in_do' => '',
            'scope' => '',
            'credit_code' => '',
            'register_status' => '',
            'register_unit' => '',
            'filing_tel' => '',
            'filing_email' => '',
            'tax_type' => '',
            'official_website' => '',
            'company_remark' => '',
            'personal_address'=>''
        ];
    }

    public function attributeHints()
    {
        return [
            'registered_capital'=>'万元',
            'industry_name'=>'请输入行业名称',
//            'operating_period_begin'=>'成立时间',
//            'operating_period_end'=>'到期时间',
        ];
    }


    public function timeFormat()
    {
        if(isset($this->operating_period_begin))
        {
            $this->operating_period_begin = Yii::$app->formatter->asDate($this->operating_period_begin);
        }
        if(isset($this->operating_period_end))
        {
            $this->operating_period_end = Yii::$app->formatter->asDate($this->operating_period_end);
        }
    }

    public function scene()
    {
        if($this->subject_type)
        {
            $this->setScenario('personal');
            $this->operating_period_begin = 0;
            $this->operating_period_end = 0;
            $this->register_status = 0;
        }
        else
        {
            $this->setScenario('business');
        }
    }

    /**
     * @return bool|BusinessSubject
     */
    public function save()
    {
        $model = new BusinessSubject();
        $this->scene();
        if(!$this->validate()) return false;
        if(!Yii::$app->user->can('business-subject/create'))
        {
            $this->addError('customer_id', '您没有对该客户操作的权限');
            return false;
        }
        $model->load($this->attributes,'');
        if($model->save(false))
        {
            //统一记录为客户的操作记录6.11修改需求
//            CrmCustomerLog::add('创建业务主体：'.$model->getCompanyName(), $model->customer_id,
//                false,false,$model->subject_type?CrmCustomerLog::TYPE_CUSTOMER_PERSON:CrmCustomerLog::TYPE_CUSTOMER_SUBJECT,$model->id);
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '';
            if ($administrator)
            {
                if($administrator->department)
                {
                    $message = '由'.$administrator->department->name.'的'.$administrator->name.'新增业务主体"'.$model->getCompanyName().'"';
                }
                else
                {
                    $message = '由'.$administrator->name.'新增业务主体"'.$model->getCompanyName().'"';
                }
            }
            CrmCustomerLog::add($message, $model->customer_id,false,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD,$model->id);
            return $model;
        }
        return false;
    }

    /**
     * @param BusinessSubject $BusinessSubject
     * @return bool
     */
    public function update($BusinessSubject)
    {
        $this->scene();
        if(!$this->validate()) return false;
        if(!Yii::$app->user->can('business-subject/update'))
        {
            $this->addError('customer_id', '您没有对该客户操作的权限');
            return false;
        }
        $BusinessSubject->load($this->attributes, '');
        $BusinessSubject->registered_capital = isset($BusinessSubject->registered_capital) ? $BusinessSubject->registered_capital : 0;
        if($this->getChangeData($BusinessSubject))
        {
            //统一记录为客户的操作记录6.11修改需求
//            CrmCustomerLog::add('编辑：'.$this->getChangeData($BusinessSubject), $BusinessSubject->customer_id,
//                false,false,$BusinessSubject->subject_type?CrmCustomerLog::TYPE_CUSTOMER_PERSON:CrmCustomerLog::TYPE_CUSTOMER_SUBJECT,$BusinessSubject->id);

            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '';
            if ($administrator)
            {
                if($administrator->department)
                {
                    $message = '由'.$administrator->department->name.'的'.$administrator->name.'编辑了业务主体"'.$BusinessSubject->getCompanyName().'"';
                }
                else
                {
                    $message = '由'.$administrator->name.'编辑了业务主体"'.$BusinessSubject->getCompanyName().'"';
                }
            }
            CrmCustomerLog::add($message, $BusinessSubject->customer_id,false,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD,$BusinessSubject->id);
        }
        if($BusinessSubject->save(false))
        {
            return true;
        }
        return false;
    }

    /**
     * @param BusinessSubject $businessSubject
     * @return string
     */
    private function getChangeData($businessSubject)
    {
        $oldData = $businessSubject->oldAttributes;
        $data = $businessSubject->attributes;
        $labels = $businessSubject->subject_type ? $businessSubject->getPersonAttribute() : $businessSubject->attributeLabels();
        $changeData = null;
        foreach($oldData as $i => $old)
        {
            if($old != $data[$i])
            {
                if(in_array($i,array_keys($labels)) && $labels[$i])
                {
                    $changeData .= '"'.$labels[$i].'"';
                }
            }
        }
        return $changeData;
    }


}