<?php

namespace backend\modules\niche\models;

/**
 * 商机列表
 * @SWG\Definition(required={}, @SWG\Xml(name="PublicList"))
 */
class PublicList extends NicheList
{

    /**
     * 公海名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $public_name;

    /**
     * 商品名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $product_name;

    /**
     * 客户名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $custom_name;


}