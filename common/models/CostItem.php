<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "cost_item".
 *
 * @property integer $id
 * @property string $name
 * @property string $price
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class CostItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cost_item}}';
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
            [['name', 'creator_name'], 'string', 'max' => 10],
            [['price'], 'number'],
            [['creator_id', 'created_at'], 'integer'],
            [['name','price'], 'required'],
            ['name','unique','message'=>'成本名称已经被存在','on'=>'insert'],
            ['name','validateName','on'=>'edit']
        ];
    }

    public function validateName()
    {
        /** @var CostItem $model */
       $model = self::find()->where(['name' => $this->name])->one();
       if($model->id != $this->id)
       {
           $this->addError('name','成本名称已存在');
       }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '成本名称',
            'price' => '成本金额',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return int
     */
    public static function getCostCount()
    {
        return self::find()->count();
    }

    public function scene()
    {

    }
}
