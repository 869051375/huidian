<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\redis\Cache;

/**
 * This is the model class for table "crm_customer".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property integer $gender
 * @property string $birthday
 * @property integer $level
 * @property integer $source
 * @property string $tel
 * @property string $caller
 * @property string $phone
 * @property string $email
 * @property string $qq
 * @property string $wechat
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property string $street
 * @property string $remark
 * @property integer $get_way
 * @property integer $administrator_id
 * @property integer $is_receive
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $last_record
 * @property integer $operation_time
 * @property integer $last_record_creator_id
 * @property string $last_record_creator_name
 * @property string $abandon_reason
 * @property string $customer_hobby
 * @property integer $last_operation_creator_id
 * @property string $last_operation_creator_name
 * @property string $administrator_name
 * @property integer $is_protect
 * @property integer $customer_public_id
 * @property integer $extract_time
 * @property integer $move_public_time
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $channel_id
 * @property integer $is_share
 * @property integer $next_record
 * @property integer $distribution_time
 * @property integer $transfer_time
 *
 * @property User $user
 * @property Administrator $administrator
 * @property UserLoginLog[] $userLoginLog
 * @property CrmDepartment $department
 * @property CrmCustomerCombine[] $crmCustomerCombine
 * @property CrmCustomerCombine $crmCombine
 * @property CrmDepartment[] $combineDepartments
 * @property CustomerPublic $customerPublic
 * @property CrmOpportunity[] $opportunities
 * @property Order[] $orders
 * @property Company $company
 * @property CustomerTag $customerTag
 * @property Source $customerSource
 * @property BusinessSubject $businessSubject
 * @property CrmContacts $crmContacts
 */
class CrmCustomer extends \yii\db\ActiveRecord
{
    const CUSTOMER_LEVEL_DISABLED = 0; //无效客户
    const CUSTOMER_LEVEL_ACTIVE = 1; //有效客户

    const GENDER_MALE = 0; //男
    const GENDER_FEMALE = 1; //女

    const TYPE_SOURCE_BD = 1; //百度
    const TYPE_SOURCE_SLL = 2; //360
    const TYPE_SOURCE_HD = 3; //活动
    const TYPE_SOURCE_DT = 4; //地推
    const TYPE_SOURCE_DESC = 5; //客户介绍
    const TYPE_SOURCE_CHANNEL = 6; //合作渠道
    const TYPE_SOURCE_OLD = 7; //老客户复购
    const TYPE_SOURCE_YQ = 8; //其他搜索引擎
    const TYPE_SOURCE_TEL = 9; //400电话
    const TYPE_SOURCE_TQ = 10; //TQ线索
    const TYPE_SOURCE_ALIYUN = 11; //阿里云服务市场
    const TYPE_SOURCE_PUSH = 12; //自己推广
    const TYPE_SOURCE_FRANCHISER = 16; //加盟商推荐
    const TYPE_SOURCE_OTHER = 0; //其他方式
    const TYPE_SOURCE_LXB = 13; //离线宝
    const TYPE_SOURCE_JRTT = 14; //今日头条
    const TYPE_SOURCE_PARTNERSHIP = 15; //同行合作
    const TYPE_SOURCE_WK_EXTENSION = 17;//悟空财税推广咨询
    const TYPE_BRANCH_COMPANY = 18;//分公司业务
    const TYPE_SOURCE_TEL_CHANNEL = 19;//电销渠道
    const TYPE_INFORMATION_FLOW = 20;//信息流推广
    const TYPE_SMALL_CAN = 21;//小能线索
    const TYPE_SOURCE_TX = 22;//腾讯创业平台


    const GET_WAY_CRM_INPUT = 0; //CRM录入
    const GET_WAY_REGISTER = 1; //自动注册

    const RECEIVE_DISABLED = 0; //未转入
    const RECEIVE_ACTIVE = 1; //已转入

