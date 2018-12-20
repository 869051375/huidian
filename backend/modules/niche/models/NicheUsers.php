<?php

namespace backend\modules\niche\models;

use Yii;
use yii\base\Model;


/**
 * 商机电商用户
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheUsers"))
 */
class NicheUsers extends Model
{

    /**
     * id
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $id;


    /**
     * 昵称
     * @SWG\Property(example = "这是昵称")
     * @var integer
     */
    public $name;

    /**
     * 注册手机号
     * @SWG\Property(example = "18101361111")
     * @var string
     */
    public $phone;

    /**
     * 邮箱
     * @SWG\Property(example = "18101361111@163.com")
     * @var integer
     */
    public $email;

    /**
     * 邮寄地址
     * @SWG\Property(example = "这是邮寄地址")
     * @var integer
     */
    public $address;

}
