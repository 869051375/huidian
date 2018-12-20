<?php

namespace backend\controllers;

use backend\models\RewardProportionForm;
use common\models\RewardProportionRule;
use common\models\RewardProportionVersion;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RewardProportionRuleController extends BaseController
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
                'only' => ['delete', 'validation', 'detail'],
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
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['reward-proportion/delete'],
                    ],
                    [
                        'actions' => ['create','update', 'validation', 'detail'],
                        'allow' => true,
                        'roles' => ['reward-proportion/update'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($id = 0)
    {
        $models = RewardProportionRule::find()->where(['reward_proportion_version_id' => $id])->all();
        $version = $this->findVersion($id);
        return $this->render('list', [
            'models' => $models,
            'version' => $version,
        ]);
    }

    // 新增方案
    public function actionCreate()
    {
        $model = new RewardProportionRule();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['list','id' => $model->reward_proportion_version_id]);
            }
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['list','id' => $model->reward_proportion_version_id]);
    }

    public function actionValidation()
    {
        $model = new RewardProportionRule();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $ruleData = $this->serializeData($model);
        return ['status' => 200, 'model' => $ruleData];
    }

    // 更新方案
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
        } else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list','id' => $model->reward_proportion_version_id]);
    }

    // 删除方案
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    // 加载一个提成规则时，当找不到时抛出异常
    private function findModel($id)
    {
        $model = RewardProportionRule::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的方案规则!');
        }
        return $model;
    }

    private function findVersion($id)
    {
        $model = RewardProportionVersion::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的方案版本!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}