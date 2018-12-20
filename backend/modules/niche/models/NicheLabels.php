<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机标签
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheLabels"))
 */
class NicheLabels extends Model
{

    /**
     * id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 标签名称
     * @SWG\Property(example = "重要得客户")
     * @var integer
     */
    public $name;

    /**
     * 颜色
     * @SWG\Property(example = "ff77fb")
     * @var string
     */
    public $color;

}
