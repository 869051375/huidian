<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\City;
use common\models\District;
use common\models\Product;
use common\models\ProductPrice;
use common\models\Province;
use common\utils\BC;
use Yii;
use yii\base\Model;

/**
 * Class ProductPriceForm
 * @property Province $province
 * @property City $city
 * @property District $district
 */
class DistrictPriceUpdateForm extends Model
{
    public $id;
    public $product_id;
    public $original_price;
    public $sort = 100;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductPrice
     */
    private $productPrice;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['original_price', 'id', 'product_id'], 'required'],
            ['product_id', 'validateProductId'],
            [['sort'], 'integer'],
            ['id', 'validateId'],
            [['original_price'], 'default', 'value' => 0],
            [['original_price'], 'number', 'min' => 0],
            [['sort'], 'default', 'value' => 0],
        ];
    }

    public function validateId()
    {
        $this->productPrice = ProductPrice::findOne($this->id);
        if(null == $this->productPrice)
        {
            $this->addError('original_price', '找不到数据。');
        }
    }

    public function validateProductId()
    {
        $this->product = Product::findOne($this->product_id);
        if(null == $this->product)
        {
            $this->addError('original_price', '找不到商品信息。');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => '商品ID',
            'original_price' => '商品原价',
            'sort' => '排序',
        ];
    }

    /**
     * @return ProductPrice
     */
    public function save()
    {
        if(!$this->validate()) return null;

        //编辑套餐商品区分区域价格时，进行最新价格计算
        if($this->product->isPackage())
        {
            $productPrice = ProductPrice::findOne($this->id);
            $packageOriginalPrice = 0.00;
            foreach ($this->product->packageProducts as $product)
            {
                //如果套餐下的商品价格区分区域，则进行区域校验
                if($product->isAreaPrice())
                {
                    $productPrice = $product->getProductPriceByDistrict($productPrice->district_id);
                    if(null == $productPrice) return null;
                    $packageOriginalPrice = BC::add($productPrice->price, $packageOriginalPrice);
                }
                else if($product->isBargain())
                {
                    //return ['status' => 200, 'packageOriginalPrice' => '0.00'];
                }
                else
                {
                    $packageOriginalPrice = BC::add($product->price, $packageOriginalPrice);
                }
            }
            $this->original_price = $packageOriginalPrice;
        }

        $this->productPrice->original_price = $this->original_price;
        $this->productPrice->sort = $this->sort;
        return $this->productPrice->save(false) ? $this->productPrice : null;
    }
}