<?php
namespace backend\controllers;

use backend\models\CrmCustomerCheckForm;
use backend\models\CrmCustomerForm;
use backend\models\CrmCustomerImportForm;
use backend\models\CrmCustomerSearch;
use backend\models\CustomerBatchShareForm;
use backend\models\CustomerChangeAdministratorForm;
use backend\models\CustomerConfirmReceiveForm;
use backend\models\CustomerExportSearch;
use backend\models\CustomerReleaseForm;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\BusinessSubject;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\CustomerCustomField;
use common\models\MessageRemind;
use common\utils\BC;
use Exception;
use League\Csv\Writer;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\redis\Cache;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class CrmCustomerController extends BaseController
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
                    'ajax-check',
                    'confirm-receive',
                    'change-administrator',
                    'batch-customer-share',
                    'validation',
                    'ajax-administrator-list',
                    'ajax-company-list',
                    'ajax-department-list',
                    'confirm-release',
                    'ajax-opportunity',
                    'ajax-opportunity-detail',
                    'custom-field',
                    'ajax-custom-field-list',
                    'call'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'list',
                            'change-administrator',
                            'create',
                            'confirm-receive',
                            'batch-customer-share',
                            'check',
                            'ajax-check',
                            'validation',
                            'update',
                            'confirm-release',
                            'common-create',
                        ],
                        'allow' => true,
                        'roles' => ['customer/*'],
                    ],
                    [
                        'actions' => ['all'],
                        'allow' => true,
                        'roles' => ['customer/all'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['customer/export'],
                    ],
                    [
                        'actions' => ['import','download'],
                        'allow' => true,
                        'roles' => ['customer/import'],
                    ],
                    [
                        'actions' => ['ajax-administrator-list', 'ajax-company-list', 'ajax-department-list', 'ajax-opportunity', 'ajax-opportunity-detail','call-in', 'call','custom-field','ajax-custom-field-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($id = null, $status = null)
    {
        $search = new CrmCustomerSearch();
        $search->page_size = isset(Yii::$app->request->get()['per-page']) ? Yii::$app->request->get()['per-page'] : 20;
        $search->load(Yii::$app->request->queryParams);
        $search->search();
        $customerField = CustomerCustomField::find()->select('fields')->where(['administrator_id' => Yii::$app->user->id])->one();
        $customField = null;
        if($customerField)
        {
            $customField = json_decode($customerField->fields);
        }
        /** @var CrmCustomer $customer */
        $customer = null;
        $records = null;
        $opportunities = null;
//        $opportunityDataProvider = null;
        if($id != null)
        {
            $customer = $this->findModel($id);
            /** @var CrmCustomerLog[] $records */
            $records = CrmCustomerLog::find()->where(['customer_id' => $customer->id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])
                ->orderBy(['created_at'=>SORT_DESC])->all();
            //默认显示跟进中商机(不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机),默认显示50条
            $opportunities = CrmOpportunity::find()->where(['customer_id' => $id, 'opportunity_public_id' => 0, 'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_NOT_DEAL])->all();
//            $opportunityDataProvider = new ActiveDataProvider([
//                'query' => $query,
//                'pagination' => [
//                  'pageSize' => 50,
//                ],
//            ]);
        }
        // 如果是ajax请求，则是请求待办列表，
        // 放到这个控制器下主要是为了兼容老旧浏览器不支持pjax
//        if(Yii::$app->request->isAjax)
//        {
//            return $this->renderAjax('upcoming', [
//                'search' => $search,
//                'records' => $records,
//                'customer' => $customer,
//                'opportunityDataProvider' => $opportunityDataProvider,
//            ]);
//        }
        return $this->render('list', ['records' => $records, 'search' => $search, 'customer' => $customer, 'opportunities' => $opportunities,'customField' => $customField]);
    }

    public function actionAll($id = null, $status = null)
    {
        $search = new CrmCustomerSearch();
        $search->page_size = isset(Yii::$app->request->get()['per-page']) ? Yii::$app->request->get()['per-page'] : 20;
        $search->load(Yii::$app->request->queryParams);
        $search->range = 'all';
        $search->search();
        $customerField = CustomerCustomField::find()->select('fields')->where(['administrator_id' => Yii::$app->user->id])->one();
        $customField = null;
        if($customerField)
        {
            $customField = json_decode($customerField->fields);
        }
        /** @var CrmCustomer $customer */
        $customer = null;
        $records = null;
        $opportunities = null;
//        $opportunityDataProvider = null;
        if($id != null)
        {
            $customer = $this->findModel($id);
            /** @var CrmCustomerLog[] $records */
            $records = CrmCustomerLog::find()->where(['customer_id' => $customer->id, 'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])
                ->orderBy(['created_at'=>SORT_DESC])->all();
            //默认显示跟进中商机(不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机),默认显示50条
            $opportunities = CrmOpportunity::find()->where(['customer_id' => $id, 'opportunity_public_id' => 0, 'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_NOT_DEAL])->all();
//            $opportunityDataProvider = new ActiveDataProvider([
//                'query' => $opportunityQuery,
//                'pagination' => [
//                    'pageSize' => 50,
//                ],
//            ]);
        }

        // 如果是ajax请求，则是请求待办列表，
        // 放到这个控制器下主要是为了兼容老旧浏览器不支持pjax
//        if(Yii::$app->request->isAjax)
//        {
//            return $this->renderAjax('upcoming', [
//                'search' => $search,
//                'records' => $records,
//                'customer' => $customer,
//                'opportunityDataProvider' => $opportunityDataProvider,
//            ]);
//        }
        return $this->render('list', ['search' => $search, 'records' => $records, 'customer' => $customer, 'opportunities' => $opportunities, 'customField' => $customField]);
    }

    public function actionCall($id)
    {
        $customer = $this->findModel($id);
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $callCenter = $admin->callCenterAssignCompany ? $admin->callCenterAssignCompany->callCenter ? $admin->callCenterAssignCompany->callCenter : null :null;
        if(null == $callCenter || empty($customer->phone) || empty($admin->call_center))
        {
            return ['status' => 400, 'message' => '呼叫失败！'];
        }

        if($callCenter)
        {
            if(!$callCenter->isOnline())
            {
                return ['status' => 400, 'message' => '呼叫失败，api呼叫已禁用！'];
            }
            $client = new Client();
            $url = $callCenter->url.'&phonenum='.$customer->phone.'&integratedid='.trim($admin->call_center);
            $response = $client->get($url)->send();
            if($response->getIsOk())
            {
                $jsonString = $response->getContent();
                if($jsonString == 200)
                {
                    return ['status' => 200, 'message' => '呼叫成功！'];
                }
                else
                {
                    //失败时返回json数据
                    //$jsonDecodeString = json_decode($jsonString);
                    return ['status' => 400, 'message' => '呼叫失败！'];
                }
            }
        }
        return ['status' => 403, 'message' => '呼叫失败！'];
    }

    public function actionCheck($no = null)
    {
        $tel = stripos($no, '-') !== false ? $no : '';
        $phone = stripos($no, '-') === false ? $no : '';
        $model = new CrmCustomerCheckForm();
        $model->tel = $tel;
        $model->phone = $phone;
        return $this->render('check', ['model' => $model]);
    }

    public function actionAjaxCheck()
    {
        $model = new CrmCustomerCheckForm();
        $data = Yii::$app->request->post();
        if($model->load($data))
        {
            if(!$model->validate())
            {
                $customer = $model->getCrmCustomer();
                $customerId = 0;
                $customerPublicId = 0;
                if($customer)
                {
                    $customerId = $customer->id;
                    $customerPublicId = $customer->customer_public_id;
                }
                return ['status'=>400, 'customer_id' => $this->serializeData($customerId), 'customer_public_id' => $this->serializeData($customerPublicId), 'message'=> reset($model->getFirstErrors())];
            }
            else
            {
                return ['status' => 200];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionCreate()
    {
        $model = new CrmCustomerForm();
        $model->setScenario('insert');
        $data = Yii::$app->request->get();
        $next = Yii::$app->request->post('next');
        if($data)
        {
            $data = Yii::$app->request->get();
            $model->phone = $data['phone'];
            $model->qq = $data['qq'];
            $model->tel = $data['tel'];
            $model->email = $data['email'];
            $model->wechat = $data['wechat'];
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $crmCustomer = $model->save();
            if($crmCustomer)
            {
                Yii::$app->session->setFlash('success', '保存成功!');
                if($next == 'save-subject')
                {
                    return $this->redirect(['business-subject/create','id' => $crmCustomer->id]);
                }
                else if($next == 'save-opportunity')
                {
                    return $this->redirect(['opportunity/create','customer_id' => $crmCustomer->id]);
                }
                else if($next == 'save')
                {
                    return $this->redirect(['customer-detail/business-subject','id' => $crmCustomer->id]);
                }
            }
        }
        if ($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', $model->getFirstErrors());
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * 新建客户和商机信息
     * @param null|int $id
     * @return string|Response
     */
    public function actionCommonCreate($id = null)
    {
        $businessSubject = null;
        $customer = null;
        $opportunities = null;
        if($id)
        {
            $model = $this->findModel($id);
            $customer = $model;
            /** @var BusinessSubject $businessSubject */
            $businessSubject = BusinessSubject::find()->where(['customer_id' => $id])->all();
            $opportunities = CrmOpportunity::find()->where(['customer_id' => $id])->all();
        }
        else
        {
            $model = new CrmCustomerForm();
            $model->setScenario('common-insert');
            $data = Yii::$app->request->get();
            if($data)
            {
                $data = Yii::$app->request->get();
                $model->phone = $data['phone'];
                $model->qq = $data['qq'];
                $model->tel = $data['tel'];
                $model->email = $data['email'];
                $model->wechat = $data['wechat'];
            }
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            //一起创建客户和商机默认显示当前登录人所属公司
            $model->company_id = $administrator->company_id;
            $model->administrator_id = $administrator->id;

            $crmCustomer = null;
            if ($model->load(Yii::$app->request->post()) && $model->validate())
            {
                $crmCustomer = $model->commonSave();
                if($crmCustomer)
                {
                    $crmCustomer->refresh();
                    Yii::$app->session->setFlash('success', '保存成功!');
                    return $this->redirect(['crm-customer/common-create', 'id' => $crmCustomer->id]);
                }
            }
        }

        if ($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', $model->getFirstErrors());
        }
        return $this->render('common-create', ['model' => $model, 'businessSubject' => $businessSubject, 'customer' => $customer, 'opportunities' => $opportunities]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $next = Yii::$app->request->post('next');
        if(Yii::$app->request->post())
        {
            $customerForm = new CrmCustomerForm();
            $customerForm->setAttributes($model->attributes);
            if($customerForm->load(Yii::$app->request->post()))
            {
                $customerForm->setScenario('update');
                $customerForm->company_id = $model->company_id;
                $customerForm->administrator_id = $model->administrator_id;
                $customerForm->update($model);
                Yii::$app->session->setFlash('success', '保存成功!');
                if($next == 'save-subject')
                {
                    return $this->redirect(['business-subject/create','id' => $model->id]);
                }
                else if($next == 'save-opportunity')
                {
                    return $this->redirect(['opportunity/create','customer_id' => $model->id]);
                }
                else if($next == 'save')
                {
                    return $this->redirect(['customer-detail/business-subject','id' => $model->id]);
                }
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
            }
        }
        return $this->render('update',['model' => $model]);
    }

    public function actionCustomField()
    {
        $model = CustomerCustomField::find()->where(['administrator_id' => Yii::$app->user->id])->one();
        $data = Yii::$app->request->post();

        if($model->load($data))
        {
            $fields = $data['CustomerCustomField']['fields'];
            if(count($fields) != count($model->fields))
            {
                return ['status' => 400, 'message' => '自定义列表不存在'];
            }
            if($model->fieldSave($model))
            {
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionAjaxCustomFieldList()
    {
        $model = new CustomerCustomField();
        $fields = $model->checkField();
        $fields = $model->getFields($fields);
        return ['status' => 200, 'fields' => $this->serializeData($fields)];
    }

    public function actionValidation()
    {
        $model = new CrmCustomerForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            $model->setScenario('update');
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionConfirmReceive()
    {
        $model = new CustomerConfirmReceiveForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->confirm())
            {
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionConfirmRelease()
    {
        $model = new CustomerReleaseForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->confirm())
            {
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    /**
     * 更换负责人
     * @return array
     */
    public function actionChangeAdministrator()
    {
        $model = new CustomerChangeAdministratorForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->change())
            {
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionBatchCustomerShare()
    {
        $model = new CustomerBatchShareForm();
        if($model->load(Yii::$app->request->post()))
        {
            $data = Yii::$app->request->post()['CustomerBatchShareForm'];
            $oldCustomerCombine = CrmCustomerCombine::find()->where(['in', 'customer_id', $data['customer_ids']])->andWhere(['administrator_id' => $data['administrator_id']])->all();
            $oldCustomerIds = [];
            if(null != $oldCustomerCombine)
            {
                /** @var CrmCustomerCombine $customerCombine */
                foreach ($oldCustomerCombine as $customerCombine)
                {
                    $oldCustomerIds[] = $customerCombine->customer_id;
                }
            }
            $newCustomerIds = array_diff($data['customer_ids'], $oldCustomerIds);
            if($model->batchShare())
            {
                if(count($newCustomerIds) > 0)
                {
                    foreach ($newCustomerIds as $customerId)
                    {
                        $customer = CrmCustomer::findOne($customerId);
                        /** @var Administrator $administrator */
                        $administrator = Yii::$app->user->identity;
                        $type = MessageRemind::TYPE_COMMON;
                        $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
                        $receive_id = $data['administrator_id'];
                        $customer_id = $customerId;
                        $sign = 'd-'.$receive_id.'-'.$customerId.'-'.$type.'-'.$type_url;
                        $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                        if($customer && null == $messageRemind)
                        {
                            $message = '你有一个新分享客户“'. $customer->name .'”，请及时查看跟进！';
                            $popup_message = '您收到一个新客户，请及时查看哦！';
                            MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id,  0,  0, $administrator);
                        }
                    }
                }
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    /**
     * 导出全部客户
     * @return string|Response
     */
    public function actionExport()
    {
        $url = Yii::$app->request->getReferrer();
        $export_code = Yii::$app->cache->get('crm-customer-export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;
        $search = new CustomerExportSearch();
        $search->load(Yii::$app->request->queryParams);
        if(!$search->validate())
        {
            Yii::$app->session->setFlash('error', reset($search->getFirstErrors()));
            return $this->redirect($url);
        }
        $dataProvider = $search->search(true);
        $count = $dataProvider->totalCount;
        if(empty($count))
        {
            Yii::$app->session->setFlash('error', '没有获取到任何客户记录！');
            return $this->redirect($url);
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['客户创建时间','客户ID','客户名称','客户来源','标签','客户获取方式','负责人', '负责人客户级别','负责人所属公司',
            '负责人所属部门', '合作人/合作人客户级别/合作人所属公司/合作人所属部门','最后跟进人','最后跟进时间'];
        $csv->insertOne($header);

        for($i = 0; $i < $batch; $i++)
        {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $crmCustomer)
            {
                $lastRecord = $this->getLastRecordInfo($crmCustomer['id'],$crmCustomer['last_record_creator_name'],$crmCustomer['last_record']);
                $csv->insertOne([
                    "\t" . date('Y-m-d H:i:s',$crmCustomer['created_at']),
                    "\t" . $crmCustomer['id'],
                    "\t" . $this->trimStr($crmCustomer['name']),
                    "\t" . $this->trimStr($crmCustomer['source_name']),
                    "\t" . $this->trimStr($crmCustomer['tag_name']),
                    "\t" . $crmCustomer['get_way'] == 0 ? 'CRM录入' : '自动注册',
                    "\t" . $this->trimStr($crmCustomer['administrator_name']),
//                    "\t" . $this->getAdministratorLevelName($crmCustomer['id'],$crmCustomer['administrator_id']),
                    "\t" . $this->getLevel($crmCustomer['level']),
                    "\t" . $this->trimStr($crmCustomer['company_name']),
                    "\t" . $this->trimStr($crmCustomer['department_name']),
                    "\t" . $this->trimStr($this->getCrmCustomerCombines($crmCustomer['id'])),
                    "\t" . $this->trimStr($lastRecord['name']),
                    "\t" . $this->trimStr($lastRecord['time']),
                ]);
                unset($lastRecord);
                unset($crmCustomer);
            }
            unset($models);
        }
        //记录操作日志
        AdministratorLog::logExport('客户',$count);

        $filename = date('YmdHis').rand(10000,99999).'_客户记录.csv';
        Yii::$app->cache->set('crm-customer-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }

    public function getLevel($level){
        if(isset($level)){
            if($level == 0){
                $str = '无效客户';
            }else if ($level == 1 ){
                $str = '有效客户';
            }else{
                $str = '--';
            }
        }else{
            $str = '--';
        }
        return $str;
    }

    public function getCrmCustomerCombines($id)
    {
        //优化查询，减少内存使用
        $crmCustomerCombines = CrmCustomerCombine::find()->select(['a.name as administrator_name', 'co.name as company_name', 'd.name as department_name', 'c.level'])->alias('c')
            ->leftJoin(['a'=>Administrator::tableName()],'c.administrator_id = a.id')
            ->leftJoin(['d'=>CrmDepartment::tableName()],'c.department_id = d.id')
            ->leftJoin(['co' => Company::tableName()],'c.company_id = co.id')
            ->where(['c.customer_id' => $id])
            ->asArray()
            ->all();
        if($crmCustomerCombines){
            $str = '';
            $count = count($crmCustomerCombines);
            foreach($crmCustomerCombines as $key => $val){

                $administratorName = $val['administrator_name'] ? $val['administrator_name'] : '--';
                $companyName = $val['company_name'] ? $val['company_name'] : '--';
                $departmentName = $val['department_name'] ? $val['department_name'] : '--';
                $level_name = $val['level'] == 0 ? '无效客户' :($val['level'] ==1 ? '有效客户' : '--');
                if($key != $count -1)
                {
                    $str .= $administratorName.'/'.$level_name.'/'.$companyName.'/'.$departmentName."&";
                }
                else
                {
                    $str .= $administratorName.'/'.$level_name.'/'.$companyName.'/'.$departmentName;
                }
                unset($administrator);
                unset($administratorName);
                unset($company);
                unset($companyName);
                unset($department);
                unset($departmentName);
            }
            return $str;
        }
        return '';
    }

    //跟进记录
    public function getLastRecordInfo($id,$last_record_creator_name,$last_record)
    {
        /** @var  CrmCustomerLog $lastRecord */
        $lastRecord = CrmCustomerLog::find()->select(['creator_name', 'created_at'])->where(['customer_id' => $id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])->orderBy(['created_at'=>SORT_DESC])->one();
        $data = [];
        if(!empty($last_record_creator_name))
        {
            $data['name'] = $last_record_creator_name;
        }
        else
        {
            $data['name'] = $lastRecord ? $lastRecord->creator_name : '--';
        }

        if(!empty($last_record))
        {
            $data['time'] = date('Y-m-d H:i:s',$last_record);
        }
        else
        {
            $data['time'] = $lastRecord ? date('Y-m-d H:i:s',$lastRecord->created_at) : '--';
        }
        return $data;
    }


    public function actionImport()
    {
        $model = new CrmCustomerImportForm();
        $ok = "";
        $err = '';
        $total = 0;
        if (Yii::$app->request->isPost)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;//响应转成json
            $file = UploadedFile::getInstance($model, 'file');  //获取上传的文件实例
            $import = Yii::$app->request->post()['CrmCustomerImportForm'];
            if($file == null)
            {
                return ['status' => 400, 'message' => '请上传文件'];
            }
            $filename = Yii::$app->getRuntimePath().'/'.time().'.'.$file->extension;
            $file->saveAs($filename);//保存文件
            if ($filename) {
                /*exit;
                $format = $file->extension;*/
                if(in_array($file->extension,array('xls','xlsx','csv'))){
                    if($file->extension =='xlsx')
                    {
                        $objReader = new \PHPExcel_Reader_Excel2007();
                        $objPHPExcel = $objReader->load($filename);
                    }
                    else if ($file->extension =='xls')
                    {
                        $objReader = new \PHPExcel_Reader_Excel5();
                        $objPHPExcel = $objReader->load($filename);
                    }
                    else if ($file->extension=='csv')
                    {
                        $PHPReader = new \PHPExcel_Reader_CSV();
                        $PHPReader->setInputEncoding('GBK');//默认输入字符集
                        $PHPReader->setDelimiter(',');//默认的分隔符
                        $objPHPExcel = $PHPReader->load($filename);//载入文件
                    }
                    else
                    {
                        die('文件格式不对!');
                    }
                    $objWorksheet = $objPHPExcel->getSheet(0);//载入文件并获取第一个sheet
                    $highestRow = $objWorksheet->getHighestRow();//总行数
                    $highestColumn = $objWorksheet->getHighestColumn();//总列数
//                    $columnNum = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数转换成数字
                    //检查是否和模版列数不同
                    if($highestColumn != 'K' )
                    {
                        if(file_exists($filename))
                        {
                            unlink($filename);
                        }
                        return ['status' => 400, 'message' => '不能低于标准模版的列数，请检查'];
                    }
                    $colArr = ['C','D','E','F','G'];
                    //手机号、座机、微信、邮箱、QQ的关系为五者必填其一；都不能和现有数据重复,并且导入数据中也不能相同
                    for($col = 'A'; $col <= $highestColumn; $col++){
                        if(in_array($col, $colArr))
                        {
                            $repeatArr = [];
                            for($row = 2; $row <= $highestRow; $row++){
                                //注意：手机号、座机、微信、邮箱、QQ的数据在模版中的位置不可随便调换
                                if(in_array($col, $colArr))
                                {
                                    $repeatArr[] = trim($objWorksheet->getCell($col.$row)->getValue());//列字母不转换为数字时的写法
                                }
                            }
                            $count = count($repeatArr);
                            //当为空时校验不完整
//                            if ($count > 0 && $count != count(array_unique($repeatArr))) {
//                                unset($repeatArr);
//                                if(file_exists($filename))
//                                {
//                                    unlink($filename);
//                                }
//                                return ['status' => 400, 'message' => $col.'列的数据有重复,请检查'];
//                            }

                            $flag = false;//假设不重复
                            $repeatValue = '';
                            for($i = 0; $i < $count; $i++){ //循环开始元素
                                for($j = $i + 1; $j < $count; $j++){ //循环后续所有元素
                                    //如果相等，则重复
                                    if(!empty($repeatArr[$i]) && $repeatArr[$i] == $repeatArr[$j]){
                                        $flag = true;//设置标志变量为重复
                                        $repeatValue = $repeatArr[$i];
                                        break;//结束循环
                                    }
                                }
                            }
                            unset($repeatArr);
                            if($flag)
                            {
                                if(file_exists($filename))
                                {
                                    unlink($filename);
                                }
                                return ['status' => 400, 'message' => $col.'列的数据有重复,重复值为:'. $repeatValue .',请检查'];
                            }
                        }
                        //判断第一列的客户姓名不能为空，模版不能轻易更改对应关系
                        if($col == 'A')
                        {
                            $colA = [];
                            for($row = 2; $row <= $highestRow; $row++){
                                //注意：模版不能轻易更改对应关系
                                $data = trim($objWorksheet->getCell($col.$row)->getValue());//列字母不转换为数字时的写法
                                if(!empty($data))
                                {
                                    $colA[] = $data;
                                }
                            }
                            $countA = count($colA);
                            if($countA == 0)
                            {
                                unset($colA);
                                if(file_exists($filename))
                                {
                                    unlink($filename);
                                }
                                return ['status' => 400, 'message' => '客户姓名不能为空，请检查！'];
                            }
                            else
                            {
                                if ($countA != $highestRow-1) {
                                    unset($colA);
                                    if(file_exists($filename))
                                    {
                                        unlink($filename);
                                    }
                                    return ['status' => 400, 'message' => '客户姓名必须全部填写,请检查！'];
                                }
                            }
                        }

//                        if($col == 'C')
//                        {
//                            $colC = [];
//                            for($row = 2; $row <= $highestRow; $row++){
//                                //注意：模版不能轻易更改对应关系
//                                $data = trim($objWorksheet->getCell($col.$row)->getValue());//列字母不转换为数字时的写法
//                                if($data != '')
//                                {
//                                    $colC[] = $data;
//                                }
//                            }
//                            $countC = count($colC);
//                            if($countC == 0)
//                            {
//                                unset($colC);
//                                if(file_exists($filename))
//                                {
//                                    unlink($filename);
//                                }
//                                return ['status' => 400, 'message' => '客户来源不能为空，请检查！'];
//                            }
//                            else
//                            {
//                                if ($countC != $highestRow-1) {
//                                    unset($colA);
//                                    if(file_exists($filename))
//                                    {
//                                        unlink($filename);
//                                    }
//                                    return ['status' => 400, 'message' => '客户来源必须全部填写,请检查！'];
//                                }
//                            }
//                        }
                    }

                    if($highestRow > 1){
                        $transaction=Yii::$app->db->beginTransaction();
                        try {
                            for($row = 2; $row <= $highestRow; $row++){
                                $data = [];
                                for($col = 'A'; $col <= $highestColumn; $col++){
                                    $data[] = trim($objWorksheet->getCell($col.$row)->getValue());//列字母不转换为数字时的写法
                                }
                                $customer = new CrmCustomerImportForm();
                                $customer->name = $data[0];
                                $customer->gender = $data[1] ? $data[1] : 0;
                                $customer->phone = $data[2];
                                $customer->tel = $data[3];
                                $customer->wechat = $data[4];
                                $customer->email = $data[5];
                                $customer->qq = $data[6];
                                $customer->birthday = str_replace('/', '-', $data[7]);
                                $customer->caller = $data[8];
                                $customer->street = $data[9];
                                $customer->remark = $data[10];
                                $customer->administrator_id = $import['administrator_id'] > 0 ? $import['administrator_id'] : '';
                                $customer->level = $import['level'];
                                $customer->source = $import['source'];
                                //判断是否达到上限
//                                if (!$this->checkmaxuser((new OrgSearch())->getOrg())){
//                                    throw new Exception('达到上限了!');
//                                };
                                if(!$customer->validate())
                                {
                                    if(file_exists($filename))
                                    {
                                        unlink($filename);
                                    }
                                    throw new Exception('第'.$row.'行出现错误:'.reset($customer->getFirstErrors()));
                                }
                                //判断用户是否已存在
                                $existUser = $customer->getCrmCustomer();
                                if ($existUser) {
                                    if(file_exists($filename))
                                    {
                                        unlink($filename);
                                    }
                                    throw new Exception('第'.$row.'行用户已经存在');
                                }

                                //crm_customer表添加客户
                                if(!$customer->saveCustomer()){
                                    if(file_exists($filename))
                                    {
                                        unlink($filename);
                                    }
                                    throw new Exception('第'.$row.'行客户导入失败');
                                };
                            }
                            $transaction->commit();
                            $ok = 1;
                            $total = $highestRow - 1;
                        } catch (Exception $e) {
                            $transaction->rollBack();
                            $err = $e->getMessage();
                        }
                    }

                    if(file_exists($filename))
                    {
                        unlink($filename);
                    }
                    if($ok == 1)
                    {
                        return ['status' => 200, 'message' => '客户导入成功', 'total' => $total];
                    }
                    else
                    {
                        return ['status' => 400, 'message' => $err];
                    }
                }
            }
            else
            {
                return ['status' => 400, 'message' => '请上传文件'];
            }
        }
        return $this->render('import', ['model' => $model]);
    }

    public function actionDownload()
    {
        $filename = 'upload/客户批量导入模板.csv';
        header('Content-Type:application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Content-Length:'.filesize($filename));
        /**  将文件内容读取出来并直接输出，以便下载 */
        readfile($filename);
    }

    //获取客户相应的负责人/合伙人
    public function actionAjaxAdministratorList($range = null, $keyword = null, $member_type = null)
    {
        /** @var Cache $cache */
        $cache = Yii::$app->get('redisCache');
        if($member_type == 'customer-combine')
        {
            if(empty($keyword))
            {
                $data = $cache->get('customer-combine-administrator-id'. $range);
                if(null != $data)
                {
                    return ['status' => 200, 'items' => $this->serializeData($data)];
                }
            }
        }
        else
        {
            if(empty($keyword))
            {
                $data = $cache->get('customer-administrator-id'. $range);
                if(null != $data)
                {
                    return ['status' => 200, 'items' => $this->serializeData($data)];
                }
            }
        }

        /** @var Query $query */
        $ids = CrmCustomer::customer($range);
        $administratorIds = array_unique(ArrayHelper::getColumn($ids, 'administrator_id'));
        if($member_type == 'customer-combine')
        {
            $customerIds = array_unique(ArrayHelper::getColumn($ids, 'id'));

//            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE
            $CustomerCombineQuery = CrmCustomerCombine::find();
            if($range == 'effective')
            {
                $CustomerCombineQuery->andWhere(['level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE]);
            }
            $crmCustomerCombine = $CustomerCombineQuery->andWhere(['in', 'customer_id', $customerIds])->asArray()->all();
            $administratorIds = array_unique(ArrayHelper::getColumn($crmCustomerCombine, 'administrator_id'));
        }
        $query = Administrator::find()->select(['a.id', 'a.name'])->alias('a')
//            ->andWhere(['a.status' => Administrator::STATUS_ACTIVE])
            ->andWhere(['in', 'a.id', $administratorIds]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();

        if($member_type == 'customer-combine')
        {
            if(empty($keyword))
            {
                $cache->set('customer-combine-administrator-id'. $range, $data, 60);
            }
        }
        else
        {
            if(empty($keyword))
            {
                $cache->set('customer-administrator-id'. $range, $data, 60);
            }
        }

        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    //获取客户相应的公司
    public function actionAjaxCompanyList($range = null, $keyword = null)
    {
        /** @var Query $query */
        $ids = CrmCustomer::customer($range);
        $ids = array_unique(ArrayHelper::getColumn($ids, 'company_id'));
        $query = Company::find()->select(['id', 'name'])
            ->andWhere(['in', 'id', $ids]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'company' => $this->serializeData($data)];
    }

    //获取客户相应的部门
    public function actionAjaxDepartmentList($company_id = null, $keyword = null, $range = null)
    {
        /** @var Query $query */
        $ids = CrmCustomer::customer($range, $company_id);
        $ids = array_unique(ArrayHelper::getColumn($ids, 'department_id'));
        $query = CrmDepartment::find()->select(['id', 'name', 'company_id'])
            ->andWhere(['in', 'id', $ids]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    public function actionAjaxOpportunity($customer_id = null, $status = null)
    {
        $model = null;
        if($customer_id)
        {
            /** @var CrmOpportunity $model */
            $model = $this->findOpportunityModel($customer_id, $status);
        }
        if($model)
        {
            return ['status' => 200, 'opportunity' => $this->serializeData($model->getModels())];
        }
        return ['status' => 400, 'message' => '操作有误！'];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    private function findModel($id)
    {
        $model = CrmCustomer::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的客户!');
        }
        return $model;
    }

    private function findOpportunityModel($id, $status)
    {
        if($status == 'deal')
        {
            //已成交商机
            $query = CrmOpportunity::find()->where(['customer_id' => $id, 'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_DEAL]);
        }
        elseif ($status == 'fail')
        {
            //已作废商机
            $query = CrmOpportunity::find()->where([ 'customer_id' => $id, 'status' => CrmOpportunity::STATUS_FAIL,'is_receive' => CrmOpportunity::RECEIVE_ACTIVE]);
        }
        else if ($status == 'not_deal')
        {
            //跟进中商机（不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            $query = CrmOpportunity::find()->where(['opportunity_public_id' => 0, 'customer_id' => $id, 'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_NOT_DEAL]);
        }
        else if ($status == 'apply')
        {
            //申请中的商机
            $query = CrmOpportunity::find()->where(['opportunity_public_id' => 0, 'customer_id' => $id, 'is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'status' => CrmOpportunity::STATUS_APPLY]);
        }
        else if ($status == 'no_receive')
        {
            //待确认
            $query = CrmOpportunity::find()->where(['opportunity_public_id' => 0, 'customer_id' => $id, 'is_receive' => CrmOpportunity::RECEIVE_DISABLED, 'status' => CrmOpportunity::STATUS_NOT_DEAL]);
        }
        else if ($status == 'no_extract')
        {
            //待提取
            $query = CrmOpportunity::find()->where(['customer_id' => $id])->andWhere(['>', 'opportunity_public_id', 0]);;
        }

        $opportunityDataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (null == $opportunityDataProvider) {
            throw new NotFoundHttpException('找不到指定的商机!');
        }
        return $opportunityDataProvider;
    }

    //删除特殊符号
    private function trimStr($str)
    {
        //注意有个特殊空格符号" "
        $needReplace = [" ","　"," ","\t","\n","\r"];
        $result = ["","","","",""];
        return str_replace($needReplace,$result,$str);
    }

    public function actionCallIn()
    {
        $id=Yii::$app->request->post('customer_id');
        $customer = $this->findModel($id);
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $callCenter = $admin->callCenterAssignCompany ? $admin->callCenterAssignCompany->callCenter ? $admin->callCenterAssignCompany->callCenter : null :null;
        if(null == $callCenter || empty($customer->phone) || empty($admin->call_center))
        {
            return ['status' => 400, 'message' => '呼叫失败！'];
        }

        if($callCenter)
        {
            if(!$callCenter->isOnline())
            {
                return ['status' => 400, 'message' => '呼叫失败，api呼叫已禁用！'];
            }
            $client = new Client();
            $url = $callCenter->url.'&phonenum='.$customer->phone.'&integratedid='.trim($admin->call_center);
            $response = $client->get($url)->send();
            if($response->getIsOk())
            {
                $jsonString = $response->getContent();
                if($jsonString == 200)
                {
                    return ['status' => 200, 'message' => '呼叫成功！'];
                }
                else
                {
                    //失败时返回json数据
                    //$jsonDecodeString = json_decode($jsonString);
                    return ['status' => 400, 'message' => '呼叫失败！'];
                }
            }
        }
        return ['status' => 403, 'message' => '呼叫失败！'];
    }
}