<?php

namespace backend\controllers;

use common\models\OrderCostRecord;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

class OrderCostRecordController extends BaseController
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
                'only' => ['create', 'validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'validation'],
                        'allow' => true,
                        'roles' => ['order-cost-record/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $costRecord = new OrderCostRecord();
        if($costRecord->load(Yii::$app->request->post()) && $costRecord->validate())
        {
            if($costRecord->save())
            {
                $costRecord->created_at = date('Y-m-d H:i:s',$costRecord->created_at);
                return ['status' => 200,'item' => $costRecord];
            }
        }
        return ['status' => 400,'message' => reset($costRecord->getFirstErrors())];
    }

    public function actionValidation()
    {
        $model = new OrderCostRecord();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}