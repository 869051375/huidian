<?php

namespace backend\models;

use common\models\Administrator;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "virtual_order_cost".
 *
 * @property integer $id
 * @property integer $virtual_order_id
 * @property string $cost_name
 * @property string $cost_price
 * @property string $remark
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class VirtualOrderCost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%virtual_order_cost}}';
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
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $this->year = date('Y');
            $this->month = date('m');
            $this->day = date('d');
            $this->creator_id = $user->id;
            $this->creator_name = $user->name;
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
            [['virtual_order_id', 'year', 'month', 'day', 'creator_id', 'created_at'], 'integer'],
            [['virtual_order_id','cost_name','cost_price'], 'required'],
            [['cost_price'], 'number'],
            [['remark'], 'string'],
            [['cost_name'], 'string', 'max' => 15],
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
            'virtual_order_id' => 'Virtual Order ID',
            'cost_name' => 'Cost Name',
            'cost_price' => 'Cost Price',
            'remark' => 'Remark',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }
}
