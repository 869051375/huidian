<?php
namespace backend\models;

use common\models\Product;
use common\models\ProductPrice;
use yii\base\Model;

/**
 * Class PriceDetailForm
 * @package backend\models
 *
 */
class PriceDetailForm extends Model
{
    public $name;
    public $price;
    public $unit;
    public $tax_rate = '0.00';
    public $is_invoice;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price', 'unit', 'tax_rate'], 'trim'],
            [['name', 'price', 'unit', 'tax_rate'], 'required'],
            [['name'], 'string', 'max'=>10],
            [['unit'], 'string', 'max'=>3],
            [['price', 'tax_rate'], 'number', 'min'=>0],
            [['tax_rate'], 'number', 'max' => 100 ],
            [['tax_rate'], 'default', 'value' => '0' ],
            [['is_invoice'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '费用类型',
            'price' => '价格',
            'unit' => '单位',
            'tax_rate' => '税率(%)',
            'is_invoice' => '支持开发票',
        ];
    }

    /**
     * @param Product|ProductPrice $model
     * @return null|array
     */
    public function save($model)
    {
        $item = $model->addPriceDetail([
            'name' => $this->name,
            'price' => $this->price,
            'unit' => $this->unit,
            'tax_rate' => $this->tax_rate,
            'is_invoice' => $this->is_invoice == 1 ? true : false,
        ]);
        if($model->save(false)) return $item;
        return null;
    }
}