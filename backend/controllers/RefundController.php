<?php

namespace backend\controllers;

use backend\models\ConfirmRefundForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\web\Response;

class RefundController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['do','do-refund'],
                        'allow' => true,
                        'roles' => ['refund/do'],
                    ],
                ],
            ],
        ];
    }

    public function actionDo($is_validate = 0)
    {
        $model = new ConfirmRefundForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        $rs = $model->refund();
        if($rs === true)
        {
            Yii::$app->session->setFlash('success', '退款成功!');
            return $this->redirect(['refund-record/list', 'virtual_order_id' => $model->refundRecord->virtual_order_id]);
        }
        else if($rs === false)
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
            return $this->redirect(['refund-record/list', 'virtual_order_id' => $model->refundRecord->virtual_order_id]);
        }
        else
        {
            return $rs;
        }
    }

    public function actionDoRefund($is_validate = 0)
    {
        $model = new ConfirmRefundForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        $rs = $model->cashRefund();
        if($rs === true)
        {
            Yii::$app->session->setFlash('success', '退款成功!');
            return $this->redirect(['refund-record/list', 'virtual_order_id' => $model->refundRecord->virtual_order_id]);
        }
        else if($rs === false)
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
            return $this->redirect(['refund-record/list', 'virtual_order_id' => $model->refundRecord->virtual_order_id]);
        }
        else
        {
            return $rs;
        }
    }
}
