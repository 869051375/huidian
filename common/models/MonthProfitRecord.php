<?php

namespace common\models;

/**
 * This is the model class for table "{{%month_profit_record}}".
 *
 * @property integer $id
 * @property integer $year
 * @property integer $month
 * @property integer $range_start_time
 * @property integer $range_end_time
 * @property integer $performance_start_time
 * @property integer $performance_end_time
 * @property integer $is_settlement_performance
 * @property integer $creator_id
 * @property integer $status
 * @property string $creator_name
 * @property integer $created_at
 */
class MonthProfitRecord extends \yii\db\ActiveRecord
{
    const STATUS_READY = 0;
    const STATUS_DOING_SETTLEMENT = 1;
    const STATUS_SETTLEMENT_FINISH = 2;

    const SETTLEMENT_PERFORMANCE_NO = 0;
    const SETTLEMENT_PERFORMANCE_DOING = 1;
    const SETTLEMENT_PERFORMANCE_FINISH = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%month_profit_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'month', 'range_start_time', 'range_end_time','performance_start_time','performance_end_time', 'is_settlement_performance','creator_id', 'status', 'created_at'], 'integer'],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'year' => 'Year',
            'month' => 'Month',
            'range_start_time' => 'Range Start Time',
            'range_end_time' => 'Range End Time',
            'performance_start_time' => 'Performance Start Time',
            'performance_end_time' => 'Performance End Time',
            'is_settlement_performance' => 'Is Settlement Performance',
            'creator_id' => 'Creator ID',
            'status' => 'Status',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 获得当前是准备状态的记录
     * @return MonthProfitRecord|null
     */
    public static function getReadyRecord()
    {
        /** @var MonthProfitRecord|null $model */
        $model = static::find()->where(['status' => MonthProfitRecord::STATUS_READY])->limit(1)->one();
        return $model;
    }

    /**
     * 获得当前正在执行结算的记录
     * @return MonthProfitRecord|null
     */
    public static function getDoingRecord()
    {
        /** @var MonthProfitRecord|null $model */
        $model = static::find()->where(['status' => MonthProfitRecord::STATUS_DOING_SETTLEMENT])->limit(1)->one();
        return $model;
    }

    /**
     * 获得最后一个结算完成的记录
     * @return MonthProfitRecord|null
     */
    public static function getLastFinishRecord()
    {
        /** @var MonthProfitRecord|null $model */
        $model = static::find()->where(['status' => MonthProfitRecord::STATUS_SETTLEMENT_FINISH])->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])->limit(1)->one();
        return $model;
    }

    /**
     * 获得最后一个结算业绩的记录
     * @return MonthProfitRecord|null
     */
    public static function getLastFinishUnSettlementRecord()
    {
        /** @var MonthProfitRecord|null $model */
        $model = static::find()->where(['is_settlement_performance' => MonthProfitRecord::SETTLEMENT_PERFORMANCE_FINISH])->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])->limit(1)->one();
        return $model;
    }

    public static function getLastFinishUnSettlementRecord2($id)
    {
        /** @var MonthProfitRecord|null $model */
        $model = static::find()->where(['id' => $id])->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])->limit(1)->one();
//        $model = static::find()->where(['is_settlement_performance' => MonthProfitRecord::SETTLEMENT_PERFORMANCE_FINISH])->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])->limit(1)->one();
        return $model;
    }


    /**
     * 获得所有结算完成的记录
     * @return MonthProfitRecord[]
     */
    public static function getAllFinishRecord()
    {
        /** @var MonthProfitRecord[] $models */
        $models = static::find()->where(['status' => MonthProfitRecord::STATUS_SETTLEMENT_FINISH])
            ->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])->all();
        return $models;
    }

    /**
     * 获得最后一次结算的记录
     * @return null|MonthProfitRecord
     */
    public static function getLastRecord()
    {
        /** @var MonthProfitRecord|null $model */
        $model = static::find()->orderBy(['id' => SORT_DESC])->limit(1)->one();
        return $model;
    }

    public function isReady()
    {
        return $this->status == MonthProfitRecord::STATUS_READY;
    }

    public function isDoing()
    {
        return $this->status == MonthProfitRecord::STATUS_DOING_SETTLEMENT;
    }

    public function isFinish()
    {
        return $this->status == MonthProfitRecord::STATUS_SETTLEMENT_FINISH;
    }

    //是否有未结算业绩
    public function isPerformanceReady()
    {
        return $this->is_settlement_performance == self::SETTLEMENT_PERFORMANCE_NO;
    }

    //是否准备结算业绩
    public function isPerformanceDoing()
    {
        return $this->is_settlement_performance == self::SETTLEMENT_PERFORMANCE_DOING;
    }

    //是否结算完成
    public function isPerformanceFinish()
    {
        return $this->is_settlement_performance == self::SETTLEMENT_PERFORMANCE_FINISH;
    }

    public function getNextMonth()
    {
        if($this->month == 12)
        {
            $year = $this->year + 1;
            $month = 1;
        }
        else
        {
            $year = $this->year;
            $month = $this->month + 1;
        }
        return [
            'year' => $year,
            'month' => $month,
        ];
    }

    public static function getAllFinishMonth()
    {
        $data = [];
        $models = MonthProfitRecord::getAllFinishRecord();
        foreach($models as $model)
        {
            $data[$model->id] = $model->year.'年'.$model->month.'月';
        }
        return $data;
    }

    public function getYearMonth()
    {
        $month = $this->month < 9 ? '0'.$this->month : $this->month;
        return $this->year.$month;
    }
}
