<?php

namespace backend\controllers;

use backend\models\ApplyInvoiceForm;
use backend\models\ConfirmInvoiceForm;
use backend\models\InvoicedForm;
use backend\models\SendInvoiceForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class InvoiceActionController extends BaseController
{
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['apply-invoice', 'apply-invoice-validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['confirm'],
                        'allow' => true,
                        'roles' => ['invoice-action/confirm'],
                    ],
                    [
                        'actions' => ['invoiced'],
                        'allow' => true,
                        'roles' => ['invoice-action/invoiced'],
                    ],
                    [
                        'actions' => ['send'],
                        'allow' => true,
                        'roles' => ['invoice-action/send'],
                    ],
                    [
                        'actions' => ['apply-invoice', 'apply-invoice-validation'],
                        'allow' => true,
                        'roles' => ['invoice-action/apply-invoice'],
                    ]
                ],
            ],
        ];
    }

    // 确认开票
    public function actionConfirm($is_validate = 0)
    {
        $model = new ConfirmInvoiceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 已开发票
    public function actionInvoiced($is_validate = 0)
    {
        $model = new InvoicedForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 已寄出
    public function actionSend($is_validate = 0)
    {
        $model = new SendInvoiceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    private function responseJson($isSuccess, $errors = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($isSuccess)
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => $errors ? reset($errors) : '您的操作有误。'];
    }

    public function actionApplyInvoice()
    {
        $model = new ApplyInvoiceForm();
        if($model->load(Yii::$app->request->post()))
        {
            if(!$model->validate())
            {
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
            if($model->save())
            {
                return ['status' => 200];
            }
            else
            {
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionApplyInvoiceValidation()
    {
        $model = new ApplyInvoiceForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }
}