<?php

namespace backend\controllers;

use common\models\Administrator;
use common\models\MonthProfitRecord;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;

/**
 * BannerController implements the CRUD actions for Banner model.
 */

/**
 * Created by PhpStorm.
 * User: xinqiangWang
 * Date: 2017/2/20
 * Time: 14:28
 */
abstract class BaseController extends Controller
{
    public function behaviors()
    {
        $behaviors = [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'text/html' => Response::FORMAT_HTML,
                ],
            ],
        ];
        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }

    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    public function beforeAction($action)
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $session = Yii::$app->session;
        $redis = Yii::$app->redis;
        $s = $session->get('rootGoBack');
        //通过内部Force Login登录不执行强退
        if($s != 'root')
        {
            if(YII_ENV == 'prod') // 一个账号只能允许一个人登录逻辑，只有在生产环境情况下生效。
            {
                if($administrator && $redis->get('user-login-id-'.$administrator->id) != $session->id)
                {
                    Yii::$app->user->logout(); //执行登出操作
                }
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




}