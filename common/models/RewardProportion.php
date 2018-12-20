<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "reward_proportion".
 *
 * @property integer $id
 * @property string $name
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 *
 * @property CrmDepartment[] $department
 */
class RewardProportion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%reward_proportion}}';
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
            ['name', 'required'],
            ['name', 'string', 'max' => 30],
            ['name', 'unique','on'=>'insert'],
            ['name', 'validateName','on'=>'edit'],
        ];
    }

    public function validateName()
    {
        /** @var RewardProportion $model */
        $model = RewardProportion::find()->where(['name' => $this->name])->one();
        if(isset($model) && $model->id != $this->id)
        {
            $this->addError('name','方案名称已存在！');
        }
    }

    public function getDepartment()
    {
        return $this->hasMany(CrmDepartment::className(),['reward_proportion_id' => 'id'])->where(['status' => CrmDepartment::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '方案名称',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public function createVersion()
    {
        $version = new RewardProportionVersion();
        $version->reward_proportion_id = $this->id;
        $version->reward_proportion_name = $this->name;
        $version->save(false);
        return $version;
    }
}
