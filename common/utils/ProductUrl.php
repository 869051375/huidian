<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/31
 * Time: 下午1:23
 */

namespace common\utils;


use common\models\Product;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class ProductUrl
{
    /**
     * @param Product $product
     * @param [] $params
     * @return string
     */
    public static function to($product, $params = [])
    {
        if(null == $product) return null;
        if($product->slug) return Url::to(ArrayHelper::merge(['product/view', 'slug' => $product->slug], $params), true);
        return Url::to(ArrayHelper::merge(['product/view', 'id' => $product->id ], $params), true);
    }
}