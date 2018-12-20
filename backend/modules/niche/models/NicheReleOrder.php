<?php

namespace backend\modules\niche\models;

use common\models\Order;
/**
 * 订单列表
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheReleOrder"))
 */
class NicheReleOrder extends Order
{
    /**
     * 虚拟订单号
     * @SWG\Property(example = "V2315101618176813")
     * @var string
     */
    public $virtual_order_sn;
    public $order_id;
    public $jump;


    public function fields()
    {
        $fields = parent::fields();
        $fields['virtual_order_sn'] = 'virtual_order_sn';
        $fields['order_id'] = 'order_id';
        $fields['jump'] = function() {
             if($this->status == 0){
                 return 0;
             }
             if($this->is_installment == 0 && $this->price != $this->payment_amount){
                 return 0;
             }
             return 1;
        };
        return $fields;
    }

}