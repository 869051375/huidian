<?php
namespace backend\controllers;

use common\models\DocumentCategory;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class DocumentCategoryController extends BaseController
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
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'update','detail'],
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
                        'roles' => ['document-category/list'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['document-category/delete'],
                    ],
                    [
                        'actions' => ['create', 'update', 'detail'],
                        'allow' => true,
                        'roles' => ['document-category/update'],
                    ],
                ],
            ],
        ];
    }

    //获得所有分类文档库列表
    public function actionList()
    {
        $query = DocumentCategory::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        /** @var Query $query */
        $query = $dataProvider->query;
        $query->where(['parent_id' => 0])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC]);
        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new DocumentCategory();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);
            return $this->redirect(['list', 'id' => !empty($model->parent_id) ? $model->parent_id : $model->id]);
        }
        Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        return $this->redirect(['list', 'id' => $model->parent_id]);
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $data = $this->serializeData($model);
        return ['status' => 200, 'model' => $data];
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
            }
        }
        return $this->redirect(['list', 'id' => !empty($model->parent_id) ? $model->parent_id : $model->id]);
    }

    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    private function findModel($id)
    {
        /** @var DocumentCategory $model */
        $model = DocumentCategory::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的分类文档库!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}