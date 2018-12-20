<?php

namespace common\models;

/**
 * This is the model class for table "{{%trademark_category_group_item}}".
 *
 * @property integer $id
 * @property integer $group_id
 * @property string $code
 * @property string $items
 */
class TrademarkCategoryGroupItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trademark_category_group_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'integer'],
            [['items'], 'string'],
            [['code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'group_id' => '群组id',
            'code' => '编号',
            'items' => '群组内容',
        ];
    }
}
