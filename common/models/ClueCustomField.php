<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clue_custom_field".
 *
 * @property int $id 自增id
 * @property int $administrator_id 人员ID
 * @property string $field 字段
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $type 类型 (0:跟进中列表，1：已转换列表 2：公海列表)
 */
class ClueCustomField extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clue_custom_field';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['administrator_id', 'created_at', 'updated_at', 'type'], 'integer'],
            [['field'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'administrator_id' => 'Administrator ID',
            'field' => 'Field',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'type' => 'Type',
        ];
    }
}
