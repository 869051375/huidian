<?php

namespace console\controllers;

use console\record\DailyRecord;
use console\record\OrderStatusRecord;
use console\record\ProductDetailRecord;
use console\record\ProductStatisticsRecord;
use yii\console\Controller;

class StatisticsController extends Controller
{
    public function actionRecord($date = null)
    {
        // 必须第一步执行
        $record = new ProductDetailRecord();
        $record->record($date);

        // 必须在 ProductDetailRecord 后执行
        $record = new ProductStatisticsRecord();
        $record->record($date);

        $record = new DailyRecord();
        $record->record($date);

        $record = new OrderStatusRecord();
        $record->record($date);
    }
}
