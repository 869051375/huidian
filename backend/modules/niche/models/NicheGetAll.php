<?php
namespace backend\modules\niche\models;
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 2018/11/21
 * Time: 2:27 PM
 */

use common\models\Niche;
class NicheGetAll extends Niche{

    public $label_color;
    public $customer_name;
    public $customer_number;
    public $customer_created_at;

    public function fields()
    {
        $fields = parent::fields();
        $fields['label_color'] = 'label_color';
        $fields['customer_name'] = 'customer_name';
        $fields['customer_number'] = 'customer_number';
        $fields['customer_created_at'] = 'customer_created_at';
        return $fields;
    }
}