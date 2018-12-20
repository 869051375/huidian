<?php

namespace common\models;

use backend\modules\niche\models\CustomerExchangeList;
use Think\Exception;
use Yii;
use yii\db\Query;
use common\models\CrmCustomerApi;

/**
 * This is the model class for table$business_subject->business_subject".
 *
 * @property string $id
 * @property string $user_id
 * @property string $customer_id
 * @property string $credit_code
 * @property string $register_status
 * @property string $register_unit
 * @property string $filing_tel
 * @property string $filing_email
 * @property string $tax_type
 * @property string $official_website
 * @property string $company_remark
 * @property string $order_id
 * @property string $subject_type
 * @property string $position
 * @property string $name
 * @property string $region
 * @property string $feature
 * @property string $organization_form
 * @property string $company_name
 * @property string $province_id
 * @property string $province_name
 * @property string $city_id
 * @property string $city_name
 * @property string $district_id
 * @property string $district_name
 * @property string $address
 * @property string $registered_capital
 * @property string $legal_person_name
 * @property string $operating_period_begin
 * @property string $operating_period_end
 * @property string $industry_id
 * @property string $industry_name
 * @property string $image
 * @property string $scope
 * @property string $enterprise_type
 * @property string $created_at
 * @property string $updated_at
 * @property string $creator_id
 * @property string $creator_name
 * @property string $updater_id
 * @property string $updater_name
 * @property string $customer_number
 * @property integer $company_type_id
 */
class BusinessSubjectApi extends \yii\db\ActiveRecord
{
    public $business_id;
    public $subject_type;
    public $company_name;
    public $business_name;
    public $industry_id;
    public $industry_name;
    public $tax_type;
    public $credit_code;
    public $register_status;
    public $company_type_id;
    public $enterprise_type;
    public $legal_person_name;
    public $registered_capital;
    public $operating_period_begin;
    public $operating_period_end;
    public $register_unit;
    public $province_id;
    public $business_province_id;
    public $province_name;
    public $business_province_name;
    public $city_id;
    public $business_city_id;
    public $city_name;
    public $business_city_name;
    public $district_id;
    public $business_district_id;
    public $district_name;
    public $business_district_name;
    public $address;
    public $scope;
    public $official_website;
    public $filing_tel;
    public $filing_email;
    public $company_remark;
    public $customer_number;

    public $customer_id;
    public $customer_name;
    public $gender;
    public $phone;
    public $wechat;
    public $qq;
    public $tel;
    public $caller;
    public $email;
    public $birthday;
    public $source;
    public $source_name;
    public $channel_id;
    public $position;
    public $department;
    public $customer_province_id;
    public $customer_province_name;
    public $customer_city_id;
    public $customer_city_name;
    public $customer_district_id;
    public $customer_district_name;
    public $street;
    public $customer_hobby;
    public $remark;
    public $level;
    public $department_id;
    public $administrator_id;
    public $is_receive;
    public $company_id;
    public $last_record;
    public $last_record_creator_id;
    public $operation_time;
    public $customer_public_id;

