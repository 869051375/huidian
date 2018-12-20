<?php
namespace backend\controllers;

use backend\models\AdministratorSearch;
use backend\models\ChangeJobsForm;
use backend\models\ClerkSearch;
use backend\models\CompanySearch;
use backend\models\DimissionForm;
use backend\models\HireForm;
use common\actions\UploadImageAction;
use common\models\Administrator;
use common\models\Clerk;
use common\models\Company;
use common\models\CrmDepartment;
use common\models\CustomerService;
use common\models\Order;
use common\models\OrderTeam;
use common\models\Salesman;
use common\models\Supervisor;
use common\models\UploadImageForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\redis\Connection;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * 管理员账号管理控制器
 */
class AdministratorController extends BaseController
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
                    'status-manager',
                    'status-customer-service',
                    'status-supervisor',
                    'status-clerk',
                    'status-salesman',
                    'ajax-department-list',
                    'ajax-list',
                    'ajax-detail',
                    'customer-service-validation',
                    'supervisor-validation',
                    'clerk-validation',
                    'salesman-validation',
                    'ajax-salesman-list',
                    'ajax-company-department-list',
                    'ajax-hire',
                    'ajax-personnel-list',
                    'ajax-new-list',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    //管理员
                    [
                        'actions' => ['list-manager'],
                        'allow' => true,
                        'roles' => ['administrator/list-manager'],
                    ],
                    [
                        'actions' => ['add-manager'],
                        'allow' => true,
                        'roles' => ['administrator/add-manager'],
                    ],
                    [
                        'actions' => ['update-manager'],
                        'allow' => true,
                        'roles' => ['administrator/update-manager'],
                    ],
                    [
                        'actions' => ['status-manager'],
                        'allow' => true,
                        'roles' => ['administrator/status-manager'],
                    ],
                    //客服
                    [
                        'actions' => ['list-customer-service'],
                        'allow' => true,
                        'roles' => ['administrator/list-customer-service'],
                    ],
                    [
                        'actions' => ['add-customer-service', 'customer-service-validation'],
                        'allow' => true,
                        'roles' => ['administrator/add-customer-service'],
                    ],
                    [
                        'actions' => ['update-customer-service', 'customer-service-validation', 'customer-service-update'],
                        'allow' => true,
                        'roles' => ['administrator/update-customer-service'],
                    ],
                    [
                        'actions' => ['status-customer-service'],
                        'allow' => true,
                        'roles' => ['administrator/status-customer-service'],
                    ],
                    //嘟嘟妹
                    [
                        'actions' => ['list-supervisor'],
                        'allow' => true,
                        'roles' => ['administrator/list-supervisor'],
                    ],
                    [
                        'actions' => ['add-supervisor', 'supervisor-validation'],
                        'allow' => true,
                        'roles' => ['administrator/add-supervisor'],
                    ],
                    [
                        'actions' => ['update-supervisor', 'supervisor-validation', 'supervisor-update'],
                        'allow' => true,
                        'roles' => ['administrator/update-supervisor'],
                    ],
                    [
                        'actions' => ['status-supervisor'],
                        'allow' => true,
                        'roles' => ['administrator/status-supervisor'],
                    ],
                    //服务人员
                    [
                        'actions' => ['list-clerk'],
                        'allow' => true,
                        'roles' => ['administrator/list-clerk'],
                    ],
                    [
                        'actions' => ['add-clerk', 'clerk-validation'],
                        'allow' => true,
                        'roles' => ['administrator/add-clerk'],
                    ],
                    [
                        'actions' => ['update-clerk', 'clerk-validation', 'clerk-update'],
                        'allow' => true,
                        'roles' => ['administrator/update-clerk'],
                    ],
                    [
                        'actions' => ['status-clerk'],
                        'allow' => true,
                        'roles' => ['administrator/status-clerk'],
                    ],
                    //业务人员
                    [
                        'actions' => ['list-salesman'],
                        'allow' => true,
                        'roles' => ['administrator/list-salesman'],
                    ],
                    [
                        'actions' => ['add-salesman', 'salesman-validation'],
                        'allow' => true,
                        'roles' => ['administrator/add-salesman'],
                    ],
                    [
                        'actions' => ['update-salesman', 'salesman-validation', 'salesman-update'],
                        'allow' => true,
                        'roles' => ['administrator/update-salesman'],
                    ],
                    [
                        'actions' => ['status-salesman'],
                        'allow' => true,
                        'roles' => ['administrator/status-salesman'],
                    ],
                    [
                        'actions' => ['change-jobs'],
                        'allow' => true,
                        'roles' => ['administrator/change-jobs'],//调岗权限
                    ],
                    [
                        'actions' => ['leave','ajax-detail'],
                        'allow' => true,
                        'roles' => ['administrator/leave'],//离职权限
                    ],
                    [
                        'actions' => ['system'],
                        'allow' => true,
                        'roles' => ['administrator/system'],//系统设置管理
                    ],
                    [
                        'actions' => ['dimission','ajax-hire'],
                        'allow' => true,
                        'roles' => ['administrator/dimission'],//离职人员列表权限
                    ],
                    [
                        'actions' => ['ajax-list', 'ajax-department-list', 'upload','ajax-salesman-list', 'ajax-company-department-list','ajax-personnel-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['force-login','ajax-new-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadImageAction::className(),
                'modelClass' => UploadImageForm::className(),
                'keyTemplate' => 'administrator/{date:Ymd}-{time}.{ext}',
                'thumbnailWidth' => 100,
                'thumbnailHeight' => 100,
            ],
        ];
    }

    /**
     * 管理员列表
     * @return string
     */
    public function actionListManager()
    {
        return $this->search('manager');
    }

    /**
     * 客服列表
     * @return string
     */
    public function actionListCustomerService()
    {
        return $this->search('customer-service');
    }

    /**
     * 嘟嘟妹列表
     * @return string
     */
    public function actionListSupervisor()
    {
        return $this->search('supervisor');
    }

    /**
     * 服务人员列表
     * @return string
     */
    public function actionListClerk()
    {
        $searchModel = new ClerkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('clerk-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 业务人员列表
     * @return string
     */
    public function actionListSalesman()
    {
        return $this->search('salesman');
    }

    private function search($status)
    {
        $searchModel = new AdministratorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        /** @var Query $query */
        $query = $dataProvider->query;
        $query->select(['id', 'name', 'phone', 'is_root', 'type', 'username', 'status', 'department_id', 'title', 'image', 'email']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['status' => SORT_DESC, 'id' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'searchModel' => $searchModel,
//            'dataProvider' => $dataProvider,
            'provider' => $provider,
            'type' => $status,
        ]);
    }

    /**
     * 新增管理员
     * @var int $type
     * @return null|string|Response
     * @throws \Exception
     */
    public function actionAddManager($type)
    {
        return $this->add($type);
    }

    /**
     * 编辑管理员
     * @param int $id 管理员id
     * @param int|null $type 账号类型
     * @return string
     */
    public function actionUpdateManager($id, $type)
    {
        return $this->update($id, $type);
    }

    private function update($id, $type)
    {
        $model = $this->findModel($id);
        if($model->is_root)
        {
            throw new NotFoundHttpException('您没有权限修改超级管理员！！！');
        }
        if($type == Administrator::TYPE_CUSTOMER_SERVICE || $type == Administrator::TYPE_SUPERVISOR)
        {
            $model->setScenario('update_personnel');
        }
        $response = $this->saveModel($model, $type, 'update');
        if ($response) {
            return $response;
        }
        return $this->render('update', [
            'model' => $model,
            'type' =>$type,
        ]);
    }

    /**
     * 新增客服
     * @var int $type
     * @return null|string|Response
     * @throws \Exception
     */
    public function actionAddCustomerService($type)
    {
        return $this->add($type);
    }

    /**
     * 编辑客服
     * @param int $id 客服id
     * @return array|string|Response
     */
    public function actionCustomerServiceUpdate($id)
    {
        $model = $this->findCustomerServiceModel($id);
        $response = $this->saveCustomerServiceModel($model);
        if ($response) {
            return $response;
        }
        return $this->render('/customer-service/update', [
            'model' => $model,
        ]);
    }

    /**
     * 编辑管理员（客服）
     * @param int $id 客服类型管理员id
     * @param int|null $type 账号类型
     * @return string
     */
    public function actionUpdateCustomerService($id, $type)
    {
        return $this->update($id, $type);
    }

    /**
     * 编辑客服
     * @param CustomerService $model
     * @return array|Response
     */
    private function saveCustomerServiceModel($model)
    {
        if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()){
//            $model->updateAdministrator();
            $model->save(false);
//            $this->processRole($model->administrator);
            Yii::$app->session->setFlash('success', '保存成功!');
            return $this->goBack();
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败：'.reset($model->getFirstErrors()));
        }
        return null;
    }

    /**
     * 新增嘟嘟妹
     * @var int $type
     * @return null|string|Response
     * @throws \Exception
     */
    public function actionAddSupervisor($type)
    {
        return $this->add($type);
    }

    /**
     * 编辑嘟嘟妹
     * @param int $id 嘟嘟妹id
     * @return array|string|Response
     */
    public function actionSupervisorUpdate($id)
    {
        $model = $this->findSupervisorModel($id);
        $response = $this->saveSupervisorModel($model);
        if ($response) {
            return $response;
        }
        return $this->render('/supervisor/update', [
            'model' => $model,
        ]);
    }

    /**
     * 编辑管理员（嘟嘟妹）
     * @param int $id 嘟嘟妹类型的管理员id
     * @param int|null $type 账号类型
     * @return string
     */
    public function actionUpdateSupervisor($id, $type)
    {
        return $this->update($id, $type);
    }

    /**
     * 编辑嘟嘟妹
     * @param Supervisor $model
     * @return array|Response
     */
    private function saveSupervisorModel($model)
    {
        if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()){
            $model->save(false);
//            $this->processRole($model->administrator);
            Yii::$app->session->setFlash('success', '保存成功!');
            return $this->goBack();
        }
        if($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        }
        return null;
    }

    /**
     * 新增服务人员
     * @var int $type
     * @return null|string|Response
     * @throws \Exception
     */
    public function actionAddClerk($type)
    {
        return $this->add($type);
    }

    /**
     * 编辑服务人员
     * @param int $id 服务人员id
     * @return array|string|Response
     */
    public function actionClerkUpdate($id)
    {
        $model = $this->findClerkModel($id);
        $response = $this->saveClerkModel($model);
        if ($response) {
            return $response;
        }
        return $this->render('/clerk/update', [
            'clerk' => $model,
        ]);
    }

    /**
     * 编辑管理员（服务人员）
     * @param int $id 服务人员类型管理员id
     * @param int|null $type 账号类型
     * @return string
     */
    public function actionUpdateClerk($id, $type)
    {
        return $this->update($id, $type);
    }

    /**
     * 编辑服务人员
     * @param Clerk $model
     * @return array|Response
     */
    private function saveClerkModel($model)
    {
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
//            $model->updateAdministrator();
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
            return $this->goBack();
        }
        if($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', '保存失败');
        }
        return null;
    }

    /**
     * 新增业务员
     * @var int $type
     * @return null|string|Response
     * @throws \Exception
     */
    public function actionAddSalesman($type)
    {
        return $this->add($type);
    }

    /**
     * 编辑业务员
     * @param int $id 业务员id
     * @return array|string|Response
     */
    public function actionSalesmanUpdate($id)
    {
        $model = $this->findSalesmanModel($id);
        $response = $this->saveSalesmanModel($model);
        if ($response) {
            return $response;
        }
        return $this->render('/salesman/update', [
            'model' => $model,
        ]);
    }

    /**
     * 编辑管理员（业务员）
     * @param int $id 业务员类型管理员id
     * @param int|null $type 账号类型
     * @return string
     */
    public function actionUpdateSalesman($id, $type)
    {
        return $this->update($id, $type);
    }

    /**
     * 编辑业务员
     * @param Salesman $model
     * @return array|Response
     */
    private function saveSalesmanModel($model)
    {
        if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()){
//            $model->updateAdministrator();
            $model->save(false);
//            $this->processRole($model->administrator);
            Yii::$app->session->setFlash('success', '保存成功!');
            return $this->redirect(['/company/all']);
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败!');
        }
        return null;
    }

    private function add($type)
    {
        $model = new Administrator();
        $model->loadDefaultValues();
        $model->auth_key = Yii::$app->security->generateRandomString();
        $model->setScenario('create');
        $model->type = $type;
        $name = Yii::$app->request->post('name');
        $person = Administrator::find()->where(['name'=>$name])->one();
        if($person){
            Yii::$app->session->setFlash('error', '姓名名称不能重复!');
            return $this->render('update', [
                'model' => $model, 'type' => $type
            ]);
        }
        $response = $this->saveModel($model, $type, 'insert');
        if ($response)
        {
            return $response;
        }
        return $this->render('update', [
            'model' => $model, 'type' => $type
        ]);
    }

    public function actionStatusManager()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        return $this->saveStatus($id, $status);
    }

    public function actionStatusCustomerService()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        return $this->saveStatus($id, $status);
    }

    public function actionStatusSupervisor()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        return $this->saveStatus($id, $status);
    }

    public function actionStatusClerk()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        return $this->saveStatus($id, $status);
    }

    public function actionStatusSalesman()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        return $this->saveStatus($id, $status);
    }

    private function saveStatus($id, $status)
    {
        $model = $this->findModel($id);
        $model->status = $status;
        if($model->validate(['status']))
        {
            if($model->is_root == 1)
            {
                return ['status' => 400, 'message' => '超级管理员不可更改状态'];
            }
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $model->save(false);
                if($model->type != Administrator::TYPE_ADMIN)
                {
                    $model->saveStatus($model);
                }
                $t->commit();
                return ['status' => 200];
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
        }
        return ['status' => 400, 'message' => reset($model->getFirstErrors())];
    }

    /**
     * @param Administrator $model
     * @param int $type
     * @param string $status
     * @return null|\yii\web\Response
     * @throws \Exception
     */
    private function saveModel($model, $type, $status)
    {
        if ($model->load(\Yii::$app->request->post()) && $model->validate()){
            if ($model->password){
                $model->setPassword($model->password);
            }
            $t = \Yii::$app->db->beginTransaction();
            try
            {
                if($model->is_belong_company == Administrator::BELONG_COMPANY_DISABLED)
                {
                    $model->company_id = 0;
                    $model->department_id = 0;
                }
                $model->save(false);
//                $url = '';
                if($type == Administrator::TYPE_CUSTOMER_SERVICE)
                {
                    //客服
                    $model->saveCustomerService($model->id, $status);
//                    $url = 'list-customer-service';
                }
                elseif($type == Administrator::TYPE_SUPERVISOR)
                {
                    //嘟嘟妹
                    $model->saveSupervisor($model->id, $status);
//                    $url = 'list-supervisor';
                }
                elseif($type == Administrator::TYPE_CLERK)
                {
                    //服务人员
                    $model->saveClerk($model->id, $status);
//                    $url = 'list-clerk';
                }
                elseif($type == Administrator::TYPE_SALESMAN)
                {
                    //业务员
                    $model->saveSalesman($model->id, $status);
//                    $url = 'list-salesman';
                }
//                elseif($type == Administrator::TYPE_ADMIN)
//                {
//                    $url = 'list-manager';
//                }
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            $this->processRole($model);
            Yii::$app->session->setFlash('success', '保存成功!');
            //return $this->redirect([$url, 'id' => $model->id , 'type' => $type]);
            return $this->redirect(['/company/all']);
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败!');
        }
        return null;
    }

    private function processRole($account)
    {
        $roles = Yii::$app->request->post('role');
        $auth = Yii::$app->authManager;
        $auth->revokeAll($account->id);
        if (is_array($roles)) {
            foreach ($roles as $role) {
                $auth->assign($auth->getRole($role), $account->id);
            }
        }
    }

    public function actionAjaxDepartmentList($keyword = null)
    {
        $query = CrmDepartment::find()->select(['id', 'name'])->andWhere(['status' => CrmDepartment::STATUS_ACTIVE]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'department' => $this->serializeData($data)];
    }

    public function actionAjaxCompanyDepartmentList($keyword = null, $company_id = null)
    {
        $query = CrmDepartment::find()->select(['id', 'name'])->andWhere(['status' => CrmDepartment::STATUS_ACTIVE, 'company_id' => $company_id]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'department' => $this->serializeData($data)];
    }

    public function actionAjaxList($company_id = null, $department_id = null, $type = null, $has_all_manager = null, $is_sub = null, $same_department = null, $sub_department = null, $keyword = null,$administrator_id = null)
    {
        $query = Administrator::find()->select(['a.id', 'a.name', 'a.company_id','a.department_id'])->alias('a')->andWhere(['a.status' => Administrator::STATUS_ACTIVE]);
        if(null !== $type)
        {
            $query->andWhere(['a.type' => $type]);
        }
        if(null !== $administrator_id)
        {
            $query->andWhere(['not in','id',$administrator_id]);
        }
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if($company_id)
        {
            $query->andWhere(['a.company_id' => $company_id]);
        }

        if($department_id)
        {
            if($has_all_manager)
            {
                $query->joinWith('department d');
                $query->andWhere(['or', ['a.department_id' => $department_id], ['a.is_department_manager' => 1], 'd.leader_id=a.id']);
            }
            else
            {
                $query->andWhere(['a.department_id' => $department_id]);
            }
        }
        elseif($same_department)
        {
            if($sub_department)
            {
                $query->joinWith('department d');
                if($has_all_manager)
                {
                    $query->andWhere(['or', ['a.department_id' => $admin->department_id], 'd.`path` LIKE :path', ['a.is_department_manager' => 1], 'd.leader_id=a.id'], [':path' => $admin->department->path.'-%']);
                }
                else
                {
                    $query->andWhere(['or', ['a.department_id' => $admin->department_id], 'd.`path` LIKE :path'], [':path' => $admin->department->path.'-%']);
                }
            }
            else
            {
                $query->andWhere(['a.department_id' => $admin->department_id]);
            }
        }
        if($is_sub && $admin->department)
        {
            $in = [];
            $in[] = $admin->department_id;
            if($admin->id == $admin->department->leader_id)
            {
                /** @var CrmDepartment[] $departments */
                $departments = CrmDepartment::find()->where("path like '". $admin->department->path."-%'")->all();
                foreach ($departments as $department)
                {
                    $in[] = $department->id;
                }
            }

            if($has_all_manager)
            {
                $query->joinWith('department d');
                $query->andWhere(['or', ['in', 'a.id', $in], ['a.is_department_manager' => 1], 'd.leader_id=a.id'], [':path' => $admin->department->path.'-%']);
            }
            else
            {
                $query->andWhere(['in', 'a.id', $in]);
            }
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $admins = $query->all();

        $data = [];
        foreach($admins as $i => $admin)
        {
            $data[$i]['id'] = $admin->id;
            $data[$i]['name'] = $admin->name;
            $data[$i]['d_name'] = $admin->department ? $admin->department->name : '--';
            $data[$i]['c_name'] = $admin->company ? $admin->company->name : '--';
        }

        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    public function actionAjaxNewList($company_id = null, $department_id = null, $type = null, $has_all_manager = null, $is_sub = null, $same_department = null, $sub_department = null, $keyword = null,$administrator_id = null)
    {
        $query = Administrator::find()->select(['a.id', 'a.name', 'a.name','a.latter'])->alias('a')->andWhere(['a.status' => Administrator::STATUS_ACTIVE]);
        if(null !== $type)
        {
            $query->andWhere(['a.type' => $type]);
        }
        if(null !== $administrator_id)
        {
            $query->andWhere(['not in','id',$administrator_id]);
        }
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if($company_id)
        {
            $query->andWhere(['a.company_id' => $company_id]);
        }

        if($department_id)
        {
            if($has_all_manager)
            {
                $query->joinWith('department d');
                $query->andWhere(['or', ['a.department_id' => $department_id], ['a.is_department_manager' => 1], 'd.leader_id=a.id']);
            }
            else
            {
                $query->andWhere(['a.department_id' => $department_id]);
            }
        }
        elseif($same_department)
        {
            if($sub_department)
            {
                $query->joinWith('department d');
                if($has_all_manager)
                {
                    $query->andWhere(['or', ['a.department_id' => $admin->department_id], 'd.`path` LIKE :path', ['a.is_department_manager' => 1], 'd.leader_id=a.id'], [':path' => $admin->department->path.'-%']);
                }
                else
                {
                    $query->andWhere(['or', ['a.department_id' => $admin->department_id], 'd.`path` LIKE :path'], [':path' => $admin->department->path.'-%']);
                }
            }
            else
            {
                $query->andWhere(['a.department_id' => $admin->department_id]);
            }
        }
        if($is_sub && $admin->department)
        {
            $in = [];
            $in[] = $admin->department_id;
            if($admin->id == $admin->department->leader_id)
            {
                /** @var CrmDepartment[] $departments */
                $departments = CrmDepartment::find()->where("path like '". $admin->department->path."-%'")->all();
                foreach ($departments as $department)
                {
                    $in[] = $department->id;
                }
            }

            if($has_all_manager)
            {
                $query->joinWith('department d');
                $query->andWhere(['or', ['in', 'a.id', $in], ['a.is_department_manager' => 1], 'd.leader_id=a.id'], [':path' => $admin->department->path.'-%']);
            }
            else
            {
                $query->andWhere(['in', 'a.id', $in]);
            }
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();
        $data = $this->addPeople($data);
        return ['status' => 200, 'items' => $data];
    }

    public function getFirstChar($s){
        if(empty($s)) return '';
        $s0 = mb_substr($s,0,1,\Yii::$app->charset); //获取名字的姓
        //$s = iconv('UTF-8','gb2312', $s0); //将UTF-8转换成GB2312编码
        $s = iconv(Yii::$app->charset,'gbk//IGNORE', $s0); //将UTF-8转换成GB2312编码
        if (ord($s0)>128) { //汉字开头，汉字没有以U、V开头的
            $asc=ord($s{0})*256+ord($s{1})-65536;
            if($asc>=-20319 and $asc<=-20284)return "A";
            if($asc>=-20283 and $asc<=-19776)return "B";
            if($asc>=-19775 and $asc<=-19219)return "C";
            if($asc>=-19218 and $asc<=-18711)return "D";
            if($asc>=-18710 and $asc<=-18527)return "E";
            if($asc>=-18526 and $asc<=-18240)return "F";
            if($asc>=-18239 and $asc<=-17760)return "G";
            if($asc>=-17759 and $asc<=-17248)return "H";
            if($asc>=-17247 and $asc<=-17418)return "I";
            if($asc>=-17417 and $asc<=-16475)return "J";
            if($asc>=-16474 and $asc<=-16213)return "K";
            if($asc>=-16212 and $asc<=-15641)return "L";
            if($asc>=-15640 and $asc<=-15166)return "M";
            if($asc>=-15165 and $asc<=-14923)return "N";
            if($asc>=-14922 and $asc<=-14915)return "O";
            if($asc>=-14914 and $asc<=-14631)return "P";
            if($asc>=-14630 and $asc<=-14150)return "Q";
            if($asc>=-14149 and $asc<=-14091)return "R";
            if($asc>=-14090 and $asc<=-13319)return "S";
            if($asc>=-13318 and $asc<=-12839)return "T";
            if($asc>=-12838 and $asc<=-12557)return "W";
            if($asc>=-12556 and $asc<=-11848)return "X";
            if($asc>=-11847 and $asc<=-11056)return "Y";
            if($asc>=-11055 and $asc<=-10247)return "Z";
            return null;
        }else if(ord($s)>=48 and ord($s)<=57){ //数字开头
            switch(iconv_substr($s,0,1,'utf-8')){
                case 1:return "Y";
                case 2:return "E";
                case 3:return "S";
                case 4:return "S";
                case 5:return "W";
                case 6:return "L";
                case 7:return "Q";
                case 8:return "B";
                case 9:return "J";
                case 0:return "L";
            }
        }else if(ord($s)>=65 and ord($s)<=90){ //大写英文开头
            return substr($s,0,1);
        }else if(ord($s)>=97 and ord($s)<=122){ //小写英文开头
            return strtoupper(substr($s,0,1));
        }else{
            return iconv_substr($s0,0,1,Yii::$app->charset);
            //中英混合的词语，不适合上面的各种情况，因此直接提取首个字符即可
        }
    }
    public function addPeople($userName)
    {
        //sort($userName);
        $str = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $charArray = ['A'=>[],'B'=>[],'C'=>[],'D'=>[],'E'=>[],'F'=>[],'G'=>[],'H'=>[],'I'=>[],'J'=>[],'K'=>[],'L'=>[],'M'=>[],'N'=>[],'O'=>[],'P'=>[],'Q'=>[],'R'=>[],'S'=>[],'T'=>[],'U'=>[],'V'=>[],'W'=>[],'X'=>[],'Y'=>[],'Z'=>[],'other'=>[]];
        foreach($userName as $k => $name){
            if($name->latter)
            {
                $char = ucwords(mb_substr($name->latter,0,1,\Yii::$app->charset)); //获取名字的姓
            }
            else
            {
                $char = $this->getFirstChar(trim($name->name));
            }
            //$char = $this->getFirstChar(trim($name));
            $nameArray = array();
            if(in_array($char,$str))
            {
                if(!empty($char))
                {
                    if(count($charArray[$char])!=0)
                    {
                        $nameArray = $charArray[$char];
                    }

                    array_push($nameArray,[$name->id => trim($name->name).'|'.$char]);
                    $charArray[$char] = $nameArray;
                }
            }
            else
            {
                if(!empty($char))
                {
                    $nameArray = $charArray['other'];
                    array_push($nameArray,[$name->id => trim($name->name)]);
                    $charArray['other'] = $nameArray;
                }
            }
        }
        //ksort($charArray);
        return $charArray;
    }

    public function actionAjaxPersonnelList($company_id = null, $type = null, $keyword = null,$administrator_id = null)
    {
        $query = Administrator::find()->alias('a')->select('a.id, a.name,a.department_id')->andWhere(['a.status' => Administrator::STATUS_ACTIVE]);
        if(null !== $type)
        {
            $query->andWhere(['a.type' => $type]);
        }
        if(null !== $administrator_id)
        {
            $query->andWhere(['not in','id',$administrator_id]);
        }

        if($company_id)
        {
            $query->andWhere(['a.company_id' => $company_id]);
        }

        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        /** @var Administrator[] $admins */
        $admins = $query->all();

        $data = [];
        foreach($admins as $i => $admin)
        {
            $data[$i]['id'] = $admin->id;
            $data[$i]['name'] = $admin->name;
            $data[$i]['d_name'] = $admin->department ? $admin->department->name : '--';
        }
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    private function findModel($id)
    {
        $model = Administrator::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的管理员');
        }
        return $model;
    }

    private function findCustomerServiceModel($id)
    {
        $model = CustomerService::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的客服！');
        }
        return $model;
    }

    private function findSupervisorModel($id)
    {
        $model = Supervisor::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的嘟嘟妹！');
        }
        return $model;
    }

    private function findClerkModel($id)
    {
        $model = Clerk::findOne($id);
        if (null == $model){
            throw new NotFoundHttpException('找不到指定的服务人员！');
        }
        return $model;
    }

    private function findSalesmanModel($id)
    {
        $model = Salesman::findOne($id);

        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的业务人员！');
        }
        return $model;
    }

    public function actionClerkValidation()
    {
        $data = Yii::$app->request->post('Clerk');
        $model = new Clerk();
        if(!empty($data['id']))
        {
            $model = $this->findClerkModel($data['id']);
            $model->setScenario('update');
        }
        else
        {
            $model->setScenario('insert');
        }
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    public function actionCustomerServiceValidation()
    {
        $data = Yii::$app->request->post('CustomerService');
        $model = new CustomerService();
        if(!empty($data['id']))
        {
            $model = $this->findCustomerServiceModel($data['id']);
            $model->setScenario('update');
        }
        else
        {
            $model->setScenario('insert');
        }
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    public function actionSupervisorValidation()
    {
        $data = Yii::$app->request->post('Supervisor');
        $model = new Supervisor();
        if(!empty($data['id']))
        {
            $model = $this->findModel($data['id']);
            $model->setScenario('update');
        }
        else
        {
            $model->setScenario('insert');
        }
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    public function actionSalesmanValidation()
    {
        $data = Yii::$app->request->post('Salesman');
        $model = new Salesman();
        if(!empty($data['id']))
        {
            $model = $this->findModel($data['id']);
            $model->setScenario('update');
        }
        else
        {
            $model->setScenario('insert');
        }
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    public function actionForceLogin($id)
    {
        $s = Yii::$app->session->get('rootGoBack');
        if(Yii::$app->user->can('administrator/force-login') || $s == 'root')
        {
            /** @var Administrator $administrator */
            $administrator = $this->findModel($id);
            Yii::$app->user->login($administrator);
            Yii::$app->session->set('rootGoBack', 'root');
        }
        return $this->goHome();
    }

    public function actionSystem()
    {
        $admin_id = Yii::$app->user->id;
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        $backendNavItemsKey = 'backend-nav-'.$admin_id;
        if($redis->get('system'.$admin_id))
        {
            $redis->del('system'.$admin_id);
            Yii::$app->cache->delete($backendNavItemsKey);
        }
        else
        {
            $redis->set('system'.$admin_id,$admin_id);
            Yii::$app->cache->delete($backendNavItemsKey);
        }
        return $this->goHome();
    }

    //select2中的业务人员-place订单列表页
    public function actionAjaxSalesmanList($keyword=null,$order_id=null)
    {
        /** @var OrderTeam[] $order_team */
        $order_team = OrderTeam::find()->where(['order_id' => $order_id])->all();
        /** @var Order $order */
        $order = Order::findOne($order_id);
        $teams = [];
        $teams[] = $order->salesman_aid;
        foreach($order_team as $team)
        {
            $teams[] = $team->administrator_id;
        }
        /** @var ActiveQuery $query */
        $query = Administrator::find()->select(['id', 'name', 'phone'])->where(['type' => Administrator::TYPE_SALESMAN,'status' => Administrator::STATUS_ACTIVE]);
        if(!empty($keyword))
        {
            $query->andWhere(['or', ['like', 'name', $keyword], ['like', 'phone', $keyword]]);
        }
        if(!empty($teams))
        {
            $query->andWhere(['not in', 'id', $teams]);
        }
        return ['status' => 200, 'items' => $this->serializeData($query->all())];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    //调岗
    public function actionChangeJobs($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $changeJobsForm = new ChangeJobsForm();
            $changeJobsForm->administrator_id = $model->id;
            if($changeJobsForm->load(Yii::$app->request->post()) && $changeJobsForm->validate())
            {
                $changeJobsForm->changeJobs();
                $this->processRole($model);
                Yii::$app->session->setFlash('success', '人员调岗操作成功！');
                return $this->redirect(['company/all']);
            }
            if($changeJobsForm->getErrors())
            {
                Yii::$app->session->setFlash('error', reset($changeJobsForm->getFirstErrors()));
            }
        }
        return $this->render('jobs',['model' => $model]);
    }

    //离职
    public function actionLeave($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $takeOverForm = new DimissionForm();
            if($takeOverForm->load(Yii::$app->request->post()) && $takeOverForm->validate())
            {
                if($takeOverForm->takeOver())
                {
                    Yii::$app->session->setFlash('success', '离职操作成功！');
                    return $this->redirect(['administrator/dimission']);
                }
            }
            if($takeOverForm->getErrors())
            {
                Yii::$app->session->setFlash('error', reset($takeOverForm->getFirstErrors()));
            }
        }
        return $this->render('leave',['model' => $model]);
    }

    public function actionAjaxDetail($id)
    {
        $model = $this->findModel($id);
        $data = $this->serializeData($model);
        $data['imageUrl'] = $model->getImageUrl();
        $data['typeName'] = $model->getTypeName();
        return ['status' => 200, 'model' => $data];
    }

    //离职列表
    public function actionDimission()
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
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,'dimission');
        return $this->render('dimission',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'companies' => $companies,
            ]
        );
    }

    public function actionAjaxHire()
    {
        $hireForm = new HireForm();
        if($hireForm->load(Yii::$app->request->post()) && $hireForm->validate())
        {
            if($hireForm->hire())
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400,'message' => reset($hireForm->getFirstErrors())];
    }
}
