<?php
namespace backend\controllers;

use common\models\Administrator;
use Yii;
use yii\filters\AccessControl;


class AdministratorApiController extends ApiController
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
        return $this->response(self::SUCCESS,'获取成功',Administrator::find()->select('id,name,is_root,phone')->where(['id'=>Yii::$app->user->id])->one());
    }
}