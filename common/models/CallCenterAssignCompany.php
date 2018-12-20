<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%call_center_assign_company}}".
 *
 * @property integer $id
 * @property integer $call_center_id
 * @property integer $company_id
 * @property CallCenter $callCenter
 */
class CallCenterAssignCompany extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%call_center_assign_company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['call_center_id', 'company_id'], 'integer'],
            [['call_center_id', 'company_id'], 'unique', 'targetAttribute' => ['call_center_id', 'company_id'], 'message' => 'The combination of Call Center ID and Company ID has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'call_center_id' => 'Call Center ID',
            'company_id' => 'Company ID',
        ];
    }

    public function getCallCenter()
    {
        return $this->hasOne(CallCenter::className(), ['id' => 'call_center_id']);
    }
}
