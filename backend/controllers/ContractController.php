<?php

namespace backend\controllers;

use backend\models\ContractApprovalForm;
use backend\models\ContractChangeAdministratorForm;
use backend\models\ContractChangeForm;
use backend\models\ContractForm;
use backend\models\ContractInvalidForm;
use backend\models\ContractSearch;
use backend\models\ContractSignatureForm;
use backend\models\ContractUpdateForm;
use backend\models\ContractUploadForm;
use backend\models\CrmCustomerSearch;
use common\models\Administrator;
use common\models\Company;
use common\models\Contract;
use common\models\ContractRecord;
use common\models\ContractType;
use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\CrmOpportunityProduct;
use common\models\CustomerCustomField;
use common\models\Niche;
use common\models\NicheProduct;
use common\models\Product;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ContractController extends BaseController
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'detail', 'detail', 'delete','effective','ajax-status','ajax-type-list','create-contract'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create','detail','delete','ajax-status','effective'],
                        'allow' => true,
                        'roles' => ['contract/type'],
                    ],
                    [
                        'actions' => ['type'],
                        'allow' => true,
                        'roles' => ['contract/type-list'],
                    ],
                    [
                        'actions' => ['contract','create-contract','update-contract','update','upload','correct-contract'],
                        'allow' => true,
                        'roles' => ['contract/create'],
                    ],
                    [
                        'actions' => ['upload','view','add-record','invalid-contract','add-record'],
                        'allow' => true,
                        'roles' => ['contract/view'],
                    ],
                    [
                        'actions' => ['pending-contract','review','receipt-finish','pending-receipt','invalid','all'],
                        'allow' => true,
                        'roles' => ['contract/list'],
                    ],
                    [
                        'actions' => ['sign-contract'],
                        'allow' => true,
                        'roles' => ['contract/sign'],
                    ],
                    [
                        'actions' => ['invalid-review'],
                        'allow' => true,
                        'roles' => ['contract/invalid'],
                    ],
                    [
                        'actions' => ['change-admin'],
                        'allow' => true,
                        'roles' => ['contract/change-administrator'],
                    ],
                    [
                        'actions' => ['change-signature'],
                        'allow' => true,
                        'roles' => ['contract/change-signature'],
                    ],
                    [
                        'actions' => ['ajax-type-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    // 全部合同
    public function actionAll()
    {
        return $this->searchContract(null);
    }

    // 待签约
    public function actionPendingContract()
    {
        return $this->searchContract('pending-contract');
    }

    // 审核中
    public function actionReview()
    {
        return $this->searchContract('review');
    }

    // 回款完成
    public function actionReceiptFinish()
    {
        return $this->searchContract('receipt-finish');
    }

    // 已作废
    public function actionPendingReceipt()
    {
        return $this->searchContract('pending-receipt');
    }

    // 回款完成
    public function actionInvalid()
    {
        return $this->searchContract('invalid');
    }

    private function searchContract($range)
    {
        $searchModel = new ContractSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $range);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionContract($c_id = 0,$o_id = 0)
    {

        $customer = $this->findCustomer($c_id);

        /** @var Niche $opportunity */
        $opportunity = $this->findOpportunity($o_id);


//        var_dump($opportunity->administrator->id);die;
        $product = $this->getProduct($opportunity);
//        foreach ($opportunity->opportunityProducts as $item)
//        {
//            var_dump($item->productPrice);die;
//        }
        return $this->render('create',[
            'customer' => $customer,
            'opportunity' => $opportunity,
            'products' => $product,
        ]);
    }


    public function getProduct($opportunity)
    {
        /** @var NicheProduct $opportunity_product */
        $opportunity_product = NicheProduct::find()->where(['niche_id'=>$opportunity->id])->one();
        if(!empty($opportunity_product)){
            /** @var Product $product */
            $product = Product::find()->where(['id'=>$opportunity_product->product_id])->one();
            return $product;
        }
        return [];
    }

    //更换负责人
    public function actionChangeAdmin($is_validate = 0)
    {
        $model = new ContractChangeAdministratorForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //更换负责人
    public function actionChangeSignature($is_validate = 0)
    {
        $model = new ContractSignatureForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //作废
    public function actionInvalidContract($is_validate = 0)
    {
        $model = new ContractInvalidForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //作废审核
    public function actionInvalidReview($is_validate = 0)
    {
        $model = new ContractInvalidForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //签约审核
    public function actionSignContract($is_validate = 0)
    {
        $model = new ContractApprovalForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //变更审核
    public function actionCorrectContract($is_validate = 0)
    {
        $model = new ContractChangeForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //添加
    public function actionCreateContract($is_validate = 0)
    {
        $model = new ContractForm();

        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //变更
    public function actionUpdateContract($is_validate = 0)
    {
        $model = new ContractUpdateForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //添加动态
    public function actionAddRecord($is_validate = 0)
    {
        $model = new ContractRecord();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        ContractRecord::CreateRecord($model->contract_id,$admin->name.'添加了跟进记录：'.$model->content,$admin);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 200];
    }

    public function actionView($cid = 0)
    {
        $contract = $this->findContract($cid);
        return $this->render('view',['contract' => $contract]);
    }

    public function actionUpdate($cid = 0)
    {
        $contract = $this->findContract($cid);
        return $this->render('update',['contract' => $contract]);
    }

    private function findContract($id)
    {
        $model = Contract::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的合同');
        }
        return $model;
    }

    private function findCustomer($id)
    {
        $model = CrmCustomer::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的客户');
        }
        return $model;
    }

    private function findOpportunity($id)
    {
        $model = Niche::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的商机');
        }
        return $model;
    }

    public function actionType()
    {
        $query = ContractType::find();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if($admin->isCompany())
        {
            $query->andWhere(['company_id' => $admin->company_id]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('type', [
            'provider' => $provider,
        ]);
    }

    //新增合同类型
    public function actionCreate($is_validate = 0)
    {
        $id = Yii::$app->getRequest()->post()['ContractType']['id'];
        $model = ContractType::findOne($id);
        if($id && $model)
        {
            $model->setScenario('update');
        }
        else
        {
            $model = new ContractType();
            $model->setScenario('insert');
        }
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $model->creator_id = $user->id;
        $model->creator_name = $user->name;
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //合同类型详情
    public function actionDetail($id)
    {
        $model = ContractType::find()->select('id,name,desc,serial_number,company_id')->where(['id' => $id])->limit(1)->one();
        $proportionData = $this->serializeData($model);
        return ['status' => 200, 'model' => $proportionData];
    }

    //合同类型删除
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = ContractType::findOne($id);
        if($model == null)
        {
            return ['status' => 400 , 'message' => '找不到指定的合同'];
        }
        $model->delete();
        return ['status' => 200];
    }

    //合同类型生效
    public function actionEffective()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = ContractType::findOne($id);
        if($model == null)
        {
            return ['status' => 400 , 'message' => '找不到指定的合同'];
        }
        $model->is_enable = ContractType::ENABLE_ACTIVE;
        $model->save(false);
        return ['status' => 200];
    }

    //合同类型状态
    public function actionAjaxStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = ContractType::findOne($id);
        if($model == null)
        {
            return ['status' => 400 , 'message' => '找不到指定的合同'];
        }
        $model->status = $status;
        if($model->validate(['status']))
        {
            $model->save(false);
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionUpload()
    {
        $model = new ContractUploadForm();
        if($model->load(Yii::$app->request->post(), ''))
        {
            $model->file = UploadedFile::getInstanceByName('file');
            $rs = $model->upload();
            if($rs)
            {
                return Json::encode([
                    'files' => [
                        $rs
                    ]
                ]);
            }
        }
        $errors = $model->getFirstErrors();
        return Json::encode([
            'files' => [
                ['error' => $model->hasErrors() ? reset($errors) : '上传失败!']
            ],
        ]);
    }

    public function actionAjaxTypeList($keyword = null,$company_id = null)
    {
        $query = ContractType::find()->select('id,name,serial_number,company_id')
            ->where(['status' => ContractType::STATUS_ACTIVE]);
        if($keyword)
        {
            $query->where(['like','name',$keyword]);
        }
        if($company_id)
        {
            $query->andWhere(['company_id' => $company_id]);
        }
        return ['status' => 200 ,'items' => $query->all()];
    }

    private function responseJson($isSuccess, $errors = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($isSuccess)
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => $errors ? reset($errors) : '您的操作有误!'];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}