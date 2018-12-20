<?php
namespace backend\models;
use common\models\Administrator;
use common\models\ExpectedProfitSettlementDetail;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\Property;
use common\models\VirtualOrder;
use common\utils\BC;
use Yii;
use yii\base\Model;


class CalculateExpectedProfitForm extends Model
{
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
            [['virtual_order_id'], 'required'],
            [['virtual_order_id'], 'integer'],
            ['virtual_order_id', 'validateVirtualOrderId'],
        ];
    }

    public function validateVirtualOrderId()
    {
        $this->virtualOrder = VirtualOrder::findOne($this->virtual_order_id);
        $this->orders = Order::find()->where(['virtual_order_id' => $this->virtual_order_id])->all();
        if(null == $this->virtualOrder || empty($this->orders))
        {
            $this->addError('virtual_order_id','找不到要计算的订单');
        }
    }

    public function dropCost()
    {
        //1.子订单预计成本金额上限不能超过虚拟订单上的成本总金额
        if($this->virtualOrder->getOrderTotalExpectedCost() && $this->virtualOrder->getOrderTotalExpectedCost() > $this->virtualOrder->getTotalExpectedCost())
        {
            $this->addError('virtual_order_id','子订单预计成本总金额上限不能超过虚拟订单上的成本总金额！');
            return false;
        }
        $expectedCost = $this->virtualOrder->getOrderTotalExpectedCost() == null ? $this->virtualOrder->getTotalExpectedCost() : BC::sub($this->virtualOrder->getTotalExpectedCost(),$this->virtualOrder->getOrderTotalExpectedCost());
        //2.子订单的预计成本为空值时，如果没有预计利润则判断订单负责人是否两两相同，把虚拟订单预计利润分配下去，如果互不相同则必须补录预计成本
        if($this->virtualOrder->getTotalExpectedCost() && $this->virtualOrder->getOrderTotalExpectedCost() == null)
        {
            $salesman_aid = 0;
            $isSame = true;
            $isGx = false;
            $team = [];
            $count = count($this->virtualOrder->orders);
            foreach($this->virtualOrder->orders as $i => $order)
            {
                if($order->orderTeams)
                {
                    foreach($order->orderTeams as $item)
                    {
                        $team[$order->id][] = $item->administrator_id;
                    }
                    $isGx = true;
                }

                if($order->salesman_aid == $salesman_aid)
                {
                    $isSame = true;
                }
                else
                {
                    if($i == 0)
                    {
                        $salesman_aid = $order->salesman_aid;
                    }
                    else
                    {
                        $isSame = false;
                    }
                }
            }
            //是否负责人两两相同
            if($isSame)
            {
                if($isGx)
                {
                    if(($this->isSame($team) || $count != count($team)) && $count > 1)
                    {
                        $this->addError('virtual_order_id','对不起，当前订单存在多业务员未录入预计成本，请检查后重新计算。');
                        return false;
                    }
                    else
                    {
                        $this->saveCost(0,$expectedCost,1);
                        return true;
                    }
                }
                else
                {
                    $this->saveCost(0,$expectedCost,1);
                    return true;
                }
            }
            else
            {
                $this->addError('virtual_order_id','订单负责业务员互不相同请补录成本！');
                return false;
            }

        }
        else if($this->virtualOrder->getOrderTotalExpectedCost() != null && $this->virtualOrder->getTotalExpectedCost() > $this->virtualOrder->getOrderTotalExpectedCost())
        {
            //有子订单预计成本
            //子订单
            $order_ids = [];
            $salesman_ids = [];
            $isGx = false;
            $team = [];
            foreach($this->virtualOrder->orders as $i => $order)
            {
                if($order->getExpectedCost() == null)
                {
                    if(empty($order->orderTeams))
                    {
                        $order_ids[] = $order->id;
                        $salesman_ids[] = $order->salesman_aid;
                    }
                    else
                    {
                        $isGx = true;
                        $order_ids[] = $order->id;
                        $salesman_ids[] = $order->salesman_aid;
                        foreach($order->orderTeams as $item)
                        {
                            $team[$order->id][] = $item->administrator_id;
                        }
                    }
                }
            }
            $unique_ids = array_unique($salesman_ids);
            $order_num = count($order_ids);
            if(count($unique_ids) > 1)
            {
                $this->addError('virtual_order_id','订单负责业务员互不相同请补录成本！');
                return false;
            }
            if(count($unique_ids) == 1 && $isGx == false)
            {
                $this->saveCost($order_ids,$expectedCost,0);
                return true;
            }
            else
            {
                if($this->isSame($team) || $order_num != count($team))
                {
                    $this->addError('virtual_order_id','对不起，当前订单存在多业务员未录入预计成本，请检查后重新计算。');
                    return false;
                }
                else
                {
                    $this->saveCost($order_ids,$expectedCost,0);
                    return true;
                }
            }
        }
        else if($this->virtualOrder->getTotalExpectedCost() == 0 && $this->virtualOrder->getOrderTotalExpectedCost() == null)
        {
            $this->saveCost(0,$expectedCost,2);
            return true;
        }

        if($this->virtualOrder->getOrderTotalExpectedCost() != $this->virtualOrder->getTotalExpectedCost())
        {
            $this->addError('virtual_order_id','对不起，当前虚拟订单预计成本和子订单已录入预计成本存在金额不相等的情况，请检查后计算。');
            return false;
        }
        else
        {
            return true;
        }
    }

    public function saveCost($order_ids,$expectedCost,$status)
    {
        if($status == 0)
        {
            $total = 0;
            $count = count($order_ids);
            $rate = [];
            if($count == 1)
            {
                foreach($order_ids as $i => $id)
                {
                    $order = Order::findOne($id);
                    OrderExpectedCost::createExpectedCost($order->id,OrderExpectedCost::TYPE_CALCULATION,$order->virtualOrder->id,'虚拟订单分配成本',$expectedCost);
                }
            }
            elseif ($count > 1)
            {
                foreach($order_ids as $i => $id)
                {
                    $order = Order::findOne($id);
                    if($count > 1 && $i+1 != $count)
                    {
                        //按照子订单应付金额/未计算分配预计成本订单的应付金额
                        $rate[$id]['rate'] = BC::div($order->price,$order->getSurplusPrice(),5);
                        $rate[$id]['virtual_order_id'] = $order->virtual_order_id;
                    }
                    else
                    {
                        $rate[$id]['virtual_order_id'] = $order->virtual_order_id;
                    }
                }
                foreach($order_ids as $i => $id)
                {
                    if($count > 1 && $i+1 == $count)
                    {
                        $cost_price = BC::sub($expectedCost,$total);
                        OrderExpectedCost::createExpectedCost($id,OrderExpectedCost::TYPE_CALCULATION,$rate[$id]['virtual_order_id'],'虚拟订单分配成本',$cost_price);
                    }
                    else
                    {
                        $cost_price = round(BC::mul($expectedCost,$rate[$id]['rate'],5),2);
                        $total += $cost_price;
                        OrderExpectedCost::createExpectedCost($id,OrderExpectedCost::TYPE_CALCULATION,$rate[$id]['virtual_order_id'],'虚拟订单分配成本',$cost_price);
                    }
                }
            }
        }
        else if($status == 1)
        {
            $count = count($this->virtualOrder->orders);
            if($count > 1)
            {
                $total = 0;
                $rate = [];
                foreach($this->virtualOrder->orders as $i => $order)
                {
                    if($count > 1 && $i+1 != $count)
                    {
                        $rate[$order->id] = BC::div($order->price,$this->virtualOrder->total_amount,5);
                    }
                }
                foreach($this->virtualOrder->orders as $i => $order)
                {
                    if($count > 1 && $i+1 == $count)
                    {
                        $cost_price = BC::sub($expectedCost,$total);
                        OrderExpectedCost::createExpectedCost($order->id,OrderExpectedCost::TYPE_CALCULATION,$order->virtual_order_id,'虚拟订单分配成本',$cost_price);
                    }
                    else
                    {
                        $cost_price = round(BC::mul($expectedCost,$rate[$order->id],5),2);
                        $total += $cost_price;
                        OrderExpectedCost::createExpectedCost($order->id,OrderExpectedCost::TYPE_CALCULATION,$order->virtual_order_id,'虚拟订单分配成本',$cost_price);
                    }
                }
            }
            elseif($count == 1)
            {
                OrderExpectedCost::createExpectedCost($this->virtualOrder->order->id,OrderExpectedCost::TYPE_CALCULATION,$this->virtualOrder->id,'虚拟订单分配成本',$expectedCost);
            }
        }
        else if($status == 2)
        {
            foreach($this->virtualOrder->orders as $i => $order)
            {
                OrderExpectedCost::createExpectedCost($order->id,OrderExpectedCost::TYPE_CALCULATION,$order->virtual_order_id,'虚拟订单分配成本',$expectedCost);
            }
        }
    }

    public function isSame($array)
    {
        if(count($array) > 1)
        {
            $arr = [];
            foreach($array as $item)
            {
                if(empty($arr))
                {
                    $arr = $item;
                }
                else
                {
                    if($arr != $item)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'virtual_order_id' => '',
        ];
    }

    public function getPreviousMonth()
    {
        $year = date('Y',time());
        $month = date('m',time());
        if($month == 1)
        {
            $year = $year - 1;
            $month = 12;
            return $year.$month;
        }
        else
        {
            $month = $month - 1;
            return $year.$month;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        if($this->dropCost() == false)
        {
            return false;
        }
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $data = [];//最终生成expected_profit_settlement_detail数据
        $profit_rule = Property::get('profit_rule');
        foreach($this->orders as $i => $order)
        {
            if(!$order->settlement_month)
            {
                $lastRecord = MonthProfitRecord::getLastFinishRecord();
                if($lastRecord && $lastRecord->getYearMonth() == date('Ym',time()))
                {
                    $order->settlement_month = $profit_rule ? $lastRecord->year.$lastRecord->month : $lastRecord->getNextMonth()['year'].$lastRecord->getNextMonth()['month'];
                }
                else
                {
                    $order->settlement_month = $profit_rule ? $this->getPreviousMonth() : date('Ym',time());
                }
                $order->save(false);
            }
            $expected_profit = $order->getExpectedProfits()->sum('expected_profit');
            $price = $order->getExpectedCost() == null ? $order->price : BC::sub($order->price,$order->getExpectedCost());
            $surplusExpectedProfit = BC::sub($price,$expected_profit);
            if(floatval($surplusExpectedProfit) == 0) continue;
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
                    'type' => ExpectedProfitSettlementDetail::TYPE_GENERAL,
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

            if($order->expected_profit_calculate <= 0)
            {
                $order->expected_profit_calculate = 1;
                $order->save(false);
            }

            $data[]= [
                'year' => $year,
                'month' => $month,
                'order_id' => $order->id,
                'virtual_order_id' => $order->virtual_order_id,
                'sn' => $order->sn,
                'v_sn' => $order->virtualOrder->sn,
                'type' => ExpectedProfitSettlementDetail::TYPE_GENERAL,
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
        if(empty($data))
        {
            $this->addError('virtual_order_id','对不起，当前无剩余可计算预计利润金额！');
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
