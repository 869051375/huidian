<?php

namespace common\models;

use backend\models\ClueSearch;
use backend\modules\niche\models\CustomerExchangeList;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "crm_clue".
 *
 * @property integer $id
 * @property string $name
 * @property string $company_name
 * @property integer $source_id
 * @property integer $channel_id
 * @property string $mobile
 * @property integer $gander
 * @property string $tel
 * @property string $email
 * @property string $qq
 * @property string $wechat
 * @property string $call
 * @property string $birthday
 * @property string $department
 * @property string $position
 * @property string $native_place
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property string $address
 * @property string $interest
 * @property string $remark
 * @property integer $label_id
 * @property integer $status
 * @property integer $is_new
 * @property integer $follow_status
 * @property integer $invalid_time
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $business_subject_id
 * @property integer $last_record
 * @property integer $last_record_creator_id
 * @property string $last_record_creator_name
 * @property integer $next_follow_time
 * @property integer $clue_public_id
 * @property integer $extract_time
 * @property integer $move_public_time
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $clue_id
 * @property integer $is_repeat
 * @property integer $recovery_at
 * @property integer $created_at_start
 * @property integer $created_at_end
 * @property integer $updated_at_start
 * @property integer $updated_at_end
 * @property integer $transfer_at_start
 * @property integer $transfer_at_end
 * @property integer $recovery_at_start
 * @property integer $recovery_at_end
 * @property integer $shift_at
 * @property integer $distribution_at
 * @property integer $user_id
 * @property Administrator $administrator
 * @property CrmDepartment $departments
 * @property integer $transfer_at
 * @property Channel $channel
 * @property Source $source
 * @property ClueRecord $recordDesc
 * @property User $user
 * @property integer $is_abandon
 *
 *
 * @property Tag $tag
 */
class CrmClue extends \yii\db\ActiveRecord
{

    public $clue_id;
    public $clue;
    public $transfer;
    public $submit_type;
    public $page;
    public $page_num;
    public $label_name;
    public $limit;
    public $created_at_start;
    public $created_at_end;
    public $updated_at_start;
    public $updated_at_end;
    public $transfer_at_start;
    public $transfer_at_end;
    public $recovery_at_start;
    public $recovery_at_end;

    const CLUE_TYPE_MY_CLUE = 'my_clue';                            //我的线索
    const CLUE_TYPE_MY_SUBORDINATE = 'my_subordinate';              //我的下属
    const CLUE_TYPE_WHOLE = 'whole';                                //全部数据 我的下属+我的线索
    const CLUE_TYPE_CHANGE = 'my_change';                           //我的已转换的线索
    const CLUE_TYPE_CHANGE_SUBORDINATE = 'my_change_subordinate';   //我的下属已转换的线索
    const CLUE_TYPE_CHANGE_WHOLE = 'change_whole';                  //全部已转换的线索  我的下属+我的线索

    //我的线索数据组
    const CLUE_TYPE = [
        self::CLUE_TYPE_MY_CLUE,
        self::CLUE_TYPE_MY_SUBORDINATE,
        self::CLUE_TYPE_WHOLE,
        self::CLUE_TYPE_CHANGE,
        self::CLUE_TYPE_CHANGE_SUBORDINATE,
        self::CLUE_TYPE_CHANGE_WHOLE,
    ];

    //提交类型
    const SUBMIT_TYPE_ME = 'me';        //我的线索提交
    const SUBMIT_TYPE_PUBLIC = 'public';//公海提交

    //跟进状态
    const FOLLOW_STATUS_UNRELATED = 0;  //未处理
    const FOLLOW_STATUS_CONTACT = 1;    //已联系
    const FOLLOW_STATUS_CLOSE  = 4;     //已关闭

    //跟进状态数据组
    const FOLLOW_STATUS = [
        [
            'id' => self::FOLLOW_STATUS_UNRELATED,
            'name' => '未处理'
        ],
        [
            'id' => self::FOLLOW_STATUS_CONTACT,
            'name' => '已联系'
        ],
        [
            'id' => self::FOLLOW_STATUS_CLOSE,
            'name' => '已关闭'
        ],
    ];

