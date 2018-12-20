<?php
namespace backend\controllers;

use yii\filters\AccessControl;
use Yii;
use yii\filters\Cors;
use yii\web\Response;
use yii\helpers\ArrayHelper;


class ApiController extends BaseController
{
    const SUCCESS = 200;    //成功
    const FAIL  =   400;    //失败

    /**
     * 200成功
     * 400失败
     */

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [
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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
        $behaviors = ArrayHelper::merge(parent::behaviors(), $behaviors);
        return $behaviors;
    }

    protected function response($code = 200,$message,$data = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['code' => $code,'message' => $message, 'data' => $data];
    }
}