<?php
namespace backend\modules\niche\models;
/**
 * Created by PhpStorm.
 * User: yangge
 * Date: 2018/11/8
 * Time: 3:09 PM
 */

use common\models\CrmCustomer;
use common\models\Niche;
use common\models\Tag;

class NichePublicLists extends Niche{

    public $public_name;
    public $customer_number;
    public $product;
    public $customer_created_at;
    public $label_color;


    public function fields()
    {
        $fields = parent::fields();
        $fields['product'] = function() {
            $products = \common\models\NicheProduct::find()->where(['niche_id'=>$this->id])->asArray()->all();
            $product = '';
            foreach($products as $key=>$val){
                $product .= $val['product_name'].'*'.$val['qty'].',';
            }
            return $product;
        };
        $fields['customer_created_at'] = function() {
            /** @var CrmCustomer $customer */
            $customer = CrmCustomer::find()->where(['id'=>$this->customer_id])->one();
            if(isset($customer->created_at)){
                return date('Y-m-d',$customer->created_at);
            }
            return '';
        };
        $fields['label_color'] = function() {
            if (isset($this->label_id) && $this->label_id != 0){
                /** @var Tag $label */
                $label = Tag::find()->where(['id'=>$this->label_id])->one();
                return $label->color;
            }else{
                return '';
            }
        };

        $fields['public_name'] = 'public_name';
        $fields['customer_name'] = 'customer_name';
        $fields['customer_number'] = 'customer_number';
        return $fields;
    }
}