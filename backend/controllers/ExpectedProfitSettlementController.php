<?php

namespace backend\controllers;

use common\models\Administrator;
use common\models\ExpectedProfitSettlementDetail;
use common\models\MonthProfitRecord;
use common\models\Order;
use common\models\OrderCalculateCollect;
use common\models\PayRecord;
use common\models\VirtualOrder;
use common\utils\BC;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class ExpectedProfitSettlementController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ready' => ['POST'],
                    'cancel-ready' => ['POST'],
                    'settlement' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'ready', 'cancel-ready', 'settlement'],
                        'allow' => true,
                        'roles' => ['expected-profit-settlement/*'],
                    ]
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = MonthProfitRecord::getLastRecord();
        $dataProvider = null;
        if($model && $model->isReady())
        {
            $query =  $this->getOrderQuery($model);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);
        }
        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionReady()
    {
        $record = MonthProfitRecord::getLastRecord();
        if(null != $record && ($record->isDoing() || $record->isReady()))
        {
            return $this->redirect(['index']);
        }
        else
        {
            if($record && ($record->year.$record->month == date('Ym')))
            {
                \Yii::$app->session->setFlash('error', '对不起，当前不可进行预计利润的结算！');
                return $this->redirect(['index']);
            }
            if($this->collect())
            {
                // 生成一个ready状态的记录，用于截止订单最后支付时间
                /** @var Administrator $administrator */
                $administrator = \Yii::$app->user->identity;
                $model = new MonthProfitRecord();
                $model->year = $record ? $record->getNextMonth()['year'] : date('Y');
                $model->month = $record ? $record->getNextMonth()['month'] : date('m');
                $model->status = MonthProfitRecord::STATUS_READY;
                $model->range_start_time = $record ? ($record->range_end_time+1) : (strtotime(date('Y-m-1')));
                $model->range_end_time = time();
                $model->performance_start_time = $record ? ($record->performance_end_time ? $record->performance_end_time+1 : $record->performance_start_time+1) : time();
                $model->creator_id = $administrator->id;
                $model->creator_name = $administrator->name;
                $model->created_at = time();
                $model->save(false);
            }
        }
        return $this->redirect(['index']);
    }

    public function actionCancelReady()
    {
        $model = MonthProfitRecord::getReadyRecord();
        if(null == $model)
        {
            return $this->redirect(['index']);
        }
        else
        {
            $model->delete();
        }
        return $this->redirect(['index']);
    }

    public function actionSettlement()
    {
        $model = MonthProfitRecord::getReadyRecord();
        if(null == $model)
        {
            return $this->redirect(['index']);
        }
        else
        {
            $query = $this->getOrderQuery($model);
            if(0 < $query->count())
            {
                \Yii::$app->session->setFlash('error', '以下订单（有首付款记录的）存在订单业绩提点月为空的情况，请尽快处理。');
                return $this->redirect(['index']);
            }
            $record = MonthProfitRecord::getLastRecord();
            $model->range_end_time = $record->range_end_time ? $record->range_end_time : $record -> created_at;
            $model->performance_start_time = $record ? ($record->performance_end_time ? $record->performance_end_time+1 : $record->performance_start_time+1) : time();
             $model->status = MonthProfitRecord::STATUS_DOING_SETTLEMENT;
            $model->save(false);
        }
        return $this->redirect(['index']);
    }

    /**
     * @param $model MonthProfitRecord
     * @return Query
     */
    private function getOrderQuery($model)
    {
        /** @var Order[] $orders */
        $orders = Order::find()
            ->alias('o')->select('o.id,o.virtual_order_id')
            ->joinWith('virtualOrder vo')
            ->where(['o.is_vest' => '0','o.settlement_month' => '0'])
            ->andWhere(['not in', 'vo.status', [ VirtualOrder::STATUS_BREAK_PAYMENT]])
            ->andWhere(['between', 'o.first_payment_time', $model->range_start_time, $model->range_end_time])
            ->andWhere(['in', 'o.status', [Order::STATUS_PENDING_PAY,Order::STATUS_BREAK_SERVICE, Order::STATUS_PENDING_SERVICE,
                Order::STATUS_PENDING_ALLOT, Order::STATUS_COMPLETE_SERVICE, Order::STATUS_IN_SERVICE]])
            ->asArray()->all();
        $ids = ArrayHelper::getColumn($orders,'id');
        $query = Order::find()->where(['in','id',$ids]);
        return $query;
    }

    private function collect()
    {
        $data = [];
        $time = time();
        $year = date('Y',$time);
        $month = date('m',$time);
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
                        'expect_profit_time' => time()-1,//预计利润更新时间戳
                    ]);
                }
                else
                {
                    $isExist[$key][] = $model->order_id;
                    $isCustomerExist[$key][] = $model->order->user_id;
                    $data[$key] = [
                        'company_id' => isset($model->administrator->company_id) ? $model->administrator->company_id : 0,
                        'administrator_id' => $model->administrator_id,
                        'administrator_name' => $model->administrator_name,
                        'title' => $model->administrator ? $model->administrator->title : '',
                        'department_id' => isset($model->administrator->department_id) ? $model->administrator->department_id : 0,
                        'department_name' => isset($model->administrator->department->name) ? $model->administrator->department->name : '',
                        'department_path' => isset($model->administrator->department->path) ? $model->administrator->department->path : '',
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
                        'expect_profit_time' => time()-1,//预计利润更新时间戳
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
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
        return true;
    }


    private function collectDepartment()
    {
        $data = [];
        $time = time();
        $year = date('Y',$time);
        $month = date('m',$time);
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
                $key = 'd_'.$model->department_id;
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
                        'expect_profit_time' => time()-1,//预计利润更新时间戳
                    ]);
                }
                else
                {
                    $isExist[$key][] = $model->order_id;
                    $isCustomerExist[$key][] = $model->order->user_id;
                    $data[$key] = [
                        'company_id' => isset($model->company_id) ? $model->company_id : 0,
                        'administrator_id' => 0,
                        'administrator_name' => '',
                        'title' => '',
                        'department_id' => isset($model->department_id) ? $model->department_id : 0,
                        'department_name' => isset($model->department_name) ? $model->department_name : '',
                        'department_path' => isset($model->department_path) ? $model->department_path : '',
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
                        'expect_profit_time' => time()-1,//预计利润更新时间戳
                    ];
                }
            }
        }
        return $data;
    }
}