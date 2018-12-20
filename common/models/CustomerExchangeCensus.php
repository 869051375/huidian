<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer_exchange_census".
 *
 * @property int $id 自增id
 * @property int $niche_id 商机ID
 * @property int $clue_id 线索ID
 * @property int $customer_id 客户ID
 * @property int $source_id 来源ID
 * @property int $channel_id 来源渠道ID
 * @property int $province_id 省ID
 * @property int $city_id 市ID
 * @property int $district_id 区ID
 * @property int $num 数量
 * @property string $amount 总金额
 * @property int $administrator_id 所属负责人ID
 * @property int $type 类型
 * @property int $year 年
 * @property int $month 月
 * @property int $day 日
 * @property int $create_at 日
 */
class CustomerExchangeCensus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_exchange_census';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'num', 'administrator_id', 'type', 'year', 'month', 'day','province_id','city_id','district_id','source_id','channel_id'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'niche_id' => 'Niche ID',
            'num' => 'Num',
            'amount' => 'Amount',
            'administrator_id' => 'Administrator ID',
            'type' => 'Type',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
        ];
    }
}
