<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%opportunity_tag}}".
 *
 * @property integer $tag_id
 * @property integer $opportunity_id
 * @property integer $company_id
 * @property Tag $tag
 */
class OpportunityTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%opportunity_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'opportunity_id'], 'required'],
            [['tag_id', 'opportunity_id', 'company_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => 'Tag ID',
            'opportunity_id' => 'Opportunity ID',
            'company_id' => 'Company ID',
        ];
    }

    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }
}
