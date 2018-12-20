<?php

namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmDepartment;
use common\models\ExpectedProfitSettlementDetail;
use common\models\Order;
use common\models\Property;
use common\models\MonthProfitRecord;
use common\utils\BC;
use Yii;
use yii\base\Model;


class CalculateOrderExpectedProfitForm extends Model
{
    public $order_id;

    public $expected_profit;

    /**
     * @var Order
     */
    public $order;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'integer'],
            [['order_id'], 'required'],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateOrderId()
    {
       $this->order = Order::findOne($this->order_id);
       if(null == $this->order)
       {
           $this->addError('order_id','订单不存在！');
       }
       if($this->order->getTotalExpectedCost() != $this->order->virtualOrder->getTotalExpectedCost())
       {
            $this->addError('order_id','对不起，当前虚拟订单预计成本和子订单已录入预计成本存在金额不相等的情况，请检查后计算。');
       }
       $expected_profit = $this->order->getExpectedProfits()->sum('expected_profit');
       $order_profit = $this->order->getExpectedCost() == null ? $this->order->price : BC::sub($this->order->price,$this->order->getExpectedCost());
       $this->expected_profit = BC::sub($order_profit,$expected_profit);
//       if(floatval($this->expected_profit) == 0)
//       {
//           $this->addError('order_id','对不起，此订单没有可计算的预计利润！');
//       }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $detailData = []; //最终生成expected_profit_settlement_detail数据
        $rate = 100;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $time = time();
        $year = date('Y',$time);
        $month = date('m',$time);
        $profit = 0;

        $profit_rule = Property::get('profit_rule');


        if(!$this ->order ->settlement_month)
        {
            $lastRecord = MonthProfitRecord::getLastFinishRecord();
            if($lastRecord && $lastRecord->getYearMonth() == date('Ym',time()))
            {
                $this ->order->settlement_month = $profit_rule ? $lastRecord->year.$lastRecord->month : $lastRecord->getNextMonth()['year'].$lastRecord->getNextMonth()['month'];
            }
            else
            {
                $this->order->settlement_month = $profit_rule ? $this->getPreviousMonth() : date('Ym',time());
            }
            $this -> order ->save(false);
        }

        //处理共享人的预计利润
        foreach($this->order->orderTeams as $orderTeam)
        {
            $teamExpectedProfit = BC::div(BC::mul($this->expected_profit, $orderTeam->divide_rate), 100);
            $profit += $teamExpectedProfit;
            $rate = BC::sub($rate, $orderTeam->divide_rate, 2);
            $detailData[]= [
            'year' => $year,
            'month' => $month,
            'order_id' => $orderTeam->order_id,
            'virtual_order_id' => $orderTeam->order->virtual_order_id,
            'sn' => $orderTeam->order->sn,
            'v_sn' => $orderTeam->order->virtualOrder->sn,
            'type' => ExpectedProfitSettlementDetail::TYPE_GENERAL,
            'company_id' => $orderTeam->administrator ? $orderTeam->administrator->company_id : 0,
            'company_name' => $orderTeam->administrator ? $orderTeam->administrator->company->name : '',
            'title' => '预计利润计算',
            'remark' => '',
            'administrator_id' => $orderTeam->administrator ? $orderTeam->administrator->id : 0,
            'administrator_name' => $orderTeam->administrator ? $orderTeam->administrator->name : '',
            'department_id' => $orderTeam->administrator ? $orderTeam->administrator->department_id : 0,
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
            $this->expected_profit = BC::sub($this->expected_profit, $profit);
        }


        if($this->order->expected_profit_calculate <= 0)
        {
            $this ->order->expected_profit_calculate = 1;
            
            $this ->order->save(false);
        }

        $detailData[]= [
            'year' => $year,
            'month' => $month,
            'order_id' => $this->order->id,
            'virtual_order_id' => $this->order->virtual_order_id,
            'sn' => $this->order->sn,
            'v_sn' => $this->order->virtualOrder->sn,
            'type' => ExpectedProfitSettlementDetail::TYPE_GENERAL,
            'company_id' => $this->order->salesman->company_id,
            'company_name' => $this->order->salesman->company->name,
            'title' => '预计利润计算',
            'remark' => '',
            'administrator_id' => $this->order->salesman->id,
            'administrator_name' => $this->order->salesman->name,
            'department_id' => $this->order->salesman->department_id,
            'department_name' => $this->order->salesman->department->name,
            'department_path' => $this->order->salesman->department->path,
            'expected_profit' => $this->expected_profit,
            'created_at' => $time,
            'creator_name' => $admin->name,
            'creator_id' => $admin->id,
        ];

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
            ], $detailData)->execute();
            if($this->order->expected_profit_calculate <= 0)
            {
                $this->order->expected_profit_calculate = 1;
                $this->order->save(false);
            }
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
        ];
    }
}
