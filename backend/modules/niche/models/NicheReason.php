<?php

namespace backend\modules\niche\models;

use yii\base\Model;

/**
 * 用于赢单和输单原因
 * @SWG\Definition(required={"id", "name"}, @SWG\Xml(name="NicheReason"))
 */
class NicheReason extends Model
{
    /**
     * id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 原因名称
     * @SWG\Property(example = "价格原因")
     * @var string
     */
    public $name;
}

