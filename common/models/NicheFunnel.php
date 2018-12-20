<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_funnel".
 *
 * @property int $id 自增id
 * @property int $niche_id 商机ID
 * @property int $administrator_id 所属负责人ID
 * @property int $team_id 协作人ID
 * @property int $province_id 省ID
 * @property int $city_id 市ID
 * @property int $district_id 县ID
 * @property int $source_id 来源ID
 * @property int $channel_id 来源渠道ID
 * @property int $times 年
 * @property int $type 类型 (10:目标识别，30：需求确定 60：谈判审核 ，80：合同确认 100：赢单 0 输单)
 */
class NicheFunnel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_funnel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'administrator_id', 'team_id', 'province_id', 'city_id', 'district_id', 'source_id', 'channel_id', 'year', 'month', 'day', 'type'], 'integer'],
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
            'administrator_id' => 'Administrator ID',
            'team_id' => 'Team ID',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'district_id' => 'District ID',
            'source_id' => 'Source ID',
            'channel_id' => 'Channel ID',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'type' => 'Type',
        ];
    }
}
