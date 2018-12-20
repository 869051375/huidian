<?php

namespace backend\controllers;

use backend\models\ClerkServiceSearch;
use backend\models\ClerkServiceStatusForm;
use common\models\Administrator;
use common\models\ClerkServicePause;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ClerkServiceController extends BaseController
{
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
                        'actions' => ['list', 'ajax-status'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        /** @var \common\models\Administrator $user */
        $user = Yii::$app->user->identity;
        if($user->type != Administrator::TYPE_CLERK || null == $user->clerk)
        {
            throw new BadRequestHttpException('您的请求无效。');
        }
    }

    // 服务项目列表
    public function actionList()
    {
        $searchModel = new ClerkServiceSearch();
        /** @var \common\models\Administrator $user */
        $user = Yii::$app->user->identity;
        $dataProvider = $searchModel->search($user->clerk, Yii::$app->request->queryParams);
        $pausedList = ClerkServicePause::find()->where(['clerk_id' => $user->clerk->id])->all();
        return $this->render('list', [
            'searchModel' => $searchModel,
            'provider' => $dataProvider,
            'pausedList' => $pausedList,
        ]);
    }

    public function actionAjaxStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new ClerkServiceStatusForm();
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            /** @var \common\models\Administrator $user */
            $user = Yii::$app->user->identity;
            if($model->save($user->clerk))
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => $model->hasErrors() ? reset($model->getFirstErrors()) : '您的操作有误'];
    }
}