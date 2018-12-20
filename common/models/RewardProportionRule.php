<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "reward_proportion_rule".
 *
 * @property integer $id
 * @property integer $reward_proportion_version_id
 * @property string $expected_total_profit
 * @property string $reward_proportion
 * @property integer $created_at
 */
class RewardProportionRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%reward_proportion_rule}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reward_proportion_version_id'], 'integer'],
            [['reward_proportion_version_id', 'expected_total_profit','reward_proportion'], 'required'],
            [['expected_total_profit', 'reward_proportion'], 'number'],
            ['reward_proportion', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['reward_proportion', 'compare', 'compareValue' => 100, 'operator' => '<='],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reward_proportion_version_id' => 'Reward Proportion Version ID',
            'expected_total_profit' => '预计总利润金额范围（元）≥',
            'reward_proportion' => '提成比例',
            'created_at' => 'Created At',
        ];
    }
}
