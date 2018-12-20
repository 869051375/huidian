<?php

namespace backend\modules\niche\models;

use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="PublicNicheOrderList"))
 */
class PublicNicheOrderList extends Model
{
    /**
     * 虚拟订单号
     * @SWG\Property(example = "V2315101618176813")
     * @var string
     */
    public $virtual_order_sn;


    /**
     * 订单编号
     * @SWG\Property(example = "V23151016181768131")
     * @var string
     */
    public $sn;


    /**
     * 商品名称
     * @SWG\Property(example = "公司注册")
     * @var string
     */
    public $product_name;

    /**
     * 业务员名称
     * @SWG\Property(example = "张三")
     * @var string
     */
    public $salesman_name;

    /**
     * 订单状态 （0:待付款、4:服务终止、1:待分配、2:待服务、3:服务中、8:服务完成）
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $status;

    /**
     * 发票状态 （0：未开具，1：已开局）
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $is_invoice;


}