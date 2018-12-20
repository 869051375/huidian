<?php

namespace backend\controllers;

use backend\models\ProportionVersionCopyForm;
use backend\models\RewardProportionVersionForm;
use common\models\RewardProportion;
use common\models\RewardProportionVersion;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RewardProportionVersionController extends BaseController
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
                'only' => ['validation','copy','effective'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['reward-proportion/list'],
                    ],
                    [
                        'actions' => ['copy','effective','update', 'validation'],
                        'allow' => true,
                        'roles' => ['reward-proportion/update'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($id = 0)
    {
        $models = RewardProportionVersion::find()->where(['reward_proportion_id' => $id])->all();
        $proportion = $this->findProportion($id);
        return $this->render('list', [
            'models' => $models,
            'proportion' => $proportion,
        ]);
    }

    //生效
    public function actionEffective()
    {
        $versionForm = new RewardProportionVersionForm();
        if($versionForm->load(Yii::$app->request->post()) && $versionForm->validate())
        {
            if($versionForm->effective())
            {
                return ['status' => 200];
            }
        }
        if($versionForm->hasErrors())
        {
            return ['status' => 400,'message' => reset($versionForm->getFirstErrors())];
        }
    }

    public function actionValidation()
    {
        $model = new RewardProportionVersionForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

    // 复制版本
    public function actionCopy()
    {
        $versionCopyForm = new ProportionVersionCopyForm();
        if($versionCopyForm->load(Yii::$app->request->post(),'') && $versionCopyForm->validate())
        {
            if($versionCopyForm->copy())
            {
                return ['status' => 200];
            }
        }
        if($versionCopyForm->hasErrors())
        {
            return ['status' => 400,'message' => reset($versionCopyForm->getFirstErrors())];
        }
    }

    // 加载一个方案时，当找不到时抛出异常
    private function findProportion($id)
    {
        $model = RewardProportion::findOne($id);
        if (null == $model)
        {
            throw new NotFoundHttpException('找不到指定的方案!');
        }
        return $model;
    }
}