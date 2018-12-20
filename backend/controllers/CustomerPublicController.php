<?php
namespace backend\controllers;

use backend\models\CustomerConfirmClaimForm;
use backend\models\CustomerPublicSearch;
use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use common\models\CustomerPublic;
use common\models\VirtualOrder;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\redis\Cache;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CustomerPublicController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['confirm-claim', 'validation', 'delete', 'detail', 'company-list','ajax-administrator-list','ajax-public-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'update', 'confirm-claim','ajax-administrator-list','ajax-public-list'],
                        'allow' => true,
                        'roles' => ['customer-public/list'],
                    ],
                    [
                        'actions' => ['setting', 'validation', 'delete', 'detail', 'company-list'],
                        'allow' => true,
                        'roles' => ['customer-public/setting'],
                    ],
                    [
                        'actions' => ['confirm-claim'],
                        'allow' => true,
                        'roles' => ['customer-public/confirm-claim'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($id = null)
    {
        $search = new CustomerPublicSearch();
        $search->load(Yii::$app->request->queryParams);
        $search->search();

        /** @var CrmCustomerLog[] $records */
        $records = CrmCustomerLog::find()->where(['customer_id' => $id, 'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])->orderBy(['created_at' => SORT_DESC])->all();

        $customer = null;
		if($id)
		{
            $customer = $this->findCustomerModel($id);
		}
        // 如果是ajax请求，则是请求待办列表，
        // 放到这个控制器下主要是为了兼容老旧浏览器不支持pjax
        if(Yii::$app->request->isAjax)
        {
            return $this->renderAjax('upcoming', [
                'records' => $records,
                'customer' => $customer,
            ]);
        }
        return $this->render('list', ['search' => $search, 'records' => $records, 'customer' => $customer,]);
    }

    public function actionSetting()
    {
        /** @var Query $query */
        $query = CustomerPublic::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        return $this->render('setting', [
            'dataProvider' => $dataProvider,
        ]);
    }

    // 新增客户公海
    public function actionCreate()
    {
        $model = new CustomerPublic();
        $model->setScenario('insert');
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->defaultValues();
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
            return $this->redirect(['setting']);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['setting']);
    }

    // 更新客户公海
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->defaultValues();
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
            return $this->redirect(['setting']);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['setting']);
    }

    // 删除客户公海
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->company_id > 0 && $model->company)
        {
            return ['status' => 400,'message' => '该客户公海不可删除！'];
        }
        if(null != $model->customers)
        {
            return ['status' => 400, 'message' => '对不起，当前公海中还存在未提取或提取中的客户，请提取成功后，再删除！'];
        }
        $model->delete();
        return ['status' => 200];
    }

    public function actionValidation()
    {
        $model = new CustomerPublic();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $this->serializeData($model),'company' => $this->serializeData($model->company)];
    }

    public function actionConfirmClaim()
    {
        $model = new CustomerConfirmClaimForm();
        if($model->load(Yii::$app->request->post()))
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $success = $model->confirmClaim();
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
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    //获取2级部门
    public function actionCompanyList($keyword=null, $company_id = null)
    {
        $companyIds = CustomerPublic::find()->select('company_id')->asArray();
        /** @var ActiveQuery $query */
        $query = Company::find()->select(['id', 'name'])
            ->andWhere(['not in', 'id', $companyIds])
            ->orderBy(['created_at' => SORT_ASC]);
        if(!empty($company_id))
        {
            $query->andWhere(['company_id' => $company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        return ['status' => 200, 'companies' => $this->serializeData($query->all())];
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

    /**
     * 最后跟进人列表
     * @param null $keyword
     * @return array
     */
    public function actionAjaxAdministratorList($keyword = null)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        /** @var Cache $cache */
        $cache = Yii::$app->get('redisCache');
        $data = $cache->get('crm-customer-last-record-creator-id'.$administrator->id);
        if(empty($keyword))
        {
            if(null != $data)
            {
                return ['status' => 200, 'items' => $this->serializeData($data)];
            }
        }
        $ids = $this->customers();
        $ids = array_unique(ArrayHelper::getColumn($ids, 'last_record_creator_id'));
        $query = Administrator::find()->select(['a.id', 'a.name'])->alias('a')
            ->andWhere(['in', 'a.id', $ids]);

        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();
        if(empty($keyword))
        {
            $cache->set('crm-customer-last-record-creator-id'.$administrator->id, $data, 60);
        }
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    private function customers()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query = CrmCustomer::find()->select(['last_record_creator_id'])->andWhere(['>', 'customer_public_id', 0]);;
        //启用公司与部门
        if($administrator->isBelongCompany() && $administrator->company_id)
        {
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        $query = $query->asArray()->all();
        return $query;
    }

    public function actionAjaxPublicList($keyword = null)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query = CustomerPublic::find()->select(['id', 'name']);
        if($administrator->isBelongCompany())
        {
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'a.name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    private function findModel($id)
    {
        $model = CustomerPublic::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到客户公海信息');
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
    
    /**
     * @param int $id
     * @return CrmCustomer
     * @throws NotFoundHttpException
     */
    private function findCustomerModel($id)
    {
        $model = CrmCustomer::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到客户信息');
        }
        return $model;
    }
}