    const REGISTER_MODE_CRM = 2;  // crm录入
    const REGISTER_MODE_ADD = 1;  // 后台新增
    const REGISTER_MODE_USER = 0; // 自主注册

    //客户是否受保护
    const PROTECT_DISABLED = 0;//否
    const PROTECT_ACTIVE = 1;//是

    public $crm_customer_id;
    public $customer_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_customer}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'gender', 'level', 'source', 'province_id', 'city_id', 'district_id', 'get_way','administrator_id', 'is_receive', 'company_id', 'department_id', 'creator_id', 'updater_id', 'created_at', 'updated_at', 'last_record', 'operation_time', 'last_record_creator_id', 'last_operation_creator_id', 'is_protect', 'customer_public_id','extract_time', 'move_public_time','is_share','next_record','distribution_time','transfer_time'], 'integer'],
            [['street', 'remark', 'last_record_creator_name', 'last_operation_creator_name','crm_customer_id','abandon_reason','customer_id','administrator_name'], 'string'],
            [['crm_customer_id', 'administrator_id'],'required','on'=> 'remove'],
            [['customer_id','abandon_reason'],'required','on' => 'abandon'],
            [['customer_id'],'required','on' => 'protect'],
            [['creator_name', 'updater_name', 'last_record_creator_name', 'last_operation_creator_name'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 30],
            [['tel', 'caller', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 64],
            [['birthday'], 'string', 'max' => 10],
            [['qq', 'wechat'], 'string', 'max' => 20],
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
            'name' => '姓名',
            'gender' => '性别',
            'birthday' => '客户生日',
            'level' => '客户级别',
            'source' => '客户来源',
            'tel' => '办公电话',
            'caller' => '来电电话',
            'phone' => '手机号',
            'email' => '常用邮箱',
            'qq' => '个人QQ',
            'wechat' => '个人微信',
            'province_name' => '省份名称',
            'city_name' => '城市名称',
            'district_id' => '邮寄地址',
            'district_name' => '区县名称',
            'street' => '街道地址',
            'remark' => '信息备注',
            'get_way' => '获取方式',
            'administrator_id' => '客户负责人',
            'is_receive' => 'Is Receive',
            'company_id' => '所属公司',
            'department_id' => 'Department ID',
            'creator_id' => '',
            'creator_name' => 'Creator Name',
            'last_record' => '最后一次跟进时间',
            'last_record_creator_id' => 'Last Record Creator Id',
            'last_record_creator_name' => 'Last Record Creator Name',
            'operation_time' => 'Operation Time',
            'last_operation_creator_id' => 'Last Operation Creator Id',
            'last_operation_creator_name' => 'Last Operation Creator Name',
            'is_protect' => 'Is Protect',
            'customer_public_id' => 'Customer Public Id',
            'extract_time' => 'Extract Time',
            'move_public_time' => 'Move Public Time',
            'updater_id' => '',
            'updater_name' => '',
            'updated_at' => '',
            'abandon_reason' => '放弃原因'
        ];
    }

    public function getAddress()
    {
        return $this->province_name.$this->city_name.$this->district_name.$this->street;
    }

    public function getBusinessSubject()
    {
        return self::hasOne(BusinessSubject::className(), ['customer_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id'=>'administrator_id']);
    }

    public function getUserLoginLog()
    {
        return $this->hasMany(UserLoginLog::className(),['user_id'=>'user_id']);
    }

    public function getCrmContacts()
    {
        return $this->hasOne(CrmContacts::className(),['customer_id'=>'id']);

    }

    public function getDepartment()
    {
        return $this->hasOne(CrmDepartment::className(),['id'=>'department_id']);
    }

    public function getCombineDepartments()
    {
        return $this->hasMany(CrmDepartment::className(),['id'=>'department_id'])->via('crmCustomerCombine');
    }

    public function getCustomerPublic()
    {
        return $this->hasOne(CustomerPublic::className(), ['id' => 'customer_public_id']);
    }

    public function getOpportunities()
    {
        return $this->hasMany(CrmOpportunity::className(),['customer_id'=>'id']);
    }

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'user_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getCustomerTag()
    {
        return $this->hasOne(CustomerTag::className(), ['customer_id' => 'id']);
    }

    public function getCustomerSources()
    {
        return $this->hasOne(Source::className(), ['id' => 'tag_id']);
    }
    public function getCustomerSourcess()
    {
        return $this->hasOne(Source::className(), ['id' => 'source']);
    }

    public function getSourceName()
    {
//        $sourceList = static::getSourceList();
//        if($this->source != 999)
//        {
//            return isset($sourceList[$this->source]) ? $sourceList[$this->source] : '';
//        }
//        return null;
        $source = Source::find()->select(['name'])->where(['id' => $this->source])->asArray()->one();
        return $source ? $source['name'] : '';

    }

    public static function getSourceList()
    {
        //获取客户来源数据
        $customerSource = Source::find()->select(['id','name'])->orderBy(['sort' => SORT_ASC])->asArray()->all();
        $key = array_column($customerSource,'id');  //键
        $name = array_column($customerSource,'name');  //值
        $customerSource = array_combine($key,$name) ;
        return $customerSource;
//        return [
//            self::TYPE_SOURCE_TEL => '400电话',
//            self::TYPE_SOURCE_TQ => 'TQ线索',
//            self::TYPE_SMALL_CAN => '小能线索',
//            self::TYPE_SOURCE_LXB => '离线宝',
//            self::TYPE_SOURCE_ALIYUN => '阿里云服务市场',
//            self::TYPE_SOURCE_TX => '腾讯创业平台',
//            self::TYPE_SOURCE_OLD => '老客户复购',
//            self::TYPE_SOURCE_DESC => '客户介绍',
//            self::TYPE_SOURCE_PUSH => '自己推广',
//            self::TYPE_INFORMATION_FLOW => '信息流推广',
//            self::TYPE_SOURCE_CHANNEL => '合作渠道',
//            self::TYPE_SOURCE_TEL_CHANNEL => '电销渠道',
//            self::TYPE_SOURCE_HD => '活动',
//            self::TYPE_SOURCE_FRANCHISER => '加盟商推荐',
//            self::TYPE_SOURCE_WK_EXTENSION => '悟空财税推广咨询',
//            self::TYPE_SOURCE_PARTNERSHIP => '同行合作',
//            self::TYPE_SOURCE_BD => '百度',
//            self::TYPE_SOURCE_SLL => '360',
//            self::TYPE_SOURCE_JRTT => '今日头条',
//            self::TYPE_SOURCE_DT => '地推',
//            self::TYPE_SOURCE_YQ => '其他搜索引擎',
//            self::TYPE_BRANCH_COMPANY => '分公司业务',
//            self::TYPE_SOURCE_OTHER => '其他方式',
//        ];
    }

    public static function getSourceLists()
    {
        //获取客户来源数据
        $customerSource = Source::find()->select(['id','name'])->where(['status' => Source::STATUS_ACTIVE])->orderBy(['sort' => SORT_ASC])->asArray()->all();
        $key = array_column($customerSource,'id');  //键
        $name = array_column($customerSource,'name');  //值
        $customerSource = array_combine($key,$name) ;
        return $customerSource;
//        return [
//            self::TYPE_SOURCE_TEL => '400电话',
////            self::TYPE_SOURCE_TQ => 'TQ线索',
//            self::TYPE_SMALL_CAN => '小能线索',
//            self::TYPE_SOURCE_LXB => '离线宝',
//            self::TYPE_SOURCE_ALIYUN => '阿里云服务市场',
//            self::TYPE_SOURCE_TX => '腾讯创业平台',
//            self::TYPE_SOURCE_OLD => '老客户复购',
//            self::TYPE_SOURCE_DESC => '客户介绍',
//            self::TYPE_SOURCE_PUSH => '自己推广',
//            self::TYPE_INFORMATION_FLOW => '信息流推广',
//            self::TYPE_SOURCE_CHANNEL => '合作渠道',
//            self::TYPE_SOURCE_TEL_CHANNEL => '电销渠道',
//            self::TYPE_SOURCE_HD => '活动',
//            self::TYPE_SOURCE_FRANCHISER => '加盟商推荐',
//            self::TYPE_SOURCE_WK_EXTENSION => '悟空财税推广咨询',
//            self::TYPE_SOURCE_PARTNERSHIP => '同行合作',
//            self::TYPE_BRANCH_COMPANY => '分公司业务',
//            self::TYPE_SOURCE_OTHER => '其他方式',
//        ];
    }

    public static function getLevel()
    {
        return [
            self::CUSTOMER_LEVEL_DISABLED => '无效客户',
            self::CUSTOMER_LEVEL_ACTIVE => '有效客户',
        ];
    }

    public function getLevelName()
    {
        $level = self::getLevel();
        return $level[$this->level];
    }

    public function subRemark()
    {
        if(mb_strlen($this->remark) > 10)
        {
            return mb_substr($this->remark,0,10).'......';
        }
        return $this->remark;
    }

    public function getGenderName()
    {
        $genderList = static::getGenderList();
        return $genderList[$this->gender];
    }

    public static function getGenderList()
    {
        return [
            self::GENDER_MALE => '先生',
            self::GENDER_FEMALE => '女士',
        ];
    }

    public function getWayName()
    {
        $level = self::getWay();
        return $level[$this->get_way];
    }

    public static function getWay()
    {
        return [
            self::GET_WAY_CRM_INPUT => 'CRM录入',
            self::GET_WAY_REGISTER => '自动注册',
        ];
    }

    //跟进中商机数量,跟进中商机（不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
    public function getOpportunityNotDealCounts()
    {
        $key = 'getOpportunityNotDealCounts-customer-id-'.$this->id.'-'.CrmOpportunity::STATUS_NOT_DEAL;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(false === $count)
        {
            $count = CrmOpportunity::find()->where(['opportunity_public_id' => 0, 'customer_id' => $this->id,'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_NOT_DEAL])->count();
            $countRedisCache->set($key, $count,30);
        }
        return $count;
    }

    //申请中商机数量,（商机状态为申请中100%的商机）
    public function getOpportunityApplyCounts()
    {
        $key = 'getOpportunityApplyCounts-customer-id-'.$this->id.'-'.CrmOpportunity::STATUS_APPLY;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(false === $count)
        {
            $count = CrmOpportunity::find()->where(['opportunity_public_id' => 0, 'customer_id' => $this->id,'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_APPLY])->count();
            $countRedisCache->set($key, $count,30);
        }
        return $count;
    }

    //已成交商机数量
    public function getOpportunityDealCounts()
    {
        $key = 'getOpportunityDealCounts-customer-id-'.$this->id.'-'.CrmOpportunity::STATUS_DEAL;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(false === $count)
        {
            $count = CrmOpportunity::find()->where(['customer_id' => $this->id,'status' => CrmOpportunity::STATUS_DEAL])->count();
            $countRedisCache->set($key, $count ,30);
        }
        return $count;
    }

    //已失败商机数量
    public function getOpportunityFailedCounts()
    {
        $key = 'getOpportunityFailedCounts-customer-id-'.$this->id.'-'.CrmOpportunity::STATUS_FAIL;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(false === $count)
        {
            $count = CrmOpportunity::find()->where(['customer_id' => $this->id,'is_receive' => CrmOpportunity::RECEIVE_ACTIVE,'status' => CrmOpportunity::STATUS_FAIL])->count();
            $countRedisCache->set($key, $count,30);
        }
        return $count;
    }

    //待确认商机数量(（不在公海里面的商机，且是待确认商机状态的，且商机状态为20%、40%、60%、80%的商机）)
    public function getOpportunityNoReceiveCounts()
    {
        $key = 'getOpportunityNoReceiveCounts-customer-id-'.$this->id.'-'.CrmOpportunity::RECEIVE_DISABLED;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(false === $count)
        {
            $count = CrmOpportunity::find()->where(['opportunity_public_id' => 0, 'customer_id' => $this->id,'is_receive' => CrmOpportunity::RECEIVE_DISABLED])->count();
            $countRedisCache->set($key, $count ,30);
        }
        return $count;
    }

    //待提取商机数量(在公海里面的商机，且商机状态为20%、40%、60%、80%的商机)
    public function getOpportunityNoExtractCounts()
    {
        //根据要求，实时显示数量
        $key = 'getOpportunityNoExtractCounts-customer-id-'.$this->id.'-'.CrmOpportunity::RECEIVE_DISABLED;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(false === $count)
        {
            $count = CrmOpportunity::find()->select('customer_id,opportunity_public_id')->where(['customer_id' => $this->id])
                ->andWhere(['>', 'opportunity_public_id', 0])->count();
            $countRedisCache->set($key, $count,30);
        }
        return $count;
    }

    /**
     * 排除了当前商机后，客户对应的其他跟进中商机数量，甩出商机使用
     * @param CrmOpportunity $opportunity
     * @return int|string
     */
    public function getOpportunityFollowingCounts($opportunity)
    {
        $query = CrmOpportunity::find()
            ->andWhere(['customer_id' => $this->id, 'is_receive' => 1, 'administrator_id' => $opportunity->administrator_id])
            ->andWhere(['in', 'status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]])
            ->andWhere(['not in', 'id',$opportunity->id])
            ->andWhere(['=', 'opportunity_public_id', 0]);
        return $query->count() ? $query->count() : 0;
    }

    /**
     * @param $administrator Administrator
     * @return boolean
     */
    public function isSubFor($administrator)
    {
        if(null == $administrator->department) return false;
        if($administrator->department->leader_id != $administrator->id && !$administrator->isDepartmentManager()) return false;
        if($this->department_id == $administrator->department_id) return true;
        return 0 < CrmDepartment::find()->where("path like '". $administrator->department->path."-%'")
                ->andWhere(['id' => $this->department_id])->count();
    }

    /**
     * 我待确认的客户数
     * @param Administrator $administrator
     * @return int|string
     */
    public static function countNeedConfirm($administrator)
    {
        $key = 'countNeedConfirm-administrator-id-'.$administrator->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmCustomer::find()->alias('c');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['c.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['c.administrator_id' => $administrator->id,'c.is_receive' => CrmCustomer::RECEIVE_DISABLED]);
            $query->andWhere(['c.customer_public_id' => 0]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * 我下属待确认的客户数
     * @param Administrator $administrator
     * @return int|string
     */
    public static function countSubNeedConfirm($administrator)
    {
        $key = 'countSubNeedConfirm-administrator-id-'.$administrator->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = 0;
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query = CrmCustomer::find()->alias('c');
//                if($administrator->isBelongCompany() && $administrator->company_id)
//                {
//                    $query->andWhere(['c.company_id' => $administrator->company_id]);
//                }
                $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_DISABLED]);
//                $query->joinWith(['department d']);
//                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->leftJoin("(SELECT id,path FROM ".CrmDepartment::tableName()." WHERE path LIKE '".$administrator->department->path."-%') as d",'d.id = c.department_id');
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id],['c.department_id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'c.administrator_id', [$administrator->id]]);
                $query->andWhere(['c.customer_public_id' => 0]);
                $count = $query->count();
                $countRedisCache->set($key,$count,30);
            }
        }
        return $count;
    }

    /**
     * 我无效的客户
     * @param Administrator $administrator
     * @return int|string
     */
    public static function countInvalid($administrator)
    {
        $key = 'countInvalid-administrator-id-'.$administrator->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmCustomer::find()->alias('c');
//            $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_ACTIVE, 'c.administrator_id' => $administrator->id]);
//            $query->joinWith(['crmCustomerCombine cc']);
            $query->leftJoin("(SELECT administrator_id,level,customer_id FROM ".CrmCustomerCombine::tableName()." WHERE administrator_id = ".$administrator->id." AND level = ".CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED.") as cc",'cc.customer_id = c.id');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['c.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['c.customer_public_id' => 0]);
            $query->andWhere(['cc.administrator_id' => $administrator->id,'cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * 我有效的客户数
     * @param Administrator $administrator
     * @return int|string
     */
    public static function countEffective($administrator)
    {
        $key = 'countEffective-administrator-id-'.$administrator->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmCustomer::find()->alias('c');
//            $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_ACTIVE, 'c.administrator_id' => $administrator->id]);
//            $query->joinWith(['crmCustomerCombine cc']);
            $query->leftJoin("(SELECT administrator_id,level,customer_id FROM ".CrmCustomerCombine::tableName()." WHERE administrator_id = ".$administrator->id." AND level = ".CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE.") as cc",'cc.customer_id = c.id');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['c.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['c.customer_public_id' => 0]);
            $query->andWhere(['or',['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE], ['cc.administrator_id' => $administrator->id]]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * 我下属负责的客户数
     * @param Administrator $administrator
     * @return int|string
     */
    public static function countSubResponsible($administrator)
    {
        $key = 'countSubResponsible-administrator-id-'.$administrator->id;
        /** @var Cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = 0;
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query = CrmCustomer::find()->alias('c');
                $query->joinWith(['department d']);
                $query->joinWith(['crmCustomerCombine cc']);
                $query->joinWith(['combineDepartments cd']);
//                if($administrator->isBelongCompany() && $administrator->company_id)
//                {
//                    $query->andWhere(['c.company_id' => $administrator->company_id]);
//                }
                $query->andWhere(['or', 'd.path like :path', ['cc.department_id' => $administrator->department_id], 'cd.path like :path'], [':path' => $administrator->department->path.'-%']);
                $query->andWhere(['not in', 'cc.administrator_id', [$administrator->id]]);
                $query->andWhere(['c.customer_public_id' => 0]);
                $query->groupBy(['c.id']);
                $count = $query->count();
            }
        }
        return $count;
    }

    /**
     * 判断一个用户是否属于客户合作人员
     * @param Administrator $administrator
     * @return boolean
     */
    public function isCombine($administrator)
    {
        /** @var CrmCustomerCombine $c */
        return CrmCustomerCombine::find()->where(['customer_id' => $this->id,
            'administrator_id' => $administrator->id])->count() > 0;
    }

    /**
     * 判断一个用户是否属于客户负责人
     * @param Administrator $administrator
     * @return boolean
     */
    public function isPrincipal($administrator)
    {
        return $this->administrator_id == $administrator->id;
    }

    public function isReceive()
    {
        return $this->is_receive == self::RECEIVE_ACTIVE;
    }

    /**
     * 获取合作人信息
     * @param Administrator $administrator
     * @return array|CrmCustomerCombine|null|ActiveRecord
     */
    public function getCombine($administrator)
    {
        return CrmCustomerCombine::find()->where(['customer_id' => $this->id, 'administrator_id' => $administrator->id])->one();
    }

    public function getCrmCustomerCombine()
    {
//        return $this->hasOne(CrmCustomerCombine::className(),['customer_id' => 'id', 'administrator_id' => 'administrator_id']);
        return $this->hasMany(CrmCustomerCombine::className(),['customer_id' => 'id']);
    }

    public function getCrmCombine()
    {
        return $this->hasOne(CrmCustomerCombine::className(),['customer_id' => 'id', 'administrator_id' => 'administrator_id']);
    }

    public function isRegister()
    {
        return $this->user && $this->user->isRegister();
    }

    public function getRegisterMode()
    {
        if(!$this->user || !$this->user->isRegister()) return 2;
        return $this->user->register_mode;
    }

    public function getRegisterModeName()
    {
        if($this->getRegisterMode() == self::REGISTER_MODE_CRM) return 'CRM录入';
        if($this->getRegisterMode() == self::REGISTER_MODE_USER) return '自主注册';
        return '后台新增';
    }

    public function isProtect()
    {
        return $this->is_protect == self::PROTECT_ACTIVE;
    }

    public function isPublic()
    {
        return self::find()->where(['id' => $this->id])->andWhere(['>', 'customer_public_id', 0])->one();
    }

    public function hasCustomerPublic()
    {
        return CustomerPublic::find()->where(['company_id' => $this->company_id])->one();
    }

    public function getLastRecord()
    {
        return CrmCustomerLog::find()->where(['customer_id' => $this->id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])->orderBy(['created_at'=>SORT_DESC])->one();
    }

    public static  function customer($range, $company_id = 0)
    {
        if(empty($range))
        {
            $range = 'all';
        }

        $query = CrmCustomer::find()->alias('c')->andWhere(['=', 'c.customer_public_id', 0]);

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        // sub_need_confirm
        if($range == 'sub_need_confirm')
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_DISABLED]);
                $query->joinWith(['department d']);
//                $query->leftJoin("(SELECT id,path FROM ".CrmDepartment::tableName()." WHERE path LIKE '".$administrator->department->path."-%') as d",'d.id = c.department_id');
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'c.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($range == 'effective')
        {
            $query->joinWith(['crmCustomerCombine cc']);
            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE, 'cc.administrator_id' => $administrator->id]);
//            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE, 'cc.administrator_id' => $administratorId]);
        }
        else if($range == 'sub')
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->joinWith(['department d']);
                $query->joinWith(['crmCustomerCombine cc']);
                $query->joinWith(['combineDepartments cd']);
                $query->andWhere(['or', 'd.path like :path', ['cc.department_id' => $administrator->department_id], 'cd.path like :path'], [':path' => $administrator->department->path.'-%']);
                $query->andWhere(['not in', 'cc.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($range == 'invalid')
        {
            $query->joinWith(['crmCustomerCombine cc']);
            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED, 'cc.administrator_id' => $administrator->id]);
        }
        else if($range == 'all')
        {
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
                $query->andWhere(['c.company_id' => $administrator->company_id]);
            }
        }
        else
        {
            $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_DISABLED, 'c.administrator_id' => $administrator->id]);
        }

        if($company_id)
        {
            $query->andWhere(['c.company_id' => $company_id]);
        }

        $query->orderBy(['c.created_at' => SORT_DESC]);
        $query->groupBy(['c.id']);
        $query = $query->asArray()->all();
        return $query;
    }

    public function canRelease()
    {
        if($this->last_record > 0)
        {
            $customerPublic = $this->hasCustomerPublic();
            if(null != $customerPublic && $customerPublic->release_time > 0)
            {
                $time = Holidays::workDay($this->last_record, $customerPublic->release_time);
//                if($this->extract_time + $customerPublic->release_time * 3600  >= time()) return false;
                if($time >= time()) return false;
            }
        }
        return true;
    }

    public function getAdministratorName()
    {
        //优化查询，减少内存使用
        $administrator = Administrator::find()->select('name')->where(['id' => $this->administrator_id])->one();
        return $administrator ? $administrator->name : '--';
        //return $this->administrator ? $this->administrator->name : '--';
    }

    public function getAdministratorLevelName()
    {
        //优化查询，减少内存使用
        $crmCombine = CrmCustomerCombine::find()->select('level')->where(['customer_id' => $this->id, 'administrator_id' => $this->administrator_id])->one();
        return $crmCombine ? $crmCombine->getLevelName() : '--';
        //return $this->crmCombine ? $this->crmCombine->getLevelName() : '--';
    }

    public function getCompanyName()
    {
        //优化查询，减少内存使用
        $company = Company::find()->select('name')->where(['id' => $this->company_id])->one();
        return $company ? $company->name : '--';
        //return $this->company ? $this->company->name : '--';
    }

    public function getDepartmentName()
    {
        //优化查询，减少内存使用
        $department = CrmDepartment::find()->select('name')->where(['id'=>$this->department_id])->one();
        return $department ? $department->name : '--';
        //return $this->department ? $this->department->name : '--';
    }

    public function getCustomerSource()
    {
        return Source::find()->where(['id' => $this->source])->one();
    }

    public function getCrmCustomerCombines()
    {
        //优化查询，减少内存使用
        $crmCustomerCombines = CrmCustomerCombine::find()->select(['administrator_id', 'company_id', 'department_id', 'level'])->where(['customer_id' => $this->id])->all();
        if($crmCustomerCombines)
        {
            $val = '';
            $count = count($crmCustomerCombines);
            /** @var CrmCustomerCombine $crmCustomerCombine */
            foreach ($crmCustomerCombines as $key => $crmCustomerCombine)
            {
//                $administratorName = $crmCustomerCombine->administrator ? $crmCustomerCombine->administrator->name : '--';
//                $companyName = $crmCustomerCombine->company ? $crmCustomerCombine->company->name : '--';
//                $departmentName = $crmCustomerCombine->crmDepartment ? $crmCustomerCombine->crmDepartment->name : '--';
                $administrator = Administrator::find()->select(['name'])->where(['id' => $crmCustomerCombine->administrator_id])->one();
                $administratorName = $administrator ? $administrator->name : '--';
                $company = Company::find()->select(['name'])->where(['id' => $crmCustomerCombine->company_id])->one();
                $companyName = $company ? $company->name : '--';
                $department = CrmDepartment::find()->select(['name'])->where(['id' => $crmCustomerCombine->department_id])->one();
                $departmentName = $department ? $department->name : '--';

                if($key != $count -1)
                {
                    $val .= $administratorName.'/'.$crmCustomerCombine->getLevelName().'/'.$companyName.'/'.$departmentName."&";
                }
                else
                {
                    $val .= $administratorName.'/'.$crmCustomerCombine->getLevelName().'/'.$companyName.'/'.$departmentName;
                }
                unset($administrator);
                unset($administratorName);
                unset($company);
                unset($companyName);
                unset($department);
                unset($departmentName);
            }
            return $val;
        }
        return '';
    }

    public function getLastRecordInfo()
    {
        $lastRecord = CrmCustomerLog::find()->select(['creator_name', 'created_at'])->where(['customer_id' => $this->id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])->orderBy(['created_at'=>SORT_DESC])->one();
        $data = [];
        if(!empty($this->last_record_creator_name))
        {
            $data['name'] = $this->last_record_creator_name;
        }
        else
        {
            $data['name'] = $lastRecord ? $lastRecord->creator_name : '--';
        }

        if(!empty($this->last_record))
        {
            $data['time'] = Yii::$app->formatter->asDatetime($this->last_record);
        }
        else
        {
            $data['time'] = $lastRecord ? Yii::$app->formatter->asDatetime($lastRecord->created_at) : '--';
        }
        return $data;
    }

    public function getTag()
    {
        /** @var Tag $tag */
        $tag = (new Query())
            ->select('t.name')
            ->from(['t' => Tag::tableName()])
            ->innerJoin(['ct' => CustomerTag::tableName()], 'ct.tag_id = t.id')
            ->where(['ct.customer_id' => $this->id])->one();
        return $tag ? $tag['name'] : '--';
    }
}
