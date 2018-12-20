<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%customer_tag}}".
 *
 * @property integer $tag_id
 * @property integer $customer_id
 */
class CustomerTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_tag}}';
    }

    public $company_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id'], 'integer'],
            [['customer_id'], 'string'],
            [['tag_id', 'customer_id'], 'required','on' => 'add'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => 'Tag ID',
            'customer_id' => 'Customer ID',
        ];
    }

    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }
}
