<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 订单列表
 * @SWG\Definition(required={"id", "sn", "product_name", "province_name", "city_name", "district_name", "price", "status", "is_invoice"}, @SWG\Xml(name="OrderList"))
 */
class OrderList extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $id;

    /**
     * 订单编号
     * @SWG\Property(example = "2315101618177770")
     * @var string
     */
    public $sn;


    /**
     * 创建时间
     * @SWG\Property(example = "1541404757")
     * @var integer
     */
    public $created_at;


    /**
     * 金额
     * @SWG\Property(example = "10000.00")
     * @var integer
     */
    public $total_amount;

    /**
     * @SWG\Property()
     *
     * @var Order[]
     */
    public $order;

}