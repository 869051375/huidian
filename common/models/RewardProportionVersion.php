<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "reward_proportion_version".
 *
 * @property integer $id
 * @property integer $reward_proportion_id
 * @property string $reward_proportion_name
 * @property string $effective_month
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property RewardProportionRule[] $proportionRule
 */
class RewardProportionVersion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%reward_proportion_version}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {

            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $this->creator_id = $user->id;
            $this->creator_name = $user->name;
            $this->updater_id = $user->id;
            $this->updater_name = $user->name;
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reward_proportion_id', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['effective_month', 'creator_name', 'updater_name'], 'string', 'max' => 10],
            [['reward_proportion_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reward_proportion_id' => 'Reward Proportion ID',
            'effective_month' => 'Effective Month',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getProportionRule()
    {
        return $this->hasMany(RewardProportionRule::className(),['reward_proportion_version_id' => 'id'])->orderBy(['expected_total_profit' => SORT_DESC]);
    }

    /**
     * 获取方案版本规则
     * @param integer $department_id 部门id
     * @param float $expected_total_profit
     * @return string
     */
    public static function getVersionRule($department_id, $expected_total_profit)
    {
        $d = CrmDepartment::find()->select('reward_proportion_id')->where(['id' => $department_id])->asArray()->one();
        if($d)
        {
            /** @var RewardProportionVersion $model */
            $model = self::find()
                ->where(['reward_proportion_id' => $d['reward_proportion_id']])
                ->andWhere(['<=', 'effective_month', date('Y-m')])
                ->orderBy(['effective_month' => SORT_DESC])->limit(1)
                ->one();
            if($model)
            {
                foreach($model->proportionRule as $proportionRule)
                {
                    //按照规则的利润金额降序,然后比对利润金额取相对应的提成比率
                    if($expected_total_profit >= $proportionRule->expected_total_profit)
                    {
                        return $proportionRule->reward_proportion;
                    }
                }
            }
        }
        return 0;
    }
}
