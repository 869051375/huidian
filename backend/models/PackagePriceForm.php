<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\PackageProduct;
use common\models\Product;
use yii\base\Model;

/**
 * Class PackagePriceForm
 * @package backend\models
 *
 */
class PackagePriceForm extends Model
{
    public $deposit;
    public $price;
    public $wx_remit_amount;
    public $tax;
    public $original_price;
    public $price_detail;
    public $is_area_price;
    public $district_price;
    public $service_area;
    public $remit_amount;

    public $is_installment;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_area_price', 'is_installment'], 'boolean'],
            ['service_area', 'string', 'max' => 6],
            [['deposit', 'original_price', 'wx_remit_amount'], 'number', 'min' => 0, ],
            [['deposit', 'original_price', 'wx_remit_amount'], 'default', 'value' => 0],
            [['price', 'tax'], 'safe', 'on' => 'init-form'],
            [['is_area_price'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'deposit' => '押金',
            'is_area_price' => '区分区域',
            'price' => '商品销售价',
            'remit_amount' => '优惠金额',
            'wx_remit_amount' => '微信端下单优惠金额',
            'service_area' => '商品服务区域',
            'tax' => '税额',
            'original_price' => '商品原价',
            'price_detail' => '价格明细',
            'city_id' => '服务区域及价格',
            'district_price' => '区域及价格',
            'is_installment' => '付款方式',
        ];
    }

    public function attributeHints()
    {
        return [
            'deposit' => '不填默认为0',
            'service_area' => '当非区分区域商品时有效，用于提示用户该商品的服务区域',
            'wx_remit_amount' => '当用户在微信中下单后，直接减免的金额',
        ];
    }

    /**
     * @param Product $product
     *
     * @return boolean|int
     */
    public function save($product)
    {
        if(!$this->validate()) return false;
        $product->deposit = $this->deposit;
        if(PackageProduct::isAreaPrice($product->id))
        {
            $product->is_area_price = 1;
            $product->original_price = $this->original_price;
        }
        else
        {
            $product->is_area_price = $this->is_area_price;
            $product->original_price = PackageProduct::getPackageOriginalPrice($product);
        }
        $product->wx_remit_amount = $this->wx_remit_amount;
        $product->service_area = $this->service_area;
        $product->is_installment = $this->is_installment;
        if($product->save(false))
        {
            //新增后台操作日志
            AdministratorLog::logUpdateProductPriceType($product);
            return true;
        }
        return false;
    }
}