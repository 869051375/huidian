<?php

namespace backend\controllers;

use backend\models\RewardProportionForm;
use common\models\Administrator;
use common\models\FixedPoint;
use common\models\RewardProportion;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RewardProportionController extends BaseController
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
                'only' => ['delete', 'validation', 'detail','proportion', 'point-detail', 'point-delete','effective'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['proportion'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['list','fixed-point'],
                        'allow' => true,
                        'roles' => ['reward-proportion/list'],
                    ],
                    [
                        'actions' => ['create', 'create-point','validation','update', 'detail', 'point-detail','effective'],
                        'allow' => true,
                        'roles' => ['reward-proportion/update'],
                    ],
                    [
                        'actions' => ['delete','point-delete'],
                        'allow' => true,
                        'roles' => ['reward-proportion/delete'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $query = RewardProportion::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    public function actionFixedPoint()
    {
        $query = FixedPoint::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('fixed-point', [
            'provider' => $provider,
        ]);
    }

    // 新增方案
    public function actionCreate()
    {
        $model = new RewardProportion();
        $model->setScenario('insert');
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save())
            {
                $version = $model->createVersion();
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['reward-proportion-rule/list','id'=>$version->id]);
            }
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect('list');
    }

    public function actionValidation()
    {
        $model = new RewardProportion();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $proportionData = $this->serializeData($model);
        return ['status' => 200, 'model' => $proportionData];
    }

    // 更新方案
    public function actionUpdate($id)
    {
        $rewardProportion = $this->findModel($id);
        $rewardProportion->setScenario('edit');
        if ($rewardProportion->load(Yii::$app->request->post()) && $rewardProportion->validate())
        {
            if($rewardProportion->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
        } else
        {
            $errors = $rewardProportion->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list']);
    }

    // 删除方案
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->department)
        {
            return ['status' => 400 , 'message' => '当前方案已关联部门，不能删除！'];
        }
        $model->delete();
        return ['status' => 200];
    }

    // 加载一个方案时，当找不到时抛出异常
    private function findModel($id)
    {
        $model = RewardProportion::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的方案!');
        }
        return $model;
    }

    public static function actionProportion($keyword = null)
    {
        $query = RewardProportion::find();
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        return ['status' => 200, 'proportion' => $query->all()];
    }

    //固定点位
    public function actionCreatePoint($is_validate = 0)
    {
        $id = Yii::$app->getRequest()->post()['FixedPoint']['id'];
        $model = FixedPoint::findOne($id);
        if($id && $model)
        {
            $model->setScenario('update');
        }
        else
        {
            $model = new FixedPoint();
            $model->setScenario('insert');
        }
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $model->creator_id = $user->id;
        $model->creator_name = $user->name;
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionPointDetail($id)
    {
        $model = FixedPoint::find()->select('id,name,rate')->where(['id' => $id])->limit(1)->one();
        $proportionData = $this->serializeData($model);
        return ['status' => 200, 'model' => $proportionData];
    }

    public function actionPointDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = FixedPoint::findOne($id);
        if($model == null)
        {
            return ['status' => 400 , 'message' => '找不到指定的方案'];
        }
        $model->delete();
        return ['status' => 200];
    }

    public function actionEffective()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = FixedPoint::findOne($id);
        if($model == null)
        {
            return ['status' => 400 , 'message' => '找不到指定的方案'];
        }
        $model->status = FixedPoint::STATUS_ACTIVE;
        $model->save(false);
        return ['status' => 200];
    }

    private function responseJson($isSuccess, $errors = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($isSuccess)
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => $errors ? reset($errors) : '您的操作有误!'];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}