<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\City;
use common\models\District;
use common\models\Product;
use common\models\ProductPrice;
use common\models\Province;
use Yii;
use yii\base\Model;
use yii\db\Query;

/**
 * Class ProductPriceForm
 * @property Province $province
 * @property City $city
 * @property District $district
 */
class DistrictPriceForm extends Model
{
    public $product_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $district_name;
    public $price;
    public $original_price;
    public $price_detail;
    public $district_price;
    public $sort = 100;

    /**
     * @var District
     */
    private $district;

    /**
     * @var Product
     */
    private $product;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['original_price', 'district_id'], 'required'],
            [['product_id', 'district_id'], 'integer'],
            [['sort'], 'integer'],
            [['price', 'original_price'], 'number'],
            [['price_detail'], 'string'],
            //[['product_id'], 'exist', 'targetClass' => Product::className(), 'targetAttribute' => 'id'],
            [['district_name'], 'string', 'max' => 15],
            ['product_id', 'validateProductId'],
            ['district_id', 'validateDistrictId'],
            [[ 'original_price'], 'default', 'value' => 0],
            [[ 'original_price'], 'number', 'min' => 0],
            [['sort'], 'default', 'value' => 0],
        ];
    }

    public function validateProductId()
    {
        $this->product = Product::findOne($this->product_id);
        if(null == $this->product)
        {
            $this->addError('district_id', '找不到商品信息。');
        }
        else if($this->product->isPackage())
        {
            foreach ($this->product->packageProducts as $product)
            {
                if($product->isAreaPrice())
                {
                    $productPrice = $product->getProductPriceByDistrict($this->district_id);
                    if(null == $productPrice)
                    {
                        $this->addError('district_id', '该套餐不能选择该区域。');
                    };
                }
            }
        }
    }

    public function validateDistrictId()
    {
        $district = District::findOne($this->district_id);
        if(null == $district)
        {
            $this->addError('district_id', '找不到地区信息');
        }
        else if(ProductPrice::find()->where(['product_id'=> $this->product_id, 'district_id' => $this->district_id])->count() > 0)
        {
            $this->addError('district_id', '不能重复添加改地区。');
        }
        else
        {
            $this->district = $district;
        }
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id!=:city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id!=:province_id', [':province_id' => $this->province_id])
            ->one();
    }

    public function getCity()
    {
        return City::find()->where(['id' => $this->city_id])
            ->andWhere('province_id!=:province_id', [':province_id' => $this->province_id])->one();
    }

    public function getProvince()
    {
        return Province::find()->where(['id' => $this->province_id])->one();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => '商品ID',
            'district_id' => '地区',
            'city_id' => '城市',
            'province_id' => '省份',
            'district_name' => '地区名称',
            'price' => '商品销售价',
            'original_price' => '商品原价',
            'price_detail' => '价格明细',
            'district_price' => '区域及价格',
            'sort' => '排序',
        ];
    }

    /**
     * @return ProductPrice
     */
    public function save()
    {
        $model = new ProductPrice();
        $model->product_id = $this->product_id;
        $model->original_price = $this->original_price;
        $model->sort = $this->sort;
        $model->province_id = $this->district->province_id;
        $model->city_id = $this->district->city_id;
        $model->district_id = $this->district->id;
        $model->province_name = $this->district->province_name;
        $model->city_name = $this->district->city_name;
        $model->district_name = $this->district->name;
        $model->status = 0;
        $model->price = 0;
        $model->tax = 0;
        if($model->save(false))
        {
            //新增后台操作日志
            AdministratorLog::logSaveDistrictPrice($this->product, $model);
            return $model;
        }
        return null;
    }
}