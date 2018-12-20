<?php

namespace console\controllers;

use common\models\MonthProfitRecord;
use common\models\OrderCalculateCollect;
use common\models\PersonMonthProfit;
use common\models\RewardProportionVersion;
use yii\console\Controller;
use yii\data\ActiveDataProvider;

class ExpectedProfitSettlementController extends Controller
{
    public function actionSettlement()
    {
        $record = MonthProfitRecord::getDoingRecord();
        if(null == $record)
        {
            return '1';
        }

        if(\Yii::$app->cache->exists("actionSettlement")) {
            return '1';
        }

        \Yii::$app->cache->add("actionSettlement", '1', 600);

        $data = [];
        $query = OrderCalculateCollect::find()->where(['between', 'expect_profit_time', $record->range_start_time, $record->range_end_time]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $count = $dataProvider->totalCount;
        $batchNum = 100;
        $batch = ceil($count / $batchNum);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var OrderCalculateCollect[] $models */
            $models = $dataProvider->query->offset($i * $batchNum)->limit($batchNum)->all();
            foreach($models as $model)
            {
                $topDepartment = $model->department->getTopDepartment();
                $rate = RewardProportionVersion::getVersionRule($model->department_id,$model->order_expected_amount);
                $data[] = [
                    'company_id' => $model->company_id,
                    'administrator_id' => $model->administrator_id,
                    'administrator_name' => $model->administrator_name,
                    'title' => $model->title,
                    'top_department_id' => isset($topDepartment->id) ? $topDepartment->id : 0,
                    'top_department_name' => isset($topDepartment->name) ? $topDepartment->name : '',
                    'department_path' => $model->department_path,
                    'department_id' => $model->department_id,
                    'department_name' => $model->department_name,
                    'year' => $model->year,
                    'month' => $model->month,
                    'order_amount' => $model->total_order_price,
                    'order_count' => $model->order_count,
                    'customer_count' => $model->total_customer_count,
                    'new_customer_count' => $model->new_customer_count,
                    'refund_amount' => $model->refund_order_count,
                    'receivable' => 0,
                    'total_cost' => 0,
                    'reward_proportion' => $rate,
                    'expected_profit' => $model->order_expected_amount,
                    'already_payment' => 0,
                    'correct_front_expected_amount' => $model ->correct_front_expected_amount,
                ];
            }
        }

        $t = \Yii::$app->db->beginTransaction();
        try
        {
            \Yii::$app->db->createCommand()->batchInsert(PersonMonthProfit::tableName(), [
                'company_id',
                'administrator_id',
                'administrator_name',
                'title',
                'top_department_id',
                'top_department_name',
                'department_path',
                'department_id',
                'department_name',
                'year',
                'month',
                'order_amount',
                'order_count',
                'customer_count',
                'new_customer_count',
                'refund_amount',
                'receivable',
                'total_cost',
                'reward_proportion',
                'expected_profit',
                'already_payment',
                'correct_front_expected_amount',
            ], $data)->execute();

            $record->status = MonthProfitRecord::STATUS_SETTLEMENT_FINISH;
            $record->save(false);
            $t->commit();
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            \Yii::$app->cache->delete("actionSettlement");
            throw $e;
        }
        \Yii::$app->cache->delete("actionSettlement");
        return '0';
    }
}
