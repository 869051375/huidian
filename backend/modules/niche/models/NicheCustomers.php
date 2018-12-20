<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机客户信息
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCustomer"))
 */
class NicheCustomers extends Model
{

    /**
     * id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $id;


    /**
     * 客户名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $name;
}
