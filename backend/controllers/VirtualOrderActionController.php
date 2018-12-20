<?php

namespace backend\controllers;

use backend\models\AdjustPriceForm;
use backend\models\AllocationPriceForm;
use backend\models\BatchAdjustPriceForm;
use backend\models\BatchCalculateProfitForm;
use backend\models\BatchChangeSalesmanForm;
use backend\models\BatchKnotExpectedProfitForm;
use backend\models\BatchOrderFinancialForm;
use backend\models\BatchOrderSettlementMonthForm;
use backend\models\BillsExpectedProfitCorrectForm;
use backend\models\BillsPerformanceCorrectForm;
use backend\models\CalculateCostForm;
use backend\models\CalculateExpectedProfitForm;
use backend\models\CalculateOrderExpectedProfitForm;
use backend\models\CalculateProfitForm;
use backend\models\CancelOrderForm;
use backend\models\ChangeOrderClerkForm;
use backend\models\ChangeOrderCustomerServiceForm;
use backend\models\ChangeOrderTeamRate;
use backend\models\ChangeSalesmanForm;
use backend\models\ConfirmPayForm;
use backend\models\ExpectedProfitCorrectForm;
use backend\models\OrderApplyPerformanceForm;
use backend\models\OrderApplyRejectForm;
use backend\models\OrderFileSaveForm;
use backend\models\OrderFileUploadForm;
use backend\models\OrderFinancialForm;
use backend\models\OrderFlowActionForm;
use backend\models\OrderRemarkForm;
use backend\models\OrderReplaceTeamForm;
use backend\models\OrderSatisfactionForm;
use backend\models\OrderTeamForm;
use backend\models\PaymentModeForm;
use backend\models\PerformanceCorrectForm;
use backend\models\RefundOrderForm;
use backend\models\ReviewAdjustPriceForm;
use backend\models\StartServiceForm;
use backend\models\VirtualOrderCost;
use backend\models\VirtualOrderExpectedCost;
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

