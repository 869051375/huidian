<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 商机商品基础表单
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheProductForm"))
 */
class NicheProductForm extends Model
{
    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    /**
     * 商品id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $product_id;

    /**
     * 数量
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $qty;

    /**
     * 商品销售单价
     * @SWG\Property(example = 999.99)
     * @var string
     */
    public $price;
}