    //线索主状态
    const STATUS_INIT = 0;          //自建
    const STATUS_TRANSFER = 1;      //转移
    const STATUS_DISTRIBUTION = 2;  //分配
    const STATUS_EXTRACT = 3;       //提取
    const STATUS_DISCARDED = 4;     //废弃
    const STATUS_PUBLIC = 5;        //公海
    const STATUS_REMOVE = -1;       //删除

    //是否是新状态
    const IS_NEW_YES = 1;           //是新状态
    const IS_NEW_NO = 0;            //不是新状态

    //有效的状态
    const STATUS_EFFECTIVE =
        [
           self:: STATUS_INIT,
           self:: STATUS_TRANSFER,
           self:: STATUS_DISTRIBUTION,
           self:: STATUS_EXTRACT,
        ];

    //自定义筛选
    const CUSTOM_FILTER = [
        [
            'key'=>'name',
            'val' => '姓名'
        ],
        [
            'key'=>'company_name',
            'val' => '公司名称'
        ],
        [
            'key'=>'mobile',
            'val' => '手机'
        ],
        [
            'key'=>'tel',
            'val' => '电话'
        ],
        [
            'key'=>'email',
            'val' => '邮箱'
        ],
        [
            'key'=>'qq',
            'val' => 'QQ'
        ],
        [
            'key'=>'wechat',
            'val' => '微信'
        ],
        [
            'key'=>'call',
            'val' => '来电电话'
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_clue';
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
            [[ 'source_id','page_num','user_id','page','limit','is_repeat','recovery_at','transfer_at','gander', 'channel_id', 'province_id', 'city_id', 'district_id', 'label_id', 'status', 'is_new', 'follow_status', 'invalid_time', 'administrator_id', 'company_id', 'department_id', 'business_subject_id', 'last_record', 'last_record_creator_id', 'next_follow_time', 'clue_public_id', 'extract_time', 'move_public_time', 'creator_id', 'updater_id', 'created_at', 'updated_at','is_abandon'], 'integer'],
            [[ 'administrator_name', 'last_record_creator_name', 'creator_name', 'updater_name','birthday'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 20, 'tooLong'=>'请输入正确的姓名'],
            [['company_name'], 'string', 'max' => 100, 'tooLong'=>'请输入正确的公司名称'],
//            [['wechat'], 'string', 'max' => 25, 'tooLong'=>'请输入正确的微信'],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{5,25})+$/', 'message' => '请输入正确的微信'],
            [['mobile'], 'string', 'max' => 11,'tooLong'=>'请输入正确的手机号码'],
            [['tel'], 'string', 'max' => 15,'tooLong'=>'请输入正确的联系座机'],
            [['position'], 'string', 'max' => 50 ,'tooLong'=>'内容超过最长长度限制'],
            [['qq'], 'string', 'max' => 25,'tooLong'=>'请输入正确的QQ'],
            [['department'], 'string', 'max' => 50,'tooLong'=>'内容超过最长长度限制'],
            [['interest'], 'string', 'max' => 100,'tooLong'=>'内容超过最长长度限制'],
            [['native_place'], 'string', 'max' => 50,'tooLong'=>'内容超过最长长度限制'],
            [['address'], 'string', 'max' => 200,'tooLong'=>'请输入正确的联系地址'],
            [['remark'], 'string', 'max' => 200,'tooLong'=>'内容超过最大长度限制'],
            [['email'], 'string', 'max' => 100,'tooLong'=>'请输入正确的联系邮箱'],
            [['call'], 'string', 'max' => 20,'tooLong'=>'请输入正确的来电电话'],
            [[ 'province_name', 'city_name', 'district_name'], 'string', 'max' => 10],
            [[ 'mobile'], 'match', 'pattern' => '/^1\d{10}$/','message'=>'请输入正确的手机号码'],
            [[ 'qq'], 'match', 'pattern' => '/^[1-9]*[1-9][0-9]*$/','message'=>'请输入正确的QQ'],
            [[ 'tel'], 'match', 'pattern' => '/^([0-9]{3,4}-)?[0-9]{7,8}$/','message'=>'请输入正确的联系座机'],
            [[ 'email'], 'match', 'pattern' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/','message'=>'请输入正确的联系邮箱'],
        ];
    }

    public function validateClueId()
    {
        $this->clue = CrmClue::findOne($this->id);
        if ($this->clue == null){
            $this->addError('id','查询的线索不存在');
        }
    }


    /**
     * @return array
     *
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company_name' => 'Company Name',
            'source_id' => 'Source ID',
            'channel_id' => 'Channel ID',
            'mobile' => 'Mobile',
            'gander' => 'Gander',
            'tel' => 'Tel',
            'email' => 'Email',
            'qq' => 'Qq',
            'wechat' => 'Wechat',
            'call' => 'Call',
            'birthday' => 'Birthday',
            'department' => 'Department',
            'position' => 'Position',
            'native_place' => 'Native Place',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'address' => 'Address',
            'interest' => 'Interest',
            'remark' => 'Remark',
            'label_id' => 'Label ID',
            'status' => 'Status',
            'is_new' => 'Is New',
            'follow_status' => 'Follow Status',
            'invalid_time' => 'Invalid Time',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
            'business_subject_id' => 'Business Subject ID',
            'last_record' => 'Last Record',
            'last_record_creator_id' => 'Last Record Creator ID',
            'last_record_creator_name' => 'Last Record Creator Name',
            'next_follow_time' => 'Next Follow Time',
            'clue_public_id' => 'Clue Public ID',
            'extract_time' => 'Extract Time',
            'move_public_time' => 'Move Public Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created_at',
            'updated_at' => 'Updated At',
            'transfer_at' => '转换时间',
            'recovery_at' => '回收时间',
            'is_abandon' => '是否为放弃',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return self::hasOne(Tag::className(), ['id' => 'label_id']);
    }

    public function getRecord()
    {
        return self::hasOne(ClueRecord::className(), ['clue_id' => 'id']);
    }

    public function getRecordDesc()
    {
        return ClueRecord::find()->where(['clue_id'=>$this->id])->orderBy('created_at desc')->one();
    }


    public function getChannel()
    {
        return self::hasOne(Channel::className(), ['id' => 'channel_id']);
    }

    public function getSource()
    {
        return self::hasOne(Source::className(), ['id' => 'source_id']);
    }

    public function getAdministrator()
    {
        return self::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDepartments()
    {
        return self::hasOne(CrmDepartment::className(), ['id' => 'department_id']);
    }

    public function getCluePublic()
    {
        return self::hasOne(CluePublic::className(), ['id' => 'clue_public_id']);
    }

    /**
     * @param $data
     * @return mixed
     * 条件查询线索列表
     */
    public function getList($data)
    {
        $this->name = $data['name'];

        $this->load($data);

        if (!$this->validate()){
            echo 1;die;
        }

//        $count = CrmClue::find()->count();
        $query = CrmClue::find()->asArray()
                    ->JoinWith('tag a')
                    ->where(['in','administrator_id',$data['administrator_id']]);
//                    ->where(['administrator_id' => $data['administrator_id']]);
        //如果有标签查询
        if (isset($data['tag']))
        {
            $query->andWhere(['label_id'=>$data['tag']]);
        }
        //按照所属部门查询
        if (isset($data['department_id']))
        {
            $query->andWhere(['department_id' => $data['department_id']]);
        }
        //按照创建人查询
        if (isset($data['creator_id']))
        {
            $query->andWhere(['creator_id' => $data['creator_id']]);
        }
        //根据跟进状态查询
        if (isset($data['follow_status']))
        {
            if ($data['follow_status'] == self::FOLLOW_STATUS_UNRELATED)
            {
                $follow_status = self::FOLLOW_STATUS_UNRELATED;
            }
            elseif ($data['follow_status'] == self::FOLLOW_STATUS_CONTACT)
            {
                $follow_status = self::FOLLOW_STATUS_CONTACT;
            }
            elseif ($data['follow_status'] == self::FOLLOW_STATUS_CLOSE)
            {
                $follow_status = self::FOLLOW_STATUS_CLOSE;
            }
            else
            {
                $this->addError('follow_status','跟进状态不存在');
                return false;
            }
            $query->andWhere(['follow_status' => $follow_status]);
        }
        //按照创建时间查询
        if (isset($data['created_at']))
        {
            $query->andWhere(['>=','created_at', $data['created_at'][0]]);
            $query->andWhere(['<=','created_at', $data['created_at'][1]]);
        }

        //按照最后修改时间查询
        if (isset($data['updated_at']))
        {
            $query->andWhere(['>=','updated_at', $data['updated_at'][0]]);
            $query->andWhere(['<=','updated_at', $data['updated_at'][1]]);
        }

        //按照姓名查询
        if (isset($data['name']))
        {
            $query->andWhere(['crm_clue.name'=>$data['name']]);
        }

        //按照公司名称查询
        if (isset($data['company_name']))
        {
            $query->andWhere(['crm_clue.company_name'=>$data['company_name']]);
        }

        //按照手机号查询
        if (isset($data['mobile']))
        {
            $query->andWhere(['crm_clue.mobile'=>$data['mobile']]);
        }

        //按照电话号查询
        if (isset($data['tel']))
        {
            $query->andWhere(['crm_clue.tel'=>$data['tel']]);
        }

        //按照邮箱查询
        if (isset($data['email']))
        {
            $query->andWhere(['crm_clue.email'=>$data['email']]);
        }

        //按照QQ查询
        if (isset($data['qq']))
        {
            $query->andWhere(['crm_clue.qq'=>$data['qq']]);
        }

        //按照微信号查询
        if (isset($data['wechat']))
        {
            $query->andWhere(['crm_clue.wechat'=>$data['wechat']]);
        }

        //按照来电电话查询
        if (isset($data['call']))
        {
            $query->andWhere(['crm_clue.call'=>$data['call']]);
        }

        //按照线索来源查询
        if (isset($data['source_id']))
        {
            $query->andWhere(['crm_clue.source_id'=>$data['source_id']]);
        }

        //按照线索来源渠道查询
        if (isset($data['channel_id']))
        {
            $query->andWhere(['crm_clue.channel_id'=>$data['channel_id']]);
        }


        $query->limit($data['page'])->offset($data['offset']);

        var_dump($query->all());die;
        return  $query->createCommand()->sql;

    }

    //条件查询线索公海
    public function getPublicList($count = null){

        $query = CrmClue::find();
        if ($this->clue_public_id != 0 || $this->clue_public_id != ''){
            $query->where(['in','clue_public_id',$this->clue_public_id]);
        }

        if (isset($this->label_id) && $this->label_id!= ''){

            $query->andWhere(['label_id'=>$this->label_id]);
        }

        if (isset($this->administrator_id) && $this->administrator_id!= ''){
            $query->andWhere(['administrator_id'=>$this->administrator_id]);
        }

        if (isset($this->department_id) && $this->department_id!= ''){
            $query->andWhere(['department_id'=>$this->department_id]);
        }

        if (isset($this->creator_id) && $this->creator_id!= ''){
            $query->andWhere(['creator_id'=>$this->creator_id]);
        }

        if (isset($this->follow_status) && $this->follow_status!= ''){
            $query->andWhere(['follow_status'=>$this->follow_status]);
        }

//        if (isset($this->created_at_start) && $this->created_at[0]!= ''){
//            $query->andWhere(['>=','created_at', strtotime($this->created_at[0])]);
//            $query->andWhere(['<=','created_at', strtotime($this->created_at[1])]);
//        }

        //如果创建时间开始时间存在的时候
        if (isset($this->created_at_start) && $this->created_at_start != '')
        {
            if (isset($this->created_at_end) && $this->created_at_end != '')
            {
                $query->andWhere(['>=','created_at', strtotime($this->created_at_start)]);
                $query->andWhere(['<=','created_at', strtotime($this->created_at_end)+86400-1]);
            }
            else
            {
                $query->andWhere(['>=','created_at', strtotime($this->created_at_start)]);
            }
        }

        //如果创建时间结束时间存在的时候
        if (isset($this->created_at_end) && $this->created_at_end != '')
        {
            if (isset($this->created_at_start) && $this->created_at_start != '')
            {
                $query->andWhere(['>=','created_at', strtotime($this->created_at_start)]);
                $query->andWhere(['<=','created_at', strtotime($this->created_at_end)+86400-1]);
            }
            else
            {
                $query->andWhere(['<=','created_at', strtotime($this->created_at_end)+86400-1]);
            }
        }

//        if (isset($this->recovery_at) && $this->recovery_at[0]!= ''){
//            $query->andWhere(['>=','recovery_at', strtotime($this->recovery_at[0])]);
//            $query->andWhere(['<=','recovery_at', strtotime($this->recovery_at[1])]);
//        }

        //如果回收时间开始时间存在的时候
        if (isset($this->recovery_at_start) && $this->recovery_at_start != '')
        {
            if (isset($this->recovery_at_end) && $this->recovery_at_end != '')
            {
                $query->andWhere(['>=','recovery_at', strtotime($this->recovery_at_start)]);
                $query->andWhere(['<=','recovery_at', strtotime($this->recovery_at_end)+86400-1]);
            }
            else
            {
                $query->andWhere(['>=','recovery_at', strtotime($this->recovery_at_start)]);
            }
        }

        //如果回收时间结束时间存在的时候
        if (isset($this->recovery_at_end) && $this->recovery_at_end != '')
        {
            if (isset($this->recovery_at_start) && $this->recovery_at_start != '')
            {
                $query->andWhere(['>=','recovery_at', strtotime($this->recovery_at_start)]);
                $query->andWhere(['<=','recovery_at', strtotime($this->recovery_at_end)+86400-1]);
            }
            else
            {
                $query->andWhere(['<=','recovery_at', strtotime($this->recovery_at_end)+86400-1]);
            }
        }

//        if (isset($this->updated_at) && $this->updated_at[0]!= ''){
//            $query->andWhere(['>=','updated_at', strtotime($this->updated_at[0])]);
//            $query->andWhere(['<=','updated_at', strtotime($this->updated_at[1])]);
//        }

        //如果最后修改时间开始时间存在的时候
        if (isset($this->updated_at_start) && $this->updated_at_start != '')
        {
            if (isset($this->updated_at_end) && $this->updated_at_end != '')
            {
                $query->andWhere(['>=','updated_at', strtotime($this->updated_at_start)]);
                $query->andWhere(['<=','updated_at', strtotime($this->updated_at_end)+86400-1]);
            }
            else
            {
                $query->andWhere(['>=','updated_at', strtotime($this->updated_at_start)]);
            }
        }

        //如果最后修改时间结束时间存在的时候
        if (isset($this->updated_at_end) && $this->updated_at_end != '')
        {
            if (isset($this->updated_at_start) && $this->updated_at_start != '')
            {
                $query->andWhere(['>=','updated_at', strtotime($this->updated_at_start)]);
                $query->andWhere(['<=','updated_at', strtotime($this->updated_at_end)+86400-1]);
            }
            else
            {
                $query->andWhere(['<=','updated_at', strtotime($this->updated_at_end)+86400-1]);
            }
        }


        if (isset($this->name) && $this->name != ''){
            $query->andWhere(['LIKE','name',$this->name]);
        }


        if (isset($this->company_name) && $this->company_name != ''){
            $query->andWhere(['LIKE','company_name',$this->company_name]);
        }

        if (isset($this->mobile) && $this->mobile != ''){
            $query->andWhere(['LIKE','mobile',$this->mobile]);
        }

        if (isset($this->tel) && $this->tel != ''){
            $query->andWhere(['LIKE','tel',$this->tel]);
        }

        if (isset($this->email) && $this->email != ''){
            $query->andWhere(['LIKE','email',$this->email]);
        }

        if (isset($this->qq) && $this->qq != ''){
            $query->andWhere(['LIKE','qq',$this->qq]);
        }

        if (isset($this->wechat) && $this->wechat != ''){
            $query->andWhere(['LIKE','wechat',$this->wechat]);
        }

        if (isset($this->call) && $this->call != ''){
            $query->andWhere(['LIKE','call',$this->call]);
        }

        if (isset($this->source_id) && $this->source_id != ''){
            $query->andWhere(['source_id'=>$this->source_id]);
        }

        if (isset($this->channel_id) && $this->channel_id != ''){
            $query->andWhere(['channel_id'=>$this->channel_id]);
        }

        if (isset($this->is_new) && $this->is_new != ''){
            $query->andWhere(['is_new'=>$this->is_new]);
        }

        if (isset($this->is_abandon) && $this->is_abandon != ''){
            $query->andWhere(['is_abandon'=>$this->is_abandon]);
        }

        if (isset($this->status) && $this->status != ''){
            $query->andWhere(['status'=>$this->status]);
        }


        if (empty($count)){
            $query->andWhere(['in','status',[self::STATUS_DISCARDED,self::STATUS_PUBLIC]])
                ->limit($this->page_num)
                ->offset($this->page);
            return $query->orderBy('recovery_at desc')->all();
        }else{
            return $query->andWhere(['in','status',[self::STATUS_DISCARDED,self::STATUS_PUBLIC]])->count();
        }

    }

    /**
     * @return bool
     * 给线索添加标签
     */
    public function clueAddTag(){
        /** @var CrmClue $clue_one */
        $clue_one = CrmClue::find()->where(['id'=>$this->id])->one();
        if (empty($clue_one)){
            return $this->addError('id','线索不存在');
        }
        if (!$this->validate()){
            return false;
        }
        $clue_one->label_id = $this->label_id;
        $user = Yii::$app->user->identity;
        $clue_one->updater_id = $user->id;              //最后修改人ID
        $clue_one->updater_name = $user->name;          //最后修改人名字
        $clue_one->updated_at = time();                 //最后修改时间


        return $clue_one->save(false);
    }

    /**
     * @return bool
     * 清除线索标签
     */
    public function ClueRemoveTag()
    {
        if (!$this->validate()){
            return false;
        }

        $this->clue->label_id = 0;
        $user = Yii::$app->user->identity;
        $this->clue->updater_id = $user->id;              //最后修改人ID
        $this->clue->updater_name = $user->name;          //最后修改人名字
        $this->clue->updated_at = time();                 //最后修改时间
        return $this->clue->save(false);
    }

    /**
     * @param $data
     * @return bool
     * 转移线索给其他负责人
     */
    public function transfer($data){
        /** @var CrmClue $transfer */
        $transfer = CrmClue::find()->where(['in','id',$data['clue_id']])->all();
        $count = 0;
        foreach ($transfer as $v){
            $v->administrator_id = $data['administrator_id'];
            $v->administrator_name = $data['administrator_name'];
            $v->status = self::STATUS_TRANSFER;
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $v->updater_id = $user->id;              //最后修改人ID
            $v->updater_name = $user->name;          //最后修改人名字
            $v->updated_at = time();                 //最后修改时间
            $v->shift_at = time();                  //转移时间
            $v->save();
            $count++;
            //统计埋点
            $niche = new CustomerExchangeList();
            $niche->clue(['id'=>$v->id,'from'=> $user->id,'administrator_id'=>$data['administrator_id'],'province_id'=> isset($v->province_id) ? $v->province_id : 0,'city_id'=> isset($v->city_id) ? $v->city_id : 0,'district_id' => isset($v->district_id) ? $v->district_id : 0,'source_id'=>isset($v->source_id) ? $v->source_id : 0,'channel_id'=>isset($v->channel_id) ? $v->channel_id : 0],'change');

        }
        return $count;
    }

    /**
     * @param $data
     * @return bool
     * 放弃线索到自己部门公海
     */
    public function abandon($id,$clue_public_id){

        $abandon = CrmClue::find()->where(['id'=>$id])->one();
        $abandon->clue_public_id = $clue_public_id;
        $abandon->status = self::STATUS_PUBLIC;
        $abandon->recovery_at = time();
        $abandon->is_abandon = 1;   //修改字段为是放弃
        $abandon->administrator_id = 0;   //修改字段为是放弃
        $abandon->administrator_name = '';   //修改字段为是放弃
        $user = Yii::$app->user->identity;
        $abandon->updater_id = $user->id;              //最后修改人ID
        $abandon->updater_name = $user->name;          //最后修改人名字
        $abandon->updated_at = time();                 //最后修改时间
        return $abandon->save(false);
    }

    /**
     * @param $data
     * @return bool
     * //废弃线索到自己部门公海
     */
    public function discarded($id,$clue_public_id)
    {
        $abandon = CrmClue::find()->where(['id'=>$id])->one();
        $abandon->clue_public_id = $clue_public_id;
        $abandon->status = self::STATUS_DISCARDED;
        $abandon->recovery_at = time();
        $abandon->administrator_id = 0;   //修改字段为是放弃
        $abandon->administrator_name = '';   //修改字段为是放弃
        $abandon->is_abandon = 0;
        $user = Yii::$app->user->identity;
        $abandon->updater_id = $user->id;              //最后修改人ID
        $abandon->updater_name = $user->name;          //最后修改人名字
        $abandon->updated_at = time();                 //最后修改时间
        return $abandon->save(false);
    }

    /**
     * @param $data
     * @return bool
     * 删除线索（逻辑删除）
     */
    public function remove($data)
    {
        $abandon = CrmClue::find()->where(['in','id',$data['clue_id']])->all();
        $count = 0;
        /** @var CrmClue $v */
        foreach ($abandon as $v)
        {
//            $v->clue_public_id = $data['clue_public_id'];
            $this->is_repeat($v->attributes,true);
            $v->status = self::STATUS_REMOVE;
            $user = Yii::$app->user->identity;
            $v->updater_id = $user->id;              //最后修改人ID
            $v->updater_name = $user->name;          //最后修改人名字
            $v->updated_at = time();                 //最后修改时间
            $v->save(false);
            $count++;
        }
        return $count;
    }

    /**
     * @param $administrator
     * @return array|bool|\yii\db\ActiveRecord[]
     * 查询下属部门包括本部门下的业务员
     */
    public function filterRole($administrator)
    {

        if($administrator->isCompany())
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $Department = CrmDepartment::find()->select('id')->where(['like','path',$administrator->department_id.'%',false])->all();
                if (!empty($Department)){
                    $Department_id = '';
                    foreach ($Department as $v){
                        $Department_id.=$v['id'].',';
                    }
                    $department_id = explode(',',substr($Department_id, 0, -1));
                }
                else
                {
                    $department_id = $administrator->department_id;
                }

                return Administrator::find()->select('id,name')->where(['in','department_id',$department_id])->andWhere(['not in','id',[$administrator->id]])->all();
            }
            else
            {
                return false;
            }
        }
        return false;
    }

