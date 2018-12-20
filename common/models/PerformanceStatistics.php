<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "performance_statistics".
 *
 * @property integer $id
 * @property integer $virtual_order_id
 * @property integer $order_id
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property integer $department_id
 * @property string $department_name
 * @property integer $year
 * @property integer $month
 * @property integer $type
 * @property integer $algorithm_type
 * @property string $title
 * @property string $remark
 * @property string $calculated_performance
 * @property string $performance_reward
 * @property string $reward_proportion
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 *
 * @property Administrator $administrator
 * @property Order $order
 */
class PerformanceStatistics extends \yii\db\ActiveRecord
{
    const TYPE_GENERAL = 0; //普通计算
    const TYPE_CORRECT = 1; //更正金额

    const ALGORITHM_GENERAL = 0; //阶梯算法
    const ALGORITHM_POINT = 1; //固定点位

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%performance_statistics}}';
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

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                /** @var Administrator $user */
                $user = Yii::$app->user->identity;
                $this->creator_id = $user->id;
                $this->creator_name = $user->name;
            }
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
            [['virtual_order_id', 'administrator_id', 'department_id', 'year', 'month','type','algorithm_type','order_id', 'creator_id', 'created_at'], 'integer'],
            [['reward_proportion','calculated_performance', 'performance_reward'], 'number'],
            [['administrator_name', 'creator_name'], 'string', 'max' => 10],
            [['department_name'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 10],
            [['remark'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'virtual_order_id' => 'Virtual Order ID',
            'order_id' => 'Order ID',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'year' => 'Year',
            'month' => 'Month',
            'type' => 'Type',
            'algorithm_type' => 'Algorithm Type',
            'title' => 'Title',
            'remark' => 'Remark',
            'calculated_performance' => 'Calculated Performance',
            'performance_reward' => 'Performance Reward',
            'reward_proportion' => 'Reward Proportion',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }
    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id' => 'order_id']);
    }

    public function getTypeName()
    {
        $type = [
            self::TYPE_GENERAL => '常规计算',
            self::TYPE_CORRECT => '更正计算',
        ];
        return $type[$this->type];
    }

    public function getAlgorithmName()
    {
        $type = [
            self::ALGORITHM_GENERAL => '阶梯点位算法',
            self::ALGORITHM_POINT => '固定点位算法',
        ];
        if($this->type == self::TYPE_CORRECT)
        {
            return '更正点位算法';
        }
        else
        {
            return $type[$this->algorithm_type];
        }
    }
}
