<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NichePublicTree"))
 */
class NichePublicTree extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    /**
     * 部门名称
     * @SWG\Property(example = "公司1")
     * @var string
     */
    public $label;

    /**
     * 父级部门id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $parent_id;

    /**
     * 父级部门id
     * @SWG\Property(example = true)
     * @var boolean
     */
    public $disabled;

}