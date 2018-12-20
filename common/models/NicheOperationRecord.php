<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_operation_record".
 *
 * @property int $id 自增id
 * @property int $niche_id 商机表ID
 * @property string $content 操作记录内容
 * @property string $item 操作项
 * @property int $creator_id 操作人ID
 * @property string $creator_name 操作人名字
 * @property int $created_at 操作时间
 */
class NicheOperationRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_operation_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'creator_id', 'created_at'], 'integer'],
            [['content','item'], 'string','tooLong'=>500],
            [['creator_name'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'niche_id' => 'Niche ID',
            'content' => 'Content',
            'item' => 'Item',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->created_at);
        };
        return $fields;
    }

}
