<?php

namespace common\models;

use backend\models\CustomerConfirmClaimNewForm;
use backend\modules\niche\models\CustomerExchangeList;
use imxiangli\select2\Select2Asset;
use SwaggerFixures\Customer;
use Think\Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "crm_customer".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property integer $gender
 * @property integer $channel_id
 * @property string $birthday
 * @property string $level
 * @property string $source
 * @property string $tel
 * @property string $caller
 * @property string $phone
 * @property string $email
 * @property string $qq
 * @property string $wechat
 * @property string $province_id
 * @property string $province_name
 * @property string $city_id
 * @property string $city_name
 * @property string $district_id
 * @property string $district_name
 * @property string $street
 * @property string $remark
 * @property string $get_way
 * @property string $department_id
 * @property string $administrator_id
 * @property integer $is_receive
 * @property string $company_id
 * @property string $creator_id
 * @property string $creator_name
 * @property string $updater_id
 * @property string $updater_name
 * @property string $last_record
 * @property string $last_record_creator_id
 * @property string $last_record_creator_name
 * @property string $operation_time
 * @property string $last_operation_creator_id
 * @property string $last_operation_creator_name
 * @property integer $is_protect
 * @property integer $contact_id
 * @property string $customer_public_id
 * @property string $extract_time
 * @property string $move_public_time
 * @property string $created_at
 * @property string $updated_at
 * @property string $scene_type
 * @property string $customer_hobby
 * @property string $keyword_field
 * @property string $keyword_value
 * @property string $native_place
 * @property string $administrator_name
 *
 */
class CrmCustomerApi extends \yii\db\ActiveRecord
{

    public $subject_type;
    public $company_name;
    public $business_name;
    public $name;
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

    public $page;
    public $page_num;
    public $scene_type;
    public $customer_public_id;
    public $customer_address;
    public $last_record_creator_id;
    public $last_record;
    public $business_address;
    public $keyword;
    public $labels;
    public $levels;
    public $create_start_time;
    public $create_end_time;
    public $weihu_start_time;
    public $weihu_end_time;
    public $get_way;
    public $combine_id;
    public $administrator_id;
    public $department_id;
    public $dates;

    public $id;

    public $customer_id;
    public $business_id;
    public $user_id;
    public $user_name;
    public $id_number;
    public $contacts_id;

    public $administrator_name;
    public $keyword_field;
    public $keyword_value;
    public $customer_type;
    public $native_place;
    public $start_last_record;
    public $end_last_record;
    public $contact_id;
    public $region;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'gender', 'level', 'source', 'province_id', 'city_id', 'district_id', 'get_way', 'department_id', 'administrator_id', 'is_receive', 'company_id', 'creator_id', 'updater_id', 'last_record', 'last_record_creator_id', 'operation_time', 'last_operation_creator_id', 'is_protect', 'customer_public_id', 'extract_time', 'move_public_time', 'created_at', 'updated_at', 'channel_id', 'subject_type', 'industry_id', 'tax_type', 'company_type_id', 'customer_province_id', 'customer_city_id', 'customer_district_id', 'department_id', 'business_province_id', 'business_city_id', 'business_district_id', 'page', 'page_num', 'scene_type', 'combine_id', 'customer_id', 'business_id', 'contacts_id', 'customer_type','contact_id'], 'integer'],
            [['id', 'street', 'remark', 'customer_name', 'company_name', 'industry_name', 'credit_code', 'register_status', 'enterprise_type', 'register_unit', 'address', 'scope', 'official_website', 'filing_tel', 'filing_email', 'company_remark', 'source_name', 'position', 'department', 'customer_province_name', 'customer_city_name', 'customer_district_name', 'customer_hobby', 'business_name', 'business_province_name', 'business_city_name', 'business_district_name', 'customer_address', 'business_address', 'keyword', 'labels', 'levels', 'create_start_time', 'create_end_time', 'weihu_start_time', 'weihu_end_time', 'dates', 'user_name', 'id_number', 'administrator_name', 'keyword_field', 'keyword_value', 'operating_period_end', 'operating_period_begin', 'start_last_record', 'end_last_record','region'], 'string'],

            [['page', 'page_num', 'scene_type', 'customer_public_id', 'subject_type'], 'required', 'on' => 'list'],
            [['subject_type'], 'required', 'on' => 'export'],
            [['create_start_time', 'create_end_time', 'source', 'get_way', 'company_id', 'department_id', 'levels', 'administrator_id', 'keyword', 'customer_address', 'last_record_creator_id', 'last_record', 'industry_id', 'business_address', 'labels', 'combine_id', 'keyword_field', 'keyword_value', 'customer_type', 'weihu_start_time', 'weihu_end_time'], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false, 'on' => 'export'],
            [['id', 'customer_public_id'], 'required', 'on' => 'detail'],
            [['id'], 'required', 'on' => 'update'],
            [['customer_id'], 'required', 'on' => 'basic'],
            [['id', 'administrator_id'], 'required', 'on' => 'distribution'],
            [['id'],'required','on'=>'extract'],

            [['registered_capital'], 'number'],
            [['region','name','customer_name','qq', 'operating_period_end', 'operating_period_begin','birthday','caller','register_status'], 'string', 'max' => 20],
            [['customer_hobby','industry_name','official_website','filing_email','company_name','email'], 'string', 'max' => 100],
            [['credit_code','business_name','id_number'], 'string', 'max' => 18],
            [['filing_tel'], 'string', 'max' => 30],
            [['legal_person_name','register_unit','enterprise_type','native_place','wechat','source_name','position','department'], 'string', 'max' => 50],
            [['business_address','address','company_remark','street','remark'], 'string', 'max' => 200],
            [['scope'], 'string', 'max' => 1000],
            [['creator_name', 'updater_name', 'last_record_creator_name', 'last_operation_creator_name'], 'string', 'max' => 10],
            [['tel', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 11],

            [['credit_code'],'match','pattern' => '/^[a-zA-Z0-9]{18}$/', 'message' => '信用代码只能由数字与字母组成'],
            [['filing_email'],'match', 'pattern' => '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', 'message' => '邮箱号格式错误'],
            [['tel'],'match','pattern'=>'/^([0-9]{3,4}-)?[0-9]{7,8}$/','message' => '座机格式错误'],

            [['province_name', 'district_name', 'city_name'], 'match', 'pattern' => '/^[ \x{4e00}-\x{9fa5}]+$/u', 'message' => '省市区参数错误，必须为汉字', 'on' => ['create', 'update']],
            [['name'], 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_+\/-]+$/u', 'message' => '姓名格式有誤', 'on' => 'update'],
            [['phone'], 'match', 'pattern' => '/^1\d{10}$/', 'message' => '手机号格式错误', 'on' => ['create', 'update', 'repeat', 'phone_status']],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{5,25})+$/', 'message' => '微信号格式错误', 'on' => ['create', 'update', 'repeat']],
            [['qq'], 'match', 'pattern' => '/^[1-9]*[1-9][0-9]*$/', 'message' => 'QQ号码格式错误', 'on' => ['create', 'update', 'repeat']],
            [['tel'], 'match', 'pattern' => '/^([0-9]{3,4}-)?[0-9]{7,8}$/', 'message' => '电话号格式错误', 'on' => ['create', 'update', 'repeat']],
            [['email'], 'match', 'pattern' => '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', 'message' => '邮箱号格式错误', 'on' => ['create', 'update', 'repeat']],
            [['birthday'], 'match', 'pattern' => '/^[1-2][\d]{3}\-(0\d|1[0-2])\-([0-2]\d|3[0-1])$/', 'message' => '生日参数格式错误', 'on' => ['create', 'update']],
            [['customer_id'], 'integer', 'on' => 'repeat'],
            [['contact_id'],'required', 'on' => 'update'],
            [['company_name', 'subject_type','customer_name','gender','phone'], 'required', 'on' => 'create'],
