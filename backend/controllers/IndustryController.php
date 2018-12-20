<?php

namespace backend\controllers;
use common\models\Industry;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * IndustryController implements the CRUD actions for Industry model.
 */

class IndustryController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;


    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [

                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'sort', 'validation', 'detail'],
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
                        'roles' => ['industry/list'],
                    ],
                    [
                        'actions' => ['create', 'upload', 'validation'],
                        'allow' => true,
                        'roles' => ['industry/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['industry/delete'],
                    ],
                    [
                        'actions' => ['update', 'upload', 'validation', 'detail', 'sort'],
                        'allow' => true,
                        'roles' => ['industry/update'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $query = Industry::find();
        $query->select(['id', 'name','sort']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['sort' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    // 新增
    public function actionCreate()
    {
        $model = new Industry();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            Yii::$app->session->setFlash('success', '保存成功!');
            $model->save(false);
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list']);
    }

    public function actionValidation()
    {
        $model = new Industry();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $industryData = $this->serializeData($model);
        return ['status' => 200, 'model' => $industryData];
    }

    // 更新
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        } else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list']);
    }

    // 行业排序
    public function actionSort()
    {

        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');
        $source = $this->findModel($source_id);
        $target = $this->findModel($target_id);
        // 交换两个行业的排序序号
        $sort = $target->sort;
        $target->sort = $source->sort;
        $source->sort = $sort;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    // 删除一个行业
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    // 加载数据时找不到时抛出异常
    private function findModel($id)
    {
        $model = Industry::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的行业！');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}