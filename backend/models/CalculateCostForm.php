<?php
namespace backend\models;
use common\models\Order;
use common\models\OrderCostRecord;
use common\models\PerformanceRecord;
use common\models\VirtualOrder;
use common\utils\BC;
use Yii;
use yii\base\Model;


class CalculateCostForm extends Model
{
    public $virtual_order_id;

    public $number = 0;

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
        if(null == $this->virtualOrder)
        {
            $this->addError('virtual_order_id','找不到要计算的订单');
        }
    }

    public function dropCost()
    {
        //1.子订单实际成本金额上限不能超过虚拟订单上的成本总金额
        if($this->virtualOrder->getOrderCost() > $this->virtualOrder->getTotalCost())
        {
            $this->addError('virtual_order_id','子订单成本总金额上限不能超过虚拟订单上的成本总金额！');
            return false;
        }
        $cost = BC::sub($this->virtualOrder->getTotalCost(),$this->virtualOrder->getOrderCost());
        //2.子订单的实际成本为空值时，如果没有实际利润则判断订单负责人是否两两相同，把虚拟订单实际利润分配下去，如果互不相同则必须补录实际成本
        if($this->virtualOrder->getOrderCost() == 0)
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
                        $this->addError('virtual_order_id','对不起，当前订单存在多业务员未录入实际成本，请检查后重新计算。');
                        return false;
                    }
                    else
                    {
                        $this->number += 1;
                        $this->saveCost(0,$cost,1);
                        return true;
                    }
                }
                else
                {
                    $this->number += 1;
                    $this->saveCost(0,$cost,1);
                    return true;
                }
            }
            else
            {
                $this->addError('virtual_order_id','订单负责业务员互不相同请补录成本！');
                return false;
            }

        }
        else
        {
            //有子订单实际成本
            if($this->virtualOrder->getTotalCost() > $this->virtualOrder->getOrderCost() &&  $this->virtualOrder->getOrderCost() != 0)
            {
                //子订单
                $order_ids = [];
                $salesman_ids = [];
                $isGx = false;
                $team = [];
                foreach($this->virtualOrder->orders as $i => $order)
                {
                    if($order->getCost() == '' )
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
                    $this->number += 1;
                    $this->saveCost($order_ids,$cost,0);
                    return true;
                }
                else
                {
                    if($this->isSame($team) || $order_num != count($team))
                    {
                        $this->addError('virtual_order_id','对不起，当前订单存在多业务员未录入实际成本，请检查后重新计算。');
                        return false;
                    }
                    else
                    {
                        $this->number += 1;
                        $this->saveCost($order_ids,$cost,0);
                        return true;
                    }
                }
            }
        }

        if($this->virtualOrder->getOrderCost() != $this->virtualOrder->getTotalCost())
        {
            $this->addError('virtual_order_id','对不起，当前虚拟订单实际成本和子订单已录入实际成本存在金额不相等的情况，请检查后计算。');
            return false;
        }
        else
        {
            $this->addError('virtual_order_id','对不起，当前虚拟订单实际成本和子订单成本已平衡，无需分配。');
            return false;
        }
    }

    public function saveCost($order_ids,$cost,$status)
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
                    OrderCostRecord::createCost($order->id,OrderCostRecord::TYPE_CALCULATION,$order->virtualOrder->id,'虚拟订单分配成本',$cost);
                }
            }
            elseif ($count > 1)
            {
                foreach($order_ids as $i => $id)
                {
                    $order = Order::findOne($id);
                    if($count > 1 && $i+1 != $count)
                    {
                        //按照子订单应付金额/未计算分配成本订单的应付金额
                        $rate[$id]['rate'] = BC::div($order->price,$order->getSurplusCostPrice(),5);
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
                        $cost_price = BC::sub($cost,$total);
                        OrderCostRecord::createCost($id,OrderCostRecord::TYPE_CALCULATION,$rate[$id]['virtual_order_id'],'虚拟订单分配成本',$cost_price);

                    }
                    else
                    {
                        $cost_price = round(BC::mul($cost,$rate[$id]['rate'],5),2);
                        $total += $cost_price;
                        OrderCostRecord::createCost($id,OrderCostRecord::TYPE_CALCULATION,$rate[$id]['virtual_order_id'],'虚拟订单分配成本',$cost_price);
                    }
                }
            }
        }
        else
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
                        $cost_price = BC::sub($cost,$total);
                        OrderCostRecord::createCost($order->id,OrderCostRecord::TYPE_CALCULATION,$order->virtual_order_id,'虚拟订单分配成本',$cost_price);
                    }
                    else
                    {
                        $cost_price = round(BC::mul($cost,$rate[$order->id],5),2);
                        $total += $cost_price;
                        OrderCostRecord::createCost($order->id,OrderCostRecord::TYPE_CALCULATION,$order->virtual_order_id,'虚拟订单分配成本',$cost_price);
                    }
                }
            }
            elseif($count == 1)
            {
                OrderCostRecord::createCost($this->virtualOrder->order->id,OrderCostRecord::TYPE_CALCULATION,$this->virtualOrder->id,'虚拟订单分配成本',$cost);
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

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        if($this->dropCost() == false)
        {
            //下放成本
            return false;
        }
        return true;
    }
}
