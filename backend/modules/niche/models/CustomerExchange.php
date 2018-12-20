<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 商机统计
 * @SWG\Definition(required={}, @SWG\Xml(name="CustomerExchange"))
 */
class CustomerExchange extends Model
{

    /**
     * ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 数量
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $num;

    /**
     * 金额
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $amount;

    /**
     * 百分比
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $percent;

    /**
     * 类型
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $type;


    const ADD_CLUE = 1;
    const CLUE_CUSTOMER = 2;
    const CLUE_NICHE = 3;
    const ADD_CUSTOMER = 4;
    const NEW_CUSTOMER = 5;
    const CUSTOMER_WIN = 7;
    const CUSTOMER_NICHE = 6;
    const ADD_NICHE = 8;
    const NEW_NICHE = 9;
    const NICHE_WIN = 10;
    const NICHE_LOSE = 11;
    const WIN = 12;
    const LOSE = 13;


}