    /**
     * @param $administrator
     * @return array|bool|ActiveRecord[]
     * 查询所属部门
     */
    public function getDepartment($administrator)
    {
        if($administrator->isCompany())
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $Department = CrmDepartment::find()->select('id,name')->where(['like','path',$administrator->department_id.'%',false])->all();
                if (empty($Department))
                {
                    return ['id'=>$administrator->department_id,'name'=>$administrator->department->name];
                }else
                {
                    return $Department;
                }
            }
            else
            {
                return false;
            }
        }
        return false;
    }

    //查询是否有重复   删除线索的时候 如果只有一条是重复的就把is_repeat 置为0
    public function is_repeat($data , $delete = false){
        $sql = '';
        $params = [];
        $fields = '*';
        if (!empty($data['company_name'])){
            $sql = 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE company_name=:company_name and status != -1';
            $params[':company_name'] = $data['company_name'];
        }

        if (isset($data['tel']) && $data['tel'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE tel=:tel and status != -1';
            $params[':tel'] = $data['tel'];
        };

        if (isset($data['mobile']) && $data['mobile'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE mobile=:mobile and status != -1';
            $params[':mobile'] = $data['mobile'];
        };

        if (isset($data['qq']) && $data['qq'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE qq=:qq and status != -1';
            $params[':qq'] = $data['qq'];
        };

        if (isset($data['email']) && $data['email'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE email=:email and status != -1';
            $params[':email'] = $data['email'];
        };

        if (isset($data['wechat']) && $data['wechat'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE wechat=:wechat and status != -1';
            $params[':wechat'] = $data['wechat'];
        };

        if (isset($data['call']) && $data['call'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE `call`=:call and status != -1';
            $params[':call'] = $data['call'];
        };

        if ($sql != ''){
            $data = CrmClue::findBySql($sql, $params)
                ->all();
            if (count($data) >= 1){
                foreach ($data as $item){
                    $item->is_repeat = 1;
                    $item->save();
                }
            }
            if ($delete){
                if ((count($data)-1) <= 1){
                    foreach ($data as $item){
                        $item->is_repeat = 0;
                        $item->save();
                    }
                }
            }
            return $data;
        }else{
            return [];
        }
    }


    //查重-电话号，手机号，QQ，邮箱，来电电话，微信
    public function repeat($data,$data_arr,$count = null){
        $sql = '';
        $params = [];
        $fields = '*';
        if (!empty($data['company_name'])){
            $sql = 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE company_name=:company_name and id !=:id and status != -1';
            $params[':company_name'] = $data['company_name'];
            $params[':id'] = $data_arr['clue_id'];
        }

        if (isset($data['tel']) && $data['tel'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE tel=:tel and id !=:id and status != -1';
            $params[':tel'] = $data['tel'];
            $params[':id'] = $data_arr['clue_id'];
        };

        if (isset($data['mobile']) && $data['mobile'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE mobile=:mobile and id !=:id and status != -1';
            $params[':mobile'] = $data['mobile'];
            $params[':id'] = $data_arr['clue_id'];
        };

        if (isset($data['qq']) && $data['qq'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE qq=:qq and id !=:id and status != -1';
            $params[':qq'] = $data['qq'];
            $params[':id'] = $data_arr['clue_id'];
        };

        if (isset($data['email']) && $data['email'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE email=:email and id !=:id and status != -1';
            $params[':email'] = $data['email'];
            $params[':id'] = $data_arr['clue_id'];
        };

        if (isset($data['wechat']) && $data['wechat'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE wechat=:wechat and id !=:id and status != -1';
            $params[':wechat'] = $data['wechat'];
            $params[':id'] = $data_arr['clue_id'];
        };

        if (isset($data['call']) && $data['call'] != 0){
            if($sql != '') {
                $sql .= ' UNION ';
            }
            $sql .= 'SELECT '.$fields.' FROM '.CrmClue::tableName().' WHERE `call`=:call and id !=:id and status != -1';
            $params[':call'] = $data['call'];
            $params[':id'] = $data_arr['clue_id'];
        };

        if (empty($count)){
            if ($sql != ''){
                $sql.= ' limit '.$data_arr['page_num'].' offset '.$data_arr['limit'];
                return CrmClue::findBySql($sql, $params)
                    ->all();
            }else{
                return true;
            }
        }else{

            if ($sql != ''){
                return CrmClue::findBySql($sql, $params)->count();
            }else{
                return true;
            }
        }

    }
    /**
     * @param $data
     * @return array|null|\yii\db\ActiveRecord
     * 线索详情
     */
    public function details($data){
        return CrmClue::find()
            ->where(['crm_clue.id'=>$data['clue_id']])
            ->one();
    }

    //检查是否可以新增线索
    public function checkAddClue($administrator = null , $is_add = false)
    {

        if (empty($administrator)){
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
        }
        //是否启用公海
        if (isset($administrator->department->cluePublic)){

            $clue_count = 0;
                if ($administrator->department->cluePublic->is_own == 1) {
                    //包含
                    $clue_count = CrmClue::find()->where(['administrator_id' => $administrator->id])->andWhere(['<>','status','-1'])->andWhere("clue_public_id = 0 or clue_public_id is null ")->count();
                } else {
                    if (!$is_add){
                        //不包含 获取状态为转移，分配，提取的
                        $clue_count = CrmClue::find()->where(['administrator_id' => $administrator->id])->andWhere(['in','status',[CrmClue::STATUS_TRANSFER,CrmClue::STATUS_DISTRIBUTION,CrmClue::STATUS_EXTRACT]])->andWhere("clue_public_id = 0 or clue_public_id is null ")->count();
                    }
                }
            if ((int)$clue_count >= $administrator->department->cluePublic->most_num) {
                return $this->addError('id', '对不起，当前用户拥有线索已达上限');

            }
            return $administrator->department->cluePublic->most_num - (int)$clue_count;
        }
    }


}
