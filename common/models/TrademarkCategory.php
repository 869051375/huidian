<?php

namespace common\models;

/**
 * This is the model class for table "{{%trademark_category}}".
 *
 * @property integer $id
 * @property string $trademark_no
 * @property string $name
 * @property string $full_name
 * @property string $intro
 * @property string $annotate
 * @property string $remark
 *
 * @property TrademarkCategoryGroup[] $groups
 */
class TrademarkCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trademark_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'intro', 'annotate', 'remark'], 'string'],
            [['trademark_no'], 'string', 'max' => 50],
            [['full_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'trademark_no' => '编号',
            'name' => '类别名称',
            'full_name' => '完整名称',
            'intro' => '介绍',
            'annotate' => '注解',
            'remark' => '备注',
        ];
    }

    /**
     * @param $id
     * @return null|string
     */
    public function findTrademarkCategory($id)
    {
        if(!empty($id))
        {
            /**@var $model TrademarkCategory**/
            $model = TrademarkCategory::findOne($id);

            if($model)
            {
                return $model->trademark_no;
            }
        }
        return null;
    }

    public function getGroups()
    {
        return static::hasMany(TrademarkCategoryGroup::className(), ['category_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }
}