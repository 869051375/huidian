<?php

namespace backend\controllers;

use backend\models\AdjustPriceForm;
use backend\models\BatchReviewAdjustPriceForm;
use backend\models\CancelOrderForm;
use backend\models\ChangeOrderClerkForm;
use backend\models\ChangeOrderCustomerServiceForm;
use backend\models\ChangeOrderTeamRate;
use backend\models\ChangeSalesmanForm;
use backend\models\ConfirmPayForm;
use backend\models\OrderApplyPerformanceForm;
use backend\models\OrderApplyRejectForm;
use backend\models\OrderExpectedCost;
use backend\models\OrderFileSaveForm;
use backend\models\OrderFileUploadForm;
use backend\models\OrderFinancialForm;
use backend\models\OrderFlowActionForm;
use backend\models\OrderRemarkForm;
use backend\models\OrderSatisfactionForm;
use backend\models\OrderTeamForm;
use backend\models\RefundOrderForm;
use backend\models\ReviewAdjustPriceForm;
use backend\models\SettlementMonthForm;
use backend\models\StartServiceForm;
use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\Order;
use common\utils\BC;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class OrderActionController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['create-order-team','change-order-team-rate', 'ajax-financial-validation', 'ajax-financial-info'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['refund', 'get-refund-info'],
                        'allow' => true,
                        'roles' => ['order-action/refund'],
                    ],
                    [
                        'actions' => ['change-customer-service'],
                        'allow' => true,
                        'roles' => ['order-action/change-customer-service'],
                    ],
                    [
                        'actions' => ['change-clerk'],
                        'allow' => true,
                        'roles' => ['order-action/change-clerk'],
                    ],
                    [
                        'actions' => ['start-service'],
                        'allow' => true,
                        'roles' => ['order-action/start-service'],
                    ],
                    [
                        'actions' => ['confirm-pay'],
                        'allow' => true,
                        'roles' => ['order-action/confirm-pay'],
                    ],
                    [
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['order-action/cancel'],
                    ],
                    [
                        'actions' => ['do-flow-action', 'upload', 'save-file'],
                        'allow' => true,
                        'roles' => ['order-action/do-flow-action'],
                    ],
                    [
                        'actions' => ['change-salesman','create-order-team','change-order-team-rate'],
                        'allow' => true,
                        'roles' => ['order-action/change-salesman'],
                    ],
                    [
                        'actions' => ['add-remark'],
                        'allow' => true,
                        'roles' => ['order-action/add-remark'],
                    ],
                    [
                        'actions' => ['adjust-price', 'get-adjust-info'],
                        'allow' => true,
                        'roles' => ['order-action/adjust-price'],
                    ],
                    [
                        'actions' => ['review-adjust-price', 'get-adjust-info'],
                        'allow' => true,
                        'roles' => ['order-action/review-adjust-price'],
                    ],
                    [
                        'actions' => ['financial-update', 'ajax-financial-validation', 'ajax-financial-info'],
                        'allow' => true,
                        'roles' => ['order-action/financial-update'],
                    ],
                    [
                        'actions' => ['satisfaction'],
                        'allow' => true,
                        'roles' => ['order-action/satisfaction'],
                    ],
                    [
                        'actions' => ['apply-performance'],
                        'allow' => true,
                        'roles' => ['order-action/apply-calculate'],
                    ],
                    [
                        'actions' => ['reject'],
                        'allow' => true,
                        'roles' => ['performance-statistics/*'],
                    ],
                    [
                        'actions' => ['batch-review-adjust-price'],
                        'allow' => true,
                        'roles' => ['order-action/review-adjust-price'],
                    ],
                    [
                        'actions' => ['settlement-month'],
                        'allow' => true,
                        'roles' => ['order-action/settlement-month'],
                    ],
                    [
                        'actions' => ['expected-cost'],
                        'allow' => true,
                        'roles' => ['expected-cost/insert'],
                    ],
                    [
                        'actions' => ['upload', 'save-file'],
                        'allow' => true,
                        'roles' => ['order-action/upload'],
                    ],
                    [
                        'actions' => ['service-status-update'],
                        'allow' => true,
                        'roles' => ['order-action/service-status-update'],
                    ],
                ],
            ],
        ];
    }

    // 订单退款（单个订单）
    public function actionRefund($is_validate = 0)
    {
        $model = new RefundOrderForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 获得订单退款信息
    public function actionGetRefundInfo($order_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->findOrder($order_id);
        return ['status' => 200, 'data' => [
            'require_refund_amount' => $order->require_refund_amount,
            'refund_reason' => $order->refund_reason,
            'refund_remark' => $order->refund_remark,
            'refund_explain' => $order->refund_explain,
            'is_cancel' => $order->isCancel(),
            'can_refund_amount' => $order->canRefundAmount(),
            'is_refund_apply' => $order->isRefundApply(),
        ]];
    }

    // 修改客服
    public function actionChangeCustomerService($is_validate = 0)
    {

        $model = new ChangeOrderCustomerServiceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 修改业务人员
    public function actionChangeSalesman($is_validate = 0)
    {
        $model = new ChangeSalesmanForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //创建分成业务人员
    public function actionCreateOrderTeam()
    {
        $model = new OrderTeamForm();
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $order_team = $model->save();
            if($order_team)
            {
                return ['status' => 200,'data' => $order_team,'total_rate' => BC::sub($model->order->getDivideRate(),$model->divide_rate)];
            }
        }
        ActiveForm::validate($model);
        return ['status' => 400,'message' => reset($model->getFirstErrors())];
    }

    //修改多业务员分成比例
    public function actionChangeOrderTeamRate()
    {
        $model = new ChangeOrderTeamRate();
        $model->rate = Yii::$app->request->post('rate');
        $model->team = Yii::$app->request->post('team');
        $model->order_id = Yii::$app->request->post('order_id');
        if($model->validate())
        {
            if($model->save())
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400,'message' => reset($model->getFirstErrors())];
    }

    // 修改服务人员(派单)
    public function actionChangeClerk($is_validate = 0)
    {
        $model = new ChangeOrderClerkForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 开始服务
    public function actionStartService($is_validate = 0)
    {
        $model = new StartServiceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->startService(), $model->getFirstErrors());
    }

    // 确认付款（整个虚拟订单）
    public function actionConfirmPay($is_validate = 0)
    {
        $model = new ConfirmPayForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 取消订单（整个虚拟订单）
    public function actionCancel($is_validate = 0)
    {
        $model = new CancelOrderForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 订单流程操作动作
    public function actionDoFlowAction($is_validate = 0)
    {
        $model = new OrderFlowActionForm();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if(null == $admin->clerk)
        {
            return $this->responseJson(false, ['您不是服务人员，不能进行该操作!']);
        }
        $model->clerk = $admin->clerk;
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->doAction(), $model->getFirstErrors());
    }

    public function actionUpload($is_flow = '1')
    {
        $model = new OrderFileUploadForm();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if(null == $admin->clerk && !Yii::$app->user->can('order-action/upload') && !Yii::$app->user->can('order-action/do-flow-action'))
        {
            return Json::encode([
                'files' => [
                    ['error' => '此操作需具备服务人员权限或文件上传权限!']
                ],
            ]);
        }
        $model->clerk = $admin->clerk;
        if($is_flow == '1')
        {
            $model->setScenario('flow');
            //流程操作中的文件上传需要服务人员权限
            if(null == $admin->clerk)
            {
                return Json::encode([
                    'files' => [
                        ['error' => '您不是服务人员，不能进行该操作!']
                    ],
                ]);
            }
        }
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
        return Json::encode([
            'files' => [
                ['error' => $model->hasErrors() ? reset($model->getFirstErrors()) : '上传失败!']
            ],
        ]);
    }

    // 仅限用于单独上传文件时的操作
    public function actionSaveFile($is_validate = 0)
    {
        $model = new OrderFileSaveForm();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if(null == $admin->clerk && !Yii::$app->user->can('order-action/upload') && !Yii::$app->user->can('order-action/do-flow-action'))
        {
            return $this->responseJson(false, ['此操作需具备服务人员权限或文件上传权限!']);
        }
        $model->clerk = $admin->clerk;
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    // 添加备注
    public function actionAddRemark($is_validate = 0)
    {
        $model = new OrderRemarkForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionReviewAdjustPrice($is_validate = 0)
    {
        $model = new ReviewAdjustPriceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionBatchReviewAdjustPrice($is_validate = 0)
    {
        $model = new BatchReviewAdjustPriceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionAdjustPrice($is_validate = 0)
    {
        $model = new AdjustPriceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionGetAdjustInfo($order_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        /** @var AdjustOrderPrice $adjust */
        $adjust = AdjustOrderPrice::find()->where(['order_id' => $order_id])->orderBy(['id' => SORT_DESC])->one();
        if(null == $adjust) return ['status' => 404];
        return ['status' => 200, 'data' => ['adjust_price' => $adjust->adjust_price,
            'adjust_price_reason' => $adjust->adjust_price_reason]];
    }

    //编辑财务明细编号
    public function actionFinancialUpdate($id)
    {
        $order = $this->findOrder($id);
        $model = new OrderFinancialForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->update($order))
            {
                Yii::$app->session->setFlash('success', '修改成功!');
                return $this->redirect(['order/info', 'id' => $order->id]);
            }
            else
            {
                Yii::$app->session->setFlash('error', '修改失败!');
            }
        }
        if ($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', '修改财务明细编号失败, 您的表单填写有误, 请检查!');
        }
        return $this->redirect(['order/info', 'id' => $order->id]);
    }

    public function actionAjaxFinancialValidation()
    {
        $model = new OrderFinancialForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionAjaxFinancialInfo($id)
    {
        $model = $this->findOrder($id);
        return ['status' => 200, 'model' => $this->serializeData($model)];
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

    private function findOrder($id)
    {
        $order = Order::findOne($id);
        if(null == $order)
        {
            throw new NotFoundHttpException('找不到订单信息!');
        }
        return $order;
    }

    public function actionSatisfaction($is_validate = 0)
    {
        $model = new OrderSatisfactionForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionApplyPerformance($is_validate = 0)
    {
        $model = new OrderApplyPerformanceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionReject($is_validate = 0)
    {
        $model = new OrderApplyRejectForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionSettlementMonth($is_validate = 0)
    {
        $model = new SettlementMonthForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    public function actionExpectedCost($is_validate = 0)
    {
        $model = new OrderExpectedCost();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $cost = $model->saveCost();
        if($loaded && $cost)
        {
            $cost->created_at = date('Y-m-d H:i:s',$cost->created_at);
            return ['status' => 200 , 'data' => $cost];
        }
        return ['status' => 400, 'message' => $model->getFirstErrors() ? reset($model->getFirstErrors()) : '您的操作有误!'];
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }


    //服务状态标记

    public function actionServiceStatusUpdate($is_validate = 0){

        $model = new Order();
        $loaded = $model -> load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $save = $model -> serviceStatusUpdate();

        if($loaded && $save)
        {
            return ['status' => 200 , 'data' => $save];
        }
        return ['status' => 400, 'message' => $model->getFirstErrors() ? reset($model->getFirstErrors()) : '您的操作有误!'];


    }
}