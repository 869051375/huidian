<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;


class RbacApiController extends ApiController
{
    public $post;
    public $obj;

    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        return $this->response(self::SUCCESS,'获取成功',array_keys(Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->id)));
    }
}