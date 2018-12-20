<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_name_category}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $remark
 * @property integer $status
 * @property integer $sort
 *
 * @property CompanyName[] $companyNames
 */
class CompanyNameCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_name_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'trim'],
            ['name', 'required'],

            [['sort'], 'integer'],
            [['status'], 'boolean'],

            [['name'], 'string', 'max' => 20],
            [['remark'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '分类名称',
            'remark' => '备注说明',
            'status' => '状态',
            'sort' => '排序',
        ];
    }

    public function getCompanyNames()
    {
        return static::hasMany(CompanyName::className(), ['category_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC]);
    }
}
