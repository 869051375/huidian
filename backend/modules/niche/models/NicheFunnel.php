<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\CrmDepartment;
use common\models\Tag;
use yii\base\Model;
use common\models\Niche;
use yii\data\ActiveDataProvider;


/**
 * 商机商品
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheFunnel"))
 */
class NicheFunnel extends Model
{

    /**
     * 本周
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $week;

    /**
     * 本月
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $month;

    /**
     * 本季度
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $quarter;

    /**
     * 开始时间
     * @SWG\Property(example = "2018-09-10 12：00：00")
     * @var string
     */
    public $start;

    /**
     * 结束时间
     * @SWG\Property(example = "2018-09-10 12：00：00")
     * @var string
     */
    public $end;

    /**
     * 公司
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $company_id;

    /**
     * 部门
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $department_id;

    /**
     * 员工
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $administrator_id;

    /**
     * 省
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $province_id;


    /**
     * 市
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $city_id;

    /**
     * 区
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $district_id;

    /**
     * 来源
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $source_id;


    /**
     * 来源渠道
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $channel_id;

    /**
     * 阶段
     * @SWG\Property(example = 10)
     * @var integer
     */
    public $progress;

    /**
     * 每页多少条
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page_num;

    /**
     * 页码
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page;


    public function rules()
    {
        return [
            [['week','month','quarter','start','end','company_id','department_id','administrator_id','province_id','city_id','district_id','source_id','channel_id','progress','page_num','page'],'required'],
            [['week','month','quarter','province_id','city_id','district_id','source_id','channel_id','progress','page_num','page'], 'integer'],
            [['start','end','company_id','department_id','administrator_id'],'string']
        ];
    }


    public function getList()
    {
        $query = \common\models\NicheFunnel::find();
        $query->where(['>','id',0]);
        if(!empty($this->week)){
            $date = $this->date('week');
            $query->andWhere(['between','times',$date['start'],$date['end']]);
        }
        if(!empty($this->month)){
            $BeginDate = date('Y-m-01',strtotime(date("Y-m-d")));
            $query->andwhere(['between','times',$BeginDate,strtotime("$BeginDate +1 month -1 day")]);
        }
        if(!empty($this->quarter)){
            $date = $this->date('quarter');
            $query->andWhere(['between','times',$date['start'],$date['end']]);
        }
        if(!empty($this->start)){
            $query->andwhere(['>=','times',strtotime($this->start)]);

        }
        if(!empty($this->end)) {
            $query->andwhere(['<=', 'times', strtotime($this->end)]);
        }
        /** @var \common\models\Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!empty($this->company_id)){
            $company_id = explode(',',$this->company_id);
            $company = Administrator::find()->select('id')->where(['in','company_id',$company_id])->asArray()->all();
            $administrator_id = array_column($company,'id');
            $query->andWhere(['in','administrator_id',$administrator_id]);
        }

        if($administrator->is_belong_company ==1){
            if($administrator->isLeader()){
                if(empty($this->department_id)){
                    $department_ids = $administrator->getTreeDepartmentId(true);
                }else{
                    $department_ids = explode(',',$this->department_id);
                }
                $department = Administrator::find()->select('id')->where(['in','department_id',$department_ids])->asArray()->all();
                $administrator_id = array_column($department,'id');
                $query->andWhere(['in','administrator_id',$administrator_id]);
            }else{
                if(!empty($this->department_id)){
                    $department_ids = explode(',',$this->department_id);
                }else{
                    $department_ids = [$administrator->department_id];
                }
                $department = Administrator::find()->select('id')->where(['in','department_id',$department_ids])->asArray()->all();
                $administrator_id = array_column($department,'id');
                $query->andWhere(['in','administrator_id',$administrator_id]);
            }
        }
        if(!empty($this->administrator_id)){
            $administrator_id = explode(',',$this->administrator_id);
            $query->andWhere(['in','administrator_id',$administrator_id]);
        }else{
            $query->andWhere(['administrator_id'=>$administrator->id]);
        }
        if(!empty($this->province_id)){

            $query->andWhere(['province_id'=>$this->province_id]);
        }
        if(!empty($this->city_id)){
            $query->andWhere(['city_id'=>$this->city_id]);
        }
        if(!empty($this->district_id)){
            $query->andWhere(['district_id'=>$this->district_id]);
        }
        if(!empty($this->source_id)){
            $query->andWhere(['source_id'=>$this->source_id]);
        }
        if(!empty($this->channel_id)){
            $query->andWhere(['channel_id'=>$this->channel_id]);
        }
        if(!empty($this->progress)){
            $query->andWhere(['type'=>$this->progress]);
        }
        $niche_funnel = $query->select('niche_id')->asArray()->all();
        $ids = array_column($niche_funnel,'niche_id');
        $queryData = NichePublicLists::find();
        $queryData->distinct()->select('niche.*,niche_public.name as public_name,crm_contacts.name as customer_name');
        $queryData->leftJoin(['niche_public'=>\common\models\NichePublic::tableName()],'niche_public.id = niche.niche_public_id');
        $queryData->leftJoin(['crm_contacts'=>CrmContacts::tableName()],'niche.contacts_id = crm_contacts.id');
        $queryData->where(['in','niche.id',$ids]);
        $dataProvider = new ActiveDataProvider([
            'query' => $queryData,
            'pagination' => [
                'pageSize' => $this->page_num,
                'page' => $this->page-1,
            ]
        ]);
       return $dataProvider;
    }

    public function getFunnel()
    {
        $query = \common\models\NicheFunnel::find();
        $query->where(['>','id',0]);
        if(!empty($this->week)){
            $date = $this->date('week');
            $query->andWhere(['between','times',$date['start'],$date['end']]);
        }
        if(!empty($this->month)){
            $BeginDate = date('Y-m-01',strtotime(date("Y-m-d")));
            $query->andwhere(['between','times',strtotime($BeginDate),strtotime("$BeginDate +1 month -1 day")]);
        }
        if(!empty($this->quarter)){
            $date = $this->date('quarter');
            $query->andWhere(['between','times',$date['start'],$date['end']]);
        }
        if(!empty($this->start)){
            $query->andwhere(['>=','times',strtotime($this->start)]);

        }
        if(!empty($this->end)) {
            $query->andwhere(['<=', 'times', strtotime($this->end)]);
        }
        /** @var \common\models\Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!empty($this->company_id)){
            $company_id = explode(',',$this->company_id);
            $company = Administrator::find()->select('id')->where(['in','company_id',$company_id])->asArray()->all();
            $administrator_id = array_column($company,'id');
            $query->andWhere(['in','administrator_id',$administrator_id]);
        }

        if($administrator->is_belong_company ==1){
            if($administrator->isLeader()){
                if(empty($this->department_id)){
                    $department_ids = $administrator->getTreeDepartmentId(true);
                }else{
                    $department_ids = explode(',',$this->department_id);
                }
                $department = Administrator::find()->select('id')->where(['in','department_id',$department_ids])->asArray()->all();
                $administrator_id = array_column($department,'id');
                $query->andWhere(['in','administrator_id',$administrator_id]);
            }else{
                if(!empty($this->department_id)){
                    $department_ids = explode(',',$this->department_id);
                }else{
                    $department_ids = [$administrator->department_id];
                }
                $department = Administrator::find()->select('id')->where(['in','department_id',$department_ids])->asArray()->all();
                $administrator_id = array_column($department,'id');
                $query->andWhere(['in','administrator_id',$administrator_id]);
            }
        }
        if(!empty($this->administrator_id)){
            $administrator_id = explode(',',$this->administrator_id);
            $query->andWhere(['in','administrator_id',$administrator_id]);
        }else{
            $query->andWhere(['administrator_id'=>$administrator->id]);
        }
        if(!empty($this->province_id)){

            $query->andWhere(['province_id'=>$this->province_id]);
        }
        if(!empty($this->city_id)){
            $query->andWhere(['city_id'=>$this->city_id]);
        }
        if(!empty($this->district_id)){
            $query->andWhere(['district_id'=>$this->district_id]);
        }
        if(!empty($this->source_id)){
            $query->andWhere(['source_id'=>$this->source_id]);
        }
        if(!empty($this->channel_id)){
            $query->andWhere(['channel_id'=>$this->channel_id]);
        }
        if(!empty($this->progress)){
            $query->andWhere(['type'=>$this->progress]);
        }
        $niche_funnel = $query->select('niche_id,type')->asArray()->all();
        $progress = array();
        foreach ($niche_funnel as $key=>$value){
            $progress[$value['type']][] = $value['niche_id'];

        }
        $re = [];
        if(is_array($progress)){
            foreach ($progress as $kk=>$vv){
                $res = array_unique($vv);
                $count = Niche::find()->select('count(id) as number,sum(total_amount) as total_amount')->andWhere(['in','id',$res])->asArray()->all();
                $re[$kk] = $count[0];
            }
        }
        if(!array_key_exists('10',$re) || $re[10]['number'] == 0){
            $re[10] = ['number'=>0,'total_amount'=>0];
        }
        if(!array_key_exists('30',$re) || $re[30]['number'] == 0){
            $re[30] = ['number'=>0,'total_amount'=>0];
        }
        if(!array_key_exists('60',$re) || $re[60]['number'] == 0){
            $re[60] = ['number'=>0,'total_amount'=>0];
        }
        if(!array_key_exists('80',$re) || $re[80]['number'] == 0){
            $re[80] = ['number'=>0,'total_amount'=>0];
        }
        if(!array_key_exists('100',$re) || $re[100]['number'] == 0){
            $re[100] = ['number'=>0,'total_amount'=>0];
        }
        $number  = 0;
        $total_amount = 0;
        foreach ($re as $key=>$val){
            if(!in_array($key,[0,100])){
                $rate = $key/100;
                $number += $val['number']*$rate;
                $total_amount += floatval($val['total_amount'])*$rate;

            }
        }
        $re[1] = ['number'=>round($number,2),'total_number'=>round($total_amount,2)];
        return $re;
    }

    public function date($type)
    {
        if($type == 'week') {
            $date = date("Y-m-d");
            $first = 1;
            $w = date('w', strtotime($date));
            $start = date('Y-m-d', strtotime("$date -" . ($w ? $w - $first : 6) . ' days'));
            $end = date('Y-m-d', strtotime("$start +6 days"));
        }else{
            $date = ceil(date('n') /3);
            $start = date('Y-m-d',mktime(0,0,0,($date - 1) *3 +1,1,date('y')));
            $time = date('Y-m-d',mktime(0,0,0,$date * 3,1,date('Y')));
            $times = strtotime("$time+1month")-86400;
            $end = date('Y-m-d',$times);
        }
        return ['start'=>strtotime($start),'end'=>strtotime($end)];
    }


    public function add($niche_id,$type,$team = null)
    {
        $model = new \common\models\NicheFunnel();
        if(empty($team)){
            $niche_funnel = $model::find()->where(['niche_id'=>$niche_id])->all();
            if($niche_funnel){
                return $this->update($niche_id,$type);
            }
        }
        $niche = Niche::find()->where(['id'=>$niche_id])->asArray()->one();
        /** @var CrmContacts $contract */
        $contract = CrmContacts::find()->where(['customer_id'=>$niche['customer_id']])->asArray()->one();
        $model->niche_id = $niche_id;
        $model->type = $type;
        if(empty($team)){
            $model->administrator_id = isset($niche['administrator_id']) ? $niche['administrator_id']:0;
        }else{
            $model->administrator_id = isset($team) ? $team:0;
        }
        $model->province_id = isset($contract['province_id']) ? $contract['province_id']:0;
        $model->city_id = isset($contract['city_id']) ? $contract['city_id']:0;
        $model->district_id = isset($contract['district_id'])?$contract['district_id']:0;
        $model->source_id = isset($niche['source_id'])?$niche['source_id']:0;
        $model->channel_id = isset($niche['channel_id'])?$niche['channel_id']:0;
        $model->times = isset($niche['predict_deal_time'])?$niche['predict_deal_time']:0;
        return $model->save(false);
    }

    public function update($niche_id,$type){
        $niche = \common\models\NicheFunnel::find()->where(['niche_id'=>$niche_id])->asArray()->all();
        foreach ($niche as $key=>$value){
            /** @var \common\models\NicheFunnel $record */
            $record = \common\models\NicheFunnel::find()->where(['id'=>$value['id']])->one();
            $record->type = $type;
            $record->save(false);
        }
        return true;
    }

    public function del($niche_id,$admin_id = null)
    {
        if($admin_id){
            return \common\models\NicheFunnel::deleteAll(['niche_id'=>$niche_id,'administrator_id'=>$admin_id]);
        }else{
            return \common\models\NicheFunnel::deleteAll(['niche_id'=>$niche_id]);
        }
    }



    public function getDepartmentList($company_id)
    {
        $company_ids = explode(',',$company_id);
        return CrmDepartment::find()->select('id,name')->where(['in','company_id',$company_ids])->all();
    }


    public function getPersonList($company_id,$department_id)
    {
        if($department_id == 0){
            $company_id = explode(',',$company_id);
            return Administrator::find()->select('id,name')->where(['in','company_id',$company_id])->andWhere(['status'=>1])->andWhere(['is_belong_company'=>1])->andWhere(['is_dimission'=>0])->all();
        }
        $department_id = explode(',',$department_id);
        return Administrator::find()->select('id,name')->where(['in','company_id',$company_id])->andWhere(['in','department_id',$department_id])->andWhere(['status'=>1])->andWhere(['is_belong_company'=>1])->andWhere(['is_dimission'=>0])->all();
    }



}