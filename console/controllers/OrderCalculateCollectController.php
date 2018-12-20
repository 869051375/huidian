<?php

namespace console\controllers;

use common\models\ExpectedProfitSettlementDetail;
use common\models\MonthProfitRecord;
use common\models\OrderCalculateCollect;
use common\models\PayRecord;
use common\utils\BC;
use yii\console\Controller;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * 订单预计利润计算汇总
 * Class OrderCalculateCollectController
 * @package console\controllers
 */
class OrderCalculateCollectController extends Controller
{
    public function actionCollect()
    {
        if(\Yii::$app->cache->exists("OrderCalculateCollectActionCollect")) {
            return '1';
        }

        \Yii::$app->cache->add("OrderCalculateCollectActionCollect", '1', 600);
        $data = [];
        $time = time();
        $year = date('Y',$time);
        $month = date('m',$time);

        $order_time = OrderCalculateCollect::find() -> where(['year' =>$year,'month' => $month]) -> one();

        $lastRecord = MonthProfitRecord::getLastRecord();
        $range_start_time = strtotime(date('Y-m-1'));
        if($lastRecord)
        {
            $range_start_time = $lastRecord->isReady() || $lastRecord->isDoing() ? $lastRecord->range_start_time : $lastRecord->range_end_time+1;//开始时间
        }
        if($lastRecord)
        {
            $year = $lastRecord->isFinish() ? $lastRecord->getNextMonth()['year'] : $year;
            $month = $lastRecord->isFinish() ? $lastRecord->getNextMonth()['month'] : $month;
        }
        $query = ExpectedProfitSettlementDetail::find()->where(['between', 'created_at', $range_start_time, time()]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);

        $count = $dataProvider->totalCount;
        $batchNum = 100;
        $batch = ceil($count / $batchNum);
        $isExist = [];
        $isCustomerExist = [];
        for($i = 0; $i < $batch; $i++)
        {
            /** @var ExpectedProfitSettlementDetail[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach($models as $model)
            {
                $isNewCustomer = false;
                $hasPayRecord = PayRecord::find()
                        ->where(['user_id' => $model->order->user_id])
                        ->andWhere(['<', 'pay_time', $range_start_time])
                        ->limit(1)->count() > 0;
                if(!$hasPayRecord)
                {
                    $isNewCustomer = true;
                }
                $key = 'a_'.$model->administrator_id;
                if(isset($data[$key]))
                {
                    $total_customer_count = 0;
                    $new_customer_count = 0;
                    if(isset($isCustomerExist[$key]) && !in_array($model->order->user_id,$isCustomerExist[$key]))
                    {
                        $isCustomerExist[$key][] = $model->order->user_id;
                        $total_customer_count = 1;
                        if($isNewCustomer)
                        {
                            $new_customer_count = 1;
                        }
                    }
                    $order_count = 0;
                    $refund_order_count = 0;
                    $cancel_order_count = 0;
                    $total_order_price = 0;
                    if(isset($isExist[$key]) && !in_array($model->order_id,$isExist[$key]))
                    {
                        $order_count = 1;
                        $refund_order_count = $model->order->isRefunded() ? 1 : 0;
                        $cancel_order_count = $model->order->isCancel() ? 1 : 0;
                        $total_order_price =  $model->order->price;
                        $isExist[$key][] = $model->order_id;
                    }

                    $correct_expected_amount = $model->type == ExpectedProfitSettlementDetail::TYPE_CORRECT ? $model->expected_profit : 0;
                    $knot_expected_amount = $model->type == ExpectedProfitSettlementDetail::TYPE_KNOT ? $model->expected_profit : 0;

                    $d = $data[$key];
                    $data[$key] = ArrayHelper::merge($d,[
                        'total_order_price' => BC::add($total_order_price,$d['total_order_price']),//订单总额
                        'total_customer_count' => BC::add($total_customer_count,$d['total_customer_count'],0),//总客户数
                        'new_customer_count' => BC::add($new_customer_count,$d['new_customer_count'],0),//新客户数
                        'order_count' => BC::add($order_count,$d['order_count'],0),//参与计算订单数量
                        'refund_order_count' => BC::add($refund_order_count,$d['refund_order_count'],0),//退款订单数量
                        'cancel_order_count' => BC::add($cancel_order_count,$d['cancel_order_count'],0),//取消订单数量
                        'order_expected_amount' => BC::add($model->expected_profit,$d['order_expected_amount']),//参与计算订单预计利润金额
                        'correct_expected_amount' => BC::add($correct_expected_amount,$d['correct_expected_amount']),//更正订单预计利润金额
                        'correct_front_expected_amount' => BC::add($model->expected_profit,$d['order_expected_amount']) - BC::add($correct_expected_amount,$d['correct_expected_amount']),//更正订单预计利润金额
                        'knot_expected_amount' => BC::add($knot_expected_amount,$d['knot_expected_amount']),//更正订单预计利润金额
                        'expect_profit_time' => isset($order_time -> expect_profit_time) ? $order_time -> expect_profit_time : $time,//预计利润更新时间戳
                    ]);
                }
                else
                {
                    $isExist[$key][] = $model->order_id;
                    $isCustomerExist[$key][] = $model->order->user_id;
                    $data[$key] = [
                        'company_id' => $model->administrator->company_id,
                        'administrator_id' => $model->administrator_id,
                        'administrator_name' => $model->administrator_name,
                        'title' => $model->administrator ? $model->administrator->title : '',
                        'department_id' => $model->administrator->department_id,
                        'department_name' => $model->administrator->department->name,
                        'department_path' => $model->administrator->department->path,
                        'year' => $year,
                        'month' => $month,
                        'total_order_price' => $model->order->price,//订单总额
                        'total_customer_count' => 1,//总客户数
                        'new_customer_count' => $isNewCustomer ? 1 : 0,//新客户数
                        'order_count' => 1,//参与计算订单数量
                        'refund_order_count' => $model->order->isRefunded() ? 1 : 0,//退款订单数量
                        'cancel_order_count' => $model->order->isCancel() ? 1 : 0,//取消订单数量
                        'order_expected_amount' => $model->expected_profit,//参与计算订单预计利润金额
                        'correct_expected_amount' => $model->type == ExpectedProfitSettlementDetail::TYPE_CORRECT ? $model->expected_profit: 0,//更正订单预计利润金额
                        'correct_front_expected_amount' => $model->expected_profit - ($model->type == ExpectedProfitSettlementDetail::TYPE_CORRECT ? $model->expected_profit: 0),//更正订单预计利润金额
                        'knot_expected_amount' => $model->type == ExpectedProfitSettlementDetail::TYPE_KNOT ? $model->expected_profit: 0,//结转订单预计利润金额
                        'expect_profit_time' => isset($order_time -> expect_profit_time) ? $order_time -> expect_profit_time : $time,//预计利润更新时间戳
                    ];
                }
            }
        }
        $d_data = $this->collectDepartment();
        $data = array_merge($data,$d_data);
        $rs = [];
        foreach($data as $key => $val){
            $rs[$key.$val['administrator_id'].$val['department_id']] =$val;
        }

        $t = \Yii::$app->db->beginTransaction();
        try
        {
            OrderCalculateCollect::deleteAll(['year' => $year,'month' => $month]);
            \Yii::$app->db->createCommand()->batchInsert(OrderCalculateCollect::tableName(), [
                'company_id',
                'administrator_id',
                'administrator_name',
                'title',
                'department_id',
                'department_name',
                'department_path',
                'year',
                'month',
                'total_order_price',
                'total_customer_count',
                'new_customer_count',
                'order_count',
                'refund_order_count',
                'cancel_order_count',
                'order_expected_amount',
                'correct_expected_amount',
                'correct_front_expected_amount',
                'knot_expected_amount',
                'expect_profit_time',
            ], $rs)->execute();
            $t->commit();
            \Yii::$app->cache->delete("OrderCalculateCollectActionCollect");
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            \Yii::$app->cache->delete("OrderCalculateCollectActionCollect");
            throw $e;
        }
    }
    public function collectDepartment()
    {
        $data = [];
        $time = time();
        $year = date('Y', $time);
        $month = date('m', $time);

        $order_time = OrderCalculateCollect::find()->where(['year' => $year, 'month' => $month])->one();

        $lastRecord = MonthProfitRecord::getLastRecord();
        $range_start_time = strtotime(date('Y-m-1'));
        if ($lastRecord) {
            $range_start_time = $lastRecord->isReady() || $lastRecord->isDoing() ? $lastRecord->range_start_time : $lastRecord->range_end_time + 1;//开始时间
        }
        if ($lastRecord) {
            $year = $lastRecord->isFinish() ? $lastRecord->getNextMonth()['year'] : $year;
            $month = $lastRecord->isFinish() ? $lastRecord->getNextMonth()['month'] : $month;
        }
        $query = ExpectedProfitSettlementDetail::find()->where(['between', 'created_at', $range_start_time, time()]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);

        $count = $dataProvider->totalCount;
        $batchNum = 100;
        $batch = ceil($count / $batchNum);
        $isExist = [];
        $isCustomerExist = [];
        for ($i = 0; $i < $batch; $i++) {
            /** @var ExpectedProfitSettlementDetail[] $models */
            $models = $dataProvider->query->offset($i * $batchNum)->limit($batchNum)->all();
            foreach ($models as $model) {
                $isNewCustomer = false;
                $hasPayRecord = PayRecord::find()
                        ->where(['user_id' => $model->order->user_id])
                        ->andWhere(['<', 'pay_time', $range_start_time])
                        ->limit(1)->count() > 0;
                if (!$hasPayRecord) {
                    $isNewCustomer = true;
                }
                $key = 'd_'.$model->department_id;
                if (isset($data[$key])) {
                    $total_customer_count = 0;
                    $new_customer_count = 0;
                    if (isset($isCustomerExist[$key]) && !in_array($model->order->user_id, $isCustomerExist[$key])) {
                        $isCustomerExist[$key][] = $model->order->user_id;
                        $total_customer_count = 1;
                        if ($isNewCustomer) {
                            $new_customer_count = 1;
                        }
                    }
                    $order_count = 0;
                    $refund_order_count = 0;
                    $cancel_order_count = 0;
                    $total_order_price = 0;
                    if (isset($isExist[$key]) && !in_array($model->order_id, $isExist[$key])) {
                        $order_count = 1;
                        $refund_order_count = $model->order->isRefunded() ? 1 : 0;
                        $cancel_order_count = $model->order->isCancel() ? 1 : 0;
                        $total_order_price = $model->order->price;
                        $isExist[$key][] = $model->order_id;
                    }

                    $correct_expected_amount = $model->type == ExpectedProfitSettlementDetail::TYPE_CORRECT ? $model->expected_profit : 0;
                    $knot_expected_amount = $model->type == ExpectedProfitSettlementDetail::TYPE_KNOT ? $model->expected_profit : 0;

                    $d = $data[$key];
                    $data[$key] = ArrayHelper::merge($d, [
                        'total_order_price' => BC::add($total_order_price, $d['total_order_price']),//订单总额
                        'total_customer_count' => BC::add($total_customer_count, $d['total_customer_count'], 0),//总客户数
                        'new_customer_count' => BC::add($new_customer_count, $d['new_customer_count'], 0),//新客户数
                        'order_count' => BC::add($order_count, $d['order_count'], 0),//参与计算订单数量
                        'refund_order_count' => BC::add($refund_order_count, $d['refund_order_count'], 0),//退款订单数量
                        'cancel_order_count' => BC::add($cancel_order_count, $d['cancel_order_count'], 0),//取消订单数量
                        'order_expected_amount' => BC::add($model->expected_profit, $d['order_expected_amount']),//参与计算订单预计利润金额
                        'correct_expected_amount' => BC::add($correct_expected_amount, $d['correct_expected_amount']),//更正订单预计利润金额
                        'correct_front_expected_amount' => BC::add($model->expected_profit, $d['order_expected_amount']) - BC::add($correct_expected_amount, $d['correct_expected_amount']),//更正前订单预计利润金额
                        'knot_expected_amount' => BC::add($knot_expected_amount, $d['knot_expected_amount']),//更正订单预计利润金额
                        'expect_profit_time' => isset($order_time->expect_profit_time) ? $order_time->expect_profit_time : $time,//预计利润更新时间戳
                    ]);
                } else {
                    $isExist[$key][] = $model->order_id;
                    $isCustomerExist[$key][] = $model->order->user_id;
                    $data[$key] = [
                        'company_id' => $model->company_id,
                        'administrator_id' => 0,
                        'administrator_name' => '',
                        'title' => $model->administrator ? $model->administrator->title : '',
                        'department_id' => $model->department_id,
                        'department_name' => $model->department_name,
                        'department_path' => $model->department_path,
                        'year' => $year,
                        'month' => $month,
                        'total_order_price' => $model->order->price,//订单总额
                        'total_customer_count' => 1,//总客户数
                        'new_customer_count' => $isNewCustomer ? 1 : 0,//新客户数
                        'order_count' => 1,//参与计算订单数量
                        'refund_order_count' => $model->order->isRefunded() ? 1 : 0,//退款订单数量
                        'cancel_order_count' => $model->order->isCancel() ? 1 : 0,//取消订单数量
                        'order_expected_amount' => $model->expected_profit,//参与计算订单预计利润金额
                        'correct_expected_amount' => $model->type == ExpectedProfitSettlementDetail::TYPE_CORRECT ? $model->expected_profit : 0,//更正订单预计利润金额
                        'correct_front_expected_amount' => $model->expected_profit - ($model->type == ExpectedProfitSettlementDetail::TYPE_CORRECT ? $model->expected_profit : 0),//更正前订单预计利润金额
                        'knot_expected_amount' => $model->type == ExpectedProfitSettlementDetail::TYPE_KNOT ? $model->expected_profit : 0,//结转订单预计利润金额
                        'expect_profit_time' => isset($order_time->expect_profit_time) ? $order_time->expect_profit_time : $time,//预计利润更新时间戳
                    ];
                }
            }
        }
        return $data;
    }
}
