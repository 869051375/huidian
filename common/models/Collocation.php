<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%collocation}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $collocation_product_id
 * @property string $desc
 *
 * @property Product $collocationProduct
 */
class Collocation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collocation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'collocation_product_id'], 'integer'],
            [['desc'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'product_id' => '商品id',
            'collocation_product_id' => '搭配商品id',
            'desc' => '关联商品简介',
        ];
    }

    public function getCollocationProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'collocation_product_id']);
    }
}