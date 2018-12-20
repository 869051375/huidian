<?php

namespace backend\controllers;

use common\models\CustomerService;
use common\models\Order;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

class CustomerServiceController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className()
            ],
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxList($order_id)
    {
        /** @var Order $order */
        $order = Order::findOne($order_id);
        $query = CustomerService::find()->select(['id', 'name', 'servicing_number','phone'])->where(['status' => CustomerService::STATUS_ACTIVE]);
        if($order && $order->company_id)
        {
            $query->andWhere(['company_id' => $order->company_id]);
        }
        return ['status' => 200, 'model' => $this->serializeData($query->all())];
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