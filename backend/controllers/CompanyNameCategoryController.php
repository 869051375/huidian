<?php
namespace backend\controllers;

use common\models\CompanyNameCategory;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyNameCategoryController extends BaseController
{
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
                    'status',
                    'ajax-info',
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
                        'roles' => ['company-name/list'],
                    ],
                    [
                        'actions' => ['create', 'status', 'ajax-info', 'update'],
                        'allow' => true,
                        'roles' => ['company-name/create'],
                    ],
                ],
            ],
        ];
    }

    /*
     * 列表
     */
    public function actionList()
    {
        /** @var Query $query */
        $query = CompanyNameCategory::find();
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

    /*
     * 新增
     */
    public function actionCreate($is_validate = 0)
    {
        $model = new CompanyNameCategory();
        $model->loadDefaultValues();
        if($model->load(Yii::$app->request->post()))
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($is_validate)
            {
                return ActiveForm::validate($model);
            }
            if($model->save())
            {
                return ['status' => 200];
            }
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /*
     * 编辑
     */
    public function actionUpdate($is_validate = 0)
    {
        $model = new CompanyNameCategory();
        $data = Yii::$app->request->post($model->formName());
        $model = $this->findModel(isset($data['id']) ? $data['id'] : 0);
        $model->loadDefaultValues();
        if($model->load(Yii::$app->request->post()))
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($is_validate)
            {
                return ActiveForm::validate($model);
            }
            if($model->save())
            {
                return ['status' => 200];
            }
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /*
     * 修改状态
     */
    public function actionStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->status = $status;
        if($model->validate(['status']))
        {
            $model->save(false);
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    /*
     * 修改状态
     */
    public function actionAjaxInfo($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $model->attributes];
    }

    private function findModel($id)
    {
        $model = CompanyNameCategory::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到分类');
        }
        return $model;
    }
}
