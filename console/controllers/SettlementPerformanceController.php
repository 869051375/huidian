<?php

namespace console\controllers;

use common\models\MonthPerformanceRank;
use common\models\MonthProfitRecord;
use common\models\OrderPerformanceCollect;
use yii\console\Controller;
use yii\data\ActiveDataProvider;
use yii\redis\Connection;

class SettlementPerformanceController extends Controller
{
    public function actionSettlement()
    {
        $record = MonthProfitRecord::getLastRecord();
        if(null == $record || $record->isPerformanceReady() || $record->isPerformanceFinish())
        {
            return '1';
        }

//        if(\Yii::$app->cache->exists("SettlementPerformanceActionSettlement")) {
//            return '1';
//        }
//        \Yii::$app->cache->add("SettlementPerformanceActionSettlement", '1', 600);

        /** @var Connection $redis */
        $redis = \Yii::$app->get('redis');

        $v=$redis->rpop('SettlementPerformanceActionSettlementRedis');

        if(!$v){
            return '1';
        }

        $time = time();
        $data = [];
        $query = OrderPerformanceCollect::find()->where(['between', 'performance_time', $record->performance_start_time, $time]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $count = $dataProvider->totalCount;
        $batchNum = 100;
        $batch = ceil($count / $batchNum);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var OrderPerformanceCollect[] $models */
            $models = $dataProvider->query->offset($i * $batchNum)->limit($batchNum)->all();
            foreach($models as $model)
            {
                $topDepartment = $model->department->getTopDepartment();
                $data[] = [
                    'company_id' => $model->company_id,
                    'administrator_id' => $model->administrator_id,
                    'administrator_name' => $model->administrator_name,
                    'top_department_id' => $topDepartment->id,
                    'top_department_name' => $topDepartment->name,
                    'department_id' => $model->department_id,
                    'department_name' => $model->department_name,
                    'department_path' => $model->department_path,
                    'year' => $model->year,
                    'month' => $model->month,
                    'calculated_performance' => $model->order_amount,
                    'performance_reward' => $model->total_performance_amount,
                ];
            }
        }
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            \Yii::$app->db->createCommand()->batchInsert(MonthPerformanceRank::tableName(), [
                'company_id',
                'administrator_id',
                'administrator_name',
                'top_department_id',
                'top_department_name',
                'department_id',
                'department_name',
                'department_path',
                'year',
                'month',
                'calculated_performance',
                'performance_reward'
            ], $data)->execute();

            $record->is_settlement_performance = MonthProfitRecord::SETTLEMENT_PERFORMANCE_FINISH;
            $record->performance_end_time = $time;
            $record->save(false);
//            \Yii::$app->cache->delete("SettlementPerformanceActionSettlement");
            $t->commit();
        }
        catch (\Exception $e)
        {
            $t->rollBack();
//            \Yii::$app->cache->delete("SettlementPerformanceActionSettlement");
            throw $e;
        }
        return '0';
    }
}
