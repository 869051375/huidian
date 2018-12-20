<?php
namespace backend\controllers;

use backend\models\InvoiceSearch;
use common\models\Invoice;
use common\models\Order;
use common\models\User;
use common\models\VirtualOrder;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class InvoiceListController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['invoice-list/list'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['order-list/export'],
                    ],
                    [
                        'actions' => ['can-invoice-list'],
                        'allow' => true,
                        'roles' => ['invoice-action/apply-invoice'],
                    ],
                ],
            ],
        ];
    }


    public function actionList($status = null)
    {
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

    public function actionCanInvoiceList($id, $status = null)
    {
        /** @var User $user */
        $user = $this->findModel($id);
        //虚拟订单必须已付款（先服务后付费的也不允许开发票）
        $query = VirtualOrder::find()->alias('vo')->andWhere(['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT]);
        $query->innerJoinWith(['orders o']);
        $query->andWhere(['o.user_id' => $user->id, 'o.is_invoice' => Order::INVOICE_DISABLED]);
        $query->andWhere(['in', 'o.status', [
            Order::STATUS_PENDING_ALLOT,
            Order::STATUS_PENDING_SERVICE,
            Order::STATUS_IN_SERVICE,
            Order::STATUS_COMPLETE_SERVICE,
        ]]);
        $query->andWhere(['in', 'o.refund_status', [
            Order::REFUND_STATUS_NO,
            Order::REFUND_STATUS_REFUNDED,
        ]]);
        $query->andWhere(['o.is_cancel' => Order::CANCEL_DISABLED]);
        $query->andWhere(['or', ['complete_service_time' => 0], ['>', 'complete_service_time', time()-90*86400]]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                //'pageSize' => 20,
            ],
        ]);
        return $this->render('can-invoice-list', [
            'dataProvider' => $dataProvider,
            'status' => $status,
            'user' => $user,
        ]);
    }

    public function actionExport($status = null)
    {
        $url = Yii::$app->request->getReferrer();
        $export_code = Yii::$app->cache->get('invoice-export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;
        $searchModel = new InvoiceSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        if(!$searchModel->validate())
        {
            $errors = $searchModel->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
            return $this->redirect($url);
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$status);
        $count = $dataProvider->totalCount;
        if(empty($count))
        {
            Yii::$app->session->setFlash('error', '没有获取到任何发票记录！');
            return $this->redirect($url);
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['申请时间','商品名称','订单号','订单状态','客服','发票抬头','发票金额', '收件人','手机号','收件地址','状态'];
        $csv->insertOne($header);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var Invoice[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $row)
            {
                $csv->insertOne([
                    "\t" . Yii::$app->formatter->asDatetime($row->created_at),
                    "\t" . $row->order->product_name,
                    "\t" . $row->order_sn,
                    "\t" . $row->order->getStatusName(),
                    "\t" . $row->order->customer_service_name,
                    "\t" . $row->invoice_title,
                    "\t" . (empty(floatval($row->invoice_amount)) ? $row->order->getInvoiceAmount() : $row->invoice_amount),
                    "\t" . $row->addressee,
                    "\t" . $row->phone,
                    "\t" . $row->address,
                    "\t" . $row->getStatusName(),
                ]);
            }
        }
        $filename =  date('YmdHis').'_发票记录.csv';

        Yii::$app->cache->set('invoice-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }

    private function findModel($id)
    {
        $model = User::findOne($id);

        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的数据！');
        }
        return $model;
    }
}