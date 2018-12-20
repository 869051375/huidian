<?php

namespace common\models;

/**
 * This is the model class for table "product_related".
 *
 * @property integer $product_id
 * @property integer $related_product_id
 */
class ProductRelated extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_related}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'related_product_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => '',
            'related_product_id' => '关联商品',
        ];
    }

    public static function getRelatedList($id)
    {
        $ids = self::find()->select('related_product_id')->where(['product_id'=>$id])->asArray()->all();
        $products = [];
        foreach ($ids as $key=>$id)
        {
             $products[$key][]= Product::find()->where(['id'=>$id['related_product_id']])->one();
        }
        return $products;
    }
}
