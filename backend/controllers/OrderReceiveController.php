<?php

namespace backend\controllers;

use backend\models\OrderReceiveForm;
use backend\models\OrderReceiveSearch;
use backend\models\OrderVisitAllocationForm;
use common\actions\UploadImageAction;
use common\models\Order;
use common\models\UploadImageForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class OrderReceiveController extends BaseController
{

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['validation','visit-validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['all'],
                        'allow' => true,
                        'roles' => ['order-receive/all'],
                    ],
                    [
                        'actions' => ['refund'],
                        'allow' => true,
                        'roles' => ['order-receive/all'],
                    ],
                    [
                        'actions' => ['pending-payment'],
                        'allow' => true,
                        'roles' => ['order-receive/all'],
                    ],
                    [
                        'actions' => ['unpaid'],
                        'allow' => true,
                        'roles' => ['order-receive/all'],
                    ],
                    [
                        'actions' => ['pending-assign'],
                        'allow' => true,
                        'roles' => ['order-receive/all'],
                    ],
                    [
                        'actions' => ['receive','upload','validation'],
                        'allow' => true,
                        'roles' => ['order-receive/receive'],
                    ],
                    [
                        'actions' => ['allocation','visit-validation'],
                        'allow' => true,
                        'roles' => ['order-receive/access-allocation'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadImageAction::className(),
                'modelClass' => UploadImageForm::className(),
                'keyTemplate' => 'order-receive/{date:Ymd}-{time}.{ext}',
                'thumbnailWidth' => 200,
                'thumbnailHeight' => 100,
            ],
        ];
    }

    // 全部待认领订单
    public function actionAll()
    {
        return $this->searchOrders(null);
    }

    // 退款中待认领订单
    public function actionRefund()
    {
        return $this->searchOrders('refund');
    }

    // 待付款待认领订单
    public function actionPendingPayment()
    {
        return $this->searchOrders(Order::STATUS_PENDING_PAY);
    }

    // 未付清待认领订单
    public function actionUnpaid()
    {
        return $this->searchOrders(Order::STATUS_UNPAID);
    }

    // 待分配待认领订单
    public function actionPendingAssign()
    {
        return $this->searchOrders(Order::STATUS_PENDING_ALLOT);
    }

    private function searchOrders($status)
    {
        $searchModel = new OrderReceiveSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

    public function actionReceive()
    {
        $orderReceiveForm = new OrderReceiveForm();
        if($orderReceiveForm->load(Yii::$app->request->post()) && $orderReceiveForm->validate())
        {
            if($orderReceiveForm->receive())
            {
                Yii::$app->session->setFlash('success','订单认领成功！');
                return $this->redirect('all');
            }
        }
        if($orderReceiveForm->hasErrors())
        {
            Yii::$app->session->setFlash('error', reset($orderReceiveForm->getFirstErrors()));
        }
        return $this->redirect('all');
    }

    public function actionValidation()
    {
        $model = new OrderReceiveForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionAllocation()
    {
        $orderVisitAllocationForm = new OrderVisitAllocationForm();
        if($orderVisitAllocationForm->load(Yii::$app->request->post()) && $orderVisitAllocationForm->validate())
        {
            if($orderVisitAllocationForm->allocation())
            {
                Yii::$app->session->setFlash('success','订单回访分配成功！');
                return $this->redirect('all');
            }
        }
        if($orderVisitAllocationForm->hasErrors())
        {
            Yii::$app->session->setFlash('error', reset($orderVisitAllocationForm->getFirstErrors()));
        }
        return $this->redirect('all');
    }

    public function actionVisitValidation()
    {
        $model = new OrderVisitAllocationForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

}