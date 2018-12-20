<?php

namespace common\models;

use common\utils\BC;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%package_product}}".
 *
 * @property integer $package_id
 * @property integer $product_id
 * @property integer $sort
 *
 * @property Product $product
 */
class PackageProduct extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%package_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_id', 'product_id', 'sort'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'package_id' => 'Package ID',
            'product_id' => 'Product ID',
            'sort' => 'Sort',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                $maxSort = static::find()->where('package_id=:package_id', [':package_id' => $this->package_id])
                    ->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10; // 加10 表示往后排（因为越大越靠后）
            }
            return true;
        }
        return false;
    }

    //判断套餐内是否有区分区域商品
    public static function isAreaPrice($product_id)
    {
        $packageProduct = Product::findOne($product_id);
        foreach ($packageProduct->packageProducts as $product)
        {
            if($product->isAreaPrice())
            {
                return true;
            }
        }
        return false;
    }

    /**
     * 非区域时的套餐价格
     * @param Product $packageProduct
     * @return float|string
     */
    public static function getPackageOriginalPrice($packageProduct)
    {
        $packageOriginalPrice = 0.00;
        if(null != $packageProduct)
        {
            foreach ($packageProduct->packageProducts as $product)
            {
                //如果套餐非区分区域，获取非区域的原售价
                if(!$product->isAreaPrice() && !$product->isBargain())
                {
//                    $packageOriginalPrice = BC::add($product->original_price, $packageOriginalPrice);
                    $packageOriginalPrice = BC::add($product->price, $packageOriginalPrice);
                }
            }
        }
        return $packageOriginalPrice;
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
