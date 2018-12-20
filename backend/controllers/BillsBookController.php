<?php

namespace backend\controllers;

use backend\models\BillsDepartmentSearch;
use backend\models\BillsPersonalSearch;
use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\ExpectedProfitSettlementDetail;
use common\models\MonthProfitRecord;
use common\models\OrderCalculateCollect;
use common\models\OrderPerformanceCollect;
use common\models\PerformanceStatistics;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * Created by PhpStorm.
 * User: jia yong bo
 * Date: 2018/6/1
 * Time: 下午14:21
 */

class BillsBookController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['bills-book/index'],
                    ],
                    [
                        'actions' => ['department'],
                        'allow' => true,
                        'roles' => ['bills-book/department'],
                    ],
                    [
                        'actions' => ['detail','profit-detail'],
                        'allow' => true,
                        'roles' => ['bills-book/detail'],
                    ],
                    [
                        'actions' => ['department-detail','department-profit-detail'],
                        'allow' => true,
                        'roles' => ['bills-book/department-detail'],
                    ],
                ],
            ],
        ];
    }

    //个人账单
    public function actionIndex()
    {
        $searchModel = new BillsPersonalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index',['provider' => $provider,'searchModel' => $searchModel]);
    }

    //部门账单
    public function actionDepartment()
    {
        $searchModel = new BillsDepartmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $query = $dataProvider->query;
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('department',['provider' => $provider,'searchModel' => $searchModel]);
    }

    //个人账簿详情
    public function actionDetail($id,$date = null)
    {
        $record = MonthProfitRecord::getLastRecord();
        $date = $date ? $date : date('Y-m',time());
        $year =  mb_substr($date,0,4);
        $month =  mb_substr($date,5,2);
        //预计利润部分
        $orderCalculateCollect = OrderCalculateCollect::find()
            ->where(['administrator_id' => $id])
            ->andWhere(['year' => $year,'month' => $month])->limit(1)->one();
        //业绩部分
        $orderPerformanceCollect = OrderPerformanceCollect::find()
            ->where(['administrator_id' => $id])
            ->andWhere(['year' => $year,'month' => $month])->limit(1)->one();
        //预计利润总结算记录
        /** @var MonthProfitRecord $profitRecord */
        $profitRecord = MonthProfitRecord::find()->where(['year' => $year,'month' => $month])->limit(1)->one();
        if($profitRecord && $profitRecord->range_start_time)
        {
            $start_time = $profitRecord->range_start_time;
        }
        else
        {
            $start_time = $record && $record->getNextMonth()['month'] == $month ? $record->range_end_time + 1 : time();
        }
        $end_time = $profitRecord && $profitRecord->range_end_time ?  $profitRecord->range_end_time : time();
        //预计利润计算历史
        $query = ExpectedProfitSettlementDetail::find()->where(['administrator_id' => $id])
            ->andWhere(['type' => ExpectedProfitSettlementDetail::TYPE_GENERAL])
            ->andWhere(['between','created_at',$start_time,$end_time]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        //预计利润金额更正计算历史
        $correctProfit = ExpectedProfitSettlementDetail::find()->where(['administrator_id' => $id])->andWhere(['type' => ExpectedProfitSettlementDetail::TYPE_CORRECT])
            ->andWhere(['between','created_at',$start_time,$end_time])->all();
        $model = $this->findModel($id);
        return $this->render('detail',[
            'profitRecord' => $profitRecord,
            'year' => $year,
            'month' => $month,
            'model' => $model,
            'orderCalculateCollect' => $orderCalculateCollect,
            'orderPerformanceCollect' => $orderPerformanceCollect,
            'provider' => $provider,
            'correctProfit' => $correctProfit,
        ]);
    }

    //个人账簿业绩详情
    public function actionProfitDetail($id,$date = null)
    {
        $record = MonthProfitRecord::getLastRecord();
        $date = $date ? $date : date('Y-m',time());
        $year =  mb_substr($date,0,4);
        $month =  mb_substr($date,5,2);
        //预计利润部分
        $orderCalculateCollect = OrderCalculateCollect::find()
            ->where(['administrator_id' => $id])
            ->andWhere(['year' => $year,'month' => $month])->limit(1)->one();
        //业绩部分
        $orderPerformanceCollect = OrderPerformanceCollect::find()
            ->where(['administrator_id' => $id])
            ->andWhere(['year' => $year,'month' => $month])->limit(1)->one();
        //预计利润总结算记录
        /** @var MonthProfitRecord $profitRecord */
        $profitRecord = MonthProfitRecord::find()->where(['year' => $year,'month' => $month])->limit(1)->one();
        $start_time = $profitRecord ? $profitRecord->performance_start_time : ($record ? $record->range_end_time + 1 : 0);
        $end_time = $profitRecord ? ($profitRecord->performance_end_time ? $profitRecord->performance_end_time : time()) : ($record && $record->getNextMonth()['month'] != $month ? 0 : time());
        //业绩计算历史
        $query = PerformanceStatistics::find()->where(['administrator_id' => $id])
            ->andWhere(['type' => PerformanceStatistics::TYPE_GENERAL])
            ->andWhere(['between','created_at',$start_time,$end_time]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        //业绩更正计算历史
        $correctPerformance = PerformanceStatistics::find()->where(['administrator_id' => $id])->andWhere(['type' => PerformanceStatistics::TYPE_CORRECT])
            ->andWhere(['between','created_at',$start_time,$end_time])->all();
        $model = $this->findModel($id);
        return $this->render('profit-detail',[
            'profitRecord' => $profitRecord,
            'year' => $year,
            'month' => $month,
            'model' => $model,
            'orderCalculateCollect' => $orderCalculateCollect,
            'orderPerformanceCollect' => $orderPerformanceCollect,
            'provider' => $provider,
            'correctPerformance' => $correctPerformance,
        ]);
    }

    //部门账簿详情
    public function actionDepartmentDetail($did,$date = null)
    {
        $record = MonthProfitRecord::getLastRecord();
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $model = $this->findDepartmentModel($did);
        $date = $date ? $date : date('Y-m',time());
        $year =  mb_substr($date,0,4);
        $month =  mb_substr($date,5,2);
        //预计利润部分
        $orderCalculateCollect = OrderCalculateCollect::getByDepartment($model,$year,$month,$administrator->company_id);
        $orderPerformanceCollect = OrderPerformanceCollect::getByDepartment($model,$year,$month,$administrator->company_id);

        //预计利润总结算记录
        /** @var MonthProfitRecord $profitRecord */
        $profitRecord = MonthProfitRecord::find()->where(['year' => $year,'month' => $month])->limit(1)->one();
        if($profitRecord && $profitRecord->range_start_time)
        {
            $start_time = $profitRecord->range_start_time;
        }
        else
        {
            $start_time = $record && $record->getNextMonth()['month'] == $month ? $record->range_end_time + 1 : time();
        }
        $end_time = $profitRecord && $profitRecord->range_end_time ?  $profitRecord->range_end_time : time();
        //预计利润计算历史
        $query = ExpectedProfitSettlementDetail::find()->where(['department_id' => $did])
            ->andWhere(['or',['type' => ExpectedProfitSettlementDetail::TYPE_GENERAL],['type' => ExpectedProfitSettlementDetail::TYPE_KNOT]])
            ->andWhere(['between','created_at',$start_time,$end_time]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        //预计利润金额更正计算历史
        $correctProfit = ExpectedProfitSettlementDetail::find()->where(['department_id' => $did])->andWhere(['type' => ExpectedProfitSettlementDetail::TYPE_CORRECT])
            ->andWhere(['between','created_at',$start_time,$end_time])->all();
        return $this->render('department-detail',[
            'profitRecord' => $profitRecord,
            'year' => $year,
            'month' => $month,
            'model' => $model,
            'orderCalculateCollect' => $orderCalculateCollect,
            'orderPerformanceCollect' => $orderPerformanceCollect,
            'provider' => $provider,
            'correctProfit' => $correctProfit,
        ]);
    }

    //部门账簿业绩详情
    public function actionDepartmentProfitDetail($did,$date = null)
    {
        $record = MonthProfitRecord::getLastRecord();
        $model = $this->findDepartmentModel($did);
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $date = $date ? $date : date('Y-m',time());
        $year =  mb_substr($date,0,4);
        $month =  mb_substr($date,5,2);
        $orderCalculateCollect = OrderCalculateCollect::getByDepartment($model,$year,$month,$administrator->company_id);
        $orderPerformanceCollect = OrderPerformanceCollect::getByDepartment($model,$year,$month,$administrator->company_id);

        /** @var MonthProfitRecord $profitRecord */
        $profitRecord = MonthProfitRecord::find()->where(['year' => $year,'month' => $month])->limit(1)->one();
        $start_time = $profitRecord ? $profitRecord->performance_start_time : ($record ? $record->range_end_time + 1 : 0);
        $end_time = $profitRecord ? ($profitRecord->performance_end_time ? $profitRecord->performance_end_time : time()) : ($record && $record->getNextMonth()['month'] != $month ? 0 : time());

        //业绩计算历史
        $query = PerformanceStatistics::find()->where(['department_id' => $did])
            ->andWhere(['type' => PerformanceStatistics::TYPE_GENERAL])
            ->andWhere(['between','created_at',$start_time,$end_time]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        //业绩更正计算历史
        $correctPerformance = PerformanceStatistics::find()->where(['department_id' => $did])->andWhere(['type' => PerformanceStatistics::TYPE_CORRECT])
            ->andWhere(['between','created_at',$start_time,$end_time])->all();
        return $this->render('department-profit-detail',[
            'profitRecord' => $profitRecord,
            'year' => $year,
            'month' => $month,
            'model' => $model,
            'orderCalculateCollect' => $orderCalculateCollect,
            'orderPerformanceCollect' => $orderPerformanceCollect,
            'provider' => $provider,
            'correctPerformance' => $correctPerformance,
        ]);
    }

    private function findModel($id)
    {
        $model = Administrator::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的人员');
        }
        return $model;
    }

    private function findDepartmentModel($did)
    {
        $model = CrmDepartment::findOne($did);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的部门');
        }
        return $model;
    }

}