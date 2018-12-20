<?php

namespace console\controllers;

use common\models\MonthProfitRecord;
use common\models\OrderPerformanceCollect;
use common\models\PerformanceStatistics;
use common\utils\BC;
use yii\console\Controller;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * 订单业绩计算汇总
 * Class OrderPerformanceCollectController
 * @package console\controllers
 */
class OrderPerformanceCollectController extends Controller
{
    public function actionCollect()
    {
        if(\Yii::$app->cache->exists("OrderPerformanceCollectActionCollect")) {
            return '1';
        }

        \Yii::$app->cache->add("OrderPerformanceCollectActionCollect", '1', 600);
        $data = [];
        $time = time();
        $lastRecord = MonthProfitRecord::getLastFinishUnSettlementRecord();
        $record = MonthProfitRecord::getLastRecord();
        $year = $record->isPerformanceFinish() ? $record->getNextMonth()['year'] : $record->year;
        $month = $record->isPerformanceFinish() ? $record->getNextMonth()['month'] : $record->month;
        $query = PerformanceStatistics::find()->where(['between', 'created_at', $lastRecord->performance_end_time+1, $time]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);
        $count = $dataProvider->totalCount;
        $batchNum = 100;
        $batch = ceil($count / $batchNum);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var PerformanceStatistics[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach($models as $model)
            {
                if(isset($data[$model->administrator_id]))
                {
                    $d = $data[$model->administrator_id];
                    $data[$model->administrator_id] = ArrayHelper::merge($d,[
                        'order_amount' => BC::add($d['order_amount'],$model->calculated_performance),//参与计算订单业绩提成收入
                        'correct_amount' => $model->type == PerformanceStatistics::TYPE_CORRECT ? BC::add($d['correct_amount'],$model->performance_reward) : $d['correct_amount'],//更正订单业绩提成收入
                        'ladder_amount' => $model->algorithm_type == PerformanceStatistics::ALGORITHM_GENERAL && $model->type == PerformanceStatistics::TYPE_GENERAL ? BC::add($d['ladder_amount'],$model->performance_reward) : $d['ladder_amount'],//阶梯算法订单业绩提成收入
                        'fix_point_amount' => $model->algorithm_type == PerformanceStatistics::ALGORITHM_POINT && $model->type == PerformanceStatistics::TYPE_GENERAL ? BC::add($d['fix_point_amount'],$model->performance_reward) : $d['fix_point_amount'],//固定提点订单业绩提成收入
                        'total_performance_amount' => BC::add($d['total_performance_amount'],$model->performance_reward),//订单业绩提成总收入
                        'performance_time' => $time,//业绩更新时间戳
                    ]);
                }
                else
                {
                    $data[$model->administrator_id] = [
                        'company_id' => $model->administrator->company_id,
                        'administrator_id' => $model->administrator_id,
                        'administrator_name' => $model->administrator_name,
                        'title' => $model->administrator->title,
                        'department_id' => $model->department_id,
                        'department_name' => $model->department_name,
                        'department_path' => $model->administrator->department->path,
                        'year' => $year,
                        'month' => $month,
                        'order_amount' => $model->calculated_performance,//参与计算订单业绩提成收入
                        'correct_amount' => $model->type == PerformanceStatistics::TYPE_CORRECT ? $model->performance_reward  : 0,//更正订单业绩提成收入
                        'ladder_amount' => $model->algorithm_type == PerformanceStatistics::ALGORITHM_GENERAL && $model->type == PerformanceStatistics::TYPE_GENERAL ? $model->performance_reward  : 0,//阶梯算法订单业绩提成收入
                        'fix_point_amount' => $model->algorithm_type == PerformanceStatistics::ALGORITHM_POINT && $model->type == PerformanceStatistics::TYPE_GENERAL ? $model->performance_reward  : 0,//固定提点订单业绩提成收入
                        'total_performance_amount' => $model->performance_reward,//订单业绩提成总收入
                        'performance_time' => $time,//业绩更新时间戳
                    ];
                }
            }
        }
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            OrderPerformanceCollect::deleteAll(['year' => $year,'month' => $month]);
            \Yii::$app->db->createCommand()->batchInsert(OrderPerformanceCollect::tableName(), [
                'company_id',
                'administrator_id',
                'administrator_name',
                'title',
                'department_id',
                'department_name',
                'department_path',
                'year',
                'month',
                'order_amount',
                'correct_amount',
                'ladder_amount',
                'fix_point_amount',
                'total_performance_amount',
                'performance_time',
            ], $data)->execute();
            $t->commit();
            \Yii::$app->cache->delete("OrderPerformanceCollectActionCollect");
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            \Yii::$app->cache->delete("OrderPerformanceCollectActionCollect");
            throw $e;
        }
    }
}
