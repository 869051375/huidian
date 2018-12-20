<?php

namespace backend\controllers;

use backend\models\CompanySearch;
use common\models\Administrator;
use common\models\CallCenterAssignCompany;
use common\models\Company;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyController extends BaseController
{

    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

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
                'only' => ['delete', 'validation', 'detail', 'ajax-list'],
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
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['department/list'],//共用之前的部门列表权限
                    ],
                    [
                        'actions' => ['create', 'validation'],
                        'allow' => true,
                        'roles' => ['company/create'],
                    ],
                    [
                        'actions' => ['update', 'validation', 'detail'],
                        'allow' => true,
                        'roles' => ['company/update'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['company/delete'],
                    ],
                    [
                        'actions' => ['all'],
                        'allow' => true,
                        'roles' => ['company/all'],//组织机构视图列表
                    ],
                ],
            ],
        ];
    }

    public function actionAll()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        /** @var Company $companies */
        $query = Company::find();
        if ($administrator->isBelongCompany() && $administrator->company_id)
        {
            $query->andWhere(['id' => $administrator->company_id]);
        }
        $companies = $query->orderBy(['created_at' => SORT_ASC])->all();
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,'all');
        return $this->render('all',
            [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'companies' => $companies,
        ]
    );
    }

    public function actionList()
    {
        $query = Company::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    // 新增公司
    public function actionCreate()
    {
        $model = new Company();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->setFlash('success', '保存成功!');
            if(empty($model->financial_id))
            {
                $model->financial_id = 0;
            }
            $model->save(false);
            return $this->redirect(['list']);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['list']);
    }

    public function actionValidation()
    {
        $model = new Company();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $date =[];
        $model = $this->findModel($id);
        $date['id'] = $model->id;
        $date['financial_id'] = $model->financial_id;
        $date['name'] = $model->name;
        $date['financial_name'] = $model->administratorByFinancial ? $model->administratorByFinancial->name : '';
        return ['status' => 200, 'model' => $this->serializeData($date)];
    }

    // 更新公司
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        }
        else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list']);
    }

    // 删除公司
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    //公司ajax
    public function actionAjaxList($keyword = null, $company_id = null, $call_center = '0')
    {
        $query = Company::find()->select(['id', 'name']);
        if($call_center == '1')
        {
            $callCenterCompany = CallCenterAssignCompany::find()->select('company_id')->asArray()->all();
            $callCompanyIds = array_column($callCenterCompany, 'company_id');
            if(!empty($callCompanyIds))
            {
                $query->andWhere(['not in', 'id', $callCompanyIds]);
            }
        }
        if(!empty($company_id))
        {
            $query->andWhere(['id' => $company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'company' => $this->serializeData($data)];
    }

    private function findModel($id)
    {
        $model = Company::findOne($id);

        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的数据！');
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
