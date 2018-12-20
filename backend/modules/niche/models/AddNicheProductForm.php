<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 用于在原有商机上添加一个新的商机商品
 * @SWG\Definition(required={"niche_id", "product_id", "qty", "price"}, @SWG\Xml(name="AddNicheProductForm"))
 */
class AddNicheProductForm extends NicheProductForm
{
    /**
     * 商机id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $niche_id;
}
