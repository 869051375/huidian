<?php
namespace backend\controllers;

use backend\models\AdministratorLogSearch;
use Yii;
use yii\filters\AccessControl;


class AdministratorLogController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

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
                        'actions' => ['record'],
                        'allow' => true,
                        'roles' => ['administrator-log/record'],
                    ],
                    [
                        'actions' => ['warning'],
                        'allow' => true,
                        'roles' => ['administrator-log/warning'],
                    ],
                ],
            ],
        ];
    }

    //登录记录
    public function actionRecord()
    {
        return $this->search('record');
    }
    //风险操作警告
    public function actionWarning()
    {
        return $this->search('warning');
    }

    private function search($status)
    {
        $searchModel = new AdministratorLogSearch();
        $provider = $searchModel->search(Yii::$app->request->queryParams, $status);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'provider' => $provider,
        ]);
    }

}
