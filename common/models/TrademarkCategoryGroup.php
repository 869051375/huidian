<?php

namespace common\models;

/**
 * This is the model class for table "{{%trademark_category_group}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $code
 * @property string $name
 *
 * @property TrademarkCategoryGroupItem[] $items
 *
 */
class TrademarkCategoryGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trademark_category_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id'], 'integer'],
            [['name'], 'string'],
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
            'category_id' => '上级类别id',
            'code' => '编号',
            'name' => '名称',
        ];
    }

    public function getItems()
    {
        return static::hasMany(TrademarkCategoryGroupItem::className(), ['group_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }
}