    public $id;
    public $native_place;

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
    public function rules()
    {
        return [
            [['id', 'user_id', 'customer_id', 'tax_type', 'order_id', 'subject_type', 'province_id', 'city_id', 'district_id', 'industry_id', 'created_at', 'updated_at', 'creator_id', 'updater_id','gender', 'department_id', 'administrator_id', 'is_receive', 'company_id', 'last_record', 'last_record_creator_id', 'operation_time', 'company_type_id', 'level', 'channel_id', 'customer_province_id', 'customer_city_id', 'customer_district_id', 'business_province_id', 'business_city_id', 'business_district_id', 'business_id', 'source', 'customer_public_id'], 'integer'],
            [['street', 'remark', 'customer_name', 'company_name', 'industry_name', 'credit_code', 'register_status', 'enterprise_type', 'register_unit', 'address', 'scope', 'official_website', 'filing_tel', 'filing_email', 'company_remark', 'source_name', 'department', 'customer_province_name', 'customer_city_name', 'customer_district_name', 'customer_hobby', 'business_name', 'business_province_name', 'business_city_name', 'business_district_name', 'legal_person_name', 'phone', 'wechat', 'qq', 'tel', 'caller', 'email', 'birthday', 'customer_number', 'operating_period_begin', 'operating_period_end','position'], 'string'],

            [['region'], 'string', 'max' => 20],

            [['registered_capital'], 'number'],
            [['name','customer_name','qq', 'operating_period_end', 'operating_period_begin','birthday','caller','register_status'], 'string', 'max' => 20],
            [['customer_hobby','industry_name','official_website','filing_email','company_name','email'], 'string', 'max' => 100],
            [['credit_code','business_name'], 'string', 'max' => 18],
            [['filing_tel'], 'string', 'max' => 30],
            [['legal_person_name','register_unit','enterprise_type','native_place','wechat','source_name','position','department'], 'string', 'max' => 50],
            [['address','company_remark','street','remark'], 'string', 'max' => 200],
            [['scope'], 'string', 'max' => 1000],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
            [['tel', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 11],

            [['credit_code'],'match','pattern' => '/^[a-zA-Z0-9]{18}$/', 'message' => '信用代码只能由数字与字母组成'],
            [['filing_email'],'match', 'pattern' => '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', 'message' => '邮箱号格式错误'],
            [['tel'],'match','pattern'=>'/^([0-9]{3,4}-)?[0-9]{7,8}$/','message' => '座机格式错误'],
            [['province_name', 'district_name', 'city_name'], 'match', 'pattern' => '/^[ \x{4e00}-\x{9fa5}]+$/u', 'message' => '省市区参数错误，必须为汉字', 'on' => ['business_customer', 'update']],
            [['name'], 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_+\/-]+$/u', 'message' => '姓名格式有誤', 'on' => 'update'],
            [['phone'], 'match', 'pattern' => '/^1\d{10}$/', 'message' => '手机号格式错误', 'on' => ['business_customer', 'update']],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{5,25})+$/', 'message' => '微信号格式错误', 'on' => ['business_customer', 'update']],

            [['qq'], 'match', 'pattern' => '/^[1-9]*[1-9][0-9]*$/', 'message' => 'QQ号码格式错误', 'on' => ['create', 'update']],
            [['tel'], 'match', 'pattern' => '/^([0-9]{3,4}-)?[0-9]{7,8}$/', 'message' => '电话号格式错误', 'on' => ['business_customer', 'update']],
            [['email'], 'match', 'pattern' => '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', 'message' => '邮箱号格式错误', 'on' => ['business_customer', 'update']],
            [['birthday'], 'match', 'pattern' => '/^[1-2][\d]{3}\-(0\d|1[0-2])\-([0-2]\d|3[0-1])$/', 'message' => '生日参数格式错误', 'on' => ['business_customer', 'update']],
            [['company_name','customer_name','gender','phone'], 'required', 'on' => 'business_customer'],
            [['id','customer_public_id'],'required','on'=>'detail'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'customer_id' => 'Customer ID',
            'credit_code' => 'Credit Code',
            'register_status' => 'Register Status',
            'register_unit' => 'Register Unit',
            'filing_tel' => 'Filing Tel',
            'filing_email' => 'Filing Email',
            'tax_type' => 'Tax Type',
            'official_website' => 'Official Website',
            'company_remark' => 'Company Remark',
            'order_id' => 'Order ID',
            'subject_type' => 'Subject Type',
            'position' => 'Position',
            'name' => 'Name',
            'region' => 'Region',
            'feature' => 'Feature',
            'organization_form' => 'Organization Form',
            'company_name' => 'Company Name',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'address' => 'Address',
            'registered_capital' => 'Registered Capital',
            'legal_person_name' => 'Legal Person Name',
            'operating_period_begin' => 'Operating Period Begin',
            'operating_period_end' => 'Operating Period End',
            'industry_id' => 'Industry ID',
            'industry_name' => 'Industry Name',
            'image' => 'Image',
            'scope' => 'Scope',
            'enterprise_type' => 'Enterprise Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
        ];
    }

    //查询企业基本信息
    public function getBusinessSubjectBasicDetail($data)
    {

        $query = new Query();
        $query->select('b.id,b.company_name,b.credit_code,b.register_status,b.enterprise_type,b.legal_person_name,b.registered_capital,b.operating_period_begin,b.operating_period_end,b.register_unit,b.industry_id,b.industry_name,b.province_id,b.province_name,b.district_id,b.district_name,b.city_id,b.city_name,b.address,b.scope,ct.name as company_type_name,b.tax_type,official_website,b.filing_tel,b.filing_email,b.company_remark,c.is_protect,b.address')
            ->from(['b' => BusinessSubject::tableName()])
            ->leftJoin(['c' => CrmCustomer::tableName()], 'b.customer_id = c.id')
            ->leftJoin(['ct' => CompanyType::tableName()], 'b.company_type_id = ct.id')
            ->where(['c.id' => $data['id']]);

        $rs = $query->one();
        if ($data['customer_public_id'] == 2) {
            $result = $this->businessReplace($rs);
        } else {
            $result = $rs;
        }

        return $result;
    }

    public function businessReplace($rs)
    {
        $data['id'] = $rs['id'];
        $data['company_name'] = substr_replace($rs['company_name'], $this->str_replace_val(mb_stripos($rs['company_name'], '有')), 0, stripos($rs['company_name'], '有'));
        $data['credit_code'] = substr_replace($rs['credit_code'], $this->str_replace_val(strlen($rs['credit_code']) - 2), 2, strlen($rs['credit_code']) - 2);
        $data['register_status'] = $rs['register_status'];
        $data['enterprise_type'] = $rs['enterprise_type'];
        $data['legal_person_name'] = $this->str_replace_val(mb_strlen($rs['legal_person_name']));
        $data['registered_capital'] = $rs['registered_capital'];
        $data['operating_period_begin'] = $rs['operating_period_begin'];
        $data['operating_period_end'] = $rs['operating_period_end'];
        $data['register_unit'] = $rs['register_unit'];
        $data['industry_name'] = $rs['industry_name'];
        $data['province_id'] = $rs['province_id'];
        $data['province_name'] = $rs['province_name'];
        $data['district_id'] = $rs['district_id'];
        $data['district_name'] = mb_substr($rs['district_name'], 0, 1) . $this->str_replace_val(mb_strlen($rs['district_name']));
        $data['city_id'] = $rs['city_id'];
        $data['city_name'] = $rs['city_name'];
        $data['address'] = $this->str_replace_val(mb_strlen($rs['address']));
        $data['scope'] = $rs['scope'];
        $data['enterprise_type'] = $rs['enterprise_type'];
        $data['company_type_name'] = $rs['company_type_name'];
        $data['tax_type'] = $rs['tax_type'];
        $data['official_website'] = $this->str_replace_val(strlen($rs['official_website']));
        $data['filing_tel'] = $this->str_replace_val(strlen($rs['filing_tel']));
        $data['filing_email'] = $this->str_replace_val(strlen($rs['filing_email']));
        $data['company_remark'] = $rs['company_remark'];

        return $data;
    }


    //计算星号个数
    public function str_replace_val($num)
    {
        $str = '';
        for ($i = 0; $i < $num; $i++) {
            $str .= '*';
        }
        return $str;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    //企业查询基本信息修改
    public function businessSubjectBasicUpdate()
    {

        $id = $this->id;
        /** @var BusinessSubject $business_subject */
        $business_subject = BusinessSubject::find()->where(['id' => $id])->one();

        if(empty($business_subject)){
            return false;
        }

        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find() -> where(['id' => $business_subject->customer_id])->one();

        /** @var CrmContacts $contact */
        $contact = CrmContacts::find()->where(['customer_id' => $business_subject->customer_id])->one();

        if(empty($contact)){
            return false;
        }

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        $user_id = '';
        if($customer->user_id <= 0){
            if($contact->phone != null){
                /** @var User $user */
                $user = User::find()->where(['phone' => $contact -> phone])->one();
                if(empty($user)){

                    $user = new User();
                    $user->customer_id = 0;
                    $user->name = $contact->name ? $contact->name : '';
                    $user->phone = $contact->phone ? $contact->phone : '';
                    $user->username = $contact->phone ? $contact->phone : '';
                    $user->source_id = 160;
                    $user->register_mode = 1;
                    $user->source_name = '系统同步';
                    $user->created_at = time();
                    $user->creator_id = $identity->id;
                    $user->creator_name = $identity->name;

                    if ($user->save(false)) {
                        $user_id = $connection->getlastInsertID();
                    } else {
                        return false;
                    }
                }else{
                    $user_id = $user->id;
                }
                $contact->user_id = $user_id;
                $contact->save(false);
                $customer->user_id = $user_id;
                $customer->save(false);
            }else{
                $user_id = 0;
            }

        }

        if (empty($business_subject)) {

            //生成客户编号
            $crm_customer = new CrmCustomerApi();
            $code = $crm_customer->customerNumber();

            $business_subject = new BusinessSubject();
            /** @var CrmCustomer $customer_list */
            $customer_list = CrmCustomer::find()->where(['id' => $this->customer_id])->one();

            $business_subject->customer_id = $customer_list->id;
            $business_subject->customer_number = $code;
            $business_subject->subject_type = 0;
            $business_subject->created_at = time();
            $business_subject->creator_id = $identity->id;
            $business_subject->creator_name = $identity->name;

        }else{
            $business_subject->updated_at = time();
            $business_subject->updater_id = $identity -> id;
            $business_subject->updater_name = $identity -> name;
        }

        $industry_name = Industry::find() -> where(['id' => $this->industry_id])->one()['name'];

        $operating_period_begin = strtotime($this->operating_period_begin);
        $operating_period_end = strtotime($this->operating_period_end);

        if((isset($business_subject->user_id) && $business_subject->user_id == 0) || !isset($business_subject->user_id)){
            $b_user_id = $user_id;
        }else{
            $b_user_id = $business_subject->user_id;
        }

        $business_subject->user_id = $b_user_id;
        $business_subject->company_name = $this->company_name ? $this->company_name : ($business_subject->company_name ? $business_subject->company_name : '');
        $business_subject->credit_code = $this->credit_code ? $this->credit_code : ($business_subject->credit_code ? $business_subject->credit_code : 0);
        $business_subject->register_status = $this->register_status ? $this->register_status : ($business_subject->register_status ? $business_subject->register_status : 0);
        $business_subject->enterprise_type = $this->enterprise_type ? $this->enterprise_type : ($business_subject->enterprise_type ? $business_subject->enterprise_type : 0);
        $business_subject->company_type_id = $this->company_type_id ? $this->company_type_id : ($business_subject->company_type_id ? $business_subject->company_type_id : 0);
        $business_subject->legal_person_name = $this->legal_person_name ? $this->legal_person_name : ($business_subject->legal_person_name ? $business_subject->legal_person_name : '');
        $business_subject->registered_capital = $this->registered_capital ? $this->registered_capital : ($business_subject->registered_capital ? $business_subject->registered_capital : 0.0000);
        $business_subject->operating_period_begin = $operating_period_begin ? $operating_period_begin : ($business_subject->operating_period_begin ? $business_subject->operating_period_begin : 0);
        $business_subject->operating_period_end = $operating_period_end ? $operating_period_end : ($business_subject->operating_period_end ? $business_subject->operating_period_end : 0);
        $business_subject->register_unit = $this->register_unit ? $this->register_unit : ($business_subject->register_unit ? $business_subject->register_unit : 0);
        $business_subject->province_id = $this->province_id ? $this->province_id : ($business_subject->province_id ? $business_subject->province_id : 0);
        $business_subject->province_name = $this->province_name ? $this->province_name : ($business_subject->province_name ? $business_subject->province_name : '');
        $business_subject->district_id = $this->district_id ? $this->district_id : ($business_subject->district_id ? $business_subject->district_id : 0);
        $business_subject->district_name = $this->district_name ? $this->district_name : ($business_subject->district_name ? $business_subject->district_name : '');
        $business_subject->city_id = $this->city_id ? $this->city_id : ($business_subject->city_id ? $business_subject->city_id : 0);
        $business_subject->city_name = $this->city_name ? $this->city_name : ($business_subject->city_name ? $business_subject->city_name : '');
        $business_subject->address = $this->address ? $this->address : ($business_subject->address ? $business_subject->address : '');
        $business_subject->scope = $this->scope ? $this->scope : $business_subject->scope;
        $business_subject->industry_id = $this->industry_id ? $this->industry_id : ($business_subject->industry_id ? $business_subject->industry_id : 0);
        $business_subject->industry_name = $industry_name ? $industry_name : ($business_subject->industry_name ? $business_subject->industry_name : '');
        $business_subject->tax_type = $this->tax_type ? $this->tax_type : ($business_subject->tax_type ? $business_subject->tax_type : 0);
        $business_subject->official_website = $this->official_website ? $this->official_website : $business_subject->official_website;
        $business_subject->filing_tel = $this->filing_tel ? $this->filing_tel : ($business_subject->filing_tel ? $business_subject->filing_tel : '');
        $business_subject->filing_email = $this->filing_email ? $this->filing_email : ($business_subject->filing_email ? $business_subject->filing_email : '');
        $business_subject->company_remark = $this->company_remark ? $this->company_remark : $business_subject->company_remark;

        try {

            $rs = $business_subject->save(false);

            $id = $id ? $id : $connection->getlastInsertID();

            CrmCustomerLog::add('编辑客户信息中的企业基本信息模块', $business_subject->customer_id, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $id);

            $transaction->commit();
            $change_model = new CustomerExchangeList();
            $change_model -> updateCustomer($this->customer_id);

            return $rs;

        } catch (\Exception $e) {

            $transaction->rollBack();

            return false;
        }

    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    //个人客户转企业
    public function customerBusiness()
    {
        $crmCustomerApi = new CrmCustomerApi();

        $code = $crmCustomerApi->customerNumber();

        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['id' => $this->customer_id])->one();
        if (empty($customer)) {
            return false;
        }

        /** @var CrmContacts $contact */
        $contact = CrmContacts::find()->where(['customer_id' => $this->customer_id])->one();
        if (empty($contact)) {
            return false;
        }

        $date = time();

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        $admin_id = $identity->id;

        $admin_name = $identity->name;

        $operating_period_begin = strtotime($this -> operating_period_begin);
        $operating_period_end = strtotime($this -> operating_period_end);

        /** @var BusinessSubject $business */
        $business = BusinessSubject::find()->where(['id' => $this->business_id])->one();

        $user_id = '';
        if($customer->user_id <= 0){
            if($contact->phone != null || $this->phone != null){
                /** @var User $user */
                $user = User::find()->where(['phone' => $this->phone])->one();
                if(empty($user)){

                    $user = new User();
                    $user->customer_id = 0;
                    $user->name = $contact->name ? $contact->name : '';
                    $user->phone = $this->phone ? $this->phone : '';
                    $user->username = $this->phone ? $this->phone : '';
                    $user->source_id = 160;
                    $user->register_mode = 1;
                    $user->source_name = '系统同步';
                    $user->created_at = time();
                    $user->creator_id = $identity->id;
                    $user->creator_name = $identity->name;

                    if ($user->save(false)) {
                        $user_id = $connection->getlastInsertID();
                    } else {
                        return false;
                    }
                }else{
                    $user_id = $user->id;
                }
            }else{
                $user_id = 0;
            }
        }else{
            $user_id = $customer->user_id;
        }
        if (empty($business)) {
            $business = new BusinessSubject();
            $business -> created_at = time();
            $business -> customer_id = $this->customer_id;
        }else{
            $business->updated_at = $date;
            $business->updater_name = $admin_name;
            $business->updater_id = $admin_id;
        }
        
        $business->subject_type = 0;
        $business->user_id = $user_id;
        $business->customer_number = $business->customer_number ? $business->customer_number : $code;
        $business->company_name = $this->company_name ? $this->company_name : ($business->company_name ? $business->company_name : '');
        $business->name = isset($this->business_name) ? $this->business_name : ($business->name ? $business->name : '');
        $business->industry_id = $this->industry_id ? $this->industry_id : ($business->industry_id ? $business->industry_id : 0);
        $business->industry_name = $this->industry_name ? $this->industry_name : ($business->industry_name ? $business->industry_name : '');
        $business->tax_type = $this->tax_type ? $this->tax_type : ($business->tax_type ? $business->tax_type : 0);
        $business->credit_code = $this->credit_code ? $this->credit_code : ($business->credit_code ? $business->credit_code : '');
        $business->register_status = $this->register_status ? $this->register_status : ($business->register_status ? $business->register_status : '');
        $business->company_type_id = $this->company_type_id ? $this->company_type_id : ($business->company_type_id ? $business->company_type_id : 0);
        $business->enterprise_type = $this->enterprise_type ? $this->enterprise_type : ($business->enterprise_type ? $business->enterprise_type : '');
        $business->legal_person_name = $this->legal_person_name ? $this->legal_person_name : ($business->legal_person_name ? $business->legal_person_name : '');
        $business->registered_capital = $this->registered_capital ? $this->registered_capital : ($business->registered_capital ? $business->registered_capital : 0.0000);
        $business->operating_period_begin = $operating_period_begin ? $operating_period_begin : ($business->operating_period_begin ? $business->operating_period_begin : 0);
        $business->operating_period_end = $operating_period_end ? $operating_period_end : ($business->operating_period_end ? $business->operating_period_end : 0);
        $business->register_unit = $this->register_unit ? $this->register_unit : ($business->register_unit ? $business->register_unit : '');
        $business->province_id = $this->business_province_id ? $this->business_province_id : ($business->province_id ? $business->province_id : 0);
        $business->province_name = $this->business_province_name ? $this->business_province_name : ($business->province_name ? $business->province_name : '');
        $business->city_id = $this->business_city_id ? $this->business_city_id : ($business->city_id ? $business->city_id : 0);
        $business->city_name = $this->business_city_name ? $this->business_city_name : ($business->city_name ? $business->city_name : '');
        $business->district_id = $this->business_district_id ? $this->business_district_id : ($business->district_id ? $business->district_id : 0);
        $business->district_name = $this->business_district_name ? $this->business_district_name : ($business->district_name ? $business->district_name : '');
        $business->address = $this->address ? $this->address : ($business->address ? $business->address : '');
        $business->scope = $this->scope ? $this->scope : ($business->scope ? $business->scope : '');
        $business->official_website = $this->official_website ? $this->official_website : ($business->official_website ? $business->official_website : '');
        $business->filing_tel = $this->filing_tel ? $this->filing_tel : ($business->filing_tel ? $business->filing_tel :'');
        $business->filing_email = $this->filing_email ? $this->filing_email : ($business->filing_email ? $business->filing_email : '');
        $business->company_remark = $this->company_remark ? $this->company_remark : ($business->company_remark ? $business->company_remark : '');


        $contact->user_id = $user_id;
        $contact->name = $this->customer_name ? $this->customer_name : $contact->name;
        $contact->gender = $this->gender ? $this->gender : $contact->gender;
        $contact->phone = $this->phone ? $this->phone : $contact->phone;
        $contact->wechat = $this->wechat ? $this->wechat : $contact->wechat;
        $contact->qq = $this->qq ? $this->qq : $contact->qq;
        $contact->tel = $this->tel ? $this->tel : $contact->tel;
        $contact->caller = $this->caller ? $this->caller : $contact->caller;
        $contact->email = $this->email ? $this->email : $contact->email;
        $contact->birthday = $this->birthday ? $this->birthday : $contact->birthday;
        $contact->source = $this->source ? $this->source : $contact->source;
        $contact->channel_id = $this->channel_id ? $this->channel_id : $contact->channel_id;
        $contact->position = $this->position ? $this->position : $contact->position;
        $contact->department = $this->department ? $this->department : $contact->department;
        $contact->province_id = $this->customer_province_id ? $this->customer_province_id : $contact->province_id;
        $contact->province_name = $this->customer_province_name ? $this->customer_province_name : $contact->province_name;
        $contact->city_id = $this->customer_city_id ? $this->customer_city_id : $contact->city_id;
        $contact->city_name = $this->customer_city_name ? $this->customer_city_name : $contact->city_name;
        $contact->district_id = $this->customer_district_id ? $this->customer_district_id : $contact->district_id;
        $contact->district_name = $this->customer_district_name ? $this->customer_district_name : $contact->district_name;
        $contact->street = $this->street ? $this->street : $contact->street;
        $contact->customer_hobby = $this->customer_hobby ? $this->customer_hobby : $contact->customer_hobby;
        $contact->remark = $this->remark ? $this->remark : $contact->remark;
        $contact->native_place = $this->native_place ? $this->native_place : $contact->native_place;


        $customer->user_id = $user_id;
        $customer->name = $this->customer_name ? $this->customer_name : $customer->name;
        $customer->gender = $this->gender ? $this->gender : $customer->gender;
        $customer->phone = $this->phone ? $this->phone : $customer->phone;
        $customer->wechat = $this->wechat ? $this->wechat : $customer->wechat;
        $customer->qq = $this->qq ? $this->qq : $customer->qq;
        $customer->tel = $this->tel ? $this->tel : $customer->tel;
        $customer->caller = $this->caller ? $this->caller : $customer->caller;
        $customer->email = $this->email ? $this->email : $customer->email;
        $customer->birthday = $this->birthday ? $this->birthday : $customer->birthday;
        $customer->source = $this->source ? $this->source : $customer->source;
        $customer->channel_id = $this->channel_id ? $this->channel_id : $customer->channel_id;
        $customer->province_id = $this->customer_province_id ? $this->customer_province_id : $customer->province_id;
        $customer->province_name = $this->customer_province_name ? $this->customer_province_name : $customer->province_name;
        $customer->city_id = $this->customer_city_id ? $this->customer_city_id : $customer->city_id;
        $customer->city_name = $this->customer_city_name ? $this->customer_city_name : $customer->city_name;
        $customer->district_id = $this->customer_district_id ? $this->customer_district_id : $customer->district_id;
        $customer->district_name = $this->customer_district_name ? $this->customer_district_name : $customer->district_name;
        $customer->street = $this->street ? $this->street : $customer->street;
        $customer->customer_hobby = $this->customer_hobby ? $this->customer_hobby : $customer->customer_hobby;
        $customer->remark = $this->remark ? $this->remark : $customer->remark;
        $customer->level = $this->level ? $this->level : $customer->level;
        $customer->updated_at = $date;
        $customer->updater_id = $admin_id;
        $customer->updater_name = $admin_name;

        try {

            $business->save(false);
            $business_id = $connection -> getLastInsertID();

            $customer->save(false);

            $contact->save(false);

            $business_id = $this->business_id ? $this->business_id : ($business_id ? $business_id : 0);
            CrmCustomerLog::add('个人客户转为企业客户', $this->customer_id, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            $province_id = $this->business_province_id ? $this ->business_province_id : ($this->customer_province_id ? $this -> customer_province_id : 0);
            $city_id = $this->business_city_id ? $this ->business_city_id : ($this->customer_city_id ? $this -> customer_city_id : 0);
            $district_id = $this->business_district_id ? $this ->business_district_id : ($this->customer_district_id ? $this -> customer_district_id : 0);

            $customer_model = new CustomerExchangeList();
            $data = [
                'id' => $this->customer_id ,
                'administrator_id' => $identity->id,
                'province_id' => $province_id,
                'city_id' => $city_id,
                'district_id' => $district_id,
                'source_id' =>  $this->source ? $this->source : $customer->source,
                'channel_id' =>  $this->channel_id ? $this->channel_id : $customer->channel_id
            ];
            $customer_model -> customer($data);

            $transaction->commit();

            return true;

        } catch (\Exception $e) {
            throw $e;
            $transaction->rollBack();
            return false;
        }

    }


}
