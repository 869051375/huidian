<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "source".
 *
 * @property int $id 自增id
 * @property string $name 来源名称
 * @property int $status 0失效，1生效
 * @property int $sort 排序，越小越靠前
 */
class Source extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;//生效
    const STATUS_DISABLED = 0;//未生效
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'trim'],
            ['name', 'required','message' => '客户来源名称不能为空且输出长度不能超过8个字。'],
            [['status'], 'boolean'],

            [['status', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 8, 'tooLong'=>'客户来源名称不能为空且输出长度不能超过8个字。'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'sort' => 'Sort',
        ];
    }
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                $maxSort = static::find()->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10;
            }
            return true;
        }
        return false;
    }

    public function hasCustomer()
    {
        if (CrmCustomer::find()->where(['source' => $this->id])->count() > 0){
            return true;
        }
        elseif (CrmClue::find()->where(['source_id' => $this->id])->count() > 0)
        {
            return true;
        }
        elseif (Niche::find()->where(['source_id' => $this->id])->count() > 0)
        {
            return true;
        }
        return false;
    }
}