//            [['company_name', 'business_name'], "requiredByRepeat", 'skipOnEmpty' => false, 'skipOnError' => false, 'on' => 'repeat']

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
            'name' => 'Name',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'level' => 'Level',
            'source' => 'Source',
            'tel' => 'Tel',
            'caller' => 'Caller',
            'phone' => 'Phone',
            'email' => 'Email',
            'qq' => 'Qq',
            'wechat' => 'Wechat',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'street' => 'Street',
            'remark' => 'Remark',
            'get_way' => 'Get Way',
            'department_id' => 'Department ID',
            'administrator_id' => 'Administrator ID',
            'is_receive' => 'Is Receive',
            'company_id' => 'Company ID',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'last_record' => 'Last Record',
            'last_record_creator_id' => 'Last Record Creator ID',
            'last_record_creator_name' => 'Last Record Creator Name',
            'operation_time' => 'Operation Time',
            'last_operation_creator_id' => 'Last Operation Creator ID',
            'last_operation_creator_name' => 'Last Operation Creator Name',
            'is_protect' => 'Is Protect',
            'customer_public_id' => 'Customer Public ID',
            'extract_time' => 'Extract Time',
            'move_public_time' => 'Move Public Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    //查询我的客户

    public function customerList($limit, $param, $query1 = null)
    {
        //企业客户2or个人客户1

        $customer_public_id = $this->customer_public_id;

        if ($customer_public_id === '') {
            return false;
        }
        if ($query1 == '') {
            $query = new Query();
        } else {
            $query = $query1;
        }

        //ActiveDataProvider通过使用$ query执行数据库查询来提供数据。
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $subject_type = $this->subject_type;

        $query->select('
        any_value(bs.id) AS id, 
        any_value(c.id) AS customer_id, 
        any_value(cc.id) AS contact_id,
        any_value(bs.company_name) AS company_name, 
        any_value(u.name) AS name, 
        any_value(c.phone) AS phone, 
        any_value(a.name) AS username, 
        any_value(bs.updater_name) AS updater_name, 
        any_value(bs.updated_at) AS updated_at, 
        any_value(c.created_at) AS created_at, 
        any_value(s.`name`) AS source_name, 
        any_value(tag.name) AS tag_name, 
        any_value(bs.customer_number) AS customer_number, 
        any_value(c.get_way) AS get_way,
        any_value(c.user_id) AS user_id,
        any_value(bs.name) AS id_number,
        any_value(ch.name) AS channel_name,
        any_value(c.customer_public_id) AS customer_public_id,
        any_value(c.is_protect) AS is_protect,
        any_value(c.is_share) AS is_share,
        any_value(tag.color) AS tag_color,
        any_value(cc.name) AS contact_name,
        any_value(cc.province_name) AS contact_province_name,
        any_value(cc.city_name) AS contact_city_name,
        any_value(cc.district_name) AS contact_district_name,
        any_value(bs.province_name) AS business_province_name,
        any_value(bs.city_name) AS business_city_name,
        any_value(bs.district_name) AS business_district_name,
        any_value(c.last_record_creator_name) AS last_record_creator_name,
        any_value(c.last_record) AS last_record,
        any_value(bs.industry_name) AS industry_name,
        any_value(c.next_record) AS next_record,
        any_value(c.level) AS level,
        any_value(c.operation_time) AS operation_time,
        any_value(c.last_operation_creator_name) AS last_operation_creator_name,
        any_value(c.move_public_time) AS move_public_time,
        ')
            ->from(['c' => CrmCustomer::tableName()])
            ->leftJoin(['cc' => CrmContacts::tableName()], 'c.id = cc.customer_id')
            ->leftJoin(['bs' => BusinessSubject::tableName()], 'c.id=bs.customer_id')
            ->leftJoin(['ccc' => CrmCustomerCombine::tableName()], 'c.id = ccc.customer_id')
            ->leftJoin(['u' => User::tableName()], 'c.user_id=u.id')
            ->leftJoin(['a' => Administrator::tableName()], 'c.administrator_id=a.id')
            ->leftJoin(['s' => Source::tableName()], 'cc.source=s.id')
            ->leftJoin(['ctag' => CustomerTag::tableName()], 'c.id=ctag.customer_id')
            ->leftJoin(['tag' => Tag::tableName()], 'tag.id=ctag.tag_id')
            ->leftJoin(['ch' => Channel::tableName()], 'cc.channel_id =ch.id');

        //查询企业或个人 0 企业 1个人
        if ($subject_type == 0) {
            $query->where("bs.subject_type='$subject_type'");
        } else {
            $query->where("bs.subject_type='$subject_type' OR ISNULL(bs.subject_type)");
        }

        //判断是否为公海客户
        if ($customer_public_id == 0) {
            $query->andWhere(['c.customer_public_id' => 0]);
        } else {
            $query->andWhere(['<>', 'c.customer_public_id', 0]);
        }

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        //筛选我的客户1 全部客户3 下属客户2   判断是否是领导 以及获取下属id
        //默认展示我的客户
        $scene_type = $this->scene_type ? $this->scene_type : 1;
//        $query->leftJoin(['ccc' => CrmCustomerCombine::tableName()], 'c.id = ccc.customer_id');

        if($this->customer_public_id == 0){
            if ($scene_type == 1) {
                $query->andWhere(['ccc.administrator_id' => $identity->id]);
//                $query->andWhere(['c.administrator_id' => $identity->id]);
            } else if ($scene_type == 2) {
                $administrator_id = $identity->getTreeAdministratorId(false,true);
                if ($identity->isLeader() || $identity->isDepartmentManager()) {
                    $query->andWhere(['in', 'ccc.administrator_id', $administrator_id]);
                } else {
                    $query->andWhere(['ccc.administrator_id' => $identity->id]);
                }

            } else if($scene_type == 3){
                if($identity -> isBelongCompany() && $identity->isCompany()){
                    $administrator_id = $identity->getTreeAdministratorId(true,true);
                    if ($identity->isLeader() || $identity->isDepartmentManager()) {
                        $query->andWhere(['in', 'ccc.administrator_id', $administrator_id]);
                    } else {
                        $query->andWhere(['ccc.administrator_id' => $identity->id]);
                    }
                }
            }
        }else{
            if($identity -> isBelongCompany() && $identity->isCompany()){
                $department_id = $identity->getTreeDepartmentId(true);
                $query -> leftJoin(['cp'=>CustomerPublic::tableName()],'c.customer_public_id=cp.id');
                $query -> leftJoin(['cdp' => CustomerDepartmentPublic::tableName()],'cp.id=cdp.customer_public_id');
                if ($identity->isLeader() || $identity->isDepartmentManager()) {
                    $query->andWhere(['in', 'cdp.customer_department_id', $department_id]);
                } else {
                    if($this -> customer_type != ''){
                        $query->andWhere(['c.customer_public_id' => $this->customer_type]);
                    }else{
                        $query->andWhere(['in', 'cdp.customer_department_id', $department_id]);
                    }
                }
            }
        }

        //客户公海场景筛选客户
        if ($this->customer_type != '') {
            $query->andWhere(['c.customer_public_id' => $this->customer_type]);
        }

        //户籍地址
        if ($this->customer_address != '') {
            $query->andWhere(['or', ['=', 'c.province_id', $this->customer_address], ['=', 'c.city_id', $this->customer_address], ['=', 'c.district_id', $this->customer_address]]);
        }

        //最后跟进人
        if ($this->last_record_creator_id != '') {
            $query->andWhere(['c.last_record_creator_id' => $this->last_record_creator_id]);
        }

        //最后跟进时间
        if (!empty($this->start_last_record) && !empty($this->end_last_record)) {
            $query->andWhere(['between', 'c.last_record', strtotime($this->start_last_record), strtotime($this->end_last_record)]);
        } else {
            if (!empty($this->start_last_record)) {
                $query->andWhere(['>=', 'c.last_record', strtotime($this->start_last_record)]);
            }
            if (!empty($this->end_last_record)) {
                $query->andWhere(['<=', 'c.last_record', strtotime($this->end_last_record)]);
            }
        }

        //所在行业
        if ($this->industry_id != '') {
            $query->andWhere(['bs.industry_id' => $this->industry_id]);
        }

        //注册地
        if ($this->business_address != '') {
            $business_address_list = explode(',', $this->business_address);
            $business_address_count = count($business_address_list);
            $business_address = $business_address_list[$business_address_count - 1];
            $query->andWhere(['or', ['=', 'bs.province_id', $business_address], ['=', 'bs.city_id', $business_address], ['=', 'bs.district_id', $business_address]]);
        }

        //关键词搜索
        if ($this->keyword != '') {
            $query->andWhere(['or', ['like', 'bs.customer_number', $this->keyword], ['like', 'bs.company_name', $this->keyword], ['like', 'cc.phone', $this->keyword], ['like', 'cc.name', $this->keyword]]);
        }


        //客户标签搜索
        if ($this->labels != '') {
            $query->andWhere(['ctag.tag_id' => $this->labels]);
        }

        //客户级别
        if ($this->levels != '') {
            $query->andWhere(['c.level' => $this->levels]);
        }

        //创建时间筛选
        if (!empty($this->create_start_time) && !empty($this->create_end_time)) {
            $query->andWhere(['between', 'c.created_at', strtotime($this->create_start_time), strtotime($this->create_end_time)]);
        } else {
            if (!empty($this->create_start_time)) {
                $query->andWhere(['>=', 'c.created_at', strtotime($this->create_start_time)]);
            }
            if(!empty($this->create_end_time)){
                $query->andWhere(['<=', 'c.created_at', strtotime($this->create_end_time)]);
            }
        }

        //根据最后维护时间
        if (!empty($this->weihu_start_time) && !empty($this->weihu_end_time)) {
            $query->andWhere(['between', 'c.operation_time', strtotime($this->weihu_start_time), strtotime($this->weihu_end_time) ]);
        } else {
            if (!empty($this->weihu_start_time)) {
                $query->andWhere(['>=', 'c.operation_time', strtotime($this->weihu_start_time)]);
            }
            if (!empty($this->weihu_end_time)) {
                $query->andWhere(['<=', 'c.operation_time', strtotime($this->weihu_end_time)]);
            }
        }


        //客户来源
        if ($this->source != '') {
            $query->andWhere(['=', 'c.source', $this->source]);
        }

        //获取方式
        if ($this->get_way != '') {
            if($this->get_way == 1 ){
                $query->andWhere(['=', 'c.get_way', 0]);
            }else{
                $query->andWhere(['=', 'c.get_way', 1]);
            }
        }

        //合作人
        if ($this->administrator_id != '' && $this->combine_id != '') {
            $query->leftJoin(['ccc' => CrmCustomerCombine::tableName()], 'ccc.customer_id=c.id');
            $query->andWhere(['=', 'ccc.administrator_id', $this->combine_id]);
        }


        //负责人
        if ($this->administrator_id != '') {
            $query->andWhere(['c.administrator_id' => $this->administrator_id]);
        }

        //公司
        if($this->company_id != '') {
            $query->andWhere(['c.company_id' => $this->company_id]);
        }
        //部门
        if ($this->department_id != '') {
            $query->andWhere(['c.department_id' => $this->department_id]);
        }

        //自定义字段
        if ($this->keyword_field) {
            if ($this->keyword_field == 'qq') {
                $query->andWhere(['like', 'cc.qq', $this->keyword_value]);
            } else if ($this->keyword_field == 'phone') {
                $query->andWhere(['like', 'cc.phone', $this->keyword_value]);
            } else if ($this->keyword_field == 'customer_id') {
                $query->andWhere(['like', 'c.id', $this->keyword_value]);
            } else if ($this->keyword_field == 'caller') {
                $query->andWhere(['like', 'cc.caller', $this->keyword_value]);
            } else if ($this->keyword_field == 'wechat') {
                $query->andWhere(['like', 'cc.wechat', $this->keyword_value]);
            } else if ($this->keyword_field == 'email') {
                $query->andWhere(['like', 'cc.email', $this->keyword_value]);
            }
        }


        //按照时间筛选
        if ($this->dates != '') {

            $time = strtotime(date("Y-m-d"), time());
            if ($this->dates == 'J') {

                //当天创建的
                $query->andWhere(['between', 'c.created_at', $time, $time + 86399]);

            } else if ($this->dates == 'ST') {

                //近三天创建的
                $query->andWhere(['between', 'c.created_at', $time - 2 * 86400, $time + 86399]);

            } else if ($this->dates == 'YZ') {

                //近一周创建的
                $query->andWhere(['between', 'c.created_at', $time - 6 * 86400, $time + 86399]);

            } else if ($this->dates == 'YY') {

                //近一个月创建的
                $query->andWhere(['between', 'c.created_at', strtotime("-1 months", $time + 86400), $time + 86399]);

            } else if ($this->dates == 'WHJ') {

                //当天跟进的
                $query->andWhere(['between', 'c.operation_time', $time, $time + 86399]);

            } else if ($this->dates == 'WHS') {

                //近三天跟进的
                $query->andWhere(['between', 'c.operation_time', $time - 2 * 86400, $time + 86399]);

            } else if ($this->dates == 'WHYZ') {

                //近一周跟进的
                $query->andWhere(['between', 'c.operation_time', $time - 6 * 86400, $time + 86399]);

            } else if ($this->dates == 'WHYY') {

                //近一个月跟进的
                $query->andWhere(['between', 'c.operation_time', strtotime("-1 months", $time + 86400), $time + 86399]);

            }
        }
        $query->groupBy(['c.id']);

        //公海和我的客户不同排序方式
        if($customer_public_id==0){
            $query->orderBy(['c.operation_time' => SORT_DESC,'c.created_at' => SORT_DESC,'c.updated_at'=>SORT_DESC]);
        }else{
            $query->orderBy(['c.move_public_time' => SORT_DESC,'c.last_record'=>SORT_DESC]);
        }


        if ($param == 1) {

            return $query->count();

        } else {

            if (!empty($query1)) {
                return $dataProvider;
            }
            $rs = $query->offset($limit)->limit($this->page_num)->all();

            foreach ($rs as $key => &$val) {

                $val['yichengjiao'] = Niche::find()->where(['customer_id' => $val['customer_id'], 'status' => 2])->count();

                $val['yishibai'] = Niche::find()->where(['customer_id' => $val['customer_id'],'status' => 3])->count();

                $val['weichengjiao'] = Niche::find()->where(['niche_public_id' => 0, 'customer_id' => $val['customer_id'], 'status' => 0])->count();

                $val['daitiqu'] = Niche::find()->select('customer_id,niche_public_id')->where(['customer_id' => $val['customer_id']])->andWhere(['>', 'niche_public_id', 0])->count();

                $val['order_yifukuan'] = $this->getOrderNumber($val['user_id']);

                $val['order_weifukuan'] = $this->getOrderCount($val['user_id']);

                $val['updated_at'] = $val['updated_at'] ? date('Y-m-d H:i:s', $val['updated_at']) : '';
                $val['created_at'] = $val['created_at'] ? date('Y-m-d H:i:s', $val['created_at']) : '';
                $val['move_public_time'] = $val['move_public_time'] ? date('Y-m-d H:i:s', $val['move_public_time']) : '';
                $val['call_center'] =$identity->call_center ? $identity->call_center:'';

            }
            return $rs;
        }


    }

    //获取未付款的订单数
    public function getOrderCount($user_id)
    {
        $order = Order::find()->alias('o');
        $order->innerJoinWith(['virtualOrder vo']);
        $order->innerJoinWith(['user u']);
        $order->andWhere(['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]);
        $order->andWhere(['vo.is_vest' => '0']);
        $order->andWhere(['u.id' => $user_id]);
        return $order->count();
    }


    //获取已付款的订单数
    public function getOrderNumber($user_id)
    {
        $query = new Query();
        return $query->from(['o' => Order::tableName()])->select('o.user_id')
            ->leftJoin(['vo' => VirtualOrder::tableName()], 'o.virtual_order_id=vo.id')
            ->where(['o.user_id' => $user_id])
            ->andWhere(['>', 'vo.payment_amount', 0])
            ->count();

    }


    //查看个人客户关联信息详情 （公海 我的客户共用）

    public function getCustomerDetail($data)
    {
        $query = new Query();
        $query->select('u.name as user_name,u.phone as user_phone,u.email as user_email,u.address as user_address,b.legal_person_name,b.industry_name,a.title,a.name as administrator_name,d.name as department_name,c.level,c.id as customer_id,c.administrator_id,cc.id as contact_id,cc.name as contacts_name,cc.gender,cc.birthday,cc.customer_hobby,cc.phone as contact_phone,cc.wechat,cc.qq,cc.caller,cc.tel,cc.email,cs.id as source_id,cs.name as source_name,ch.name as channel_name,cc.province_id,cc.province_name,cc.district_id,cc.district_name,cc.city_id,cc.city_name,cc.street,cc.remark,cc.customer_id as customer_contacts_id,cc.department,cc.position,c.customer_public_id,b.official_website,b.company_name,c.is_protect,cc.native_place,b.subject_type,c.administrator_name as last_administrator_name')
            ->from(['c' => CrmCustomer::tableName()])
            ->leftJoin(['cc' => CrmContacts::tableName()], 'c.id=cc.customer_id')
            ->leftJoin(['b' => BusinessSubject::tableName()], 'c.id = b.customer_id')
            ->leftJoin(['a' => Administrator::tableName()], 'c.administrator_id = a.id')
            ->leftJoin(['u' => User::tableName()], 'c.user_id = u.id')
            ->leftJoin(['cs' => Source::tableName()], 'cc.source = cs.id')
            ->leftJoin(['ch' => Channel::tableName()], 'cc.channel_id = ch.id')
            ->leftJoin(['d' => CrmDepartment::tableName()], 'c.department_id = d.id')
            ->where(['c.id' => $data['id']]);

        $rs = $query->one();

        if ($data['customer_public_id'] == 2) {
            $result = $this->customerReplace($rs);
        } else {
            $result = $rs;
        }

        return $result;
    }


    //替换个人客户关联信息详情结果
    public function customerReplace($rs)
    {
        $data['user_name'] =$rs['user_name'];
        $data['user_phone'] = $rs['user_phone'] != '' ? substr_replace($rs['user_phone'], '*********', 2, 11) :'';
        $data['user_email'] = $rs['user_email'] != '' ? substr_replace($rs['user_email'], $this->str_replace_val(strpos($rs['user_email'], '@')), 0, strpos($rs['user_email'], '@')) : '';
        $data['user_address'] = $rs['user_address'];
        $data['legal_person_name'] = $rs['legal_person_name'];
        $data['industry_name'] = $rs['industry_name'];
        $data['title'] = $rs['title'];
        $data['administrator_name'] = $rs['administrator_name'];
        $data['last_administrator_name'] = $rs['last_administrator_name'];
        $data['department_name'] = $rs['department_name'];
        $data['level'] = $rs['level'];
        $data['customer_id'] = $rs['customer_id'];
        $data['administrator_id'] = $rs['administrator_id'];
        $data['contact_id'] = $rs['contact_id'];
        $data['contacts_name'] = $rs['contacts_name'];
        $data['gender'] = $rs['gender'];
        $data['birthday'] = $rs['birthday'];
        $data['customer_hobby'] = $rs['customer_hobby'];
        $data['contact_phone'] = $rs['contact_phone'] != '' ? substr_replace($rs['contact_phone'], '*********', 2, 11) : '';
        $data['wechat'] = $this->str_replace_val(strlen($rs['wechat']));
        $data['qq'] = $this->str_replace_val(strlen($rs['qq']));
        $data['caller'] = $rs['caller'] != '' ? substr_replace($rs['caller'], '*********', 2, 11) : '';
        $data['tel'] = $rs['tel'] != '' ? substr_replace($rs['tel'], '*******', 5, 11) : '';
        $data['email'] = $rs['email'] != '' ? substr_replace($rs['email'], $this->str_replace_val(strpos($rs['email'], '@')), 0, strpos($rs['email'], '@')) : '';
        $data['source_name'] = $rs['source_name'];
        $data['channel_name'] = $rs['channel_name'];
        $data['province_id'] = $rs['province_id'];
        $data['province_name'] = $rs['province_name'];
        $data['district_id'] = $rs['district_id'];
        $data['district_name'] = mb_substr($rs['district_name'], 0, 1) . $this->str_replace_val(mb_strlen($rs['district_name']));
        $data['city_id'] = $rs['city_id'];
        $data['city_name'] = $rs['city_name'];
        $data['street'] = $this->str_replace_val(mb_strlen($rs['street']));
        $data['remark'] = $rs['remark'];
        $data['customer_contacts_id'] = $rs['customer_contacts_id'];
        $data['department'] = $rs['department'];
        $data['position'] = $rs['position'];
        $data['customer_public_id'] = $rs['customer_public_id'];
        $data['company_name'] = $rs['company_name'];
        $data['official_website'] = $rs['official_website'];
        $data['native_place'] = $rs['native_place'];
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


    //查看个人客户基本信息详情 （公海 我的客户共用）

    public function getCustomerBasicDetail($data)
    {
        $query = new Query();
        $query->select('cc.id as customer_id,b.id as business_id,u.id as user_id,b.company_name as user_name,b.name as id_number,b.province_id,b.province_name,b.district_id,b.district_name,b.city_id,b.city_name,b.address as street,b.company_remark,c.gender,cc.customer_public_id')
            ->from(['cc' => CrmCustomer::tableName()])
            ->leftJoin(['b' => BusinessSubject::tableName()], 'cc.id = b.customer_id')
            ->leftJoin(['u' => User::tableName()], 'cc.user_id = u.id')
            ->leftJoin(['c' => CrmContacts::tableName()], 'cc.id = c.customer_id')
            ->where(['cc.id' => $data['id']]);
        $rs = $query->one();
        if ($data['customer_public_id'] == 2) {
            $result = $this->customerBasicReplace($rs);
        } else {
            $result = $rs;
        }

        return $result;
    }

    //个人客户基本信息加密
    public function customerBasicReplace($rs)
    {
        $data['customer_id'] = $rs['customer_id'];
        $data['business_id'] = $rs['business_id'];
        $data['user_id'] = $rs['user_id'];
        $data['user_name'] = $rs['user_name'];
        $data['id_number'] = $rs['id_number'] != '' ? substr_replace($rs['id_number'], '************', 5, 18) : '';
        $data['province_name'] = $rs['province_name'];
        $data['district_name'] = $this->str_replace_val(mb_strlen($rs['district_name']));
        $data['city_name'] = $rs['city_name'];
        $data['street'] = $this->str_replace_val(mb_strlen($rs['street']));
        $data['remark'] = isset($rs['company_remark']) ? $rs['company_remark'] : '';
        $data['gender'] = $rs['gender'];
        $data['customer_public_id'] = $rs['customer_public_id'];

        return $data;
    }


    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    //个人基本信息编辑
    public function crmCustomerBasicUpdate()
    {
        $customer_id = $this->customer_id;
        $business_id = $this->business_id;

        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->select('id,user_id,gender,province_id,province_name,district_id,district_name,city_id,city_name,remark,level')->where(['id' => $customer_id])->one();

        /** @var BusinessSubject $business */
        $business = BusinessSubject::find()->where(['id' => $business_id])->one();

        /** @var CrmContacts $contact */
        $contact = CrmContacts::find()->where(['customer_id' => $customer_id])->one();

        if(empty($contact)){
            return false;
        }

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        $user_id = 0;
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

        //todo 如果缺少主体信息就进行添加
        if (empty($business)) {

            $code = $this->customerNumber();
            $business = new BusinessSubject();
            $business->customer_number = $code;
            $business->customer_id = $customer->id;
            $business->subject_type = 1;
            $business->user_id = $customer->user_id ?  $customer->user_id : ($user_id ? $user_id :0);
        }
        $business->name = $this->id_number;
        $business->province_id = $this->province_id ? $this->province_id : (isset($business->province_id) ? $business->province_id : 0);
        $business->province_name = $this->province_name ? $this->province_name : (isset($business->province_name) ? $business->province_name : '');
        $business->district_id = $this->district_id ? $this->district_id : (isset($business->district_id) ? $business->district_id : 0);
        $business->district_name = $this->district_name ? $this->district_name : (isset($business->district_name) ? $business->district_name : '');
        $business->city_id = $this->city_id ? $this->city_id : (isset($business->city_id) ? $business->city_id : 0);
        $business->city_name = $this->city_name ? $this->city_name : (isset($business->city_name) ? $business->city_name : '');
        $business->company_remark = $this->company_remark ? $this->company_remark : (isset($business->company_remark) ? $business->company_remark : '');
        $business->company_name = $this->user_name ? $this->user_name : (isset($business->company_name) ? $business->company_name : '');

        try {

            $business->save(false);
            $business_id = $business_id ? $business_id : $connection->getlastInsertID();
            CrmCustomerLog::add('编辑客户信息中的个人基本信息模块', $customer_id, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            $transaction->commit();

            $change_model = new CustomerExchangeList();
            $change_model -> updateCustomer($customer_id);
            return true;
        } catch (\Exception $e) {

            $transaction->rollBack();
            throw $e;
            return false;
        }
    }


    //生成客户编号
    public function customerNumber()
    {

        $identity = Yii::$app->user->identity;

        $query = new Query();

        $number_rs = $query->select('c.company_abbreviation')->from(['a' => Administrator::tableName()])->leftJoin(['c' => Company::tableName()], 'a.company_id = c.id')->where(['a.id' => $identity['id']])->one();

        $number = $number_rs['company_abbreviation'] ? $number_rs['company_abbreviation'] : 'JJJT';

        $date = date("Ymd", time());

        $num = BusinessSubject::find()->select('customer_number')->where(['<>', 'customer_number', ''])->andWhere(['like', 'customer_number', $date])->orderBy('customer_number DESC')->limit(1)->one();

        $str = substr($num['customer_number'], 12, 4);

        $str_4 = $str + 10001;

        if (empty($num)) {
            $code = $number . $date . '0001';
        } else {
            $code = $number . $date . substr($str_4, 1, 4);
        }

        return $code;
    }



    //新增客户校验
    public function addNumber($customer_type)
    {
        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $query = new Query();
        /** @var CustomerPublic $customerPublic */
        $query -> from(['c'=>CustomerPublic::tableName()])
            ->leftJoin(['d' => CustomerDepartmentPublic::tableName()],'c.id=d.customer_public_id')
            ->where(['d.customer_department_id' => $identity->department_id]);

        if($customer_type == 0){
            $rs = $query -> andWhere(['c.customer_type' => 2]) -> one();
        }else if($customer_type == 1){
            $rs = $query -> andWhere(['c.customer_type' => 1]) -> one();
        }

        if(empty($rs)){
            $customerPublic = $query->andWhere(['c.customer_type' => 0]) -> one();
        }else{
            $customerPublic = $rs;
        }

        if($customerPublic != null){
            //1:首先判断是否包含新增客户
            if($customerPublic['big_customer_status'] == 1){
                //2：判断可新增客户是否大于0  如果为0 不做限制 ，不为0做限制
                if($customerPublic['big_customer'] > 0){
                    //3：如果可新增客户数大于0，判断是适用对象 如果为企业或者个人
                    $customer_query = CrmCustomer::find() -> alias('cc')
                        ->leftJoin(['b'=>BusinessSubject::tableName()],'cc.id = b.customer_id')
                        ->leftJoin(['ccc' => CrmCustomerCombine::tableName()],'cc.id=ccc.customer_id')
                        ->where(['ccc.administrator_id' => $identity->id])
                        ->andWhere(['<>','cc.administrator_id',0]);
                    if($customerPublic['customer_type'] != 0){
                        //4：如果是企业或这个人，判断当前新增的客户是企业或者个人 然后去查询
                        if($customer_type == 0){
                            $customer_count = $customer_query ->andWhere(['b.subject_type' => 0]) ->count();
                        }else{
                            $customer_count = $customer_query ->andWhere(['b.subject_type' => 1]) ->count();
                        }
                    }else{
                        $customer_count = $customer_query ->count();
                    }
                    if ($customer_count >= $customerPublic['big_customer']) {
                        return ['code' => 400, 'message' => '您已超过当前最大客户拥有数量！', 'data' => []];
                    }
                }
            }
        }
    }
    /**
     * 新增用户
     * @return array
     * @throws \yii\db\Exception
     */
    public function  customerAdd()
    {
        $number = $this -> addNumber($this->subject_type);

        if(!empty($number)){
            return $number;
        }
        //自动生成客户编号
        $code = $this->customerNumber();

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $admin_id = $identity->id;

        $department_id = $identity->department_id;

        $admin_name = $identity->name;

        $date = time();

        $connection = Yii::$app->db;

        $user = User::find()->where(['phone' => $this->phone])->one();

        if (empty($user)) {

            $user = new User();

            $user->customer_id = 0;
            $user->name = $this->customer_name ? $this->customer_name : '';
            $user->phone = $this->phone ? $this->phone : '';
            $user->username = $this->phone ? $this->phone : '';
            $user->source_id = 160;
            $user->register_mode = 1;
            $user->source_name = '系统同步';
            $user->created_at = time();
            $user->creator_id = $this->administrator_id ? $this->administrator_id : $admin_id;
            $user->creator_name = $this->administrator_name ? $this->administrator_name : $admin_name;

            if ($user->save(false)) {
                $user_id = $connection->getlastInsertID();
            } else {
                return ['code' => 400, 'message' => 'user表更新失败', 'data' => []];
            }

        } else {
            $user_id = $user['id'];
        }

        $customer = new CrmCustomer();

        $customer->user_id = $user_id;
        $customer->name = $this->customer_name ? $this->customer_name : '';
        $customer->gender = $this->gender ? $this->gender : 0;
        $customer->phone = $this->phone ? $this->phone : '';
        $customer->wechat = $this->wechat ? $this->wechat : '';
        $customer->qq = $this->qq ? $this->qq : '';
        $customer->tel = $this->tel ? $this->tel : '';
        $customer->caller = $this->caller ? $this->caller : '';
        $customer->email = $this->email ? $this->email : '';
        $customer->birthday = $this->birthday ? $this->birthday : '';
        $customer->source = $this->source ? $this->source : 0;
        $customer->channel_id = $this->channel_id ? $this->channel_id : 0;
        $customer->province_id = $this->customer_province_id ? $this->customer_province_id : 0;
        $customer->province_name = $this->customer_province_name ? $this->customer_province_name : '';
        $customer->city_id = $this->customer_city_id ? $this->customer_city_id : 0;
        $customer->city_name = $this->customer_city_name ? $this->customer_city_name : '';
        $customer->district_id = $this->customer_district_id ? $this->customer_district_id : 0;
        $customer->district_name = $this->customer_district_name ? $this->customer_district_name : '';
        $customer->street = $this->street ? $this->street : 0;
        $customer->customer_hobby = $this->customer_hobby ? $this->customer_hobby : '';
        $customer->remark = $this->remark ? $this->remark : '';
        $customer->level = $this->level ? $this->level : 1;
        $customer->department_id = $this->department_id ? $this->department_id : $department_id;
        $customer->company_id = $identity->company_id;
        $customer->administrator_id = $this->administrator_id ? $this->administrator_id : $admin_id;
        $customer->administrator_name = $this->administrator_name ? $this->administrator_name : $admin_name;
        $customer->created_at = $date;
        $customer->creator_name = $this->administrator_name ? $this->administrator_name : $admin_name;
        $customer->creator_id = $this->administrator_id ? $this->administrator_id : $admin_id;;


        $contact = new CrmContacts();

        $contact->name = $this->customer_name ? $this->customer_name : '';
        $contact->gender = $this->gender ? $this->gender : 0;
        $contact->phone = $this->phone ? $this->phone : '';
        $contact->wechat = $this->wechat ? $this->wechat : '';
        $contact->qq = $this->qq ? $this->qq : '';
        $contact->tel = $this->tel ? $this->tel : '';
        $contact->caller = $this->caller ? $this->caller : '';
        $contact->email = $this->email ? $this->email : '';
        $contact->birthday = $this->birthday ? $this->birthday : '';
        $contact->source = $this->source ? $this->source : 0;
        $contact->channel_id = $this->channel_id ? $this->channel_id : 0;
        $contact->position = $this->position ? $this->position : '';
        $contact->department = $this->department ? $this->department : '';
        $contact->province_id = $this->customer_province_id ? $this->customer_province_id : 0;
        $contact->province_name = $this->customer_province_name ? $this->customer_province_name : '';
        $contact->city_id = $this->customer_city_id ? $this->customer_city_id : 0;
        $contact->city_name = $this->customer_city_name ? $this->customer_city_name : '';
        $contact->district_id = $this->customer_district_id ? $this->customer_district_id : 0;
        $contact->district_name = $this->customer_district_name ? $this->customer_district_name : '';
        $contact->street = $this->street ? $this->street : '';
        $contact->customer_hobby = $this->customer_hobby ? $this->customer_hobby : '';
        $contact->remark = $this->remark ? $this->remark : '';
        $contact->native_place = $this->native_place ? $this->native_place : '';

        $business = new BusinessSubject();

        $operating_period_begin = strtotime($this->operating_period_begin);
        $operating_period_end = strtotime($this->operating_period_end);
        $business->customer_number = $code;
        $business->user_id = $user_id;
        $business->subject_type = $this->subject_type;
        $business->company_name = $this->company_name ? $this->company_name : '';
        $business->name = $this->business_name ? $this->business_name : '';
        $business->industry_id = $this->industry_id ? $this->industry_id : 0;
        $business->industry_name = $this->industry_name ? $this->industry_name : '';
        $business->tax_type = $this->tax_type ? $this->tax_type : 0;
        $business->credit_code = $this->credit_code ? $this->credit_code : '';
        $business->register_status = $this->register_status ? $this->register_status : '';
        $business->company_type_id = $this->company_type_id ? $this->company_type_id : 0;
        $business->enterprise_type = $this->enterprise_type ? $this->enterprise_type : '';
        $business->legal_person_name = $this->legal_person_name ? $this->legal_person_name : '';
        $business->registered_capital = $this->registered_capital ? $this->registered_capital : 0;
        $business->operating_period_begin = $operating_period_begin ? $operating_period_begin : 0;
        $business->operating_period_end = $operating_period_end ? $operating_period_end : 0;
        $business->register_unit = $this->register_unit ? $this->register_unit : '';
        $business->province_id = $this->business_province_id ? $this->business_province_id : 0;
        $business->province_name = $this->business_province_name ? $this->business_province_name : '';
        $business->city_id = $this->business_city_id ? $this->business_city_id : 0;
        $business->city_name = $this->business_city_name ? $this->business_city_name : '';
        $business->district_id = $this->business_district_id ? $this->business_district_id : 0;
        $business->district_name = $this->business_district_name ? $this->business_district_name : '';
        $business->address = $this->address ? $this->address : '';
        $business->scope = $this->scope ? $this->scope : '';
        $business->official_website = $this->official_website ? $this->official_website : '';
        $business->filing_tel = $this->filing_tel ? $this->filing_tel : '';
        $business->filing_email = $this->filing_email ? $this->filing_email : '';
        $business->company_remark = $this->company_remark ? $this->company_remark : '';
        $business->created_at = $date;
        $business->creator_name = $this->administrator_name ? $this->administrator_name : $admin_name;
        $business->creator_id = $this->administrator_id ? $this->administrator_id : $admin_id;;

        $transaction = $connection->beginTransaction();

        try {

            $customer->save(false);
            $customer_id = $connection->getlastInsertID();

            $business->customer_id = $customer_id;
            $business->save(false);
            $business_id = $connection -> getLastInsertID();

            $contact->customer_id = $customer_id;
            $contact->save(false);

            CrmCustomerCombine::addCombine($customer->administrator, $customer, $business);

            CrmCustomerLog::add('创建客户', $customer_id, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            $transaction->commit();

            $customer_model = new CustomerExchangeList();
            $province_id = $this->business_province_id ? $this ->business_province_id : ($this->customer_province_id ? $this -> customer_province_id : 0);
            $city_id = $this->business_city_id ? $this ->business_city_id : ($this->customer_city_id ? $this -> customer_city_id : 0);
            $district_id = $this->business_district_id ? $this ->business_district_id : ($this->customer_district_id ? $this -> customer_district_id : 0);
            $data = [
                'id' => $customer_id ,
                'administrator_id' => $identity->id,
                'province_id' => $province_id,
                'city_id' => $city_id,
                'district_id' => $district_id,
                'source_id' => $this->source ? $this->source :  0,
                'channel_id' => $this->channel_id ? $this->channel_id : 0
            ];
            $customer_model -> customer($data);
            
            return ['code' => 200, 'message' => '添加成功', 'data' => ['customer_id' => $customer_id, 'business_id' => $business_id]];

        } catch (\Exception $e) {
            throw $e;
            $transaction->rollBack();
            return ['code' => 400, 'message' => '添加失败'];
        }



    }

    /**
     * 关联信息修改
     * @return bool
     * @throws \yii\db\Exception
     */
    public function updateCustomerDetail()
    {

        if (!$this->validate()) return false;

        /** @var CrmContacts $contact */
        $contact = CrmContacts::find()->where(['id' => $this->contact_id])->one();

        if (empty($contact)) {
            return false;
        }

        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find() -> where(['id' => $contact->customer_id])->one();

        /** @var BusinessSubject $business_subject */
        $business_subject = BusinessSubject::find()->where(['customer_id' => $customer->id])->one();

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

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

                $customer->user_id = $user_id;

                if(!empty($business_subject)){
                    $business_subject->user_id = $user_id ? $user_id : 0;
                    $business_subject->save(false);
                }
            }else{
                $user_id = 0;
            }
        }
        $contact->user_id = $contact->user_id ? $contact->user_id : ($user_id ? $user_id : 0);
        $contact->name = $this->customer_name ? $this->customer_name : $contact->name;
        $contact->gender = isset($this->gender) ? $this->gender : $contact->gender;
        $contact->phone = $this->phone ? $this->phone : $contact->phone;
        $contact->qq = $this->qq ? $this->qq : $contact->qq;
        $contact->wechat = $this->wechat ? $this->wechat : $contact->wechat;
        $contact->tel = $this->tel ? $this->tel : $contact->tel;
        $contact->email = $this->email ? $this->email : $contact->email;
        $contact->birthday = $this->birthday ? $this->birthday : $contact->birthday;
        $contact->caller = $this->caller ? $this->caller : $contact->caller;
        $contact->source = $this->source ? $this->source : $contact->source;
        $contact->channel_id = $this->channel_id ? $this->channel_id : $contact->channel_id;
        $contact->position = $this->position ? $this->position : $contact->position;
        $contact->department = $this->department ? $this->department : $contact->department;
        $contact->province_id = $this->province_id ? $this->province_id : $contact->province_id;
        $contact->province_name = $this->province_name ? $this->province_name : $contact->province_name;
        $contact->city_id = $this->city_id ? $this->city_id : $contact->city_id;
        $contact->city_name = $this->city_name ? $this->city_name : $contact->city_name;
        $contact->district_id = $this->district_id ? $this->district_id : $contact->district_id;
        $contact->district_name = $this->district_name ? $this->district_name : $contact->district_name;
        $contact->street = $this->street ? $this->street : $contact->street;
        $contact->customer_hobby = $this->customer_hobby ? $this->customer_hobby : $contact->customer_hobby;
        $contact->remark = $this->remark ? $this->remark : $contact->remark;
        $contact->native_place = $this->native_place ? $this->native_place : $contact->native_place;

        $customer->phone = $this->phone ? $this->phone : $customer->phone;

        try {

            $rs = $contact->save(false);

            $customer->save(false);

            CrmCustomerLog::add('编辑客户信息中的联系信息模块', $contact->customer_id, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, 0);

            $transaction->commit();

            $change_model = new CustomerExchangeList();

            $change_model -> updateCustomer($contact->customer_id);
            return $rs;

        } catch (\Exception $e) {

            $transaction->rollBack();
            throw $e;
            return false;
        }
    }

    /**
     * 公海客户提取
     * @param $id
     * @return bool
     * @throws \yii\db\Exception
     */
    public function updateExtract($id)
    {
        $id = explode(',', $id['id']);

        /** @var Administrator $identity */

        $identity = Yii::$app->user->identity;

        $department_id = $identity->department_id;

        $admin_id = $identity->id;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {
            foreach ($id as $key => $val) {
                /** @var CrmCustomer $customer */
                $customer = CrmCustomer::find()->where(['id' => $val])->one();
                $customer->department_id = $department_id;
                $customer->administrator_id = $admin_id;
                $customer->administrator_name = $identity->name;
                $customer->customer_public_id = 0;
                $customer->extract_time = time();
                $customer->level = $customer->level ? $customer->level : 0;
                $customer->is_receive = 1;
                $customer->company_id = isset($identity->company_id) ? $identity->company_id : 0;
                $customer->save(false);
                /** @var BusinessSubject $business */
                $business = BusinessSubject::find()->where(['customer_id' => $val])->one();
                /** @var CrmCustomerCombine $c */
                $c = CrmCustomerCombine::find()->where(['customer_id' => $val,
                    'administrator_id' => $admin_id])->one();
                if (null == $c) {
                    $c = new CrmCustomerCombine();
                    $c->level = 1;
                    $c->business_subject_id = isset($business->id) ? $business->id : 0;
                    $c->administrator_id = $admin_id;
                    $c->customer_id = $customer->id;
                    $c->company_id = isset($identity->company_id) ? $identity->company_id : 0;
                    $c->department_id = $department_id;
                    $c->user_id = $customer->user_id;
                    $c->status = CrmCustomerCombine::STATUS_RECEIVED;
                    $c->created_at = time();
                    $c->save(false);
                }

                $business_id = isset($business->id)? $business->id : 0;
                CrmCustomerLog::add('从公海提取', $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);
                $customer_model = new CustomerExchangeList();
                /** @var CrmContacts $contact */
                $contact = CrmContacts::find()->where(['customer_id'=>$val])->one();
                $province_id =isset($business->province_id) ? $business->province_id : ($contact->province_id ? $contact->province_id : 0);
                $city_id = isset($business->city_id) ? $business-> city_id : ($contact->city_id ? $contact->city_id : 0);
                $district_id = isset($business->district_id) ? $business->district_id : ($contact->district_id ? $contact->district_id : 0);
                $data = [
                    'id' => $val,
                    'administrator_id' => $identity->id,
                    'province_id' => $province_id,
                    'city_id' => $city_id,
                    'district_id' => $district_id,
                    'source_id' => $contact->source ,
                    'channel_id' => $contact->channel_id
                ];
                $customer_model -> customer($data);
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 公海客户分配
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function updateDistribution($data)
    {

        $id = explode(',', $data['id']);

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        /** @var Administrator $department_id */
        $department_id = Administrator::find()->where(['id' => $this->administrator_id])->one();

        try {
            foreach ($id as $key => $val) {

                /** @var CrmCustomer $customer */
                $customer = CrmCustomer::find()->where(['id' => $val])->one();
                $customer->department_id = $department_id['department_id'] ? $department_id['department_id'] : 0;
                $customer->administrator_id = $this->administrator_id;
                $customer->administrator_name = $department_id ->name;
                $customer->company_id = $department_id->company_id;
                $customer->customer_public_id = 0;
                $customer->level = 1;
                $customer->is_receive = 1;
                $customer->distribution_time = time();
                $customer->save(false);

                /** @var BusinessSubject $business */
                $business = BusinessSubject::find()->where(['customer_id' => $val])->one();
                /** @var CrmCustomerCombine $c */
                $c = CrmCustomerCombine::find()->where(['customer_id' => $val,
                    'administrator_id' => $this->administrator_id])->one();
                if (null == $c) {
                    $c = new CrmCustomerCombine();
                    $c->level = 1;
                    $c->business_subject_id = isset($business->id) ? $business->id : 0;
                    $c->administrator_id = $this->administrator_id;
                    $c->customer_id = $customer->id;
                    $c->company_id = $department_id->company_id;
                    $c->department_id = $department_id->department_id ? $department_id->department_id : 0;
                    $c->user_id = $customer->user_id;
                    $c->status = CrmCustomerCombine::STATUS_RECEIVED;
                    $c->created_at = time();
                    $c->save(false);
                }
                CrmCustomerLog::add('从公海分配给'.$department_id->name, $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, 0);

                $customer_model = new CustomerExchangeList();
                /** @var CrmContacts $contact */
                $contact = CrmContacts::find()->where(['customer_id'=>$val])->one();
                $province_id =isset($business->province_id) ? $business->province_id : ($contact->province_id ? $contact->province_id : 0);
                $city_id = isset($business->city_id) ? $business-> city_id : ($contact->city_id ? $contact->city_id : 0);
                $district_id = isset($business->district_id) ? $business->district_id : ($business->district_id ? $business->district_id : 0);
                $data = [
                    'id' => $val,
                    'administrator_id' => $this->administrator_id,
                    'province_id' => $province_id,
                    'city_id' => $city_id,
                    'district_id' => $district_id,
                    'source_id' => $contact->source ,
                    'channel_id' => $contact->channel_id
                ];
                $customer_model -> customer($data);

                //添加消息提醒
                /** @var Administrator $administrator */
                $administrator = Yii::$app->user->identity;
                $type = MessageRemind::TYPE_COMMON;
                $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
                $receive_id = $this->administrator_id;
                $customer_id = $customer->id;
                $sign = 'd-'.$receive_id.'-'.$val.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if($customer->id && null == $messageRemind)
                {
                    $business_name = isset($business->company_name) ? $business->company_name : $customer->name;

                    $message = '你有一个新分配客户“'. $business_name .'”，请及时查看跟进！';
                    $popup_message = '您收到一个新客户，请及时查看哦！';
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id,  0,  0, $administrator);
                }

            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 客户验重
     * @return array
     */
    public function getRepeat()
    {

        $query = new Query();
        $query->select('bs.id,c.id as customer_id,bs.company_name,cc.qq,cc.email,cc.wechat,cc.tel,cc.phone,a.name,c.administrator_id')
            ->from(['c' => CrmCustomer::tableName()])
            ->leftJoin(['cc' => CrmContacts::tableName()], 'c.id = cc.customer_id')
            ->leftJoin(['bs' => BusinessSubject::tableName()], 'c.id=bs.customer_id')
            ->leftJoin(['a' => Administrator::tableName()], 'c.administrator_id = a.id')
            ->orderBy(['c.created_at' => SORT_ASC]);

        if (isset($this->customer_id)) {
            $customer_id = $this->customer_id;
        } else {
            $customer_id = '';
        }
        $rs = [];

        if ($this->customer_id == null) {
            $message = '请谨慎录入商机！';
        } else {
            $message = '请检查后保存。';
        }
        if (!empty($this->company_name)) {
            $query->where(['bs.company_name' => $this->company_name])->andWhere(['<>', 'c.id', $customer_id]);
            $company_name = $query->one();
            if ($company_name) {
                $company_name['company_name'] = isset($company_name['company_name']) ? $company_name['company_name'] : '[无名称]';
                $rs['business'][] = [
                    'id' => isset($company_name['id']) ? $company_name['id'] : 0,
                    'customer_id' => $company_name['customer_id'],
                    'message' => "当前客戶信息与 ‘" . $company_name['name'] . "’ 的 ‘" . $company_name['company_name'] . "’ 信息重复，" . $message
                ];
            }
        }
        if (!empty($this->business_name)) {
            $query->where(['bs.name' => $this->business_name])->andWhere(['<>', 'c.id', $customer_id]);
            $business_name = $query->one();
            if ($business_name) {
                $business_name['company_name'] = isset($business_name['company_name']) ? $business_name['company_name'] : '[无名称]';
                $rs['business'][] = [
                    'id' => isset($business_name['id']) ? $business_name['id'] : 0,
                    'customer_id' => $business_name['customer_id'],
                    'message' => "当前客戶信息与 ‘" . $business_name['name'] . "’ 的 ‘" . $business_name['company_name'] . "’ 信息重复，" . $message
                ];
            }
        }
        return $rs;
    }

    public function requiredBySpecial($attribute)
    {
        if (empty($this->create_start_time) && empty($this->create_end_time) &&
            empty($this->source) && empty($this->get_way) &&
            empty($this->company_id) && empty($this->department_id) &&
            empty($this->administrator_id) &&
            empty($this->levels) && empty($this->keyword) &&
            empty($this->customer_address) &&
            empty($this->last_record_creator_id) && empty($this->last_record) &&
            empty($this->industry_id) && empty($this->business_address) &&
            empty($this->labels) && empty($this->combine_id) &&
            empty($this->keyword_field) && empty($this->keyword_value) &&
            empty($this->weihu_start_time) && empty($this->weihu_end_time) &&
            empty($this->customer_type)) {
            $this->addError($attribute, '请选择任意一项搜索才能导出！');
        }
    }

    public function requiredByRepeat($attribute)
    {
        if (empty($this->business_name) && empty($this->company_name)) {
            $this->addError($attribute, '请输入客户名称或者身份证号！');
        }
    }
}
