<?php

namespace common\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "clerk_items".
 *
 * @property integer $id
 * @property integer $clerk_id
 * @property integer $top_category_id
 * @property integer $category_id
 * @property string $product_ids
 *
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 */
class ClerkItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%clerk_items}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clerk_id'], 'required'],
            [['clerk_id', 'top_category_id', 'category_id'], 'integer'],
            [['product_ids'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'=>'',
            'clerk_id' => '',
            'top_category_id' => '',
            'category_id' => '服务项目',
            'product_ids' => '',
        ];
    }

    public function getCategory()
    {
        return self::hasOne(ProductCategory::className(), ['id' => 'category_id']);
    }

    public function getTopCategory()
    {
        return self::hasOne(ProductCategory::className(), ['id' => 'top_category_id']);
    }

    public function getProductIds()
    {
        $ids = trim($this->product_ids, ',');
        if(empty($ids)){
            return [];
        }
        return explode(',', trim($ids, ','));
    }

    public function addProductId($id)
    {
        $ids = $this->getProductIds();
        $ids[] = $id;
        $this->setProductIds($ids);
    }

    public function setProductIds($product_ids)
    {
        if(!empty($product_ids)){
            $product_ids = array_unique($product_ids);
            $this->product_ids = ','.implode(',', $product_ids).',';
        }
        else
        {
            $this->product_ids = '';
        }
    }

    /**
     * @param $product_id
     * @return bool 是否成功
     */
    public function removeProduct($product_id)
    {
        $ids = $this->getProductIds();
        ArrayHelper::removeValue($ids, $product_id);
        $this->setProductIds($ids);
        return $this->save(false);
    }

    /**
     * @return Product[]
     */
    public function getProductList()
    {
        $ids = $this->getProductIds();
        if(empty($ids)) return [];
        return Product::find()->where(['in', 'id', $this->getProductIds()])->all();
    }
}