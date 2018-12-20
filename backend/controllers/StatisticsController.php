<?php

namespace backend\controllers;

use backend\models\RankingSearch;
use backend\models\SummarySearch;
use backend\models\TransactionSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

class StatisticsController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

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
                'only' => ['user-source'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list','user-source'],
                        'allow' => true,
                        'roles' => ['statistics/list'],
                    ],
                    [
                        'actions' => ['this-week','yesterday','this-month','this-year'],
                        'allow' => true,
                        'roles' => ['statistics/this-week'],
                    ],
                    [
                        'actions' => ['yesterday-summary','week-summary','month-summary'],
                        'allow' => true,
                        'roles' => ['statistics/week-summary'],
                    ],
                    [
                        'actions' => ['transaction-ranking','price-ranking','visitor-ranking'],
                        'allow' => true,
                        'roles' => ['statistics/transaction-ranking'],
                    ],
                ],
            ],
        ];
    }

    //统计分析
    public function actionList()
    {
        return $this->render('list');
    }

    //-------------交易分析-----------
    //昨天
    public function actionYesterday()
    {
        return $this->searchUser('1');
    }
    //本周
    public function actionThisWeek()
    {
        return $this->searchUser('2');
    }
    //本月
    public function actionThisMonth()
    {
        return $this->searchUser('3');
    }
    //本年
    public function actionThisYear()
    {
        return $this->searchUser('4');
    }

    private function searchUser($status)
    {
        $searchModel = new TransactionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$status);
        $beforeData = $searchModel->beforeData($status);
        return $this->render('transaction', [
            'searchModel' => $searchModel,
            'beforeData' => $beforeData,
            'dataProvider' => $dataProvider,
        ]);
    }

    //-------------交易概括--------------

    public function actionYesterdaySummary()
    {
        return $this->searchProduct('1');
    }

    public function actionWeekSummary()
    {
        return $this->searchProduct('2');
    }

    public function actionMonthSummary()
    {
        return $this->searchProduct('3');
    }

    private function searchProduct($status)
    {
        $searchModel = new SummarySearch();
        //商品统计明细表数据
        $searchModel->search(Yii::$app->request->queryParams);
        //商品各区域交易数量
        $areaData = $searchModel->areaTrading($status,'pay_success_num');
        //商品各区域交易额
        $transaction_amount = $searchModel->areaTrading($status,'total_price');
        //商品交易数量排行top10
        $transaction_top = $searchModel->productTopData($status,'pay_success_no');
        //商品访问量排行top10
        $visitors_top = $searchModel->productTopData($status,'product_visitors');
        //上周，上月的商品统计主表数据
        $beforeData = $searchModel->beforeData($status);
        //商品统计主表数据
        $productStatisticsData = $searchModel->productStatisticsData($status);
        //折线图数据
        //---支付金额---
        $chart_total_amount = $searchModel->chartData($status,'total_price',2,1);
        //---商品访客数---
        $chart_product_visitors = $searchModel->chartData($status,'product_visitors',1,1);
        //---支付成功的商品数---
        $chart_pay_success = $searchModel->chartData($status,'pay_success_no',1,1);
        //---被访问的商品种类数---
        $chart_species = $searchModel->chartData($status,'product_visitors',3,1);
        //---支付成功商品种类数---
        $chart_pay_success_no = $searchModel->chartData($status,'pay_success_no',3,1);
        //---下单转化率---
        $chart_order_rate = $searchModel->chartData($status,1,4,1);
        //---支付转化率---
        $chart_pay_rate = $searchModel->chartData($status,1,5,1);
        //---下单支付转化率---
        $chart_order_pay_rate = $searchModel->chartData($status,1,6,1);
        //---折线图X轴时间---
        $chart_time = $searchModel->chartData($status,'time',0);
        //订单状态饼图的数据
        $pie_chart_data = $searchModel->orderStatus($status);
        return $this->render('summary', [
            'searchModel' => $searchModel,
            'productStatisticsData' => $productStatisticsData,
            'beforeData' => $beforeData,
            'areaData' => $areaData,
            'transaction_amount' => $transaction_amount,
            'chart_total_amount' => $chart_total_amount,
            'chart_product_visitors' => $chart_product_visitors,
            'chart_species' => $chart_species,
            'chart_pay_success_no' => $chart_pay_success_no,
            'chart_pay_success' => $chart_pay_success,
            'chart_order_rate' => $chart_order_rate,
            'chart_pay_rate' => $chart_pay_rate,
            'chart_order_pay_rate' => $chart_order_pay_rate,
            'chart_time' => $chart_time,
            'pie_chart_data' => $pie_chart_data,
            'transaction_top' => $transaction_top,
            'visitors_top' => $visitors_top,
        ]);
    }

    //交易排行
    public function actionTransactionRanking($status = 2)
    {
        $searchModel = new RankingSearch();
        $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = $searchModel->productTopData($status,'pay_success_no');
        return $this->render('ranking',[
            'dataProvider'=>$dataProvider,
            'searchModel' => $searchModel,
            ]);
    }

    //交易金额排行
    public function actionPriceRanking($status = 2)
    {
        $searchModel = new RankingSearch();
        $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = $searchModel->productTopData($status,'total_amount');
        return $this->render('ranking',[
            'dataProvider'=>$dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    //访客排行
    public function actionVisitorRanking($status = 2)
    {
        $searchModel = new RankingSearch();
        $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = $searchModel->productTopData($status,'product_visitors');
        return $this->render('ranking',[
            'dataProvider'=>$dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

}