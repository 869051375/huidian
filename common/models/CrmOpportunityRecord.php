<?php

namespace common\models;

/**
 * This is the model class for table "crm_opportunity_record".
 *
 * @property integer $id
 * @property integer $opportunity_id
 * @property integer $customer_id
 * @property integer $department_id
 * @property string $department_name
 * @property string $content
 * @property integer $next_follow_time
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class CrmOpportunityRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_opportunity_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['opportunity_id', 'customer_id', 'department_id', 'next_follow_time', 'creator_id', 'created_at'], 'integer'],
            [['content'], 'string'],
            [['creator_name'], 'string', 'max' => 10],
            [['department_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'opportunity_id' => 'Opportunity ID',
            'customer_id' => 'Customer ID',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'content' => 'Content',
            'next_follow_time' => 'Next Follow Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }
}
