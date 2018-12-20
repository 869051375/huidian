<?php
namespace backend\models;
use common\models\Administrator;
use common\models\ExpectedProfitSettlementDetail;
use common\models\FixedPoint;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderBalanceRecord;
use common\models\PerformanceRecord;
use common\models\PerformanceStatistics;
use common\models\PersonMonthProfit;
use common\models\VirtualOrder;
use common\utils\BC;
use Yii;
use yii\base\Model;

class CalculateProfitForm extends Model
{
    public $rate;
    public $point;
    public $fix_point_id;
    public $virtual_order_id;

    /**
     * @var Order[]
     */
    public $orders;

    /**
     * @var VirtualOrder
     */
    public $virtualOrder;

    public function rules()
    {
        return [
            [['virtual_order_id','rate','fix_point_id'], 'integer'],
            [['virtual_order_id','rate'], 'required'],
            [['point'], 'boolean'],
            ['rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
            ['virtual_order_id', 'validateVirtualOrderId'],
            ['point', 'validatePoint'],
        ];
    }

    public function validateVirtualOrderId()
    {
        $this->virtualOrder = VirtualOrder::findOne($this->virtual_order_id);
        if(null == $this->virtualOrder)
        {
            $this->addError('virtual_order_id','找不到要计算的订单');
        }
        else
        {
            foreach($this->virtualOrder->orders as $order)
            {
                if(empty($order->settlement_month))
                {
                    $this->addError('virtual_order_id','订单业绩提点月不能为空');
                }
                $date = $this->getDate($order->settlement_month);
                $model = MonthProfitRecord::find()->select('id')->where(['year' => $date['year'] ,'month' => $date['month']])->limit(1)->one();
                if(null == $model)
                {
                    $this->addError('virtual_order_id','对不起，当前订单没有结算过预计利润');
                }
            }
            $cost = BC::sub($this->virtualOrder->getTotalCost(),$this->virtualOrder->getOrderCost());
            if($cost < 0)
            {
                $this->addError('virtual_order_id','计算分配实际成本金额为负数不能计算业绩');
            }
//            if($this->virtualOrder->getOrderCost() != $this->virtualOrder->getTotalCost())
//            {
//                $this->addError('virtual_order_id','对不起，当前虚拟订单实际成本和子订单已录入实际成本存在金额不相等的情况，请检查后计算。');
//                return false;
//            }
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

    public function getDate($date)
    {
        $data = [];
        $data['year'] = mb_substr($date,0,4);
        $data['month'] = mb_substr($date,4,2);
        return $data;
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
    public function getCalculateAmount($price)
    {
        return BC::div(BC::mul($price,$this->rate,5),100,5);
    }

    //todo 待优化
    public function dropCost()
    {
        $cost = BC::sub($this->virtualOrder->getTotalCost(),$this->virtualOrder->getTotalExpectedCost());
        if($cost != 0)
        {
            VirtualOrderExpectedCost::create($this->virtualOrder->id,'预计成本与实际成本差额',$cost,'');
            $order_ids = $this->virtualOrder->getOrderIds();
            $data = [];
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            foreach ($this->virtualOrder->orders as $order)
            {
                if(in_array($order->id,$order_ids))
                {
                    //如果预计成本不等于实际成本
                    if($order->getExpectedCost() != $order->getCost())
                    {
                        $cost = $order->getCost() == null ? 0 : $order->getCost();
                        $expected_cost = $order->getExpectedCost() == null ? 0 : $order->getExpectedCost();
                        $cash = $cost == 0 ? (int)('-'.$expected_cost) : BC::sub($cost,$expected_cost);
                        OrderExpectedCost::createExpectedCost($order->id,OrderExpectedCost::TYPE_CALCULATION,$order->virtual_order_id,'预计成本与实际成本差额',$cash);

                        $surplusExpectedProfit = $cash >= 0 ? '-'.$cash : abs($cash);//剩余的业绩
                        $time = time();
                        $year = date('Y',$time);
                        $month = date('m',$time);
                        $rate = 100;
                        $profit = 0;
                        //处理共享人的预计利润
                        foreach($order->orderTeams as $orderTeam)
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
                            'order_id' => $order->id,
                            'virtual_order_id' => $order->virtual_order_id,
                            'sn' => $order->sn,
                            'v_sn' => $order->virtualOrder->sn,
                            'type' => ExpectedProfitSettlementDetail::TYPE_CORRECT,
                            'company_id' => $order->salesmanCompany ? $order->salesman->company_id : 0,
                            'company_name' => $order->salesmanCompany ? $order->salesman->company->name : '',
                            'title' => '预计利润计算',
                            'remark' => '',
                            'administrator_id' => $order->salesman ? $order->salesman->id : 0,
                            'administrator_name' => $order->salesman ? $order->salesman->name : '',
                            'department_id' => $order->salesmanDepartment ? $order->salesman->department_id : 0,
                            'department_name' => $order->salesmanDepartment ? $order->salesman->department->name : '',
                            'department_path' => $order->salesmanDepartment ? $order->salesman->department->path : '',
                            'expected_profit' => $surplusExpectedProfit,
                            'created_at' => $time,
                            'creator_name' => $admin->name,
                            'creator_id' => $admin->id,
                        ];
                    }
                }
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

    public function save()
    {
        if(!$this->validate()) return false;

        $data = [];
        $time = time();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $finishRecord = MonthProfitRecord::getLastRecord();//获取最后一次的预计利润结算记录
        $algorithm = $this->point ? PerformanceStatistics::ALGORITHM_POINT : PerformanceStatistics::ALGORITHM_GENERAL;
        if(null == $finishRecord || !$finishRecord->isFinish())
        {
            $this->addError('virtual_order_id','对不起，当前订单没有结算过预计利润！');
            return false;
        }
        /** @var PerformanceRecord[] $recordModels */
        $recordModels = PerformanceRecord::find()->where(['virtual_order_id' => $this->virtualOrder->id])->all();

        foreach($recordModels as $record)
        {
            if(floatval($record->lavePerformance()) == 0) continue;//如果没有业绩跳出
            $rate = 100;//负责人的百分比
            $partition_rate = 0;//提成
            $date = $this->getDate($record->order->settlement_month);

            /** @var PersonMonthProfit $personModel */
            $personModel = PersonMonthProfit::find()
                        ->where(['administrator_id' => $record->order->salesman->id])
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
            $totalPerformance = $this->getCalculateAmount($record->lavePerformance());//剩余的业绩
            $performance = 0;
            if($record->order->orderTeams)
            {
                foreach($record->order->orderTeams as $team)
                {
                    $team_partition_rate = 0;
                    $administrator = Administrator::findOne($team->administrator_id);
                    /** @var PersonMonthProfit $teamPersonModel */
                    $teamPersonModel = PersonMonthProfit::find()
                        ->where(['administrator_id' => $administrator->id])
                        ->andWhere(['year' => $date['year'],'month' => $date['month']])
                        ->limit(1)->one();
                    if(null == $teamPersonModel && $this->point == 0)
                    {
                        $this->addError('virtual_order_id','对不起，当前订单没有结算过预计利润！');
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
                    $calculated_performance = BC::div(BC::mul($totalPerformance, $team->divide_rate, 2), 100, 2);//以计算的业绩
                    $performance += $calculated_performance;
                    $rate = BC::sub($rate, $team->divide_rate, 2);
                    $data[] = [
                        'virtual_order_id' => $this->virtualOrder->id,
                        'order_id' => $record->order_id,
                        'administrator_id' => $administrator->id,
                        'administrator_name' => $administrator->name,
                        'department_id' => $administrator->department->id,
                        'department_name' => $administrator->department->name,
                        'year' => $record->year,
                        'month' => $record->month,
                        'type' => PerformanceStatistics::TYPE_GENERAL,
                        'algorithm_type' => $algorithm,
                        'title' => '业绩提成计算',
                        'remark' => '',
                        'calculated_performance' =>  $calculated_performance,//已计算业绩金额,
                        'performance_reward' => round(BC::div(BC::mul($calculated_performance,$team_partition_rate),100,5),2),//业绩提成
                        'reward_proportion' => $team_partition_rate,
                        'creator_id' => $admin->id,
                        'creator_name' => $admin->name,
                        'created_at' => $time,
                    ];
                }
            }
            $record->calculated_performance += $totalPerformance;
            $record->save(false);
            if($rate < 100)
            {
                //主要负责人分共享人分成后的剩余的全部预计利润
                $totalPerformance = BC::sub($totalPerformance, $performance);
            }
            $data[] = [
              'virtual_order_id' => $this->virtualOrder->id,
              'order_id' => $record->order_id,
              'administrator_id' => $record->order->salesman->id,
              'administrator_name' => $record->order->salesman->name,
              'department_id' => $record->order->salesman->department_id,
              'department_name' => $record->order->salesman->department->name,
              'year' => $record->year,
              'month' => $record->month,
              'type' => ExpectedProfitSettlementDetail::TYPE_GENERAL,
              'algorithm_type' => $algorithm,
              'title' => '业绩提成计算',
              'remark' => '',
              'calculated_performance' =>  $totalPerformance,//已计算业绩金额,
              'performance_reward' => round(BC::div(BC::mul($totalPerformance,$partition_rate),100,5),2),//业绩提成
              'reward_proportion' => $partition_rate,
              'creator_id' => $admin->id,
              'creator_name' => $admin->name,
              'created_at' => $time,
            ];

            /** @var Order $orders */
            $orders = Order::find()->where(['id' => $record->order_id])->one();
            $orders->is_apply = 0;
            $orders->actual_profit_calculate = 1;
            $orders->save(false);
            /** @var OrderBalanceRecord $order_balance_record */
            $order_balance_record = OrderBalanceRecord::find() -> where(['order_id' =>$orders->id]) -> orderBy(['id'=>SORT_DESC]) -> one();
            if(!empty($order_balance_record)){
                $order_balance_record -> status = 3;
                $order_balance_record->save(false);
            }
        }
        if(empty($data))
        {
            $this->addError('virtual_order_id','当前订单没有剩余业绩可计算！');
            return false;
        }
        $t = Yii::$app->db->beginTransaction();
        try
        {
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
}
