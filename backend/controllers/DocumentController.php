<?php
namespace backend\controllers;

use backend\models\DocumentForm;
use backend\models\DocumentSearch;
use common\models\Document;
use common\models\DocumentCategory;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DocumentController extends BaseController
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
                'only' => [
                    'delete',
                ],
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
                        'roles' => ['document/list'],
                    ],
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => ['document/update'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['document/delete'],
                    ],
                    [
                        'actions' => ['info'],
                        'allow' => true,
                        'roles' => ['document/info'],
                    ],
                ],
            ],
        ];
    }

    //文档列表
    public function actionList($category_id)
    {
        $documentCategory = $this->findDocumentCategory($category_id);
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $category_id);
        /** @var Query $query */
        $query = $dataProvider->query;
        $query->select(['id', 'title', 'creator_name', 'document_category_id', 'created_at', 'updated_at'])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC]);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'documentCategory' => $documentCategory
        ]);
    }

    //新增文档
    public function actionCreate($category_id)
    {
        $documentCategory = $this->findDocumentCategory($category_id);
        $model = new DocumentForm();
        $model->document_category_id = $category_id;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $document = $model->save();
            if($document){
                Yii::$app->session->setFlash('success', '文档保存成功!');
                return $this->redirect(['list', 'category_id' => $document->document_category_id]);
            }
            Yii::$app->session->setFlash('error', '保存失败!');
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败, 您的表单填写有误, 请检查!');
        }
        return $this->render('create', [
            'model' => $model,
            'category_id' => $documentCategory->id
        ]);

    }

    // 文档编辑
    public function actionUpdate($id)
    {
        $document = $this->findModel($id);
        $model = new DocumentForm();
        $model->setAttributes($document->attributes);
        if($model->load(Yii::$app->request->post())){
            if($model->update($document)){
                Yii::$app->session->setFlash('success', '更新成功!');
                return $this->redirect(['list', 'category_id' => $document->document_category_id]);
            }
            Yii::$app->session->setFlash('error', '更新失败!');
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '更新失败, 您的表单填写有误, 请检查!');
        }
        return $this->render('update', [
            'model' => $model,
            'category_id' => $document->document_category_id
        ]);
    }

    //文档详情
    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        return $this->render('info', [
            'model' => $model,
        ]);
    }

    // 删除文档
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    /**
     * @param $id
     * @return Document
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = Document::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的数据!');
        }
        return $model;
    }

    /**
     * @param $id
     * @return DocumentCategory
     * @throws NotFoundHttpException
     */
    private function findDocumentCategory($id)
    {
        $model = DocumentCategory::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的数据!');
        }
        return $model;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}