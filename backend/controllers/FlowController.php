<?php
namespace backend\controllers;


use backend\models\CopyFlowForm;
use backend\models\FlowForm;
use backend\models\FlowPublishForm;
use backend\models\FlowRelationForm;
use backend\models\FlowSearch;
use backend\models\ProductSearch;
use common\models\AdministratorLog;
use common\models\Flow;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FlowController extends BaseController
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax-publish' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'ajax-validation',
                    'ajax-info',
                    'ajax-status',
                    'ajax-list',
                    'ajax-delete',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['list', 'info', 'products'],
                        'allow' => true,
                        'roles' => ['flow/list'],
                    ],
                    [
                        'actions' => ['ajax-create', 'ajax-copy'],
                        'allow' => true,
                        'roles' => ['flow/create'],
                    ],
                    [
                        'actions' => ['ajax-update', 'ajax-info', 'relation-flow'],
                        'allow' => true,
                        'roles' => ['flow/update'],
                    ],
                    [
                        'actions' => ['ajax-delete'],
                        'allow' => true,
                        'roles' => ['flow/delete'],
                    ],
                    [
                        'actions' => ['ajax-status'],
                        'allow' => true,
                        'roles' => ['flow/status'],
                    ],
                    [
                        'actions' => ['ajax-publish'],
                        'allow' => true,
                        'roles' => ['flow/publish'],
                    ],
                ],
            ],
        ];
    }
    public function actionList()
    {

        $searchModel = new FlowSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        /** @var Query $query */
        $query = $dataProvider->query;
        $query->select(['id', 'name', 'status', 'is_publish', 'is_delete'])->orderBy('created_at DESC');

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionAjaxList($keyword = null)
    {
        $query = Flow::activeQuery()->select(['id', 'name']);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'flows' => $this->serializeData($data)];
    }

    public function actionProducts($flow_id)
    {
        $model = $this->findModel($flow_id);
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        /** @var Query $query */
        $query = $dataProvider->query;
        $query->andWhere(['flow_id' => $model->id]);
        $query->orderBy('created_at DESC');
        return $this->render('products', [
            'flowModel' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionInfo($flow_id)
    {
        $model = $this->findModel($flow_id);
        return $this->render('info', [
            'model' => $model,
        ]);
    }

    /**
     * 新增商品流程
     */
    public function actionAjaxCreate()
    {
        $model = new FlowForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model = $model->save();
            if($model){
                Yii::$app->session->setFlash('success', '流程保存成功!');
                if(Yii::$app->request->post('next') === 'save-next'){
                    if(Yii::$app->user->can('flow/list')){
                        return $this->redirect(['flow-node/list', 'flow_id' => $model->id]);
                    }
                    return $this->redirect(['list']);
                }
                return $this->redirect(['list']);
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->render('list', ['model' => $model]);
    }

    /**
     * 复制商品流程
     */
    public function actionAjaxCopy()
    {
        $model = new CopyFlowForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model = $model->copy();
            if($model){
                Yii::$app->session->setFlash('success', '流程复制成功!');
                return $this->redirect(['list']);
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->render('list');
    }

    // 更新商品流程
    public function actionAjaxUpdate($id)
    {
        $flow = $this->findModel($id);
        $model = new FlowForm();
        if ($model->load(Yii::$app->request->post())) {
            if($model->update($flow)){
                Yii::$app->session->setFlash('success', '更新成功!');
                if(Yii::$app->request->post('next') === 'save-next'){
                    if(Yii::$app->user->can('flow/list')){
                        return $this->redirect(['flow-node/list', 'flow_id' => $flow->id]);
                    }
                }
                return $this->redirect(['list']);
            }
            else
            {
                Yii::$app->session->setFlash('error', '更新失败!');
            }
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '更新失败, 您的表单填写有误, 请检查!');
        }
        return $this->redirect(['list']);
    }

    public function actionAjaxInfo($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $this->serializeData($model)];
    }
    public function actionAjaxStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->status = $status;
        if($model->validate(['status']))
        {
            $model->save(false);
            //新增后台操作日志
            AdministratorLog::logFlowAjaxStatus($model);
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }
    public function actionAjaxDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->validate(['is_delete']))
        {
            $model->status = Flow::STATUS_DISABLED;
            $model->is_delete = Flow::DELETE_ACTIVE;
            $model->save(false);
            //新增后台操作日志
            AdministratorLog::logFlowAjaxDelete($model);
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionAjaxPublish($id)
    {
        $model = $this->findModel($id);
        $formModel = new FlowPublishForm();
        $formModel->flow = $model;
        if($formModel->validate())
        {
            if($formModel->publish())
            {
                //新增后台操作日志
                AdministratorLog::logFlowAjaxPublish($model);
                return $this->redirect(['flow-node/list', 'flow_id' => $model->id]);
            }
        }
        $errors = $formModel->getFirstErrors();
        Yii::$app->session->setFlash('error',  reset($errors));
        return $this->redirect(['flow-node/list', 'flow_id' => $model->id]);
    }

    public function actionRelationFlow($flow_id)
    {
        $flow = $this->findModel($flow_id);
        $flowRelationModel = new FlowRelationForm();
        if($flowRelationModel->load(Yii::$app->request->post()) && $flowRelationModel->remove())
        {
            Yii::$app->session->setFlash('success', '取消关联成功!');
            return $this->redirect(['products', 'flow_id' => $flow->id]);
        }
        else
        {
            if ($flowRelationModel->hasErrors()) {
                $errors = $flowRelationModel->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
            else
            {
                Yii::$app->session->setFlash('error', '您的操作有误!');
            }
        }
        return $this->redirect(['products', 'flow_id' => $flow->id]);
    }
    /**
     * @param $id
     * @return Flow
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = Flow::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的流程!');
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

    public function actionAjaxValidation()
    {
        $model = new FlowForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

}