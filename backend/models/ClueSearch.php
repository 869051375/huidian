<?php
namespace backend\models;

use common\models\CrmClue;
use common\models\CrmDepartment;
use common\models\Holidays;
use common\utils\BC;
use yii\base\Model;

class ClueSearch extends \yii\db\ActiveRecord
{
    public $submit_type;
    public $name;
    public $gander;
    public $source_id;
    public $channel_id;
    public $company_name;
    public $mobile;
    public $wechat;
    public $qq;
    public $call;
    public $tel;
    public $email;
    public $birthday;
    public $department;
    public $position;
    public $native_place;
    public $province_id;
    public $province_name;
    public $city_id;
    public $city_name;
    public $district_id;
    public $district_name;
    public $address;
    public $interest;
    public $remark;

    public $status;
    public $is_new;
    public $follow_status;
    public $administrator_id;
    public $administrator_name;
    public $company_id;
    public $department_id;
    public $creator_id;
    public $creator_name;
    public $label_id;
    public $invalid_time;
    public $business_subject_id;
    public $last_record;
    public $last_record_creator_id;
    public $next_follow_time;
    public $clue_public_id;
    public $extract_time;
    public $move_public_time;
    public $updater_id;
    public $created_at;
    public $updated_at;
    public $last_record_creator_name;
    public $updater_name;
    public $clue_type;
    public $page;
    public $page_num;
    public $limit;
    public $tag;
    public $transfer_at;
    public $recovery_at;


    /**
     * @var
     */
    public $orders;

