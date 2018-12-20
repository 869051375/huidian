<?php

namespace backend\controllers;

use backend\models\FinancialStatementsSearch;
use common\models\FundsRecord;
use common\models\Order;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FinancialStatementsController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [

            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['info'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list','info'],
                        'allow' => true,
                        'roles' => ['financial-statements/list'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['financial-statements/export'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $searchModel = new FinancialStatementsSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInfo()
    {
        $order_sn = Yii::$app->request->post('id');
        /**@var $model Order **/
        $model = $this->findModel($order_sn);
        $data['product_name'] = $model->product_name;
        $data['area'] = $model->getArea();
        $data['company_name'] = $model->company_name;
        $data['clerk_name'] = empty($model->clerk_name)?'暂未分配':$model->clerk_name;
        $data['salesman_name'] = empty($model->salesman_name)?'暂未分配':$model->salesman_name;
        $data['status'] = $model->getStatus();
        return ['status'=>200,'message'=>$data];
    }

    private function findModel($order_sn)
    {
        $model = Order::find()->where(['sn'=>$order_sn])->one();
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的订单!');
        }
        return $model;
    }

    public function actionExport()
    {
        $url = Yii::$app->request->getReferrer();
        $export_code = Yii::$app->cache->get('fs-export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        $batchNum = 100;
        $searchModel = new FinancialStatementsSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $count = $dataProvider->totalCount;
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['交易时间','下单方式','回款时间','收款公司','财务明细编号','用户ID','客户ID','客户昵称','客户手机号','付款方式','线下付款方式','交易平台流水号',
            '虚拟订单号','子订单号','公司名称','商品名称','所属地区','业务人员','交易类型','交易金额'];
        $csv->insertOne($header);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var FundsRecord[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $row)
            {
                if (!empty($row->order_id)){
                    $obj_order = new Order();
                    $order_data = $obj_order->find()->where(['in','id',explode(",",$row->order_id)])->all();
                    $salesman_name = '';
                    $product_name = '';
                    foreach ($order_data as $k){
                        $salesman_name .= $k->salesman_name.',';
                        $product_name .= $k->product_name.',';
                    }
                    $row->order->salesman_name = substr($salesman_name, 0, -1);
                    $row->order->product_name = substr($product_name, 0, -1);
                }
                $csv->insertOne([
                    "\t".Yii::$app->formatter->asDatetime($row->trade_time),
                    "\t".$searchModel->getIsProxy($row->orders),
                    empty($row->receipt) ? '' : "\t".date('Y-m-d',$row->receipt->receipt_date),
                    empty($row->receipt) ? '' : "\t".$row->receipt->receipt_company,
                    empty($row->receipt) ? '' : "\t".$row->receipt->financial_code,
                    "\t".$row->user->id,
                    "\t".$row->user->customer_id,
                    "\t".$row->user->name,
                    "\t".$row->user->phone,
                    "\t".$row->getPayPlatformName(),
                    "\t".$row->getPayMethodName(),
                    "\t".$row->trade_no,
                    "\t".$row->virtual_sn,
                    "\t".$row->order_sn_list,
                    empty($row->order)?'': "\t".$row->order->company_name,
                    empty($row->order)?'': "\t".$row->order->product_name,
                    empty($row->order)?'': "\t".$row->order->getArea(),
                    empty($row->order)?'': "\t".$row->order->salesman_name,
                    "\t".$row->getOrientation(),
                    "\t".$row->amount
                ]);
            }
        }

        $filename = (empty($searchModel->starting_time) ? date('YmdHis') : ($searchModel->starting_time.'-'.$searchModel->end_time)).'_交易流水.csv';
        Yii::$app->cache->set('fs-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }
}