class VirtualOrderActionController extends BaseController
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
                'only' => ['change-payment-mode','change-order-team-rate','change-settlement-month', 'change-financial','ajax-financial-validation', 'ajax-financial-info'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['change-payment-mode'],//批量修改订单付款方式
                        'allow' => true,
                        'roles' => ['virtual-order-action/payment-mode'],
                    ],
                    [
                        'actions' => ['change-adjust-price'],//批量修改价格
                        'allow' => true,
                        'roles' => ['virtual-order-action/batch-adjust-price'],
                    ],
                    [
                        'actions' => ['change-financial'],//批量编辑财务编号
                        'allow' => true,
                        'roles' => ['virtual-order-action/change-financial'],
                    ],
                    [
                        'actions' => ['change-settlement-month'],//订单业绩提点月
                        'allow' => true,
                        'roles' => ['virtual-order-action/change-settlement-month'],
                    ],
                    [
                        'actions' => ['allot-price'],//子订单分配回款
                        'allow' => true,
                        'roles' => ['virtual-order-action/allot-price'],
                    ],
                    [
                        'actions' => ['replace-order-team'],//订单批量替换共享业务员
                        'allow' => true,
                        'roles' => ['virtual-order-action/replace-order-team'],
                    ],
                    [
                        'actions' => ['batch-change-salesman'],//订单批量替换负责业务员
                        'allow' => true,
                        'roles' => ['virtual-order-action/replace-order-salesman'],
                    ],
                    [
                        'actions' => ['expected-cost'],//预计成本录入
                        'allow' => true,
                        'roles' => ['virtual-order-action/expected-cost'],
                    ],
                    [
                        'actions' => ['cost'],//成本录入
                        'allow' => true,
                        'roles' => ['virtual-order-action/cost'],
                    ],
                    [
                        'actions' => ['knot-expected-profit'],//批量结转预计利润
                        'allow' => true,
                        'roles' => ['virtual-order-action/knot-expected-cost'],
                    ],
                    [
                        'actions' => ['calculate-expected-profit','calculate-info'],//虚拟订单计算预计利润
                        'allow' => true,
                        'roles' => ['virtual-order-action/calculate-expected-profit'],
                    ],
                    [
                        'actions' => ['calculate-order-expected-profit'],//子订单计算预计利润
                        'allow' => true,
                        'roles' => ['virtual-order-action/calculate-order-expected-profit'],
                    ],
                    [
                        'actions' => ['expected-profit-correct','bills-expected-profit-correct'],//预计利润更正
                        'allow' => true,
                        'roles' => ['virtual-order-action/expected-profit-correct'],
                    ],
                    [
                        'actions' => ['calculate-profit','drop-cost'],//虚拟订单计算业绩
                        'allow' => true,
                        'roles' => ['virtual-order-action/calculate-profit'],
                    ],
                    [
                        'actions' => ['batch-calculate-profit'],//虚拟订单批量计算业绩
                        'allow' => true,
                        'roles' => ['virtual-order-action/batch-calculate-profit'],
                    ],
                    [
                        'actions' => ['performance-correct','bills-performance-correct'],//更正业绩
                        'allow' => true,
                        'roles' => ['virtual-order-action/performance-correct'],
                    ],
                ],
            ],
        ];
    }

    //批量修改付款方式
    public function actionChangePaymentMode($is_validate = 0)
    {
        $model = new PaymentModeForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //批量编辑财务明细编号
    public function actionChangeFinancial($is_validate = 0)
    {
        $model = new BatchOrderFinancialForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //批量修改订单价格
    public function actionChangeAdjustPrice($is_validate = 0)
    {
        $model = new BatchAdjustPriceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //批量编辑订单业绩提点月
    public function actionChangeSettlementMonth($is_validate = 0)
    {
        $model = new BatchOrderSettlementMonthForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //子订单回款分配
    public function actionAllotPrice($is_validate = 0)
    {
        $model = new AllocationPriceForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //订单批量替换共享业务员
    public function actionReplaceOrderTeam($is_validate = 0)
    {
        $model = new OrderReplaceTeamForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //订单批量替换负责业务员
    public function actionBatchChangeSalesman($is_validate = 0)
    {
        $model = new BatchChangeSalesmanForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //预计成本录入
    public function actionExpectedCost($is_validate = 0)
    {
        $model = new VirtualOrderExpectedCost();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //实际成本录入
    public function actionCost($is_validate = 0)
    {
        $model = new VirtualOrderCost();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //批量结转预计利润
    public function actionKnotExpectedProfit($is_validate = 0)
    {
        $model = new BatchKnotExpectedProfitForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //虚拟订单计算预计利润
    public function actionCalculateExpectedProfit($is_validate = 0)
    {
        $model = new CalculateExpectedProfitForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //子订单计算预计利润
    public function actionCalculateOrderExpectedProfit($is_validate = 0)
    {
        $model = new CalculateOrderExpectedProfitForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //子订单预计利润更正
    public function actionExpectedProfitCorrect($is_validate = 0)
    {
        $model = new ExpectedProfitCorrectForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //虚拟订单下放实际成本
    public function actionDropCost($is_validate = 0)
    {
        $model = new CalculateCostForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(),$model->getFirstErrors());
    }

    //虚拟订单计算全部子订单业绩
    public function actionCalculateProfit($is_validate = 0)
    {
        $model = new CalculateProfitForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(),$model->getFirstErrors());
    }

    //批量计算业绩
    public function actionBatchCalculateProfit($is_validate = 0)
    {
        $model = new BatchCalculateProfitForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(),$model->getFirstErrors());
    }

    //业绩更正
    public function actionPerformanceCorrect($is_validate = 0)
    {
        $model = new PerformanceCorrectForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(),$model->getFirstErrors());
    }

    //账本预计利润更正
    public function actionBillsExpectedProfitCorrect($is_validate = 0)
    {
        $model = new BillsExpectedProfitCorrectForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            if($model->administrator_id)
            {
                $model->setScenario('admin');
            }
            elseif($model->department_id)
            {
                $model->setScenario('department');
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    //账本业绩更正
    public function actionBillsPerformanceCorrect($is_validate = 0)
    {
        $model = new BillsPerformanceCorrectForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(),$model->getFirstErrors());
    }

    //计算信息
    public function actionCalculateInfo($month,$virtual_order_id)
    {
        /** @var Order[] $orders */
        $orders = Order::find()->select(['id'])
            ->where(['virtual_order_id' => $virtual_order_id,'settlement_month' => $month])->all();
        if(empty($month))
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 400];
        }
//        $totalPrice = 0;
//        $expectedProfit = 0;
//        $cost = 0;
//        foreach($orders as $order)
//        {
//            $totalPrice += floatval($order->price);
//            $cost += $order->getExpectedCost();
//            $expectedProfit += $order->getExpectedProfit();
//        }
        $data = [
            'num' => count($orders),
//            'price' => BC::sub(BC::sub($totalPrice,$expectedProfit),$cost),
        ];
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 200,'data' => $data];
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

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}