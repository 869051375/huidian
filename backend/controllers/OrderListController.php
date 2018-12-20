<?php

namespace backend\controllers;

use backend\models\OrderExportSearch;
use backend\models\OrderSearch;
use common\models\AdministratorLog;
use common\models\Company;
use common\models\CrmDepartment;
use common\models\Order;
use common\models\Source;
use common\models\OrderRemark;
use common\models\User;
use common\models\VirtualOrder;
use common\models\CrmCustomer;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;

class OrderListController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['all'],
                        'allow' => true,
                        'roles' => ['order-list/all'],
                    ],
                    [
                        'actions' => ['refund'],
                        'allow' => true,
                        'roles' => ['order-list/refund'],
                    ],
                    [
                        'actions' => ['pending-payment'],
                        'allow' => true,
                        'roles' => ['order-list/pending-payment'],
                    ],
                    [
                        'actions' => ['unpaid'],
                        'allow' => true,
                        'roles' => ['order-list/pending-payment'],
                    ],
                    [
                        'actions' => ['pending-assign'],
                        'allow' => true,
                        'roles' => ['order-list/pending-assign'],
                    ],
                    [
                        'actions' => ['pending-service'],
                        'allow' => true,
                        'roles' => ['order-list/pending-service'],
                    ],
                    [
                        'actions' => ['in-service'],
                        'allow' => true,
                        'roles' => ['order-list/in-service'],
                    ],
                    [
                        'actions' => ['completed'],
                        'allow' => true,
                        'roles' => ['order-list/completed'],
                    ],
                    [
                        'actions' => ['break'],
                        'allow' => true,
                        'roles' => ['order-list/break'],
                    ],
                    [
                        'actions' => ['timeout'],
                        'allow' => true,
                        'roles' => ['order-list/timeout'],
                    ],
                    [
                        'actions' => ['need-refund'],
                        'allow' => true,
                        'roles' => ['refund/do'],
                    ],
                    [
                        'actions' => ['vest'],
                        'allow' => true,
                        'roles' => ['order-list/vest'],
                    ],
                    [
                        'actions' => ['apply'],
                        'allow' => true,
                        'roles' => ['order-list/apply'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['order-list/export'],
                    ],
                    [
                        'actions' => ['validation'],
                        'allow' => true,
                        'roles' => ['order-list/validation'],
                    ],
                    [
                        'actions' => ['get-remark-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    // 全部订单
    public function actionAll()
    {
        return $this->searchOrders(null);
    }

    // 退款中订单
    public function actionRefund()
    {
        return $this->searchOrders('refund');
    }

    // 待付款
    public function actionPendingPayment()
    {
        return $this->searchOrders(Order::STATUS_PENDING_PAY);
    }

    // 未付清
    public function actionUnpaid()
    {
        return $this->searchOrders(Order::STATUS_UNPAID);
    }

    // 待分配
    public function actionPendingAssign()
    {
        return $this->searchOrders(Order::STATUS_PENDING_ALLOT);
    }

    // 待服务
    public function actionPendingService()
    {
        return $this->searchOrders(Order::STATUS_PENDING_SERVICE);
    }

    // 服务中
    public function actionInService()
    {
        return $this->searchOrders(Order::STATUS_IN_SERVICE);
    }

    // 服务完成
    public function actionCompleted()
    {
        return $this->searchOrders(Order::STATUS_COMPLETE_SERVICE);
    }

    // 服务终止
    public function actionBreak()
    {
        return $this->searchOrders(Order::STATUS_BREAK_SERVICE);
    }

    // 报警订单（服务超时）
    public function actionTimeout()
    {
        return $this->searchOrders('warning');
    }

    // 需要退款的订单
    public function actionNeedRefund($status = 'need-refund-review')
    {
        return $this->searchOrders($status);
    }

    // 马甲订单
    public function actionVest()
    {
        return $this->searchOrders('vest');
    }

    // 申请计算业绩订单
    public function actionApply()
    {
        return $this->searchOrders('apply');
    }

    private function searchOrders($status)
    {
        $searchModel = new OrderSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

    public function actionExport($status = 'all')
    {
        $url = Yii::$app->request->getReferrer();//获取过来的url
        $export_code = Yii::$app->cache->get('export-' . Yii::$app->user->id);//控制操作的时间（防止损耗资源）
        if ($export_code) {
            $second = date('s', BC::sub($export_code + 30, time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待' . $second . '秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;//批量导出的条数
        $searchModel = new OrderExportSearch();
        $searchModel->load(Yii::$app->request->queryParams);//把查询参数载入模型
        if (!$searchModel->validate())//获取查询参数是否有错误
        {
            Yii::$app->session->setFlash('error', reset($searchModel->getFirstErrors()));
            return $this->redirect($url);
        }
        $status = $this->getStatus(Yii::$app->request->queryParams['status']);//获取查询的数据状态

        $query = new Query();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status, $query);

        $query->select('o.sn,o.company_name as order_company_name,o.province_name,o.city_name,o.district_name,o.service_area,o.financial_code,u.customer_id,u.id as user_id,u.name as user_name,o.salesman_name,o.customer_service_name,o.is_proxy,o.product_name,o.original_price,o.refund_status,o.adjust_amount,o.package_remit_amount,o.wx_remit_amount,o.refund_amount,o.created_at,o.begin_service_time,o.complete_service_time,o.begin_service_time,cm.name as company_name,cd.name as department_name,cs.name as source_name,o.refund_explain,o.refund_remark,o.require_refund_amount,o.break_reason,vo.payment_amount,vo.status as vo_status,o.status as order_status,o.is_satisfaction') ->from(['o' => Order::tableName()]);

        $query -> leftJoin(['vo' => VirtualOrder::tableName()],'o.virtual_order_id=vo.id');
        $query -> leftJoin(['u' => User::tableName()],'vo.user_id=u.id');
        $query -> leftJoin(['c' => CrmCustomer::tableName()],'u.id=c.user_id');
        $query -> leftJoin(['cs' => Source::tableName()],'c.source=cs.id');
        $query -> leftJoin(['cm' => Company::tableName()],'o.company_id=cm.id');
        $query -> leftJoin(['cd' => CrmDepartment::tableName()],'o.salesman_department_id=cd.id');

        $count = $query->count('o.id');

        if (empty($count)) {
            Yii::$app->session->setFlash('error', '没有获取到任何订单记录！');
            return $this->redirect($url);
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');

        $header = ['订单号', '业务主体', '区域', '客户ID', '客户姓名', '业务员姓名',
            '客服姓名', '是否代客下单', '商品名称', '商品价格', '订单状态', '已付款金额','修改金额', '套餐优惠金额', '微信优惠金额', '退款金额', '支付状态', '下单时间', '开始服务日期', '完成服务日期', '服务工作日', '订单负责人所属公司', '订单负责人所属部门', '客户来源', '客户满意度', '财务明细编号'];

        $csv->insertOne($header);
        for($i = 0; $i < $batch ; $i++){
            set_time_limit(0);
            ini_set('memory_limit', '2048M');

            $models = $query->offset($i * $batchNum)->limit($batchNum)->all();

            foreach($models as $order){
                $created_at = empty($order['created_at']) ? '' : date("Y-m-d H:i:s",$order['created_at']);
                $begin_service_time = empty($order['begin_service_time']) ? '' :date("Y-m-d H:i:s",$order['begin_service_time']);
                $complete_service_time = empty($order['complete_service_time']) ? '' : date("Y-m-d H:i:s",$order['complete_service_time']);
                $area = $order['service_area'] ? $order['service_area'] : $order['province_name'] .'-'.$order['city_name'] .'-'.$order['district_name'];
                $vo_status = $this -> getOrderStatus($order['order_status']);

                $csv->insertOne([
                    "\t" . $order['sn'],
                    "\t" . $order['order_company_name'],
                    "\t" . $area,
                    "\t" . $order['customer_id'],
                    "\t" . $order['user_name'],
                    "\t" . $order['salesman_name'],
                    "\t" . $order['customer_service_name'],
                    "\t" . $order['is_proxy'] = 0 ? '否' : '是',
                    "\t" . $order['product_name'],
                    "\t" . $order['original_price'],
                    "\t" . $vo_status,
                    "\t" . (empty($order['payment_amount'])) ? $order['payment_amount'] : "",
                    "\t" . $order['adjust_amount'],
                    "\t" . (empty(floatval($order['package_remit_amount'])) ? $order['package_remit_amount'] : '-' . $order['package_remit_amount']),
                    "\t" . (empty(floatval($order['wx_remit_amount'])) ? $order['wx_remit_amount'] : '-' . $order['refund_amount']),
                    "\t" . (empty(floatval($order['refund_amount'])) ? $order['refund_amount'] : '-' . $order['refund_amount']),
                    "\t" . $this->getPayStatus($order['vo_status']) ,
                    "\t" . $created_at,
                    "\t" . $begin_service_time,
                    "\t" . $complete_service_time,
                    "\t" . $this->getServiceDays($order['complete_service_time'],$order['begin_service_time']),
                    "\t" . $order['company_name'],
                    "\t" . $order['department_name'],
                    // "\t" . $order->user->customer->getSourceName(),
                    "\t" . $order['source_name'],
                    "\t" . $this->getSatisfactionName($order['is_satisfaction']),
                    "\t" . (empty($order['financial_code'])) ? $order['financial_code'] : "",
                ]);
            }
        }

        $admin = Yii::$app->user->identity;
        $desc = '导出订单数据时间范围是' . (empty($searchModel->starting_time) ? date('Y-m-d H:i:s', time()) : ($searchModel->starting_time . '至' . $searchModel->end_time));
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION, $admin);
        $filename = (empty($searchModel->starting_time) ? date('YmdHis') : ($searchModel->starting_time . '-' . $searchModel->end_time)) . '_订单记录.csv';

        //记录操作日志
        AdministratorLog::logExport('订单',$count);
        Yii::$app->cache->set('export-' . Yii::$app->user->id, time(), 30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset, 'gbk//IGNORE', $csv);
    }

    //订单状态
    public function getOrderStatus($status){
        $arr = [
            '0' => '待付款',
            '1' => '待分配',
            '2' => '待服务',
            '3' => '服务中',
            '4' => '服务终止',
            '8' => '服务完成',
        ];
        return $arr[$status];
    }


    public function getPayStatus($status)
    {
        if($status==VirtualOrder::STATUS_PENDING_PAYMENT){
            return '待付款';
        }elseif ($status==VirtualOrder::STATUS_ALREADY_PAYMENT){
            return '已付款';
        }elseif ($status==VirtualOrder::STATUS_UNPAID){
            return '未付清';
        }elseif ($status==VirtualOrder::STATUS_BREAK_PAYMENT){
            return '已取消';
        }
        return null;
    }

    public  function getSatisfactionName($is_satisfaction)
    {
        $satisfaction = Order::getSatisfaction();
        if($is_satisfaction)
        {
            return $satisfaction[$is_satisfaction];
        }
        return null;
    }

    public function getServiceDays($complete_service_time,$begin_service_time)
    {
        if($complete_service_time&&$begin_service_time)
        {
            return  ceil(BC::div(BC::sub($complete_service_time,$begin_service_time,0),86400, 7));
        }
        return null;
    }


    private function getStatus($status)
    {
        if ($status == "break") {
            $status = Order::STATUS_BREAK_SERVICE;
            return (int)$status;
        } elseif ($status == "completed") {
            $status = Order::STATUS_COMPLETE_SERVICE;
            return (int)$status;
        } elseif ($status == "in-service") {
            $status = Order::STATUS_IN_SERVICE;
            return (int)$status;
        } elseif ($status == "pending-service") {
            $status = Order::STATUS_PENDING_SERVICE;
            return (int)$status;
        } elseif ($status == "pending-assign") {
            $status = Order::STATUS_PENDING_ALLOT;
            return (int)$status;
        } elseif ($status == "unpaid") {
            $status = Order::STATUS_UNPAID;
            return (int)$status;
        } elseif ($status == "pending-payment") {
            $status = Order::STATUS_PENDING_PAY;
            return (int)$status;
        } else if ($status == "timeout") {
            $status = 'warning';
            return $status;
        } else {
            return $status;
        }
    }

    public function actionGetRemarkList(){
        $data = OrderRemark::find()->select('any_value(creator_id) as id,any_value(creator_name) as name')->asArray() ->groupBy('name')->all();
        $arr = ['status' => 200, 'remarkList' => $data];
        return json_encode($arr);
    }
}