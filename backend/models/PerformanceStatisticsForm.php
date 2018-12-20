<?php
namespace backend\models;

use common\models\Administrator;
use common\models\ExpectedProfitSettlementDetail;
use common\models\FixedPoint;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderBalanceRecord;
use common\models\OrderCalculateCollect;
use common\models\PerformanceRecord;
use common\models\PerformanceStatistics;
use common\models\PersonMonthProfit;
use common\utils\BC;
use Yii;
use yii\base\Model;

class PerformanceStatisticsForm extends Model
{
    public $performance_record_id;
    public $point;
    public $fix_point_id;
    public $rate; //业绩金额计提百分比

    /***
     * @var PerformanceRecord
     */
    public $performanceRecord;

    /***
     * @var Order
     */
    public $order;

    /***
     * @var Administrator
     */
    public $administrator;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['performance_record_id','fix_point_id'],'integer'],
            [['performance_record_id','rate'],'required'],
            ['performance_record_id','validatePerformanceRecordId'],
            [['rate'], 'number'],
            [['point'], 'boolean'],
            ['point', 'validatePoint'],
            ['rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
        ];
    }

    public function validatePerformanceRecordId()
    {
        $this->performanceRecord = PerformanceRecord::findOne($this->performance_record_id);
        if(null == $this->performanceRecord)
        {
            $this->addError('performance_record_id', '业绩记录不存在！');
        }
        if(floatval($this->performanceRecord->lavePerformance()) == 0)
        {
            $this->addError('performance_record_id', '业绩记录已计算完结！');
        }
        $this->order = Order::findOne($this->performanceRecord->order_id);
        if(null == $this->performanceRecord)
        {
            $this->addError('performance_record_id', '订单不存在！');
        }
        $this->administrator = Administrator::findOne($this->order->salesman_aid);
        if(null == $this->administrator)
        {
            $this->addError('performance_record_id', '业务员不存在！');
        }
        if(null == $this->administrator->department)
        {
            $this->addError('performance_record_id', '该业务员未关联部门！');
        }
    }

    public function validatePoint()
    {
        if($this->point)
        {
            if(empty($this->fix_point_id))
            {
                $this->addError('fix_point_id','固定点位选择不能为空');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rate' => '计算实际利润金额比例',
            'point' => '固定点位算法',
            'fix_point_id' => '固定点位选择',
        ];
    }

    //已计算业绩金额
    public function getCalculateAmount($performance)
    {
       return BC::div(BC::mul($performance,$this->rate),100,2);
    }

    private function getYearMonth($settlement_month)
    {
        if($settlement_month == null){
            $year = 0;
            $month = 0;
            $record = MonthProfitRecord::getLastRecord();
            if($record == null)
            {
                $year = date('Y');
                $month = date('m');
            }
            else
            {
                if($record->isFinish() && ($record->isPerformanceReady() ||  $record->isPerformanceDoing()))
                {
                    $year = $record->year;
                    $month = $record->month;
                }
                elseif ($record->isFinish() && $record->isPerformanceFinish())
                {
                    $year = $record->getNextMonth()['year'];
                    $month = $record->getNextMonth()['month'];
                }
            }
        }else{
            $year = substr($settlement_month,0,4);
            $month = substr($settlement_month,4,2);
        }

        return [
            'year' => $year,
            'month' => $month,
        ];
    }

    //todo 待优化
    public function dropCost()
    {
        $cost = BC::sub($this->order->virtualOrder->getTotalCost(),$this->order->virtualOrder->getTotalExpectedCost());
        if($cost != 0)
        {
            VirtualOrderExpectedCost::create($this->order->virtualOrder->id,'预计成本与实际成本差额',$cost,'');
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            //如果预计成本不等于实际成本
            if($this->order->getExpectedCost() != $this->order->getCost())
            {
                $cost = $this->order->getCost() == null ? 0 : $this->order->getCost();
                $expected_cost = $this->order->getExpectedCost() == null ? 0 : $this->order->getExpectedCost();
                $cash = $cost == 0 ?  (int)('-'.$expected_cost) : BC::sub($cost,$expected_cost);
                OrderExpectedCost::createExpectedCost($this->order->id,OrderExpectedCost::TYPE_CALCULATION,$this->order->virtual_order_id,'预计成本与实际成本差额',$cash);

                $surplusExpectedProfit = $cash >= 0 ? '-'.$cash : abs($cash);//剩余的业绩
                $time = time();
                $year = date('Y',$time);
                $month = date('m',$time);
                $rate = 100;
                $profit = 0;
                //处理共享人的预计利润
                foreach($this->order->orderTeams as $orderTeam)
                {
                    $teamExpectedProfit = BC::div(BC::mul($surplusExpectedProfit, $orderTeam->divide_rate), 100);
                    $profit += $teamExpectedProfit;
                    $rate = BC::sub($rate, $orderTeam->divide_rate, 2);
                    $data[]= [
                        'year' => $year,
                        'month' => $month,
                        'order_id' => $orderTeam->order_id,
                        'virtual_order_id' => $orderTeam->order->virtual_order_id,
                        'sn' => $orderTeam->order ? $orderTeam->order->sn : '',
                        'v_sn' => $orderTeam->order ? $orderTeam->order->virtualOrder->sn : '',
                        'type' => ExpectedProfitSettlementDetail::TYPE_CORRECT,
                        'company_id' => $orderTeam->administrator ? $orderTeam->administrator->company->id : 0,
                        'company_name' => $orderTeam->administrator ? $orderTeam->administrator->company->name : '',
                        'title' => '预计利润计算',
                        'remark' => '',
                        'administrator_id' => $orderTeam->administrator ? $orderTeam->administrator->id : 0,
                        'administrator_name' => $orderTeam->administrator ? $orderTeam->administrator->name : '',
                        'department_id' => $orderTeam->administrator ? $orderTeam->administrator->department->id : 0,
                        'department_name' => $orderTeam->administrator ? $orderTeam->administrator->department->name : '',
                        'department_path' => $orderTeam->administrator ? $orderTeam->administrator->department->path : '',
                        'expected_profit' => $teamExpectedProfit,
                        'created_at' => $time,
                        'creator_name' => $admin->name,
                        'creator_id' => $admin->id,
                    ];
                }

                if($rate < 100)
                {
                    //主要负责人分共享人分成后的剩余的全部预计利润
                    $surplusExpectedProfit = BC::sub($surplusExpectedProfit, $profit);
                }

                $data[]= [
                    'year' => $year,
                    'month' => $month,
                    'order_id' => $this->order->id,
                    'virtual_order_id' => $this->order->virtual_order_id,
                    'sn' => $this->order->sn,
                    'v_sn' => $this->order->virtualOrder->sn,
                    'type' => ExpectedProfitSettlementDetail::TYPE_CORRECT,
                    'company_id' => $this->order->salesmanCompany ? $this->order->salesman->company->id : 0,
                    'company_name' => $this->order->salesmanCompany ? $this->order->salesman->company->name : '',
                    'title' => '预计利润计算',
                    'remark' => '',
                    'administrator_id' => $this->order->salesman ? $this->order->salesman->id : 0,
                    'administrator_name' => $this->order->salesman ? $this->order->salesman->name : '',
                    'department_id' => $this->order->salesmanDepartment ? $this->order->salesman->department->id : 0,
                    'department_name' => $this->order->salesmanDepartment ? $this->order->salesman->department->name : '',
                    'department_path' => $this->order->salesmanDepartment ? $this->order->salesman->department->path : '',
                    'expected_profit' => $surplusExpectedProfit,
                    'created_at' => $time,
                    'creator_name' => $admin->name,
                    'creator_id' => $admin->id,
                ];
            }
            if(empty($data))
            {
                return false;
            }
            $t = Yii::$app->db->beginTransaction();
            try
            {
                \Yii::$app->db->createCommand()->batchInsert(ExpectedProfitSettlementDetail::tableName(), [
                    'year',
                    'month',
                    'order_id',
                    'virtual_order_id',
                    'sn',
                    'v_sn',
                    'type',
                    'company_id',
                    'company_name',
                    'title',
                    'remark',
                    'administrator_id',
                    'administrator_name',
                    'department_id',
                    'department_name',
                    'department_path',
                    'expected_profit',
                    'created_at',
                    'creator_name',
                    'creator_id',
                ], $data)->execute();
                $t->commit();
                return true;
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
        }
    }

    public function calculate()
    {
        if(!$this->validate())return false;
        $data = [];
        $rate = 100;//负责人的百分比
        $partition_rate = 0;//提成
        $time = time();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        //计算提成获取指定列的年月
        /** @var PerformanceRecord $performance_date */
        $performance_date = $this->getDate($this->performance_record_id);
        $finishRecord = MonthProfitRecord::getLastRecord();//获取最后一次的预计利润结算记录
        $algorithm = $this->point ? PerformanceStatistics::ALGORITHM_POINT : PerformanceStatistics::ALGORITHM_GENERAL;
        if(null == $finishRecord || !$finishRecord->isFinish())
        {
            $this->addError('virtual_order_id','对不起，当前订单没有结算过预计利润！');
            return false;
        }
        $order = Order::findOne($this->performanceRecord->order_id);
        if($order->virtualOrder->getOrderCost() != $order->virtualOrder->getTotalCost())
        {
            $this->addError('virtual_order_id','对不起，当前虚拟订单实际成本和子订单已录入实际成本存在金额不相等的情况，请检查后计算。');
            return false;
        }
        $date = $this->getYearMonth($order->settlement_month);
        /** @var PersonMonthProfit $personModel */
        $personModel = PersonMonthProfit::find()
            ->where(['administrator_id' => $order->salesman->id])
            ->andWhere(['year' => $date['year'],'month' => $date['month']])
            ->limit(1)->one();
        if(null == $personModel && $this->point == 0)
        {
            $this->addError('virtual_order_id','对不起，当前订单没有结算过预计利润！');
            return false;
        }
        elseif($personModel && $this->point == 0)
        {
            $partition_rate = $personModel->reward_proportion;
        }
        elseif($this->point)
        {
            $fixPoint = FixedPoint::findOne($this->fix_point_id);
            $partition_rate = $fixPoint->rate;
        }
        $totalPerformance = $this->getCalculateAmount($this->performanceRecord->lavePerformance());
        $performance = 0;
        if($order->orderTeams)
        {
            foreach($order->orderTeams as $orderTeam)
            {
                $team_partition_rate = 0;
                $administrator = Administrator::findOne($orderTeam->administrator_id);
                /** @var PersonMonthProfit $teamPersonModel */
                $teamPersonModel = PersonMonthProfit::find()
                    ->where(['administrator_id' => $administrator->id])
                    ->andWhere(['year' => $date['year'],'month' => $date['month']])
                    ->limit(1)->one();
                if(null == $teamPersonModel && $this->point == 0)
                {
                    $this->addError('virtual_order_id','对不起，当前订单没有结算过预计利润！');
                    return false;
                }
                elseif($teamPersonModel && $this->point == 0)
                {
                    $team_partition_rate = $teamPersonModel->reward_proportion;
                }
                elseif($this->point)
                {
                    $fixPoint = FixedPoint::findOne($this->fix_point_id);
                    $team_partition_rate = $fixPoint->rate;
                }
                $calculated_performance = BC::div(BC::mul($totalPerformance, $orderTeam->divide_rate, 2), 100, 2);//以计算的业绩
                $performance += $calculated_performance;
                $rate = BC::sub($rate, $orderTeam->divide_rate, 2);
                $data[] = [
                    'virtual_order_id' => $order->virtualOrder->id,
                    'order_id' => $order->id,
                    'administrator_id' => $administrator->id,
                    'administrator_name' => $administrator->name,
                    'department_id' => $administrator->department->id,
                    'department_name' => $administrator->department->name,
                    'year' => $performance_date->year,
                    'month' => $performance_date->month,
                    'type' => PerformanceStatistics::TYPE_GENERAL,
                    'algorithm_type' => $algorithm,
                    'title' => '业绩提成计算',
                    'remark' => '',
                    'calculated_performance' =>  $calculated_performance,//已计算业绩金额,
                    'performance_reward' => BC::div(BC::mul($calculated_performance,$team_partition_rate),100,2),//业绩提成
                    'reward_proportion' => $team_partition_rate,
                    'creator_id' => $admin->id,
                    'creator_name' => $admin->name,
                    'created_at' => $time,
                ];
            }
        }
        if($rate < 100)
        {
            //主要负责人分共享人分成后的剩余的全部预计利润
            $totalPerformance = BC::sub($totalPerformance, $performance);
        }
        $this->performanceRecord->calculated_performance += $totalPerformance;
        $this->performanceRecord->save(false);
        $data[] = [
            'virtual_order_id' => $order->virtualOrder->id,
            'order_id' => $order->id,
            'administrator_id' => $order->salesman->id,
            'administrator_name' => $order->salesman->name,
            'department_id' => $order->salesman->department->id,
            'department_name' => $order->salesman->department->name,
            'year' => $performance_date->year,
            'month' => $performance_date->month,
            'type' => PerformanceStatistics::TYPE_GENERAL,
            'algorithm_type' => $algorithm,
            'title' => '业绩提成计算',
            'remark' => '',
            'calculated_performance' =>  $totalPerformance,//已计算业绩金额,
            'performance_reward' => BC::div(BC::mul($totalPerformance,$partition_rate),100,2),//业绩提成
            'reward_proportion' => $partition_rate,
            'creator_id' => $admin->id,
            'creator_name' => $admin->name,
            'created_at' => $time,
        ];
        $t = Yii::$app->db->beginTransaction();
        try
        {
            $order->is_apply = 0;
            $order->actual_profit_calculate = 1;
            $order->save(false);
            /** @var OrderBalanceRecord $order_balance_record */
            $order_balance_record = OrderBalanceRecord::find() -> where(['order_id' =>$this->performanceRecord->order_id]) -> orderBy(['id'=>SORT_DESC]) -> one();
            if(!empty($order_balance_record)){
                $order_balance_record -> status = 3;
                $order_balance_record->save(false);
            }
            $this->dropCost();
            \Yii::$app->db->createCommand()->batchInsert(PerformanceStatistics::tableName(), [
                'virtual_order_id',
                'order_id',
                'administrator_id',
                'administrator_name',
                'department_id',
                'department_name',
                'year',
                'month',
                'type',
                'algorithm_type',
                'title',
                'remark',
                'calculated_performance',
                'performance_reward',
                'reward_proportion',
                'creator_id',
                'creator_name',
                'created_at',
            ], $data)->execute();
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    //获取提点时间
    private function getDate($performance_record_id){
        if($performance_record_id != ''){
            /** @var PerformanceRecord $date */
            $date = PerformanceRecord::findOne($performance_record_id);
        }else{
            /** @var MonthProfitRecord $date */
            $date = MonthProfitRecord::getLastRecord();//获取最后一次的预计利润结算记录
        }
        return $date;
    }
}