<?php

namespace backend\controllers;

use backend\models\PerformanceStatisticsForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

class PerformanceStatisticsController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['create'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['performance-statistics/*'],
                    ],
                ],
            ],
        ];
    }

    // 计算业绩
    public function actionCreate($is_validate = 0)
    {
        $model = new PerformanceStatisticsForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->calculate(), $model->getFirstErrors());
    }

    private function responseJson($isSuccess, $errors = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($isSuccess)
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => $errors ? reset($errors) : '您的操作有误!'];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}