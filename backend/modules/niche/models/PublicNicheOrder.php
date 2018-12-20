<?php

namespace backend\modules\niche\models;

use common\models\Order;
/**
 * 商机公海详细信息加密
 * @SWG\Definition(required={}, @SWG\Xml(name="PublicNicheOrder"))
 */
class PublicNicheOrder extends Order
{
    /**
     * 虚拟订单号
     * @SWG\Property(example = "V2315101618176813")
     * @var string
     */
    public $virtual_order_sn;


        public function fields()
    {
        $fields = parent::fields();
        if(!empty($this->virtual_order_sn)){
            $fields['virtual_order_sn'] = function() {
                return substr($this->virtual_order_sn,0,3).'***********'.substr($this->virtual_order_sn,-3);
            };
        }
        if(!empty($this->sn)){
            $fields['sn'] = function() {
                return substr($this->sn,0,3).'***********'.substr($this->sn,-3);
            };
        }
        return $fields;
    }

}