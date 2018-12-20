<?php

namespace backend\controllers;

use backend\models\OrderReceiveRecordSearch;
use common\models\Order;
use Yii;
use yii\filters\AccessControl;

class OrderReceiveRecordController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['all'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['refund'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['pending-payment'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['unpaid'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['pending-assign'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['pending-service'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['in-service'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['completed'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                    [
                        'actions' => ['break'],
                        'allow' => true,
                        'roles' => ['order-receive-record/list'],
                    ],
                ],
            ],
        ];
    }

    // 全部订单
    public function actionAll()
    {
        return $this->searchOrders(null);
    }

    // 退款中订单
    public function actionRefund()
    {
        return $this->searchOrders('refund');
    }

    // 待付款
    public function actionPendingPayment()
    {
        return $this->searchOrders(Order::STATUS_PENDING_PAY);
    }

    // 未付清
    public function actionUnpaid()
    {
        return $this->searchOrders(Order::STATUS_UNPAID);
    }

    // 待分配
    public function actionPendingAssign()
    {
        return $this->searchOrders(Order::STATUS_PENDING_ALLOT);
    }

    // 待服务
    public function actionPendingService()
    {
        return $this->searchOrders(Order::STATUS_PENDING_SERVICE);
    }

    // 服务中
    public function actionInService()
    {
        return $this->searchOrders(Order::STATUS_IN_SERVICE);
    }

    // 服务完成
    public function actionCompleted()
    {
        return $this->searchOrders(Order::STATUS_COMPLETE_SERVICE);
    }

    // 服务终止
    public function actionBreak()
    {
        return $this->searchOrders(Order::STATUS_BREAK_SERVICE);
    }

    private function searchOrders($status)
    {
        $searchModel = new OrderReceiveRecordSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

}