<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机商品
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheProduct"))
 */
class NicheProduct extends Model
{

    /**
     * id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $id;

    /**
     * 商机id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $niche_id;

    /**
     * 商品id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $product_id;

    /**
     * 商品名称
     * @SWG\Property(example = "测试名称")
     * @var string
     */
    public $product_name;

    /**
     * 省份id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $province_id;

    /**
     * 城市id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $city_id;

    /**
     * 区县id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $district_id;

    /**
     * 省份名称
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $province_name;

    /**
     * 城市名称
     * @SWG\Property(example = "北京")
     * @var string
     */
    public $city_name;

    /**
     * 区县名称
     * @SWG\Property(example = "海淀区")
     * @var string
     */
    public $district_name;

    /**
     * 服务区域
     * @SWG\Property(example = "澳门")
     * @var string
     */
    public $service_area;

    /**
     * 数量
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $qty;

    /**
     * 原价
     * @SWG\Property(example = 1000.00)
     * @var string
     */
    public $original_price;

    /**
     * 商品销售单价
     * @SWG\Property(example = 999.99)
     * @var string
     */
    public $price;

    /**
     * 总价
     * @SWG\Property(example = 999.99)
     * @var string
     */
    public $amount;

    /**
     * 商品一级分类
     * @SWG\Property(example = "1")
     * @var string
     */
    public $category_id;

    /**
     * 商品二级分类
     * @SWG\Property(example = "1")
     * @var string
     */
    public $top_category_id;
}
