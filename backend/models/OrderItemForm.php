<?php
namespace backend\models;

use yii\base\Model;

/**
 * Class OrderForm
 * @package backend\models
 */
class OrderItemForm extends Model
{
    public $product_id;
    public $product_price_id;
    public $qty = 1;
    public $pay_way = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'qty'], 'required'],
            [['qty'], 'integer', 'min' => 1],
            [['qty'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => '商品别名',
            'product_price_id' => '地区',
            'qty' => '数量',
            'pay_way' => '付款方式',
        ];
    }

}