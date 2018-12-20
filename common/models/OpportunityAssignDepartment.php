<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%opportunity_assign_department}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $company_id
 * @property integer $department_id
 *
 * @property Company $company
 * @property CrmDepartment $department
 *
 */
class OpportunityAssignDepartment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%opportunity_assign_department}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'company_id', 'department_id'], 'integer'],
            [['company_id', 'department_id'], 'unique', 'targetAttribute' => ['company_id', 'department_id'], 'message' => 'The combination of Company ID and Department ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
        ];
    }

    public function getCompany()
    {
        return static::hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'department_id']);
    }
}
