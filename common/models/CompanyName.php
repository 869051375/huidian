<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_name}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property integer $sort
 */
class CompanyName extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_name}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'trim'],
            [['name', 'category_id'], 'required'],

            [['sort'], 'integer'],

            [['category_id'], 'integer'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_id' => '分类',
            'name' => '公司名称',
            'sort' => '排序',
        ];
    }
}
