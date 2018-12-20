<?php

namespace common\models;

/**
 * This is the model class for table "{{%product_tag}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $tag
 * @property integer $count
 */
class ProductTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'count'], 'integer'],
            [['tag'], 'string', 'max' => 10],
            [['product_id', 'tag'], 'unique', 'targetAttribute' => ['product_id', 'tag'], 'message' => '数据已经存在。'],
        ];
    }

    public static function addTag($product_id, $tag)
    {
        /** @var ProductTag $productTag */
        $productTag = ProductTag::find()->where(['product_id' => $product_id, 'tag' => $tag])->one();
        if(null == $productTag)
        {
            $productTag = new ProductTag();
            $productTag->product_id = $product_id;
            $productTag->tag = $tag;
            $productTag->count = 1;
        }
        else
        {
            $productTag->count = $productTag->count + 1;
        }
        $productTag->save(false);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'product_id' => '商品id',
            'tag' => '标签名字',
            'count' => '标签被贴数量',
        ];
    }
}
