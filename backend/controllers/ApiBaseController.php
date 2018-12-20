<?php

namespace backend\controllers;

use common\models\Administrator;
use common\models\MonthProfitRecord;
use Yii;
use yii\base\Model;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

abstract class ApiBaseController extends Controller
{
    public $enableCsrfValidation = false;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items'
    ];

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        $behaviors_arr = parent::behaviors();
        unset($behaviors_arr['contentNegotiator']);
        // 此处代码需要提取到公共中
        $behaviors = [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'except' => ['export'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'corsFilter' => [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Origin' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Expose-Headers' => [],
                ],
            ],
        ];
        /*
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            $behaviors['authenticator'] = [
                'class' => HttpBearerAuth::className(),
            ];
        }
        */
        return ArrayHelper::merge($behaviors_arr, $behaviors);
    }

    /**
     * @param $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeAction($action)
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $session = Yii::$app->session;
        $redis = Yii::$app->get('redis');
        $s = $session->get('rootGoBack');
        //通过内部Force Login登录不执行强退
        if($s != 'root')
        {
            if($administrator && $redis->get('user-login-id-'.$administrator->id) != $session->id)
            {
                Yii::$app->user->logout(); //执行登出操作
            }
        }
        $ignoreControllers = ['expected-profit-settlement', 'site'];

        if(!in_array($action->controller->id, $ignoreControllers))
        {
//            $record = MonthProfitRecord::getLastRecord();
//            if($record && $record->isDoing())
//            {
//                throw new NotFoundHttpException('系统结算中...您暂时不能进行操作，请稍等。');
//            }
        }
        return parent::beforeAction($action);
    }

    protected function response($code = 200, $message = '', $data = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['code' => $code, 'message' => $message, 'data' => $data];
    }

    protected function responseData($data)
    {
        return $this->response(200, 'OK', $this->serializeData($data));
    }

    /**
     * @param Model $model
     * @return array
     */
    protected function responseValidateError($model)
    {
        $errors = $model->getFirstErrors();
        $firstErrors = $model->getFirstErrors();
        return $this->response(400, current($firstErrors), $errors);
    }

    /**
     * @param $message
     * @return array
     */
    protected function responseError($message)
    {
        return $this->response(500, $message, null);
    }

    /**
     * @param $message
     * @return array
     */
    public function responseMessage($message)
    {
        return $this->response(200, $message, null);
    }
}