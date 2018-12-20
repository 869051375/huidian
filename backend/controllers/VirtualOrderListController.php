<?php

namespace backend\controllers;

use backend\models\VirtualOrderSearch;
use common\models\VirtualOrder;
use Yii;
use yii\filters\AccessControl;

class VirtualOrderListController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['all','unpaid','already-payment','pending-payment','cancel'],
                        'allow' => true,
                        'roles' => ['virtual-order-list/list'],
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

    // 待付款
    public function actionPendingPayment()
    {
        return $this->searchOrders(VirtualOrder::STATUS_PENDING_PAYMENT);
    }

    // 未付清
    public function actionUnpaid()
    {
        return $this->searchOrders(VirtualOrder::STATUS_UNPAID);
    }

    // 已付款
    public function actionAlreadyPayment()
    {
        return $this->searchOrders(VirtualOrder::STATUS_ALREADY_PAYMENT);
    }

    // 停止服务取消跟进
    public function actionCancel()
    {
        return $this->searchOrders(VirtualOrder::STATUS_BREAK_PAYMENT);
    }

    private function searchOrders($status)
    {
        $searchModel = new VirtualOrderSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }
}