    public function rules()
    {
        return [
            [[ 'source_id','page', 'page_num','limit','channel_id', 'gander', 'province_id', 'city_id', 'district_id', 'label_id', 'status', 'is_new', 'follow_status', 'invalid_time', 'company_id', 'department_id', 'business_subject_id', 'last_record', 'last_record_creator_id', 'next_follow_time', 'clue_public_id', 'extract_time', 'move_public_time', 'creator_id', 'tag','updater_id'], 'integer'],
            [['name', 'administrator_name', 'last_record_creator_name', 'creator_name', 'updater_name'], 'string', 'max' => 20,'on'=>'add'],
            [['company_name', 'native_place', 'address', 'interest'], 'string', 'max' => 100,'on'=>'add'],
            [['mobile'], 'string', 'max' => 11,'on'=>'add'],
            [['tel', 'wechat', 'department', 'position'], 'string', 'max' => 25,'on'=>'add'],
            [['email'], 'string', 'max' => 64,'on'=>'add'],
            [['call'], 'string', 'max' => 25,'on'=>'add'],
            [['remark'], 'string', 'max' => 200,'tooLong'=>'线索备注不允许超200个文字。','on'=>'add'],
            [['birthday', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 10,'on'=>'add'],
            [[ 'name','department','province_name','district_name','city_name'], 'match', 'pattern' => '/^[ \x{4e00}-\x{9fa5}]+$/u','message'=>'编码错误，必须为汉字','on'=>'add'],
            [['qq'], 'string', 'max' => 25,'tooLong'=>'QQ长度不允许超过25个字符。','on'=>'add'],
            [[ 'mobile'], 'match', 'pattern' => '/^1[34578]\d{9}$/','message'=>'手机号格式错误','on'=>'add'],
            [[ 'wechat'], 'match', 'pattern' => '/^[a-zA-Z]([-_a-zA-Z0-9]{5,19})+$/','message'=>'微信号格式错误','on'=>'add'],
            [[ 'qq'], 'match', 'pattern' => '/^[1-9]*[1-9][0-9]*$/','message'=>'QQ号码格式错误','on'=>'add'],
            [[ 'tel'], 'match', 'pattern' => '/^([0-9]{3,4}-)?[0-9]{7,8}$/','message'=>'电话号格式错误','on'=>'add'],
            [[ 'email'], 'match', 'pattern' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/','message'=>'邮箱号格式错误','on'=>'add'],
            [[ 'position','call','native_place','address','interest','remark'], 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u','message'=>'编码错误，必须为汉字，字母，数字组成','on'=>'add'],
            [['clue_type'], 'required'],//必填
        ];
    }

    /**
     * @inheritdoc
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
        ];
    }


    /**
     * @param $data
     * @param $count
     * @return array|bool|\yii\db\ActiveRecord[]
     * 获取列表
     * 条件筛选
     */
    public function getList($data,$count = null)
    {

        if (!$this->validate()) return false;

        $query = CrmClue::find();


        if (isset($data['status']) && $data['status'] != 0){
            $query->where(['status'=>$data['status']]);
        }else{
            $query->where(['in','status',CrmClue::STATUS_EFFECTIVE]);
        }

        //如果是管理员登录，查询所有数据
        if (isset($data['administrator_id']) && ($data['administrator_id'] != 0 || $data['administrator_id'] != ''))
        {
            if ($data['administrator_id'] == 'administrator_id_no')
            {
                $query->andWhere(['<>','administrator_id',\Yii::$app->user->id]);
            }
            elseif ($data['administrator_id'] == 'administrator_id')
            {
                //查询所有
            }
            else{
                $query->andWhere(['in','administrator_id',$data['administrator_id']]);
            }

        }
        //即将被回收的线索
        if (isset($data['soon_recovery']) && $data['soon_recovery'] != 0){
            $query->andWhere(['in','id',$this->recovery($data['administrator_id'])]);
        }

        //如果有标签查询
        if (isset($data['clue_type']) && $data['clue_type'] == CrmClue::CLUE_TYPE_CHANGE_WHOLE ||  $data['clue_type'] == CrmClue::CLUE_TYPE_CHANGE || $data['clue_type'] == CrmClue::CLUE_TYPE_CHANGE_SUBORDINATE)
        {
            $query->andWhere(['>','business_subject_id',0]);
        }else{
            $query->andWhere("ISNULL(business_subject_id) OR business_subject_id = 0");
        }

        if (!empty($data['label_id']))
        {
            $query->andWhere(['label_id'=>$data['label_id']]);
        }
        //按照所属部门查询
        if (!empty($data['department_id']))
        {
            $query->andWhere(['department_id' => $data['department_id']]);
        }

        //按照公司查询
        if (!empty($data['company_id']))
        {
            $query->andWhere(['company_id' => $data['company_id']]);
        }

        //按照创建人查询
        if (!empty($data['creator_id']))
        {
            $query->andWhere(['creator_id' => $data['creator_id']]);
        }

        //跟进状态查询
        if (isset($data['follow_status']) && $data['follow_status']!= ''){
            $query->andWhere(['follow_status'=>$data['follow_status']]);
        }

        //按照创建时间查询
//        if (isset($data['created_at'][0]) && $data['created_at'][0]!= 0)
//        {
//            $query->andWhere(['>=','created_at', strtotime($data['created_at'][0])]);
//            $query->andWhere(['<=','created_at', strtotime($data['created_at'][1])]);
//        }

        //创建时间开始时间存在的时候
        if (isset($data['created_at_start']) && $data['created_at_start'] != '')
        {
            if (isset($data['created_at_end']) && $data['created_at_end'] != '')
            {
                $query->andWhere(['>=','created_at', strtotime($data['created_at_start'])]);
                $query->andWhere(['<=','created_at', strtotime($data['created_at_end'])+86400-1]);
            }
            else
            {
                $query->andWhere(['>=','created_at', strtotime($data['created_at_start'])]);
            }
        }

        //创建时间结束时间存在的时候
        if (isset($data['created_at_end']) && $data['created_at_end'] != '')
        {
            if (isset($data['created_at_start']) && $data['created_at_start'] != '')
            {
                $query->andWhere(['>=','created_at', strtotime($data['created_at_start'])]);
                $query->andWhere(['<=','created_at', strtotime($data['created_at_end'])+86400-1]);
            }
            else
            {
                $query->andWhere(['<=','created_at', strtotime($data['created_at_end'])+86400-1]);
            }
        }

//        //按照最后修改时间查询
//        if (isset($data['updated_at'][0]) && $data['updated_at'][0] != 0)
//        {
//            $query->andWhere(['>=','updated_at', strtotime($data['updated_at'][0])]);
//            $query->andWhere(['<=','updated_at', strtotime($data['updated_at'][1])]);
//        }

        //最后修改时间开始时间存在的时候
        if (isset($data['updated_at_start']) && $data['updated_at_start'] != '')
        {
            if (isset($data['updated_at_end']) && $data['updated_at_end'] != '')
            {
                $query->andWhere(['>=','updated_at', strtotime($data['updated_at_start'])]);
                $query->andWhere(['<=','updated_at', strtotime($data['updated_at_end'])+86400-1]);
            }
            else
            {
                $query->andWhere(['>=','updated_at', strtotime($data['updated_at_start'])]);
            }
        }

        //最后修改时间结束时间存在的时候
        if (isset($data['updated_at_end']) && $data['updated_at_end'] != '')
        {
            if (isset($data['updated_at_start']) && $data['updated_at_start'] != '')
            {
                $query->andWhere(['>=','updated_at', strtotime($data['updated_at_start'])]);
                $query->andWhere(['<=','updated_at', strtotime($data['updated_at_end'])+86400-1]);
            }
            else
            {
                $query->andWhere(['<=','updated_at', strtotime($data['updated_at_end'])+86400-1]);
            }
        }



        //按照姓名查询
        if (!empty($data['name']))
        {
            $query->andWhere(['LIKE','name',$data['name']]);
        }

        //按照公司名称查询
        if (!empty($data['company_name']))
        {
            $query->andWhere(['LIKE','company_name',$data['company_name']]);
        }

        //按照手机号查询
        if (isset($data['mobile']) && $data['mobile'] != '')
        {
            $query->andWhere(['LIKE','mobile',$data['mobile']]);
        }

        //按照电话号查询
        if (!empty($data['tel']))
        {
            $query->andWhere(['LIKE','tel',$data['tel']]);
        }

        //按照邮箱查询
        if (!empty($data['email']))
        {
            $query->andWhere(['LIKE','crm_clue.email',$data['email']]);
        }

        //按照QQ查询
        if (!empty($data['qq']))
        {
            $query->andWhere(['LIKE','qq',$data['qq']]);
        }

        //按照微信号查询
        if (!empty($data['wechat']))
        {
            $query->andWhere(['LIKE','wechat',$data['wechat']]);
        }

        //按照来电电话查询
        if (!empty($data['call']))
        {
            $query->andWhere(['LIKE','call',$data['call']]);
        }

        //按照线索来源查询
        if (!empty($data['source_id']))
        {
            $query->andWhere(['source_id'=>$data['source_id']]);
        }

        //按照线索来源渠道查询
        if (!empty($data['channel_id']))
        {
            $query->andWhere(['channel_id'=>$data['channel_id']]);
        }

        //按照新线索查询
        if (isset($data['is_new']) && $data['is_new'] != 0)
        {
            $query->andWhere(['is_new'=>$data['is_new']]);
        }

        //如果转化时间的开始时间存在的话
        if (isset($data['transfer_at_start']) && $data['transfer_at_start'] != '')
        {
            if (isset($data['transfer_at_end']) && $data['transfer_at_end'] != '')
            {
                $query->andWhere(['>=','transfer_at', strtotime($data['transfer_at_start'])]);
                $query->andWhere(['<=','transfer_at', strtotime($data['transfer_at_end'])+86400-1]);
            }
            else
            {
                $query->andWhere(['>=','transfer_at', strtotime($data['transfer_at_start'])]);
            }
        }

        //转化时间的结束时间
        if (isset($data['transfer_at_end']) && $data['transfer_at_end'] != '')
        {
            if (isset($data['transfer_at_start']) && $data['transfer_at_start'] != '')
            {
                $query->andWhere(['>=','transfer_at', strtotime($data['transfer_at_start'])]);
                $query->andWhere(['<=','transfer_at', strtotime($data['transfer_at_end'])+86400-1]);
            }
            else
            {
                $query->andWhere(['<=','transfer_at', strtotime($data['transfer_at_end'])+86400-1]);
            }
        }

        //近三天维护的线索查询
        if (isset($data['nearly_three_days']) && $data['nearly_three_days'] != 0){
            $query->andWhere(['>=','updated_at', strtotime('-2 day')]);
            $query->andWhere(['<=','updated_at', time()]);
        }

        //跟进中的时候 按照创建时间倒叙排序
        if (isset($data['clue_type']) && $data['clue_type'] == CrmClue::CLUE_TYPE_MY_CLUE ||  $data['clue_type'] == CrmClue::CLUE_TYPE_MY_SUBORDINATE || $data['clue_type'] == CrmClue::CLUE_TYPE_WHOLE)
        {
            $query->orderBy('updated_at DESC');
        }
        //已转化场景的时候 按照转化时间倒叙排序
        if (isset($data['clue_type']) && $data['clue_type'] == CrmClue::CLUE_TYPE_CHANGE ||  $data['clue_type'] == CrmClue::CLUE_TYPE_CHANGE_SUBORDINATE || $data['clue_type'] == CrmClue::CLUE_TYPE_CHANGE_WHOLE)
        {
            $query->orderBy('transfer_at DESC');
        }

        if (empty($count)){
            $query->limit($data['page_num'])->offset($data['limit']);

            return $query->all();
        }else{
            return $query->count();
        }

    }

    public function recovery($administrator_id)
    {

        /** @var CrmClue $models */
        $models = CrmClue::find()->where('status != -1')->andWhere("clue_public_id = 0 or clue_public_id is null ")->andWhere("business_subject_id = 0 or business_subject_id is null")->andWhere(['administrator_id'=>$administrator_id])->all();

        $clue_id = [];
        foreach ($models as $model){

            //分配时间，提取时间，转移时间，最后跟进时间，创建时间 对比 获取最大的时间
            $time = max($model->distribution_at,$model->extract_time,$model->shift_at,$model->created_at);

            //如果所在公海是启动的
            if (isset($model->administrator->department->cluePublic->status) && $model->administrator->department->cluePublic->status == 1)
            {
                //新线索多少分钟不添加跟进记录，自动回收至线索公海
                if (isset($model->administrator->department->cluePublic->new_move_time))//公海回收时间存的时候
                {
                    //线索创建时间+公海回收时间如果小于当前时间的话，进行回收
                    if (BC::add($time,BC::mul($model->administrator->department->cluePublic->new_move_time,60,0),0) <= (time()+(3*60)))
                    {
                        //新线索条件
                        if ($model->is_new == 1)
                        {
                            $clue_id[] = $model->id;
                        }
                    }
                }

                //他们分配或者自己领取的线索多少个工作日不添加跟进记录，将自动回收到公海
                if (isset($model->administrator->department->cluePublic->distribution_move_time))
                {
                    //计算规则之后的几个工作日后的时间戳小于当前时间进行回收
                    if (Holidays::getEndTimeByDays($model->administrator->department->cluePublic->distribution_move_time,date('Y-m-d H:00:00',$time)) <= (time()+86400))
                    {
                        if ($model->status == 2 || $model->status == 3 || $model->status == 1)
                        {
                            $clue_id[] = $model->id;
                        }
                    }
                }

                //已跟进的线索几个工作日内不添加跟进记录，将自动回收到公海
                if (isset($model->administrator->department->cluePublic->follow_move_time))
                {
                    //获取最新的跟进时间加上规则的时间 小于当前时间
                    if (Holidays::getEndTimeByDays($model->administrator->department->cluePublic->follow_move_time,date('Y-m-d H:00:00',$time)) <= (time()+86400))
                    {
                        if (isset($model->recordDesc->id)){
                            $clue_id[] = $model->id;
                        }
                    }
                }

                //个人线索多少个工作日不转化为客户，将自动回收至公海
                if (isset($model->administrator->department->cluePublic->personal_move_time))
                {
                    //线索创建时间+规则时间 小于当前时间
                    if (Holidays::getEndTimeByDays($model->administrator->department->cluePublic->personal_move_time,date('Y-m-d H:00:00',$time)) <= (time()+86400))
                    {
                        $clue_id[] = $model->id;
                    }
                }
            }
        }

        return array_unique($clue_id);
    }

//    public function findOne($id)
//    {
//        return CrmClue::findOne($id);
//    }
}
