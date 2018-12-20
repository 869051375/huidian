<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmClue;
use common\models\CrmCustomer;
use common\models\CustomerExchangeCensus;
use yii\base\Model;


/**
 * 商机统计
 * @SWG\Definition(required={}, @SWG\Xml(name="CustomerExchangeList"))
 */
class CustomerExchangeList extends Model
{

    /**
     * 本周
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $week;

    /**
     * 上周
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $last_week;

    /**
     * 本月
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $month;

    /**
     * 上月
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $last_month;

    /**
     * 本季度
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $quarter;

    /**
     * 上季度
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $last_quarter;

    /**
     * 本年度
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $year;

    /**
     * 上年度
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $last_year;

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



    public function rules()
    {
        return [
            [['week','last_week','month','last_month','quarter','last_quarter','year','last_year','start','end','company_id','department_id','administrator_id','province_id','city_id','district_id','source_id','channel_id'],'required'],
            [['week','last_week','last_month','month','quarter','last_quarter','year','last_year','province_id','city_id','district_id','source_id','channel_id','progress'], 'integer'],
            [['start','end','company_id','department_id','administrator_id'],'string']
        ];
    }


    public function getList()
    {
        $query = CustomerExchangeCensus::find();
        $query->where(['>','id',0]);
        if(!empty($this->week)){
            $date = $this->dates('week');
            $query->andWhere(['between','create_at',strtotime($date['end']),strtotime($date['start'])]);
        }
        if(!empty($this->last_week)){
            $date = $this->dates('last_week');
            $query->andWhere(['between','create_at',strtotime($date['end']),strtotime($date['start'])]);
        }
        if(!empty($this->month)){
            $query->andWhere(['year'=>date('Y',time())]);
            $query->andWhere(['month'=>date('m',time())]);
        }
        if(!empty($this->last_month)){
            $query->andWhere(['year'=>date('Y',time())]);
            $query->andWhere(['month'=>date('m',time())-1]);
        }
        if(!empty($this->quarter)){
            $query->andWhere(['year'=>date('Y',time())]);
            $date = $this->dates('quarter');
            $query->andWhere(['>=','month',$date['start']]);
            $query->andWhere(['<=','month',$date['end']]);
        }
        if(!empty($this->last_quarter)){
            $query->andWhere(['year'=>date('Y',time())]);
            $date = $this->dates('last_quarter');
            $query->andWhere(['>=','month',$date['start']]);
            $query->andWhere(['<=','month',$date['end']]);
        }
        if(!empty($this->year)){
            $query->andWhere(['year'=>date('Y',time())]);
        }
        if(!empty($this->last_year)){
            $query->andWhere(['year'=>date('Y',time())-1]);
        }

        if(!empty($this->start)){
            $query->andWhere(['>=','create_at',strtotime($this->start)]);
        }
        if(!empty($this->end)){
            $query->andWhere(['<=','day',strtotime($this->end)]);
        }
        /** @var \common\models\Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!empty($this->company_id)){
            $company_id = explode(',',$this->company_id);
            $administrator = Administrator::find()->where(['in','company_id',$company_id])->asArray()->all();
            $administrator_ids = array_column($administrator,'id');
            $query->andWhere(['in','administrator_id',$administrator_ids]);
        }
        if(!empty($this->department_id)){
            $department_id = explode(',',$this->department_id);
            $administrator = Administrator::find()->Where(['in','department_id',$department_id])->asArray()->all();
            $administrator_ids = array_column($administrator,'id');
            $query->andWhere(['in','administrator_id',$administrator_ids]);
        }

        if(isset($administrator->company_id) && $administrator->company_id !=0 && isset($administrator->department_id) && $administrator->department_id !=0 && empty($this->department_id)){
            if($administrator->isLeader()){
                $department_ids = $administrator->getTreeDepartmentId(true);
                $administrator = Administrator::find()->Where(['in','department_id',$department_ids])->asArray()->all();
                $administrator_ids = array_column($administrator,'id');
                $query->andWhere(['in','administrator_id',$administrator_ids]);
            }else{
                $query->andWhere(['administrator_id'=>$administrator->id]);
            }
        }
        if(!empty($this->administrator_id)){
            $administrator_id = explode(',',$this->administrator_id);
            $query->andWhere(['in','administrator_id',$administrator_id]);
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
        $result = $query->select('count(id) as num,sum(amount) as amount,type')->groupBy('type')->asArray()->all();
        $re = [];
        foreach ($result as $key=>$val){
            $re[$val['type']] = $val;
        }
        foreach ($re as $key =>$value){
            if(!isset($re[1])){
                $re[1] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>1];
            }
            if(isset($re[2]['num']) && isset($re[1]['num'])){
                @$re[2]['percent'] = $this->getFloat($re[2]['num'],$re[1]['num']);
            }else{
                $re[2] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>2];
            }
            if(isset($re[3]['num']) && $re[3]['num'] != 0 && isset($re[1]['num'])){
                @$re[3]['percent'] = $this->getFloat($re[3]['num'],$re[1]['num']);
            }else{
                $re[3] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>3];
            }
            if (!isset($re[4])){
                $re[4] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>4];
            }
            if (!isset($re[5])){
                $re[5] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>5];
            }
            if(isset($re[6]['num']) && $re[6]['num'] != 0 && isset($re[4]['num'])){
                @$re[6]['percent'] = $this->getFloat($re[6]['num'],$re[4]['num']);
            }else{
                $re[6] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>6];
            }
            if(isset($re[7]['num']) && $re[7]['num'] != 0 && isset($re[4]['num'])){
                @$re[7]['percent'] = $this->getFloat($re[7]['num'],$re[4]['num']);
            }else{
                $re[7] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>7];
            }
            if (!isset($re[8])){
                $re[8] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>8];
            }
            if (!isset($re[9])){
                $re[9] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>9];
            }
            if(isset($re[10]['num']) && $re[10]['num'] != 0 && isset($re[8]['num'])){
                @$re[10]['percent'] = $this->getFloat($re[10]['num'],$re[8]['num']);
            }else{
                $re[10] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>10];
            }
            if(isset($re[11]['num']) && $re[11]['num'] != 0 && isset($re[8]['num'])){
                @$re[11]['percent'] = $this->getFloat($re[11]['num'],$re[8]['num']);
            }else{
                $re[11] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>11];
            }
            if (!isset($re[12])){
                $re[12] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>12];
            }
            if (!isset($re[13])){
                $re[13] = ['num'=>0,'amount'=>'0.00','percent'=>'0%','type'=>13];
            }
        }
        $data = [];
        foreach ($re as $key=>$value){
            if((isset($value['percent']) && $value['percent'] =='INF%') || (isset($value['percent']) &&$value['percent'] =='NAN%')){
                $value['percent'] = '0%';
            }
            $data[] = $value;
        }
        return $data;
    }

    private function getFloat($new, $news)
    {
        $floatNum = 0;
        if (!empty($new) && !empty($news)) {
            $floatNum = round($new / $news, 2);
        }
        return ($floatNum * 100) . '%';
    }



    public function dates($type)
    {
        $start = '';
        $end = '';
        if($type == 'week') {
            $date = date("Y-m-d");
            $first = 1;
            $w = date('w', strtotime($date));
            $end = date('Y-m-d', strtotime("$date -" . ($w ? $w - $first : 6) . ' days'));
            $start = date('Y-m-d', strtotime("$start +6 days"));
        }elseif ($type == 'last_week'){
            $date = date("Y-m-d");
            $first = 1;
            $w = date('w', strtotime($date));
            $start = date('Y-m-d', strtotime("$date -" . ($w ? $w - $first : 6) . ' days')-86400);
            $end = date('Y-m-d',strtotime($date)-86400*7);
       }elseif($type == 'quarter'){
            $date = ceil(date('n') /3);
            $start = date('m',mktime(0,0,0,($date - 1) *3 +1,1,date('y')));
            $end = date('m',mktime(0,0,0,$date * 3,1,date('Y')));
        }elseif ($type == 'last_quarter'){
            $date = ceil(date('n') /3);
            $start = date('m',mktime(0,0,0,($date - 1) *3 +1,1,date('y')))-3;
            $end = date('m',mktime(0,0,0,$date * 3,1,date('Y')))-3;
        }
        return ['start'=>$start,'end'=>$end];
    }

    public function explodeDate($date){
        $year = substr($date,0,4);
        $month = substr($date,5,2);
        $day = substr($date,-2);
        return ['year'=>$year,'month'=>$month,'day'=>$day];
    }

    public function clue($data,$type = null)
    {
        $model = new CustomerExchangeCensus();
        if($type == 'giveup'){
            return $model::deleteAll(['clue_id'=>$data['id'],'administrator_id'=>$data['administrator_id']]);
        }elseif ($type == 'change'){
            $model::deleteAll(['clue_id'=>$data['id'],'administrator_id'=>$data['from']]);
        }
        $model->clue_id = $data['id'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::ADD_CLUE;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }

    public function clueToCustomer($data)
    {
        $model = new CustomerExchangeCensus();
        $model->clue_id = $data['id'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::CLUE_CUSTOMER;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }

    public function clueToNiche($data)
    {
        $model = new CustomerExchangeCensus();
        $model->clue_id = $data['id'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::CLUE_NICHE;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }


    public function  customer($data,$type = null)
    {
        $model = new CustomerExchangeCensus();
        if($type == 'giveup'){
            return $model::deleteAll(['customer_id'=>$data['id'],'administrator_id'=>$data['administrator_id']]);
        }elseif ($type == 'change'){
            $model::deleteAll(['customer_id'=>$data['id'],'administrator_id'=>$data['from']]);
        }else{
            $this->newCustomer($data);
        }
        $id = isset($data['id'])?intval($data['id']):0;
        $model->customer_id = $id;
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::ADD_CUSTOMER;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        $model->save(false);
        return true;
    }

    public function newCustomer($data)
    {
        /** @var CrmCustomer $customer */
        $id = isset($data['id'])?intval($data['id']):0;
        $customer = CrmCustomer::find()->where(['id'=>$id])->one();
        if(isset($customer->creator_id) && $customer->creator_id == $data['administrator_id']){
            $model = new CustomerExchangeCensus();
            $model->customer_id = $data['id'];
            $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
            $model->province_id = isset($data['province_id'])?$data['province_id']:0;
            $model->city_id = isset($data['city_id'])?$data['city_id']:0;
            $model->district_id = isset($data['district_id'])?$data['district_id']:0;
            $model->source_id = isset($data['source_id'])?$data['source_id']:0;
            $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
            $model->type = CustomerExchange::NEW_CUSTOMER;
            $model->year = date('Y',time());
            $model->month = date('m',time());
            $model->day = date('d',time());
            $model->create_at = time();
            $model->save(false);
            return true;
        }
    }


    public function customerToNiche($data)
    {
        $niche = \common\models\Niche::find()->where(['id'=>$data['id']])->asArray()->one();
        $customer = CrmCustomer::find()->where(['id'=>$niche['customer_id']])->asArray()->one();
        $record = CustomerExchangeCensus::find()->where(['customer_id'=>$customer['id']])->andWhere(['type'=>CustomerExchange::CUSTOMER_NICHE])->asArray()->one();
        if($record){
            return true;
        }
        $model = new CustomerExchangeCensus();
        $model->customer_id = $customer['id'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::CUSTOMER_NICHE;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }

    public function niche($data,$type = null)
    {
        $model = new CustomerExchangeCensus();
        if($type == 'giveup'){
            return $model::deleteAll(['niche_id'=>$data['id'],'administrator_id'=>$data['administrator_id']]);
        }elseif ($type == 'change'){
            $model::deleteAll(['niche_id'=>$data['id'],'administrator_id'=>$data['from']]);
        }else{
            $this->customerToNiche($data);
            $this->newNiche($data);
        }
        $model->niche_id = $data['id'];
        $model->amount = $data['amount'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::ADD_NICHE;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }

    public function newNiche($data)
    {
        /** @var \common\models\Niche $niche */
        $niche = \common\models\Niche::find()->where(['id'=>$data['id']])->one();
        if($niche->creator_id == $data['administrator_id']){
            $model = new CustomerExchangeCensus();
            $model->niche_id = $data['id'];
            $model->amount = $data['amount'];
            $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
            $model->province_id = isset($data['province_id'])?$data['province_id']:0;
            $model->city_id = isset($data['city_id'])?$data['city_id']:0;
            $model->district_id = isset($data['district_id'])?$data['district_id']:0;
            $model->source_id = isset($data['source_id'])?$data['source_id']:0;
            $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
            $model->type = CustomerExchange::NEW_NICHE;
            $model->year = date('Y',time());
            $model->month = date('m',time());
            $model->day = date('d',time());
            $model->create_at = time();
            return $model->save(false);
        }
    }

    public function nicheToWin($data)
    {
        $model = new CustomerExchangeCensus();
        $model->niche_id = $data['id'];
        $model->amount = $data['amount'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::NICHE_WIN;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }

    public function win($data)
    {
        $model = new CustomerExchangeCensus();
        $model->amount = $data['amount'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::WIN;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        $model->save(false);
        $this->customerToWin($data);
        return $this->nicheToWin($data);
    }

    public function customerToWin($data)
    {
        $model = new CustomerExchangeCensus();
        /** @var \common\models\Niche $niche */
        $niche = \common\models\Niche::find()->where(['id'=>$data['id']])->one();
        $model->customer_id = $niche->customer_id;
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::CUSTOMER_WIN;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        $model->save(false);
    }

    public function nicheToLose($data){
        $model = new CustomerExchangeCensus();
        $model->niche_id = $data['id'];
        $model->amount = $data['amount'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::NICHE_LOSE;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        return $model->save(false);
    }

    public function lose($data)
    {
        $model = new CustomerExchangeCensus();
        $model->amount = $data['amount'];
        $model->administrator_id = isset($data['administrator_id']) ? $data['administrator_id']:0;
        $model->province_id = isset($data['province_id'])?$data['province_id']:0;
        $model->city_id = isset($data['city_id'])?$data['city_id']:0;
        $model->district_id = isset($data['district_id'])?$data['district_id']:0;
        $model->source_id = isset($data['source_id'])?$data['source_id']:0;
        $model->channel_id = isset($data['channel_id'])?$data['channel_id']:0;
        $model->type = CustomerExchange::LOSE;
        $model->year = date('Y',time());
        $model->month = date('m',time());
        $model->day = date('d',time());
        $model->create_at = time();
        $model->save(false);
        return $this->nicheToLose($data);
    }

    public function correct($id){
        $subject = BusinessSubject::find()->where(['id'=>$id])->asArray()->one();
        CustomerExchangeCensus::deleteAll(['customer_id'=>$subject['customer_id'],'type'=>5]);
        $clue = CrmClue::find()->where(['business_subject_id'=>$subject['id']])->asArray()->one();
        $data = [
            'id'=>$clue['id'],
            'administrator_id' => $clue['administrator_id'],
            'province_id'=>$clue['province_id'],
            'city_id'=>$clue['city_id'],
            'district_id'=>$clue['district_id'],
            'source_id'=>$clue['source_id'],
            'channel_id'=>$clue['channel_id']
        ];
        $this->clueToCustomer($data);
    }

    public function updateClue($id)
    {
        $census = CustomerExchangeCensus::find()->where(['clue_id'=>$id])->asArray()->all();
        if($census && !empty($census)){
            foreach ($census as $key=>$val){
                /** @var CustomerExchangeCensus $census_one */
                $census_one = CustomerExchangeCensus::find()->where(['clue_id'=>$val['id']])->one();
                if (empty($census_one))
                {
                    return true;
                }
                /** @var CrmClue $clue */
                $clue = CrmClue::find()->where(['id'=>$id])->one();
                $census_one->province_id = isset($clue->province_id)?$clue->province_id:0;
                $census_one->city_id = isset($clue->city_id)?$clue->city_id:0;
                $census_one->district_id = isset($clue->district_id)?$clue->district_id:0;
                $census_one->source_id = isset($clue->source_id)?$clue->source_id:0;
                $census_one->channel_id = isset($clue->channel_id)?$clue->channel_id:0;
                $census_one->save(false);
            }
        }
        return true;
    }
    public function updateCustomer($id)
    {
        $census = CustomerExchangeCensus::find()->where(['clue_id'=>$id])->asArray()->all();
        if($census && !empty($census)){
            foreach ($census as $key=>$val){
                /** @var CustomerExchangeCensus $census_one */
                $census_one = CustomerExchangeCensus::find()->where(['clue_id'=>$val['id']])->one();
                /** @var CrmCustomer $customer */
                $customer = CrmCustomer::find()->where(['id'=>$id])->one();
                /** @var BusinessSubject $subject */
                $subject = BusinessSubject::find()->where(['customer_id'=>$id])->one();
                $census_one->province_id = isset($subject->province_id)?$subject->province_id:(isset($customer->province_id)?$customer->province_id:0);
                $census_one->city_id = isset($subject->city_id)?$subject->city_id:isset($customer->city_id)?$customer->city_id:0;
                $census_one->district_id = isset($subject->district_id)?$subject->district_id:isset($customer->district_id)?$customer->district_id:0;
                $census_one->source_id = isset($customer->source_id)?$customer->source_id:0;
                $census_one->channel_id = isset($customer->channel_id)?$customer->channel_id:0;
                $census_one->save(false);
            }
        }
        return true;
    }
    public function updateNiche($id)
    {
        $census = CustomerExchangeCensus::find()->where(['clue_id'=>$id])->asArray()->all();
        if($census && !empty($census)){
            foreach ($census as $key=>$val){
                /** @var CustomerExchangeCensus $census_one */
                $census_one = CustomerExchangeCensus::find()->where(['clue_id'=>$val['id']])->one();
                if (!empty($census_one))
                {
                    /** @var \common\models\Niche $niche */
                    $niche = \common\models\Niche::find()->where(['id'=>$id])->one();
                    /** @var CrmCustomer $customer */
                    $customer = CrmCustomer::find()->where(['id'=>$niche['customer_id']])->one();
                    $census_one->province_id = isset($customer->province_id)?$customer->province_id:0;
                    $census_one->city_id = isset($customer->city_id)?$customer->city_id:0;
                    $census_one->district_id = isset($customer->district_id)?$customer->district_id:0;
                    $census_one->source_id = isset($niche->source_id)?$niche->source_id:0;
                    $census_one->channel_id = isset($niche->channel_id)?$niche->channel_id:0;
                    $census_one->save(false);

                    /** @var \common\models\NicheFunnel $funnel */
                    $funnel = \common\models\NicheFunnel::find()->where(['niche_id'=>$id])->one();

                    if (!empty($funnel))
                    {
                        $funnel->province_id = isset($customer->province_id)?$customer->province_id:0;
                        $funnel->city_id = isset($customer->city_id)?$customer->city_id:0;
                        $funnel->district_id = isset($customer->district_id)?$customer->district_id:0;
                        $funnel->source_id = isset($niche->source_id)?$niche->source_id:0;
                        $funnel->channel_id = isset($niche->channel_id)?$niche->channel_id:0;
                        $funnel->times = isset($niche->predict_deal_time)?$niche->predict_deal_time:0;
                        $funnel->save(false);
                    }
                }
            }
        }
        return true;
    }

}