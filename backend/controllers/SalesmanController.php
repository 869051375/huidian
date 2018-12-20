<?php

namespace backend\controllers;

use common\models\Salesman;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

class SalesmanController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['ajax-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                        'roles' => ['order-action/change-salesman'],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxList()
    {
        $models = Salesman::find()->select(['id', 'administrator_id', 'name', 'phone'])->where(['status' => Salesman::STATUS_ACTIVE])->all();
        return ['status' => 200, 'models' => $this->serializeData($models)];
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}