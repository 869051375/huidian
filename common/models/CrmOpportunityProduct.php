<?php

namespace common\models;

use common\utils\BC;

/**
 * This is the model class for table "crm_opportunity_product".
 *
 * @property integer $id
 * @property integer $opportunity_id
 * @property integer $product_id
 * @property string $product_name
 * @property integer $top_category_id
 * @property string $top_category_name
 * @property integer $category_id
 * @property string $category_name
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property string $price
 * @property integer $qty
 *
 * @property Product $product
 * @property CrmOpportunity $opportunity
 * @property ProductPrice $productPrice
 */
class CrmOpportunityProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_opportunity_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['opportunity_id', 'product_id', 'top_category_id', 'category_id', 'province_id', 'city_id', 'district_id', 'qty'], 'integer'],
            [['price'], 'number'],
            [['product_name', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['top_category_name', 'category_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'opportunity_id' => 'Opportunity ID',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'top_category_id' => 'Top Category ID',
            'top_category_name' => 'Top Category Name',
            'category_id' => 'Category ID',
            'category_name' => 'Category Name',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'price' => 'Price',
            'qty' => 'Qty',
        ];
    }

    public function getProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getOpportunity()
    {
        return static::hasOne(CrmOpportunity::className(), ['id' => 'opportunity_id']);
    }

    public function getProductPrice()
    {
        return static::hasOne(ProductPrice::className(), ['product_id' => 'product_id', 'district_id' => 'district_id']);
    }

    public function getOriginalPrice()
    {
        if($this->product->isAreaPrice() && $this->district_id)
        {
            $pp = $this->productPrice;
            if($pp) return $pp->price;
        }
        return $this->product->price;
    }

    public function getAdjustPrice()
    {
        $adjust = BC::sub($this->price,$this->getOriginalPrice());
        return floatval($adjust) ? $adjust : 0;
    }
}
