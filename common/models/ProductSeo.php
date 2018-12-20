<?php

namespace common\models;

/**
 * This is the model class for table "{{%product_seo}}".
 *
 * @property integer $product_id
 * @property string $title
 * @property string $keywords
 * @property string $description
 */
class ProductSeo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_seo}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'exist', 'targetClass' => Product::className(), 'targetAttribute' => 'id'],
            [['title', 'keywords', 'description'], 'required'],
            [['title'], 'string', 'max' => 80],
            [['keywords'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => '',
            'title' => '标题',
            'keywords' => '关键词',
            'description' => '描述',
        ];
    }
}
