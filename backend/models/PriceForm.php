<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\Product;
use yii\base\Model;

/**
 * Class PriceForm
 * @package backend\models
 *
 */
class PriceForm extends Model
{
    const INSTALLMENT_ONCE = 0;//一次付款
    const INSTALLMENT_MANY = 1;//分期付款

    public $deposit;
    public $is_bargain;
    public $is_installment;
    public $price;
    public $wx_remit_amount;
    public $tax;
    public $original_price;
    public $price_detail;
    public $is_area_price;
    public $district_price;
    public $service_area;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_bargain', 'is_area_price','is_installment'], 'boolean'],
            ['service_area', 'string', 'max' => 6],
            [['deposit', 'original_price', 'wx_remit_amount'], 'number', 'min' => 0, ],
            [['deposit', 'original_price', 'wx_remit_amount'], 'default', 'value' => 0],
            [['price', 'tax'], 'safe', 'on' => 'init-form']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'deposit' => '押金',
            'is_bargain' => '议价商品',
            'is_area_price' => '区分区域',
            'price' => '商品销售价',
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
        $product->is_bargain = $this->is_bargain;
        $product->is_area_price = $this->is_area_price;
        if($this->is_bargain == 1)
        {
            $product->is_area_price = 0;
        }
        $product->original_price = $this->original_price;
        $product->wx_remit_amount = $this->wx_remit_amount;
//        $product->service_area = $this->service_area;
        $product->is_installment = $this->is_installment;
        if(!$product->is_bargain && $product->is_installment)
        {
            $product->status = Product::STATUS_OFFLINE;
        }
        if($product->save(false))
        {
            //新增后台操作日志
            AdministratorLog::logUpdateProductPriceType($product);
            return true;
        }
        return false;
    }

    public static function getInstallmentList()
    {
        return [
            self::INSTALLMENT_ONCE => '一次付款',
            self::INSTALLMENT_MANY => '分期付款',
        ];
    }
}