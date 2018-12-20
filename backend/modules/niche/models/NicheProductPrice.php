<?php

namespace backend\modules\niche\models;

use common\models\Product;
use common\models\ProductPrice;
use yii\base\Model;


/**
 * 商机地区选择
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheProductPrice"))
 */
class NicheProductPrice extends Model
{

    /**
     * 商品ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $product_id;

    /**
     * 商品名称
     * @SWG\Property(example = "商品名称")
     * @var string
     */
    public $name;

    /**
     * 商品价格
     * @SWG\Property(example = "100.00")
     * @var float
     */
    public $price;

    /**
     * 省份ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $province_id;

    /**
     * 省份name
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $province_name;

    /**
     * 城市ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $city_id;

    /**
     * 城市name
     * @SWG\Property(example = "北京")
     * @var integer
     */
    public $city_name;

    /**
     * 地区ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $district_id;

    /**
     * 地区name
     * @SWG\Property(example = "朝阳区")
     * @var string
     */
    public $district_name;


    /** @var $currentAdministrator */
    public $currentAdministrator;

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function select($id)
    {
        /** @var Product $product_one */
        $product_one = Product::find()->where(['id'=>$id])->one();
        if ($product_one->is_area_price == 1)
        {
            $product = ProductPrice::find()->where(['product_id'=>$id])->andWhere(['status'=>1])->asArray()->all();
            $product_array = array();
            foreach ($product as $k => $v){
                $product_array[$k]['name'] = $v['province_name'].' '.$v['city_name'].' '.$v['district_name'].' ('.$v['price'].')';
                $product_array[$k]['product_id'] = $v['product_id'];
                $product_array[$k]['id'] = $v['id'];
                $product_array[$k]['price'] = $v['price'];
                $product_array[$k]['province_id'] = $v['province_id'];
                $product_array[$k]['province_name'] = $v['province_name'];
                $product_array[$k]['city_id'] = $v['city_id'];
                $product_array[$k]['city_name'] = $v['city_name'];
                $product_array[$k]['district_id'] = $v['district_id'];
                $product_array[$k]['district_name'] = $v['district_name'];
                $product_array[$k]['original_price'] = $v['original_price'];
            }
            return $product_array;
        }
        else
        {
            return [];
        }
    }
}
