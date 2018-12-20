<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheLabel"))
 */
class NicheLabel extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 公司id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $company_id;


    /**
     * 标签类型
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $type;

    /**
     * 标签名称
     * @SWG\Property(example = "重要商机")
     * @var string
     */
    public $name;

    /**
     * 标签颜色
     * @SWG\Property(example = "f000000")
     * @var string
     */
    public $color;


    const NICHE_TYPE = 1;

}