<?php

namespace common\models;

use common\utils\BC;
use Yii;

/**
 * This is the model class for table "{{%niche_product}}".
 *
 * @property int $id id
 * @property int $niche_id 商机id
 * @property int $product_id 商品id
 * @property string $product_name 商品名称
 * @property int $province_id 省份id
 * @property int $city_id 城市id
 * @property int $district_id 区县id
 * @property string $province_name 省份名称
 * @property string $city_name 城市名称
 * @property string $district_name 区县名称
 * @property string $service_area 服务区域
 * @property int $qty 数量
 * @property string $original_price 原价
 * @property string $price 商品销售单价
 * @property string $amount 总价
 * @property string $category_id 所属分类，一级分类下的二级分类id
 * @property string $top_category_id 所属分类，一级分类id
 * @property Product $product
 * @property ProductPrice $productPrice
 */
class NicheProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%niche_product}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id','category_id', 'top_category_id','product_id', 'province_id', 'city_id', 'district_id', 'qty'], 'integer'],
            [['original_price', 'price', 'amount'], 'number'],
            [['product_name', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['service_area'], 'string', 'max' => 6],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'niche_id' => '商机id',
            'product_id' => '商品id',
            'product_name' => '商品名称',
            'province_id' => '省份id',
            'city_id' => '城市id',
            'district_id' => '区县id',
            'province_name' => '省份名称',
            'city_name' => '城市名称',
            'district_name' => '区县名称',
            'service_area' => '服务区域',
            'qty' => '数量',
            'original_price' => '原价',
            'price' => '商品销售单价',
            'amount' => '总价',
        ];
    }

    public function getProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getProductPrice()
    {
        return static::hasOne(ProductPrice::className(), ['product_id' => 'product_id', 'district_id' => 'district_id']);
    }

    public function getAdjustPrice()
    {
        $adjust = BC::sub($this->price,$this->getOriginalPrice());
        return floatval($adjust) ? $adjust : 0;
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

}
