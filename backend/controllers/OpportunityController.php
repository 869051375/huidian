<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/11
 * Time: 下午5:10
 */

namespace backend\controllers;


use backend\models\OpportunityApplyDealForm;
use backend\models\OpportunityBatchChangeAdministratorForm;
use backend\models\OpportunityChangeAdministratorForm;
use backend\models\OpportunityConfirmReceiveForm;
use backend\models\OpportunityContractDealForm;
use backend\models\OpportunityExportSearch;
use backend\models\OpportunityForm;
use backend\models\OpportunityProductForm;
use backend\models\OpportunityProtectForm;
use backend\models\OpportunitySearch;
use backend\models\OpportunityVoidForm;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Company;
use common\models\Contract;
use common\models\CrmCustomer;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\CrmOpportunityProduct;
use common\models\CrmOpportunityRecord;
use common\models\Niche;
use common\models\Source;
use common\models\OpportunityCustomField;
use common\models\OpportunityTag;
use common\models\Tag;
use common\models\VirtualOrder;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\redis\Cache;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OpportunityController extends BaseController
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
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['confirm-receive', 'change-administrator', 'batch-change-administrator',
                    'void', 'get-deal-orders', 'apply-deal', 'protect', 'ajax-administrator-list','contract-deal','get-contract',
                    'ajax-company-list', 'ajax-department-list', 'ajax-detail', 'forced-deal','ajax-create','custom-field','ajax-custom-field-list','validation','is-main-part'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'list', 'confirm-receive', 'view',
                            'change-administrator', 'batch-change-administrator', 'void','contract-deal',
                            'get-deal-orders', 'apply-deal', 'protect', 'ajax-create','validation','get-contract'],
                        'allow' => true,
                        'roles' => ['opportunity/*'],
                    ],
                    [
                        'actions' => ['all', 'view'],
                        'allow' => true,
                        'roles' => ['opportunity/all'],
                    ],
                    [
                        'actions' => ['ajax-administrator-list', 'ajax-company-list', 'ajax-department-list', 'ajax-detail', 'custom-field','ajax-custom-field-list','is-main-part'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['opportunity/export'],
                    ],
                    [
                        'actions' => ['forced-deal'],
                        'allow' => true,
                        'roles' => ['opportunity/forced-deal'],
                    ],

                ],
            ],
        ];
    }

    public function actionList($id = null)
    {
        $search = new OpportunitySearch();
        $search->page_size = isset(Yii::$app->request->get()['per-page']) ? Yii::$app->request->get()['per-page'] : 20;
        $search->load(Yii::$app->request->queryParams);
        $search->search();
        $search->searchCount();
        $opportunityField = OpportunityCustomField::find()->select('fields')->where(['administrator_id' => Yii::$app->user->id])->one();
        $customField = null;
        if($opportunityField)
        {
            $customField = json_decode($opportunityField->fields);
        }
        /** @var CrmOpportunity $opportunity */
        $opportunity = null;
        $records = null;
        if($id != null)
        {
            $opportunity = $this->findModel($id);
            /** @var CrmOpportunityRecord[] $records */
            $records = CrmOpportunityRecord::find()->where(['opportunity_id' => $id])->orderBy(['created_at' => SORT_DESC])->all();
        }
        // 如果是ajax请求，则是请求待办列表，
        // 放到这个控制器下主要是为了兼容老旧浏览器不支持pjax
//        if(Yii::$app->request->isAjax)
//        {
//            return $this->renderAjax('upcoming', [
//                'search' => $search,
//                'records' => $records,
//                'opportunity' => $opportunity,
//            ]);
//        }
        return $this->render('list', ['search' => $search,  'records' => $records, 'opportunity' => $opportunity, 'customField' => $customField]);
    }

    public function actionAll($id = null)
    {
        $search = new OpportunitySearch();
        $search->page_size = isset(Yii::$app->request->get()['per-page']) ? Yii::$app->request->get()['per-page'] : 20;
        $search->load(Yii::$app->request->queryParams);
        $search->range = 'all';
        $search->search();
        $search->searchCount();
        $opportunityField = OpportunityCustomField::find()->select('fields')->where(['administrator_id' => Yii::$app->user->id])->one();
        $customField = null;
        if($opportunityField)
        {
            $customField = json_decode($opportunityField->fields);
        }
        /** @var CrmOpportunity $opportunity */
        $opportunity = null;
        $records = null;
        if($id != null)
        {
            $opportunity = $this->findModel($id);
            /** @var CrmOpportunityRecord[] $records */
            $records = CrmOpportunityRecord::find()->where(['opportunity_id' => $id])->orderBy(['created_at' => SORT_DESC])->all();
        }
        return $this->render('list', ['search' => $search, 'records' => $records,  'opportunity' => $opportunity,'customField' => $customField]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        /** @var CrmOpportunityRecord[] $records */
        $records = CrmOpportunityRecord::find()->where(['opportunity_id' => $id])->orderBy(['created_at' => SORT_DESC])->all();
        return $this->render('view', [
            'model' => $model,
            'records' => $records,
        ]);
    }

    public function actionCreate($customer_id)
    {

        $customer = CrmCustomer::findOne($customer_id);

        if(null == $customer)
        {
            throw new NotFoundHttpException('找不到客户');
        }

        /** @var \common\models\Administrator $administrator */
        $administrator = Yii::$app->user->identity;

        if(!(Yii::$app->user->can('opportunity/create-all-customer') || $customer->isSubFor($administrator) || $customer->isCombine($administrator) || $customer->isPrincipal($administrator)))
        {
            throw new NotFoundHttpException('没有新建全部客户商机权限。');
        }

        $model = new OpportunityForm();
        $model->customer_name = $customer->name;
        $model->customer_id = $customer->id;

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        if(\Yii::$app->request->isPost)
        {
            $items = [];
            foreach((array)\Yii::$app->request->post('OpportunityProductForm') as $p)
            {
                $form = new OpportunityProductForm();
                $form->load($p, '');
                $items[] = $form;
            }
            if($model->load(\Yii::$app->request->post()))
            {
                $model->products = $items;
                $model->administrator = $administrator;
                $t = Yii::$app->db->beginTransaction();
                $opportunity = null;
                try
                {
                    $opportunity = $model->save();
                    $t->commit();
                }
                catch (\Exception $e)
                {
                    $t->rollBack();
                    throw $e;
                }

                if($opportunity)
                {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    Yii::$app->session->setFlash('success', '商机保存成功!');
                    if($administrator->id != $opportunity->administrator_id)
                    {
                        return [
                            'status' => 200,
                            'url' => '/opportunity/view?id='.$opportunity->id,
                            'message' => '您当前创建的商机属于跨产品线，此商机已经被分配至'.$opportunity->department->name.'部门！您可在“由我分享的商机”中查看！',
                        ];
                    }
                    else
                    {
                        return ['status' => 200, 'url' => '/opportunity/view?id='.$opportunity->id];
                    }
                }
                else{
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    $error = $model->getFirstErrors();
                     return [
                         'status' => 400,
                         'message' => reset($error),
                    ];
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $opportunityForm = new OpportunityForm();
        $opportunityForm->setUpdateModel($model);

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        if(\Yii::$app->request->isPost)
        {
            $items = [];
            foreach((array)\Yii::$app->request->post('OpportunityProductForm') as $p)
            {
                $form = new OpportunityProductForm();
                $form->load($p, '');
                $items[] = $form;
            }
            if($opportunityForm->load(\Yii::$app->request->post()))
            {
                $opportunityForm->products = $items;
                $opportunityForm->administrator = $administrator;
                $t = Yii::$app->db->beginTransaction();
                $opportunity = null;
                try
                {
                    $opportunity = $opportunityForm->update();
                    $t->commit();
                }
                catch (\Exception $e)
                {
                    $t->rollBack();
                    throw $e;
                }
                if($opportunity)
                {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    Yii::$app->session->setFlash('success', '商机保存成功!');
                    if($administrator->id != $opportunity->administrator_id)
                    {
                        return [
                            'status' => 200,
                            'url' => '/opportunity/view?id='.$opportunity->id,
                            'message' => '您当前创建的商机属于跨产品线，此商机已经被分配至'.$opportunity->department->name.'部门！您可在“由我分享的商机”中查看！',
                        ];
                    }
                    else
                    {
                        return ['status' => 200, 'url' => '/opportunity/view?id='.$opportunity->id];
                    }
                }else{
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    $error = $model->getFirstErrors();

                    return [
                        'status' => 400,
                        'message' => reset($error),
                    ];
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 400, 'message' => '商机保存失败:'.reset($opportunityForm->getFirstErrors())];
        }

        return $this->render('create', [
            'model' => $opportunityForm,
        ]);
    }

    public function actionCustomField()
    {
        /** @var OpportunityCustomField $model */
        $model = OpportunityCustomField::find()->where(['administrator_id' => Yii::$app->user->id])->one();
        $data = Yii::$app->request->post();

        if($model->load($data))
        {
            $fields = $data['OpportunityCustomField']['fields'];
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
        $model = new OpportunityCustomField();
        $fields = $model->checkField();
        $fields = $model->getFields($fields);
        return ['status' => 200, 'fields' => $this->serializeData($fields)];
    }

    public function actionAjaxCreate($customer_id)
    {
        $customer = CrmCustomer::findOne($customer_id);
        if(null == $customer)
        {
            throw new NotFoundHttpException('找不到客户');
        }

        $model = new OpportunityForm();
        $model->setScenario('insert');//todo
        /** @var \common\models\Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $model->customer_name = $customer->name;
        $model->customer_id = $customer->id;
        $model->customer = $customer;

        $administrator = \Yii::$app->user->identity;
        if(\Yii::$app->request->isPost)
        {
            $items = [];
//            $administrator_id = \Yii::$app->request->post()['OpportunityForm']['administrator_id'];
            /** @var Administrator $administrator */
//            $administrator = Administrator::findOne((int)$administrator_id);
            foreach((array)\Yii::$app->request->post('OpportunityProductForm') as $p)
            {
                $form = new OpportunityProductForm();
//                $form->administrator = $administrator;
                $form->load($p, '');
                $items[] = $form;
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = \Yii::$app->request->post();
            if($model->load($post))
            {
                $model->products = $items;
                //分配商机商机负责人
                $model->administrator = $administrator;
                $t = Yii::$app->db->beginTransaction();
                $opportunity = null;
                try
                {
                    $model->administrator->company_id =$post['OpportunityForm']['company_id'];
//                    $model->administrator->id =$post['OpportunityForm']['administrator_id'];
                    $opportunity = $model->save();
                    $t->commit();
                }
                catch (\Exception $e)
                {
                    $t->rollBack();
                    throw $e;
                }
                Yii::$app->response->format = Response::FORMAT_JSON;
                if($opportunity)
                {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    Yii::$app->session->setFlash('success', '商机保存成功!');
                    if($administrator->id != $opportunity->administrator_id)
                    {
                        return [
                            'status' => 200,
                            'message' => '您当前创建的商机属于跨产品线，此商机已经被分配至'.$opportunity->department->name.'部门！您可在“由我分享的商机”中查看！',
                        ];
                    }
                    else
                    {
                        return ['status' => 200];
                    }
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            $error =$model->getFirstErrors();
            return ['status' => 400, 'message' => '商机保存失败:'.reset($error)];
        }
    }


    public function actionValidation()
    {
        $model = new OpportunityForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            $model->setScenario('insert');
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionConfirmReceive()
    {
        $model = new OpportunityConfirmReceiveForm();
        if($model->load(Yii::$app->request->post()))
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $success = $model->confirm();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }

            if($success)
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

    public function actionChangeAdministrator()
    {
        $model = new OpportunityChangeAdministratorForm();
        if($model->load(Yii::$app->request->post()))
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $success = $model->change();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($success)
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

    public function actionBatchChangeAdministrator()
    {
        $model = new OpportunityBatchChangeAdministratorForm();
        if($model->load(Yii::$app->request->post()))
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $success = $model->change();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($success)
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

    public function actionVoid($is_validate = '0')
    {
        $model = new OpportunityVoidForm();

        if($model->load(\Yii::$app->request->post()))
        {
            if($is_validate)
            {
                return ActiveForm::validate($model);
            }
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $record = $model->save();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($record)
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => '操作失败:'.reset($model->getFirstErrors())];
    }

    public function actionContractDeal($is_validate = '0')
    {
        $model = new OpportunityContractDealForm();
        if($model->load(\Yii::$app->request->post()))
        {
            if($is_validate)
            {
                return ActiveForm::validate($model);
            }
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $record = $model->save();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($record)
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => '操作失败:'.reset($model->getFirstErrors())];
    }

    public function actionApplyDeal($is_validate = '0')
    {
        $model = new OpportunityApplyDealForm();
        $model->setScenario('apply-deal');
        if($model->load(\Yii::$app->request->post()))
        {
            if($is_validate)
            {
                return ActiveForm::validate($model);
            }
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $record = $model->save();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($record)
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => '操作失败:'.reset($model->getFirstErrors())];
    }

    public function actionForcedDeal()
    {
        $model = new OpportunityApplyDealForm();
        $model->setScenario('forced-deal');
        if($model->load(\Yii::$app->request->post()))
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $record = $model->forcedSave();
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            if($record)
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => '操作失败:'.reset($model->getFirstErrors())];
    }

    public function actionProtect()
    {
        $model = new OpportunityProtectForm();
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

    public function actionGetDealOrders($id)
    {
        $opportunity = $this->findModel($id);
        $productIds = [];
        foreach($opportunity->opportunityProducts as $opportunityProduct)
        {
            $productIds[] = $opportunityProduct->product_id;
        }
        $query = VirtualOrder::find()->alias('vo');
        $query->joinWith('orders o');
        $query->andWhere(['in', 'o.product_id', $productIds]);
        $query->andWhere(['in', 'vo.status', [VirtualOrder::STATUS_PENDING_PAYMENT, VirtualOrder::STATUS_UNPAID, VirtualOrder::STATUS_ALREADY_PAYMENT]]);
        $query->andWhere(['vo.user_id' => $opportunity->customer->user_id]);
        $query->andWhere(['>', 'vo.created_at', $opportunity->created_at]);
        $query->andWhere(['o.salesman_aid' => Yii::$app->user->id]);
        /** @var VirtualOrder[] $vos */
        $vos = $query->all();
        $data = [];
        foreach($vos as $vo)
        {
            $item = [
                'id' => $vo->id,
                'sn' => $vo->sn,
                'total_amount' => $vo->total_amount,
                'status' => $vo->status,
                'created_at' => Yii::$app->formatter->asDatetime($vo->created_at),
                'items' => []
            ];
            foreach($vo->orders as $order)
            {
                $item['items'][] = [
                    'id' => $order->id,
                    'sn' => $order->sn,
                    'product_name' => $order->product_name,
                    'product_id' => $order->province_id,
                    'area' => $order->getArea(),
                    'price' => $order->price,
                    'status' => $order->status,
                    'status_name' => $order->getStatusName(),
                ];
            }
            $data[] = $item;
        }
        return ['status' => 200, 'data' => $data];
    }

    public function actionGetContract($id)
    {
        $opportunity = $this->findModel($id);
        $product_ids = [];
        foreach ($opportunity->opportunityProducts as $item)
        {
            $product_ids[] = $item->product_id;
        }
        /** @var Contract[] $models */
        $models = Contract::find()->select('id,virtual_order_id,name,contract_no')
            ->where(['customer_id' => $opportunity->customer->id])
            ->andWhere(['or'
                ,['status' => Contract::STATUS_CONTRACT,'sign_status' => Contract::SIGN_PENDING_REVIEW]
                ,['status' => Contract::STATUS_CONTRACT,'sign_status' => Contract::SIGN_ALREADY_REVIEW]
                ,['status' => Contract::STATUS_CONTRACT,'sign_status' => Contract::SIGN_NO_REVIEW]
                ,['status' => Contract::STATUS_MODIFY,'correct_status' => Contract::MODIFY_PENDING_REVIEW]
                ,['status' => Contract::STATUS_MODIFY,'correct_status' => Contract::MODIFY_ALREADY_REVIEW]
                ,['status' => Contract::STATUS_MODIFY,'correct_status' => Contract::MODIFY_NO_REVIEW]
            ])
            ->andWhere(['>','created_at',$opportunity->created_at])->all();
        $data = [];
        if($models)
        {
            foreach($models as $model)
            {
                foreach ($model->virtualOrder->orders as $order)
                {
                    if(in_array($order->product_id,$product_ids))
                    {
                        $data = [
                            'id' => $model->id,
                            'name' => $model->name,
                            'contract_no' => $model->contract_no,
                        ];
                    }
                }
            }
        }
        return ['status' => 200, 'data' => $data];
    }

    /**
     * 跟进人列表
     * @param null $range
     * @param null $keyword
     * @return array
     */
    public function actionAjaxAdministratorList($range = null, $keyword = null)
    {
        /** @var Cache $cache */
        $cache = Yii::$app->get('redisCache');
        $data = $cache->get('opportunity-administrator-id'. $range);
        if(empty($keyword))
        {
            if(null != $data)
            {
                return ['status' => 200, 'items' => $this->serializeData($data)];
            }
        }
        $ids = $this->opportunities($range);
        $ids = array_unique(ArrayHelper::getColumn($ids, 'administrator_id'));
        $query = Administrator::find()->select(['a.id', 'a.name'])->alias('a')
            ->andWhere(['a.type' => Administrator::TYPE_SALESMAN])
            ->andWhere(['in', 'a.id', $ids]);

        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();

        if(empty($keyword))
        {
            $cache->set('opportunity-administrator-id'. $range, $data, 60);
        }
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    //获取商机相应的公司
    public function actionAjaxCompanyList($keyword = null, $range = null)
    {
        /** @var Cache $cache */
//        $cache = Yii::$app->get('redisCache');
//        $data = $cache->get('opportunity-company-id'. $range);
//        if(empty($keyword))
//        {
//            if(null != $data)
//            {
//                return ['status' => 200, 'items' => $this->serializeData($data)];
//            }
//        }
        $ids = $this->opportunities($range);
        $ids = array_unique(ArrayHelper::getColumn($ids, 'company_id'));
        $query = Company::find()->select(['c.id', 'c.name'])->alias('c')->andWhere(['in', 'c.id', $ids]);

        if(!empty($keyword))
        {
            $query->andWhere(['like', 'c.name', $keyword]);
        }
        $data = $query->all();

//        if(empty($keyword))
//        {
//            $cache->set('opportunity-company-id' . $range, $data, 60);
//        }
        return ['status' => 200, 'company' => $this->serializeData($data)];
    }

    //获取商机相应的部门
    public function actionAjaxDepartmentList($company_id = null, $keyword = null, $range = null)
    {
        if(empty($company_id))
        {
            return ['status' => 200, 'items' => $this->serializeData([])];
        }
        $ids = $this->opportunities($range, $company_id);
        $ids = array_unique(ArrayHelper::getColumn($ids, 'department_id'));
        $query = CrmDepartment::find()->select(['cd.id', 'cd.name', 'cd.parent_id', 'cd.level', 'cd.company_id', 'cd.status'])->alias('cd')
            ->where(['cd.status' => CrmDepartment::STATUS_ACTIVE])
            ->andWhere(['in', 'cd.id', $ids])
            ->orderBy(['cd.path' => SORT_ASC]);
        if(!empty($company_id))
        {
            $query->andWhere(['cd.company_id' => $company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'cd.name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    private function opportunities($range, $company_id = 0)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query = CrmOpportunity::find()->alias('o')->andWhere(['=', 'o.opportunity_public_id', 0]);
        if(!empty($this->customer_id))
        {
            $query->andWhere(['customer_id' => $this->customer_id]);
        }
        // sub_need_confirm
        if($range == 'sub_need_confirm')
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($range == 'following')
        {
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.is_receive' => 1]);
            $query->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]]);
        }
        else if($range == 'deal')
        {
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
        }
        else if($range == 'sub')
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($range == 'fail')
        {
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
        }
        else if($range == 'shared')
        {
            $query->andWhere(['o.send_administrator_id' => $administrator->id]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
        }
        else if($range == 'all')
        {
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
                $query->andWhere(['o.company_id' => $administrator->company_id]);
            }
            if(!empty($company_id))
            {
                $query->andWhere(['o.company_id' => $company_id]);
            }
        }
        else
        {
            $query->andWhere(['o.is_receive' => 0, 'o.administrator_id' => $administrator->id]);
        }
        $query->orderBy([new Expression('ISNULL(o.next_follow_time) ASC'), 'o.next_follow_time' => SORT_ASC, 'o.created_at' => SORT_DESC]);
        $query = $query->asArray()->all();
        return $query;

    }

    public function actionExport()
    {
        $url = Yii::$app->request->getReferrer();//獲取urL
        $export_code = Yii::$app->cache->get('opportunity-export-' . Yii::$app->user->id);
        if($export_code)
        {//重定向
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;
        $search = new OpportunityExportSearch();//model對象
        $search->load(Yii::$app->request->queryParams);//查詢條件載入
        if(!$search->validate())
        {
            Yii::$app->session->setFlash('error', reset($search->getFirstErrors()));
            return $this->redirect($url);
        }

        $query = new Query();

        $dataProvider = $search->excelSearch($query);

        $query ->select('any_value(o.id) as id,any_value(o.created_at) as created_at,any_value(o.name) as name,any_value(o.status) as status,any_value(o.progress) as progress,any_value(o.administrator_name) as administrator_name,any_value(o.creator_name) as creator_name,any_value(t.name) as tag_name,any_value(o.customer_id) as customer_id,any_value(o.customer_name) as customer_name,any_value(o.last_record) as last_record,any_value(o.next_follow_time) as next_follow_time,any_value(o.invalid_reason) as invalid_reason ,any_value(co.name) as company_name,any_value(d.name) as department_name,any_value(cs.name) as source_name');
        $query -> from(['o' => CrmOpportunity::tableName()])
            ->leftJoin(['ot'=>OpportunityTag::tableName()],'o.id = ot.opportunity_id')
            ->leftJoin(['t' => Tag::tableName()],'ot.tag_id  = t.id')
            ->leftJoin(['co' => Company::tableName()],'o.company_id = co.id')
            ->leftJoin(['d' => CrmDepartment::tableName()],'o.department_id = d.id')
            ->leftJoin(['c'=>CrmCustomer::tableName()],'o.customer_id = c.id')
            ->leftJoin(['cs' => Source::tableName()],'c.source = cs.id');

        $count = $dataProvider ->totalCount;

        if(empty($count))
        {
            Yii::$app->session->setFlash('error', '没有获取到任何商机记录！');
            return $this->redirect($url);
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['商机ID','商机创建时间','商机名称','商机状态','商机状态百分比','跟进人', '创建人','标签','所属客户id',
            '所属客户名称', '所属客户来源','商机最后跟进时间','商机下次跟进时间','作废原因','所属公司','所属部门','最后一次跟进记录'];
        $csv->insertOne($header);

        for($i = 0; $i < $batch; $i++)
        {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
//            $models = $query->offset($i*$batchNum)->limit($batchNum)->all();
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();

            foreach ($models as $opportunity)
            {
                $csv->insertOne([
                    "\t" . $opportunity['id'],
                    "\t" . date('Y-m-d H:i:s',$opportunity['created_at']),
                    "\t" . $this->trimStr($opportunity['name']),
                    "\t" . $this->trimStr($this->getExportStatusName($opportunity['status'])),
                    "\t" . $opportunity['progress'].'%',
                    "\t" . $this->trimStr($opportunity['administrator_name'] ? $opportunity['administrator_name'] : '--'),
                    "\t" . $this->trimStr($opportunity['creator_name']),
                    "\t" . $this->trimStr($opportunity['tag_name']),
                    "\t" . $opportunity['customer_id'],
                    "\t" . $this->trimStr($opportunity['customer_name']),
                    "\t" . $opportunity['source_name'],
                    "\t" . ($opportunity['last_record'] != 0) ? Yii::$app->formatter->asDatetime($opportunity['last_record']) : '0000-00-00 00:00:00',
                    "\t" . Yii::$app->formatter->asDatetime($opportunity['next_follow_time']),
                    "\t" . $this->trimStr($opportunity['invalid_reason']),
                    "\t" . $this->trimStr($opportunity['company_name']),
                    "\t" . $this->trimStr($opportunity['department_name']),
                    "\t" . $this->getContent($opportunity['id']),
                ]);
            }
        }
        //记录操作日志
        AdministratorLog::logExport('商机',$count);

        $filename = date('YmdHis').rand(10000,99999).'_商机记录.csv';
        Yii::$app->cache->set('opportunity-export-' . Yii::$app->user->id,time(),30);
//        Yii::$app->response->setDownloadHeaders($filename, 'text/xlsx');
//        return iconv(Yii::$app->charset,'utf-8//IGNORE', $csv);
//

        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset, 'gbk//IGNORE', $csv);
    }
    public function getContent($opportunity_id){
        $content = CrmOpportunityRecord::find() ->select('content')->where(['opportunity_id' => $opportunity_id])->orderBy(['created_at'=> SORT_DESC]) -> one();
        return $content['content'];
    }

    public function getExportStatusName($status)
    {
        if($status == 0)
        {
            return '未成交';
        }
        else if($status == 1)
        {
            return '申请中';
        }
        else if($status == 2)
        {
            return '已成交';
        }
        else if($status == 3)
        {
            return '已失败';
        }
        return '--';
    }

    public function actionAjaxDetail($id = null)
    {
        $administratorId = Yii::$app->user->id;
        $model = null;
        if($id)
        {
            /** @var CrmOpportunity $model */
            $model = $this->findModel($id);
        }
        if($model)
        {
            $data = [];
            $data['name'] = $model->name;
            $data['progress'] = $model->progress;
            $data['progress_percent'] = $model->getStatus();
            $data['created_at'] = Yii::$app->formatter->asDatetime($model->created_at);
            $data['creator_name'] = $model->creator_name;
            $data['administrator_name'] = $model->administrator_name;
            $data['total_amount'] = Yii::$app->formatter->asCurrency($model->total_amount);
            $data['business_subject'] = '--';
            if($model->businessSubject)
            {
                if (!$model->isPublic()) {
                    $data['business_subject'] = $model->businessSubject->subject_type ? $model->businessSubject->region : $model->businessSubject->company_name;
                } else {
                    $data['business_subject'] = $model->businessSubject->subject_type ? $model->replaceName($model->businessSubject->region, 2) : $model->replaceName($model->businessSubject->company_name, 2);
                }
            }
            $data['customer'] = $model->customer_name.'-'.($model->customer ? $model->customer->phone : "--");
            $data['predict_deal_time'] = $model->predict_deal_time > 0 ? Yii::$app->formatter->asDate($model->predict_deal_time) : '--';
            $data['remark'] = $model->remark;
            $data['sn'] = $model->virtualOrder ? $model->virtualOrder->sn : '--';
            $data['is_protect'] = $model->is_protect;

            //商品信息
            $product = CrmOpportunityProduct::find()->select(['product_name', 'price', 'qty'])->where(['opportunity_id' => $id])->asArray()->all();
            $data['product'] = $product ? $product : '';

            //跟进记录
            $records = CrmOpportunityRecord::find()->select(['created_at', 'creator_name', 'next_follow_time', 'content'])->where(['opportunity_id' => $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();
            $data['records'] =  $records ? $records : '';

            //判断商机是否有权限添加跟进记录，判断是否有权限商机作废，申请成交，编辑
            $data['opportunity_can_follow'] = 0;
            $data['opportunity_void'] = 0;
            $data['opportunity_apply_deal'] = 0;
            $data['opportunity_protect'] = 0;
            $data['opportunity_edit'] = 0;
            if($model->administrator_id == $administratorId && $model->is_receive && !$model->isPublic())
            {
                if($model->isStatusNotDeal())
                {
                    $data['opportunity_void'] = 1;
                    $data['opportunity_apply_deal'] = 1;
                    $data['opportunity_edit'] = 1;

                    //判断是否有权限保护商机
                    $hasOpportunityPublic = $model->department ? $model->department->opportunityPublic ? $model->department->opportunityPublic : null : null;
                    if($hasOpportunityPublic)
                    {
                        if($model->isProtect())
                        {
                            //显示取消保护
                            $data['opportunity_protect'] = 2;
                        }
                        else
                        {
                            //显示保护
                            $data['opportunity_protect'] = 1;
                        }
                    }
                }

                //判断是否有权限显示跟进记录
                if($model->isStatusNotDeal() || $model->isStatusApply())
                {
                    $data['opportunity_can_follow'] = 1;
                }
            }
            return ['status' => 200, 'opportunity' => $this->serializeData($data)];
        }

        return ['status' => 400, 'message' => '操作有误！'];
    }

    public function actionIsMainPart($id)
    {
        $opportunity = $this->findModel($id);
        return ['status' => 200,'data' => $opportunity->business_subject_id];
    }

    private function findModel($id)
    {
        $model = Niche::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到商机信息');
        }
        /** @var Administrator $administrator */
//        $administrator = Yii::$app->user->identity;
//        if(!$model->isSubFor($administrator) && $model->administrator_id != $administrator->id)
//        {
//            throw new NotFoundHttpException('您没有权限查看该商机');
//        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    //删除特殊符号
    private function trimStr($str)
    {
        //注意有个特殊空格符号" "
        $needReplace = [" ","　"," ","\t","\n","\r"];
        $result = ["","","","",""];
        return str_replace($needReplace,$result,$str);
    }
}