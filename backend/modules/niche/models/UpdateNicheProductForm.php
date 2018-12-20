<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 用于更新商机的商机商品
 *  @SWG\Definition(required={"id", "product_id", "qty", "price"}, @SWG\Xml(name="UpdateNicheProductForm"))
 */
class UpdateNicheProductForm extends NicheProductForm
{
    /**
     * id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $id;
}
