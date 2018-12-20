<?php

namespace common\models;

use common\utils\BC;

/**
 * This is the model class for table "performance_record".
 *
 * @property integer $id
 * @property integer $year
 * @property integer $month
 * @property integer $virtual_order_id
 * @property integer $order_id
 * @property string $already_paid
 * @property string $refunds
 * @property string $pending_pay
 * @property string $cost
 * @property string $performance
 * @property string $calculated_performance
 * @property string $correct_price
 *
 * @property Order $order
 */
class PerformanceRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%performance_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'month', 'virtual_order_id', 'order_id'], 'integer'],
            [['already_paid', 'refunds', 'pending_pay', 'cost', 'performance', 'calculated_performance','correct_price'], 'number'],
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
            'virtual_order_id' => 'Virtual Order ID',
            'order_id' => 'Order ID',
            'already_paid' => 'Already Paid',
            'refunds' => 'Refunds',
            'pending_pay' => 'Pending Pay',
            'cost' => 'Cost',
            'performance' => 'Performance',
            'calculated_performance' => 'Calculated Performance',
            'correct_price' => 'Correct Price',
        ];
    }

    /**
     * 剩余的未计算业绩
     * @return integer
     */
    public function lavePerformance()
    {
        return BC::sub(BC::sub($this->performance,$this->getCalculatedPerformance()),$this->getCorrectPrice());
    }

    /**
     * @param $virtual_order_id
     * @param $order_id
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public static function isExist($virtual_order_id,$order_id)
    {
        $model = PerformanceRecord::find()->where(['virtual_order_id' => $virtual_order_id,'order_id' => $order_id,'year' => date('Y'),'month' => date('m')])->one();
        return empty($model) ? false : $model;
    }

    /**
     * 创建业绩记录
     * @param $virtual_order_id
     * @param $order_id
     * @param $already_paid
     * @param $pending_pay
     * @param $cost
     * @param $refunds
     * @param $correct_price
     */
    public static function createRecord($virtual_order_id,$order_id,$already_paid,$pending_pay,$cost,$refunds,$correct_price = 0)
    {
        /**
         * 有则更新，无则创建。
         * @var PerformanceRecord $model
         */
        $model = self::isExist($virtual_order_id,$order_id);
        if($model)
        {
            if($cost)
            {
                $model->cost += $cost;
                $model->performance = BC::sub($model->already_paid,BC::add($model->cost,$model->refunds,2),2);//业绩 = 已付金额 - 退款 - 本月总成本
            }
            else if($refunds)
            {
                $model->refunds = $refunds;
                $model->performance = BC::sub($model->already_paid,BC::add($model->cost,$refunds,2),2);//业绩 = 已付金额 - 退款 - 本月总成本
            }
            else if($already_paid)
            {
                $model->already_paid = BC::add($model->already_paid,$already_paid);
                $model->pending_pay = $pending_pay;
                $model->performance = BC::sub($model->already_paid,BC::add($model->cost,$model->refunds,2),2);//业绩 = 已付金额 - 退款 - 本月总成本
            }
            else if($correct_price)
            {
                $model->correct_price = BC::add($model->correct_price,$correct_price);
            }
            $model->save(false);
        }
        else
        {
            //上月的记录的数据
            /** @var PerformanceRecord $before_model */
            $before_model = self::getBeforeMonth($virtual_order_id,$order_id);
            $model = new PerformanceRecord();
            if($before_model && $before_model->month != date('m')) //有上月的记录
            {
                if($already_paid)
                {
                    //未付款金额 = 上个月的未付款-本月已付款
                    $model->already_paid = $already_paid;
                    $model->performance = $already_paid;
                    $model->pending_pay = BC::sub($before_model->pending_pay,$already_paid);
                }
                if($cost)
                {
                    $model->cost = BC::add($model->cost,$cost);
                    $model->performance = BC::sub($model->already_paid,$model->cost);
                }
                if($refunds)
                {
                    $model->refunds = $refunds;
                    $model->performance = BC::sub($model->already_paid,BC::add($model->cost,$model->refunds,2),2);
                }
                if($correct_price)
                {
                    $model->correct_price = $correct_price;
                }
            }
            else  //如果没有上月的记录
            {
                $model->already_paid = $already_paid;
                $model->performance = $already_paid;
                $model->pending_pay = $pending_pay;
            }
            $model->year = date('Y');
            $model->month = date('m');
            $model->virtual_order_id = $virtual_order_id;
            $model->order_id = $order_id;
            $model->save(false);
        }
    }


    public static function getBeforeMonth($virtual_order_id,$order_id)
    {
        //获取最近一个月的数据
        $model = PerformanceRecord::find()->where(['virtual_order_id' => $virtual_order_id,'order_id' => $order_id])->orderBy(['year' => SORT_DESC,'month' => SORT_DESC])->one();
        return empty($model) ? false : $model;
    }

    //预计总利润
    public function expectedTotalProfit($administrator_id)
    {
        return PersonMonthProfit::getProfit($administrator_id,$this->year,$this->month);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id' => 'order_id']);
    }

    public function getReward()
    {
        /** @var PerformanceStatistics $model */
        $performance_reward = PerformanceStatistics::find()->select('performance_reward')
            ->where(['order_id' => $this->order_id,'year' => $this->year,'month' => $this->month])
            ->sum('performance_reward');
        return $performance_reward ? $performance_reward : 0;
    }

    //已计算实际利润
    public function getCalculatedPerformance()
    {
        /** @var PerformanceStatistics $performance_reward */
        $performance_reward = PerformanceStatistics::find()->select('calculated_performance')
            ->where(['order_id' => $this->order_id,'year' => $this->year,'month' => $this->month,'type' => 0])
            ->sum('calculated_performance');
        return $performance_reward ? $performance_reward : 0;
    }

    //已计算实际利润
    public function getCorrectPrice()
    {
        /** @var PerformanceStatistics $performance_reward */
        $performance_reward = PerformanceStatistics::find()->select('calculated_performance')
            ->where(['order_id' => $this->order_id,'year' => $this->year,'month' => $this->month,'type'=>1])
            ->sum('calculated_performance');
        return $performance_reward ? $performance_reward : 0;
    }
}
