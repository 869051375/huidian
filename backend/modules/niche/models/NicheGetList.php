<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\Holidays;
use common\models\Niche;
use common\models\NicheProduct;
use common\models\NicheTeam;
use common\models\Tag;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="NicheGetList"))
 */
class NicheGetList extends Model
{


    public $scene;
    public $administrator_id;
    public $next_follow_time;
    public $last_record_creator_id;
    public $last_record;
    public $creator_id;
    public $created_at;
    public $progress;
    public $status;
    public $label_id;
    public $customer_id;
    public $source_id;
    public $channel_id;
    public $distribution_id;
    public $distribution_at;
    public $stage_update_at;
    public $update_id;
    public $update_name;
    public $is_distribution;
    public $is_extract;
    public $is_transfer;
    public $is_cross;
    public $is_new;
    public $is_protect;
    public $win_progress;
    public $lose_progress;
    public $company_id;
    public $department_id;
    public $extract_time;
    public $move_public_time;
    public $send_administrator_id;
    public $send_time;
    public $predict_deal_time;
    public $deal_time;
    public $invalid_time;
    public $user_id;
    public $updated_at;
    public $total_amount;
    public $win_describe;
    public $lose_describe;
    public $remark;
    public $invalid_reason;
    public $name;
    public $administrator_name;
    public $last_record_creator_name;
    public $creator_name;
    public $label_name;
    public $source_name;
    public $channel_name;
    public $distribution_name;
    public $win_reason;
    public $lose_reason;
    public $page_num;
    public $product_id;
    public $compare;
    public $niche_key;
    public $niche_val;
    public $follow_today;
    public $follow_three;
    public $follow_month;
    public $page;
    public $created_at_start;
    public $created_at_end;
    public $last_record_start;
    public $last_record_end;
    public $distribution_at_start;
    public $distribution_at_end;
    public $soon_recovery;
    public $top_category_id;
    public $category_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id', 'next_follow_time', 'last_record_creator_id', 'last_record', 'creator_id', 'created_at', 'progress', 'status', 'label_id', 'customer_id', 'source_id', 'channel_id', 'distribution_id', 'distribution_at', 'stage_update_at', 'update_id', 'update_name', 'is_distribution', 'is_extract', 'is_transfer', 'is_cross', 'is_new', 'is_protect', 'win_progress', 'lose_progress', 'company_id', 'department_id', 'extract_time', 'move_public_time', 'send_administrator_id', 'send_time', 'predict_deal_time', 'deal_time', 'invalid_time', 'user_id', 'updated_at','scene','page_num','page','compare','soon_recovery','follow_today','follow_three','follow_month','top_category_id','category_id','product_id'], 'integer'],
            [['total_amount'], 'number'],
            [['niche_key','niche_val'], 'string'],
            [['scene'], 'required'],
            [['scene'], 'ValidateScene'],
            [['win_describe', 'lose_describe', 'remark', 'invalid_reason','created_at_start','created_at_end','last_record_start','last_record_end','distribution_at_start','distribution_at_end'], 'string'],
            [['name', 'administrator_name', 'last_record_creator_name', 'creator_name', 'label_name', 'source_name', 'channel_name', 'distribution_name', 'win_reason', 'lose_reason'], 'string', 'max' => 25],
            [['niche_key', 'niche_val', 'label_id', 'creator_id', 'administrator_id', 'last_record_creator_id', 'source_id', 'channel_id', 'distribution_id', 'product_id', 'created_at', 'last_record', 'distribution_at', 'company_id', 'department_id', 'total_amount', 'compare', 'progress', 'is_new', 'is_distribution', 'is_transfer', 'is_extract', 'is_cross', 'is_protect', 'follow_today', 'follow_three', 'follow_month'], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false,'on'=>'export']
        ];
    }

    public function requiredBySpecial($attribute)
    {

        if(empty($this->niche_key) && empty($this->niche_val) &&
            empty($this->label_id) && empty($this->creator_id) &&
            empty($this->administrator_id) && empty($this->last_record_creator_id) &&
            empty($this->source_id) && empty($this->channel_id) &&
            empty($this->distribution_id) && empty($this->product_id) &&
            empty($this->created_at_start) && empty($this->created_at_end) &&
            empty($this->last_record_start) && empty($this->last_record_end) &&
            empty($this->distribution_at_start) && empty($this->distribution_at_end) &&
            empty($this->top_category_id) && empty($this->category_id) &&
            empty($this->soon_recovery) && empty($this->company_id) &&
            empty($this->department_id) && empty($this->total_amount) &&
            empty($this->compare) && empty($this->progress) &&
            empty($this->is_new) && empty($this->is_distribution) &&
            empty($this->is_transfer) && empty($this->is_extract) &&
            empty($this->is_cross) && empty($this->is_protect) &&
            empty($this->follow_today) && empty($this->follow_three) &&
            empty($this->follow_month))
        {
            $this->addError($attribute, '请选择任意一项搜索才能导出！');
        }
    }

    public function ValidateScene()
    {
        if (!in_array($this->scene,[1,2,3,4,5,6,7,11,12,13,14,15,16,17,21,22,23,24,25,26,27])){
            return $this->addError('scene', '场景不存在！');
        }
        return true;
    }

    //type
    public function getList($type = true)
    {
        $query = NicheGetAll::find()->alias('ni')->distinct()->select('t.color as label_color,ni.*,b.company_name as customer_name,b.customer_number as customer_number,b.created_at as customer_created_at');
        $query->leftJoin(['pr'=>NicheProduct::tableName()],'pr.niche_id=ni.id');
        $query->leftJoin(['t'=>Tag::tableName()],'ni.label_id = t.id');
        $query->leftJoin(['c'=>CrmCustomer::tableName()],'ni.customer_id = c.id');
        $query->leftJoin(['cc'=>CrmContacts::tableName()],'ni.customer_id = cc.customer_id');
        $query->leftJoin(['b'=>BusinessSubject::tableName()],'ni.customer_id = b.customer_id');

        $this->getScene($query,$this->scene);

        //自定义筛选
        if (isset($this->niche_key) && $this->niche_key != ''){
            if (isset($this->niche_val) && $this->niche_val != ''){
                //查询客户名称的时候
                if ($this->niche_key == 'customer_name')
                {
                    $query->andWhere(['like','c.name',$this->niche_val]);
                }
                //查询联系人
                elseif ($this->niche_key == 'phone')
                {
                    $query->andWhere(['like','cc.'.$this->niche_key,$this->niche_val]);
                }
                elseif ($this->niche_key == 'tel')
                {
                    $query->andWhere(['like','cc.'.$this->niche_key,$this->niche_val]);
                }
                elseif ($this->niche_key == 'wechat')
                {
                    $query->andWhere(['like','cc.'.$this->niche_key,$this->niche_val]);
                }
                elseif ($this->niche_key == 'qq')
                {
                    $query->andWhere(['like','cc.'.$this->niche_key,$this->niche_val]);
                }
                elseif ($this->niche_key == 'caller')
                {
                    $query->andWhere(['like','cc.'.$this->niche_key,$this->niche_val]);
                }
                elseif ($this->niche_key == 'email')
                {
                    $query->andWhere(['like','cc.'.$this->niche_key,$this->niche_val]);
                }
                else
                {
                    $query->andWhere(['like','ni.'.$this->niche_key,$this->niche_val]);
                }
            }
        }
        //商机标签筛选
        if (isset($this->label_id) && $this->label_id != ''){
            $query->andWhere(['ni.label_id' => $this->label_id]);
        }
        //创建人筛选
        if (isset($this->creator_id) && $this->creator_id != ''){
            $query->andWhere(['ni.creator_id' => $this->creator_id]);
        }
        //负责人筛选
        if (isset($this->administrator_id) && $this->administrator_id != ''){
            $query->andWhere(['ni.administrator_id' => $this->administrator_id]);
        }
        //最后跟进人筛选
        if (isset($this->last_record_creator_id) && $this->last_record_creator_id != ''){
            $query->andWhere(['ni.last_record_creator_id' => $this->last_record_creator_id]);
        }
        //来源筛选
        if (isset($this->source_id) && $this->source_id != ''){
            $query->andWhere(['ni.source_id'=>$this->source_id]);
        }
        //来源渠道筛选
        if (isset($this->channel_id) && $this->channel_id != ''){
            $query->andWhere(['ni.channel_id'=>$this->channel_id]);
        }
        //分配人筛选
        if (isset($this->distribution_id) && $this->distribution_id != ''){
            $query->andWhere(['ni.distribution_id'=>$this->distribution_id]);
        }

        //一级类目查询
        if (isset($this->top_category_id) && $this->top_category_id != '')
        {
            $query->andWhere(['pr.top_category_id'=>$this->top_category_id]);
        }

        //二级类目查询
        if (isset($this->category_id) && $this->category_id != '')
        {
            $query->andWhere(['pr.category_id'=>$this->category_id]);
        }

        //商品类目筛选
        if (isset($this->product_id) && $this->product_id != ''){
            $query->andWhere(['pr.product_id'=>$this->product_id]);
        }

        //创建时间开始时间存在
        if (isset($this->created_at_start) && $this->created_at_start != '')
        {
            if (isset($this->created_at_end) && $this->created_at_end != '')
            {
                $query->andWhere(['>=','ni.created_at', strtotime($this->created_at_start)]);
                $query->andWhere(['<=','ni.created_at', strtotime($this->created_at_end)]);
            }
            else
            {
                $query->andWhere(['>=','ni.created_at', strtotime($this->created_at_start)]);
            }
        }

        //创建时间结束时间存在
        if (isset($this->created_at_end) && $this->created_at_end != '')
        {
            if (isset($this->created_at_start) && $this->created_at_start != '')
            {
                $query->andWhere(['>=','ni.created_at', strtotime($this->created_at_start)]);
                $query->andWhere(['<=','ni.created_at', strtotime($this->created_at_end)]);
            }
            else
            {
                $query->andWhere(['<=','ni.created_at', strtotime($this->created_at_end)]);
            }
        }

        //创建时间筛选
//        if (isset($this->created_at[0]) && $this->created_at[0] != ''){
//            $query->andWhere(['>=','ni.created_at', strtotime($this->created_at_start[0])]);
//            $query->andWhere(['<=','ni.created_at', strtotime($this->created_at_end[1])+86400]);
//        }

        //最后跟进时间开始时间存在
        if (isset($this->last_record_start) && $this->last_record_start != '')
        {
            if (isset($this->last_record_end) && $this->last_record_end != '')
            {
                $query->andWhere(['>=','ni.last_record', strtotime($this->last_record_start)]);
                $query->andWhere(['<=','ni.last_record', strtotime($this->last_record_end)]);
            }
            else
            {
                $query->andWhere(['>=','ni.last_record', strtotime($this->last_record_start)]);
            }
        }

        //最后跟进时间结束时间存在
        if (isset($this->last_record_end) && $this->last_record_end != '')
        {
            if (isset($this->last_record_start) && $this->last_record_start != '')
            {
                $query->andWhere(['>=','ni.last_record', strtotime($this->last_record_start)]);
                $query->andWhere(['<=','ni.last_record', strtotime($this->last_record_end)]);
            }
            else
            {
                $query->andWhere(['<=','ni.last_record', strtotime($this->created_at_end)]);
            }
        }

        //最后跟进时间筛选
//        if (isset($this->last_record[0]) && $this->last_record[0] != ''){
//            $query->andWhere(['>=','ni.last_record', strtotime($this->last_record[0])]);
//            $query->andWhere(['<=','ni.last_record', strtotime($this->last_record[1])+86400]);
//        }

        //分配时间开始时间存在
        if (isset($this->distribution_at_start) && $this->distribution_at_start != '')
        {
            if (isset($this->distribution_at_end) && $this->distribution_at_end != '')
            {
                $query->andWhere(['>=','ni.distribution_at', strtotime($this->distribution_at_start)]);
                $query->andWhere(['<=','ni.distribution_at', strtotime($this->distribution_at_end)]);
            }
            else
            {
                $query->andWhere(['>=','ni.distribution_at', strtotime($this->distribution_at_end)]);
            }
        }

        //分配时间结束时间存在
        if (isset($this->distribution_at_end) && $this->distribution_at_end != '')
        {
            if (isset($this->distribution_at_start) && $this->distribution_at_start != '')
            {
                $query->andWhere(['>=','ni.distribution_at', strtotime($this->distribution_at_start)]);
                $query->andWhere(['<=','ni.distribution_at', strtotime($this->distribution_at_end)]);
            }
            else
            {
                $query->andWhere(['<=','ni.distribution_at', strtotime($this->distribution_at_end)]);
            }
        }

        //分配时间时间筛选
//        if (isset($this->distribution_at[0]) && $this->distribution_at[0] != ''){
//            $query->andWhere(['>=','ni.distribution_at', strtotime($this->distribution_at[0])]);
//            $query->andWhere(['<=','ni.distribution_at', strtotime($this->distribution_at[1])+86400]);
//        }
        //所属公司筛选
        if (isset($this->company_id) && $this->company_id != ''){
            $query->andWhere(['ni.company_id'=>$this->company_id]);
        }
        //所属部门筛选
        if (isset($this->department_id) && $this->department_id != ''){
            $query->andWhere(['ni.department_id'=>$this->department_id]);
        }
        //商机金额筛选
        if (isset($this->total_amount) && $this->total_amount != ''){
            if (isset($this->compare) && $this->compare != ''){
                if ((int)$this->compare  == 1){
                    $query->andWhere(['>=','ni.total_amount',(int)$this->total_amount]);
                }elseif ((int)$this->compare == 2){
                    $query->andWhere(['<=','ni.total_amount',(int)$this->total_amount]);
                }
            }
        }
        //商机阶段筛选
        if (isset($this->progress) && $this->progress != ''){
            $query->andWhere(['ni.progress'=>$this->progress]);
        }
        //新的商机筛选
        if (isset($this->is_new) && $this->is_new != ''){
            $query->andWhere(['ni.is_new'=>$this->is_new]);
        }
        //分配的商机查询
        if (isset($this->is_distribution) && $this->is_distribution != ''){
            $query->andWhere(['ni.is_distribution'=>$this->is_distribution]);
        }
        //转移的商机查询
        if (isset($this->is_transfer) && $this->is_transfer != ''){
            $query->andWhere(['ni.is_transfer'=>$this->is_transfer]);
        }
        //提取的商机查询
        if (isset($this->is_extract) && $this->is_extract != ''){
            $query->andWhere(['ni.is_extract'=>$this->is_extract]);
        }
        //跨产品线的商机查询
        if (isset($this->is_cross) && $this->is_cross != ''){
            $query->andWhere(['ni.is_cross'=>$this->is_cross]);
        }
        //保护的商机查询
        if (isset($this->is_protect) && $this->is_protect != ''){
            $query->andWhere(['ni.is_protect'=>$this->is_protect]);
        }
        //今天跟进的商机查询
        if (isset($this->follow_today) && $this->follow_today != ''){
            $query->andWhere(['>=','ni.last_record', mktime(0,0,0,date('m'),date('d'),date('Y'))]);
            $query->andWhere(['<=','ni.last_record', mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1]);
        }
        //近三天跟进的商机查询
        if (isset($this->follow_three) && $this->follow_three != ''){
            $query->andWhere(['>=','ni.last_record', mktime(0,0,0,date('m'),date('d')-2,date('Y'))]);
            $query->andWhere(['<=','ni.last_record', mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1]);
        }
        //近一个月跟进商机查询
        if (isset($this->follow_month) && $this->follow_month != ''){
            $query->andWhere(['>=','ni.last_record', mktime(0,0,0,date('m'),date('d')-30,date('Y'))]);
            $query->andWhere(['<=','ni.last_record', mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1]);
        }

        //即将回收筛选
        if(isset($this->soon_recovery) && $this->soon_recovery != '')
        {
            $query->andWhere(['in','ni.id',$this->recovery()]);
        }

        $this->page_num = (isset($this->page_num) && $this->page_num!='') ? $this->page_num : 20;
        $this->page = isset($this->page) ? (($this->page == 0 ? 1 : $this->page)-1) : 0;

        if ($type){
            return $query->all();
        }else{
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => (int)$this->page_num,
                    'page' => (int)$this->page,
                ]
            ]);
            return $dataProvider;
        }
    }
    
    //即将回收逻辑
    public function recovery()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        /** @var Niche $models */
        $models = Niche::find() -> where(['niche_public_id' => 0]) -> andWhere(['<>','progress',100])->andWhere(['administrator_id'=>$administrator->id])->all();

        $niche_id = [];

        foreach($models as $model)
        {
            //判断当前商机是否是保护状态  保护不回收
            if($model -> is_protect == 0)
            {
                //判断商机是否有公海有 有回收 没有不回收
                $niche_public = isset($model->administratorNichePublic) ? $model->administratorNichePublic->nichePublic : '';
                if(!empty($niche_public))
                {
                    //商机公海为启用的状态下才会被回收
                    if($niche_public->status == 1)
                    {
                        //最后跟进时间对比 获取最大的时间
                        $time = isset($model->nicheRecord->created_at) ? (int)$model->nicheRecord->created_at : 0;
                        //新规则根据商机的修改时间做回收标准
                        $create_at = isset($model->updated_at) ? (int)$model->updated_at : 0;
                        //判断他人分配的商机在指定工作日内未跟进 回收
                        if($model->is_distribution == 1)
                        {
                            //获取最新的跟进时间加上规则的时间 小于当前时间
                            if (Holidays::getEndTimeByDays($model->administratorNichePublic->nichePublic->distribution_move_time,date('Y-m-d H:00:00',$create_at)) <= (time()+86400))
                            {
                                $niche_id[] = $model->id;
                            }
                        }

                        //判断个人商机在规定时间内不添加跟进记录 则回收 判断条件：提取时间extract_time、转移时间send_time、分配时间distribution_at、最后跟进时间last_record
                        $arr = array($model->extract_time,$model->send_time,$model->distribution_at,$time);
                        rsort($arr);
                        $times = $arr[0];
                        if($model->administrator_id != 0 && $model->is_distribution != 1)
                        {
                            //获取最新的跟进时间加上规则的时间 小于当前时间
                            if (Holidays::getEndTimeByDays($model->administratorNichePublic->nichePublic->personal_move_time,date('Y-m-d H:00:00',$times)) <= (time()+86400))
                            {
                                $niche_id[] = $model->id;
                            }
                        }
                    }
                }
            }
        }
        return array_unique($niche_id);
    }

    //场景逻辑
    /**
     * @param  $query
     * @param  $scene
     */
    public function getScene($query,$scene){
        //  $scene  1：我负责的跟进中商机
        //          2：我参与的跟进中商机
        //          3: 我分享的跟进中商机
        //          4：下属负责的跟进中商机
        //          5：下属参与的跟进中商机
        //          6：下属分享的跟进中商机
        //          7：全部跟进中商机

        //          11: 我负责的已成交商机
        //          12: 我参与的已成交商机
        //          13:我分享的已成交商机
        //          14:下属负责的已成交商机
        //          15:下属参与的已成交商机
        //          16:下属分享的已成交商机
        //          17:全部的已成交商机

        //          21: 我负责的已输单商机
        //          22: 我参与的已输单商机
        //          23:我分享的已输单商机
        //          24:下属负责的已输单商机
        //          25:下属参与的已输单商机
        //          26:下属分享的已输单商机
        //          27:全部的已输单商机

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if (in_array($scene,[1,2,3,4,5,6,7]))
        {
            $query->andWhere(['in','progress',[10,30,60,80]]);
        }
        elseif (in_array($scene,[11,12,13,14,15,16,17]))
        {
            $query->andWhere(['progress' => Niche::PROGRESS_100]);
        }
        elseif (in_array($scene,[21,22,23,24,25,26,27]))
        {
            $query->andWhere(['progress' => Niche::PROGRESS_0]);
        }

        $query->andWhere(['niche_public_id'=>0]);

        if ($scene == 1)
        {
            $query->orderBy('ni.next_follow_time asc');
            $query->andWhere(['ni.administrator_id'=>$administrator->id]);
        }

        elseif ($scene == 2)
        {
            $query->orderBy('ni.next_follow_time asc');
            $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator->id])->all();
            //如果查询到按条件查询  如果没有，返回为空
            if (!empty($niche_id)){
                $niche_arr = array();
                foreach ($niche_id as $item){
                    array_push($niche_arr,$item->niche_id);
                }
                $query->andWhere(['in','ni.id',$niche_arr]);
            }else{
                $query->andWhere(['ni.id'=>-1]);
            }
        }

        elseif ($scene == 3)
        {
            $query->orderBy('ni.created_at desc');
            $query->andWhere(['ni.creator_id'=>$administrator->id]);
            $query->andWhere(['ni.is_cross'=>1]);
        }

        elseif ($scene == 4)
        {
            $query->orderBy('ni.next_follow_time asc');
            $administrator_id = $administrator->getTreeAdministratorId(false,true);

            if (!empty($administrator_id))
            {
                $query->andWhere(['in','ni.administrator_id',$administrator_id]);
            }
            else
            {
                if (!($administrator->isBelongCompany() && $administrator->isCompany()))
                {
                    $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
                }
                else
                {
                    $query->andWhere(['ni.administrator_id'=>'-1']);
                }
            }
        }

        elseif ($scene == 5)
        {
            $query->orderBy('ni.next_follow_time asc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(false,true);
                $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator_id])->all();
                //如果查询到按条件查询  如果没有，返回为空
                if (!empty($niche_id)){
                    $niche_arr = array();
                    foreach ($niche_id as $item){
                        array_push($niche_arr,$item->niche_id);
                    }
                    $query->andWhere(['in','ni.id',$niche_arr]);
                }else{
                    $query->andWhere(['ni.id'=>-1]);
                }
            }
        }

        elseif ($scene == 6)
        {
            $query->orderBy('ni.created_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(false,true);
                $query->andWhere(['in','ni.creator_id',$administrator_id]);
                $query->andWhere(['ni.is_cross'=>1]);
            }
        }

        elseif ($scene == 7)
        {
            $query->orderBy('ni.created_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
                /** @var NicheTeam $niche_id */
                $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['in','administrator_id',$administrator_id])->all();
                $niche_arr = array();
                foreach ($niche_id as $item){
                    array_push($niche_arr,$item->niche_id);
                }
                $query->andWhere(['or',['and','ni.is_cross = 1',['in','ni.creator_id',$administrator_id]],['in','ni.id',$niche_arr],['in','ni.administrator_id',$administrator_id]]);
            }
        }

        elseif ($scene == 11)
        {
            $query->andWhere(['ni.administrator_id'=>$administrator->id]);
            $query->orderBy('ni.stage_update_at desc');
        }

        elseif ($scene == 12)
        {
            $query->orderBy('ni.stage_update_at desc');
            /** @var NicheTeam $niche_id */
            $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator->id])->all();
            //如果查询到按条件查询  如果没有，返回为空
            if (!empty($niche_id)){
                $niche_arr = array();
                foreach ($niche_id as $item){
                    array_push($niche_arr,$item->niche_id);
                }
                $query->andWhere(['in','ni.id',$niche_arr]);
            }else{
                $query->andWhere(['ni.id'=>-1]);
            }
        }

        elseif ($scene == 13)
        {
            $query->orderBy('ni.created_at desc');
            $query->andWhere(['ni.creator_id'=>$administrator->id]);
            $query->andWhere(['ni.is_cross'=>1]);
        }

        elseif ($scene == 14)
        {
            $query->orderBy('ni.stage_update_at desc');
            $administrator_id = $administrator->getTreeAdministratorId(false,true);
            if (!empty($administrator_id))
            {
                $query->andWhere(['in','ni.administrator_id',$administrator_id]);
            }
            else
            {
                if (!($administrator->isBelongCompany() && $administrator->isCompany()))
                {
                    $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
                }
                else
                {
                    $query->andWhere(['ni.administrator_id'=>'-1']);
                }
            }
        }

        elseif ($scene == 15)
        {
            $query->orderBy('ni.stage_update_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(false,true);
                $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator_id])->all();
                //如果查询到按条件查询  如果没有，返回为空
                if (!empty($niche_id)){
                    $niche_arr = array();
                    foreach ($niche_id as $item){
                        array_push($niche_arr,$item->niche_id);
                    }
                    $query->andWhere(['in','ni.id',$niche_arr]);
                }else{
                    $query->andWhere(['ni.id'=>-1]);
                }
            }
        }

        elseif ($scene == 16)
        {
            $query->orderBy('ni.created_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(false,true);
                $query->andWhere(['in','ni.creator_id',$administrator_id]);
                $query->andWhere(['ni.is_cross'=>1]);
            }
        }

        elseif ($scene == 17)
        {
            $query->orderBy('ni.created_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
                if (empty($administrator_id))
                {
                    $administrator_id = '-1';
                }
                /** @var NicheTeam $niche_id */
                $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator_id])->all();
                $niche_arr = array();
                foreach ($niche_id as $item)
                {
                    array_push($niche_arr,$item->niche_id);
                }
                $query->andWhere(['or',['and','ni.is_cross = 1',['in','ni.creator_id',$administrator_id]],['in','ni.id',$niche_arr],['in','ni.administrator_id',$administrator_id]]);

            }
        }

        elseif ($scene == 21)
        {
            $query->orderBy('ni.stage_update_at desc');
            $query->andWhere(['ni.administrator_id'=>$administrator->id]);
        }

        elseif ($scene == 22)
        {
            $query->orderBy('ni.stage_update_at desc');
            /** @var NicheTeam $niche_id */
            $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator->id])->all();
            //如果查询到按条件查询  如果没有，返回为空
            if (!empty($niche_id)){
                $niche_arr = array();
                foreach ($niche_id as $item){
                    array_push($niche_arr,$item->niche_id);
                }
                $query->andWhere(['in','ni.id',$niche_arr]);
            }else{
                $query->andWhere(['ni.id'=>-1]);
            }
        }

        elseif ($scene == 23)
        {
            $query->orderBy('ni.created_at desc');
            $query->andWhere(['ni.creator_id'=>$administrator->id]);
            $query->andWhere(['ni.is_cross'=>1]);
        }

        elseif ($scene == 24)
        {
            $query->orderBy('ni.stage_update_at desc');
            $administrator_id = $administrator->getTreeAdministratorId(false,true);
            if (!empty($administrator_id))
            {
                $query->andWhere(['in','ni.administrator_id',$administrator_id]);
            }
            else
            {
                if (!($administrator->isBelongCompany() && $administrator->isCompany()))
                {
                    $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
                }
                else
                {
                    $query->andWhere(['ni.administrator_id'=>'-1']);
                }
            }
        }

        elseif ($scene == 25)
        {
            $query->orderBy('ni.stage_update_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(false,true);
                $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator_id])->all();
                //如果查询到按条件查询  如果没有，返回为空
                if (!empty($niche_id)){
                    $niche_arr = array();
                    foreach ($niche_id as $item){
                        array_push($niche_arr,$item->niche_id);
                    }
                    $query->andWhere(['in','ni.id',$niche_arr]);
                }else{
                    $query->andWhere(['ni.id'=>-1]);
                }
            }
        }

        elseif ($scene == 26)
        {
            $query->orderBy('ni.created_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(false,true);
                $query->andWhere(['in','ni.creator_id',$administrator_id]);
                $query->andWhere(['ni.is_cross'=>1]);
            }
        }

        elseif ($scene == 27)
        {
            $query->orderBy('ni.created_at desc');
            if (!($administrator->isBelongCompany() && $administrator->isCompany()))
            {
                $query->andWhere(['!=','ni.administrator_id',$administrator->id]);
            }
            else
            {
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
                if (empty($administrator_id))
                {
                    $administrator_id = '-1';
                }
                /** @var NicheTeam $niche_id */
                $niche_id = NicheTeam::find()->distinct()->select('niche_id')->where(['administrator_id'=>$administrator_id])->all();
                $niche_arr = array();
                foreach ($niche_id as $item)
                {
                    array_push($niche_arr,$item->niche_id);
                }
                $query->andWhere(['or',['and','ni.is_cross = 1',['in','ni.creator_id',$administrator_id]],['in','ni.id',$niche_arr],['in','ni.administrator_id',$administrator_id]]);

            }
        }

        return $query;
    }

}