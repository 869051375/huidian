<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * 订单列表
 * @SWG\Definition(required={}, @SWG\Xml(name="Order"))
 */
class Order extends Model
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
     * 产品名称
     * @SWG\Property(example = "公司注册")
     * @var string
     */
    public $product_name;

    /**
     * 省
     * @SWG\Property(example = "陕西省")
     * @var string
     */
    public $province_name;

    /**
     * 市
     * @SWG\Property(example = "榆林市")
     * @var string
     */
    public $city_name;


    /**
     * 县
     * @SWG\Property(example = "定边县")
     * @var string
     */
    public $district_name;


    /**
     * 价格
     * @SWG\Property(example = "112.00")
     * @var string
     */
    public $price;


    /**
     * 状态 （0:待付款、4:服务终止、1:待分配、2:待服务、3:服务中、8:服务完成）
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $status;


    /**
     * 是否开具发票 （0：未开具  1：已开具）
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $is_invoice;

}