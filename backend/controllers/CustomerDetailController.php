<?php

namespace backend\controllers;

use backend\models\BatchCustomerChangeAdministratorForm;
use backend\models\CrmCustomerForm;
use backend\models\CustomerCombineChangeLevelForm;
use backend\models\CustomerCombineDeleteForm;
use backend\models\CustomerDetailChangeAdministratorForm;
use backend\models\CustomerLogForm;
use backend\models\CustomerProtectForm;
use common\models\AdministratorLog;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\Order;
use common\models\Trademark;
use common\models\UserLoginLog;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CustomerDetailController extends BaseController
{
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
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['validation','change-administrator','add-record', 'change-level','ajax-delete', 'protect'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['add-record','change-administrator','batch-change-administrator','opportunity','salesman-list','order','trademark','business-subject','update-customer','validation', 'change-level','record','information','do-record','login-log'],
                        'allow' => true,
                        'roles' => ['customer/*'],
                    ],
                    [
                        'actions' => ['opportunity','salesman-list','order','trademark','business-subject','record','information','do-record','login-log'],
                        'allow' => true,
                        'roles' => ['customer/all'],
                    ],
                    [
                        'actions' => ['opportunity','salesman-list','order','trademark','business-subject','record','information','do-record','login-log', 'ajax-delete', 'protect'],
                        'allow' => true,
                        'roles' => ['customer-detail/*'],
                    ],
                    [
                        'actions' => ['customer-call'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['call-record'],
                        'allow' => true,
                        //'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 400呼叫调用页面
     * @param string $originCallNo
     * @return Response
     */
    public function actionCustomerCall($originCallNo = null)
    {
        $originCallNo = $this->formatCallNo($originCallNo);
        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['or', ['phone' => $originCallNo], ['tel' => $originCallNo]])->one();
        if(null != $customer)
        {
            return $this->redirect(['business-subject', 'id' => $customer->id]);
        }
        echo "<script>alert('原客户新增功能已经停用！！！')</script>";

//        return $this->redirect(['crm-customer/check', 'no' => $originCallNo]);
    }

    /**
     * 400通话记录接口
     * @param $CallNo
     * @return string
     */
    public function actionCallRecord($CallNo = null)
    {
        $CallNo = $this->formatCallNo($CallNo);
        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['or', ['phone' => $CallNo], ['tel' => $CallNo]])->one();
        if(null == $customer)
        {
            return 'success';
        }
        $callType = Yii::$app->request->get('CallType');
        $state = Yii::$app->request->get('State');
        $callTypes = ['normal' => '普通来电', 'dialout' => '外呼通话', 'transfer' => '转接电话', 'dialtransfer' => '外呼转接'];
        $states = ['dealing' => '已接听', 'notDeal' => '振铃未接听', 'voicemail' => '已留言', 'blackList' => '黑名单', 'queueLeak' => '排队放弃', 'leak' => 'ivr'];
        if(isset($callTypes[$callType]))
        {
            $callType = $callTypes[$callType];
        }
        if(isset($states[$state]))
        {
            $state = $states[$state];
        }
        $monitorFilename = Yii::$app->request->get('MonitorFilename');
        $exten = Yii::$app->request->get('Exten');
        $agentName = Yii::$app->request->get('AgentName');
        $ring = Yii::$app->request->get('Ring');

        $model = new CrmCustomerLog();
        $model->customer_id = $customer->id;
        $model->remark = $callType.'；<br />'.$state.'；<br />'.$monitorFilename;
        $model->creator_id = 0;
        $model->creator_name = $exten.'-'.$agentName;
        $model->created_at = strtotime($ring);
        $model->save(false);
        return 'success';
    }

    /**
     * 用户格式化外部传递过来的电话号码和手机号码，例如 01065898769 格式化为 010-65898769，013588889999 格式化为 13588889999
     * 目前特定用户该控制器中，以后有需要再剥离出去
     * @param $callNo
     * @return string
     */
    private function formatCallNo($callNo)
    {
        $m = null;
        if(0 < preg_match('/^01[3-9][0-9]{9}$/', $callNo) || 0 < preg_match('/^1[3-9][0-9]{9}$/', $callNo))
        {
            return ltrim($callNo, '0');
        }
        else if(0 < preg_match('/^(010|020|021|022|023|024|025|027|028|029|0[0-9]{3})?([2-9][0-9]{6,7})+$/', $callNo, $m) || 0 < preg_match('/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+$/', $callNo, $m))
        {
            return rtrim($m[1], '/').'-'.$m[2];
        }
        return $callNo;
    }

    public function actionBusinessSubject($id)
    {
        $customer = $this->findModel($id);
        $model = BusinessSubject::find()->where(['customer_id'=>$id])->all();
        return $this->render('subject', [
                'customer' => $customer,
                'model' => $model,
            ]);
    }

    public function actionRecord($id)
    {
        $customer = $this->findModel($id);
        /**@var CrmCustomerLog[] $crmCustomerLogs **/
        $query = CrmCustomerLog::find()->where(['customer_id' => $customer->id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])
            ->orderBy(['created_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'validatePage' => false,
            ],
        ]);
        return $this->render('record', [
            'customer' => $customer,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionInformation($id)
    {
        $customer = $this->findModel($id);
        //新增后台访问客户操作日志
        AdministratorLog::logVisitCustomer($customer);
        return $this->render('information', [
            'customer' => $customer,
        ]);
    }

    //客户信息
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

    public function actionUpdateCustomer($url,$id)
    {
        $model = $this->findModel($id);
        $customerForm = new CrmCustomerForm();
        $customerForm->setAttributes($model->attributes);
        if($customerForm->load(Yii::$app->request->post()))
        {
            $customerForm->update($model);
            Yii::$app->session->setFlash('success', '保存成功!');
        } else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }

        return $this->redirect([$url,'id' => $model->id]);
    }

    private function findModel($id)
    {
        $model = CrmCustomer::findOne($id);

        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的客户!');
        }
        return $model;
    }

    //商标信息
    public function actionTrademark($id)
    {
        $customer = $this->findModel($id);
        $model = null;
        if($customer->user)
        {
            /* @var Trademark[] $models */
            $models = Trademark::find()->where(['user_id'=>$customer->user->id])->all();
        }
        else
        {
            $models = null;
        }
        return $this->render('trademark',[
            'customer'=>$customer,
            'models'=>$models
        ]);
    }

    //订单信息
    public function actionOrder($id, $status = 'paid')
    {
        if(!in_array($status, ['paid', 'pending-pay', 'break']))
        {
            $status = 'paid';
        }
        $customer = $this->findModel($id);
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = null;
        if($customer->user)
        {
            $query = null;
            if($status == 'paid')
            {
                $query = Order::getPaidQueryByUserId($customer->user_id);
            }
            elseif($status == 'pending-pay')
            {
                $query = Order::getPendingPayQueryByUserId($customer->user_id);
            }
            else
            {
                $query = Order::getBreakQueryByUserId($customer->user_id);
            }
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'validatePage' => false,
                ],
            ]);
        }
        return $this->render('order',[
            'customer' => $customer,
            'status' => $status,
            'dataProvider' => $dataProvider
        ]);
    }

    //所属业务员列表
    public function actionSalesmanList($id)
    {
        $customer = $this->findModel($id);
        $model = CrmCustomerCombine::find()->where(['customer_id'=>$id])->all();
        return $this->render('salesman_list',[
            'customer'=>$customer,
            'model'=>$model,
        ]);
    }

    //我的商机,商机详情列表中的关联商机展示数据
    public function actionOpportunity($customer_id, $status = '')
    {
        $customer = $this->findModel($customer_id);

        if($status == 'deal')
        {
            //已成交商机数据
            $query = CrmOpportunity::find()->where(['status' => CrmOpportunity::STATUS_DEAL, 'customer_id' => $customer_id]);
        }
        else
        {
            //未成交商机数据(除了已成交商机)
            $query = CrmOpportunity::find()->where(['customer_id' => $customer_id])->andWhere(['not in','status',CrmOpportunity::STATUS_DEAL]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('opportunity', [
            'dataProvider' => $dataProvider,
            'customer' => $customer,
        ]);
    }

    //更换负责人
    public function actionChangeAdministrator()
    {
        $model = new CustomerDetailChangeAdministratorForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->change())
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



    //修改客户级别
    public function actionChangeLevel()
    {
        $model = new CustomerCombineChangeLevelForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->changeLevel())
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

    //添加跟进记录
    public function actionAddRecord()
    {
        $model = new CustomerLogForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->add())
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

    public function actionDoRecord($id)
    {
        $customer = $this->findModel($id);
        $query = CrmCustomerLog::find()->where(['customer_id' => $customer->id,'type' => CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD])->orderBy(['created_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'validatePage' => false,
            ],
        ]);
        return $this->render('do-record', [
            'customer' => $customer,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionLoginLog($id)
    {
        $customer = $this->findModel($id);
        $query = UserLoginLog::find()->where(['user_id' => $customer->user_id])->orderBy(['created_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'validatePage' => false,
            ],
        ]);
        return $this->render('login-log', [
            'customer' => $customer,
            'dataProvider' => $dataProvider
        ]);
    }

    // 删除客户所属合伙人，只有当前用户为客户负责人时才能删除
    public function actionAjaxDelete()
    {
        $model = new CustomerCombineDeleteForm();
        if($model->load(Yii::$app->request->post(),''))
        {
            if($model->delete())
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

    public function actionProtect()
    {
        $model = new CustomerProtectForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->confirm())
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

    //账本业绩更正
    public function actionBatchChangeAdministrator($is_validate = 0)
    {
        $model = new BatchCustomerChangeAdministratorForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(),$model->getFirstErrors());
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
}
