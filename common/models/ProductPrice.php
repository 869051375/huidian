<?php

namespace common\models;

use common\biztraits\PriceDetail;
use common\utils\BC;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%product_price}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property string $price
 * @property string $tax
 * @property integer $status
 * @property string $original_price
 * @property string $price_detail
 * @property string $sort
 *
 * @property Province $province
 * @property City $city
 * @property District $district
 */
class ProductPrice extends \yii\db\ActiveRecord
{
    use PriceDetail;
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;
    public $is_bargain;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'province_id', 'city_id', 'district_id', 'status','is_bargain'], 'integer'],
            [['price', 'tax', 'original_price'], 'number'],
            [['price_detail'], 'string'],
            [['province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['product_id', 'province_id'], 'unique', 'targetAttribute' => ['product_id', 'province_id'], 'message' => 'The combination of Product ID and Province ID has already been taken.'],
            [['product_id', 'city_id'], 'unique', 'targetAttribute' => ['product_id', 'city_id'], 'message' => 'The combination of Product ID and City ID has already been taken.'],
            [['product_id', 'district_id'], 'unique', 'targetAttribute' => ['product_id', 'district_id'], 'message' => 'The combination of Product ID and District ID has already been taken.'],
            ['status', 'validateStatus'],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $product = Product::findOne($this->product_id);
        if(!$insert && !$this->areaPriceIsValidate($product))
        {
            $cmd = Yii::$app->db->createCommand();
                $cmd->update(Product::tableName(),
                    ['status' => Product::STATUS_OFFLINE],
                    ['id' =>$this->product_id]);
                $cmd->execute();
        }
        //禁用标准商品的区域价格，对应的套餐商品的区域价格也同步禁用，当全部禁用完，套餐商品下线
        if(!$insert && !$this->isEnabled())
        {
            /** @var array $list */
            $list = PackageProduct::find()->where(['product_id' => $this->product_id])->asArray()->all();
            $ids = ArrayHelper::getColumn($list, 'package_id');
            if(!empty($ids))
            {
                $cmd = Yii::$app->db->createCommand();
                $cmd->update(ProductPrice::tableName(),
                    ['status' => ProductPrice::STATUS_DISABLED],
                    ['and', ['in', 'product_id', $ids], ['district_id' => $this->district_id]]);
                $cmd->execute();
            }

            //当套餐商品下的区域价格是最后一个，对套餐商品进行下线操作
            $packageProducts = Product::find()->where(['in', 'id', $ids])->all();
            foreach ($packageProducts as $packageProduct)
            {
                /** @var Product $packageProduct */
                if(!$this->areaPriceIsValidate($packageProduct))
                {
                    $cmd = Yii::$app->db->createCommand();
                    $cmd->update(Product::tableName(),
                        ['status' => Product::STATUS_OFFLINE],
                        ['id' =>$packageProduct->id]);
                    $cmd->execute();
                }
            }
        }
    }

    //删除区域价格，对商品的状态进行操作
    public function afterDelete()
    {
        parent::afterDelete();
        $product = Product::findOne($this->product_id);
        //删除商品的区域价格，如果区域价格为空，或者区域价格全部没有开启，则对商品进行下线操作
        if(!$this->areaPriceIsValidate($product))
        {
            $cmd = Yii::$app->db->createCommand();
            $cmd->update(Product::tableName(),
                ['status' => Product::STATUS_OFFLINE],
                ['id' =>$this->product_id]);
            $cmd->execute();
        }

        //删除商品的区域价格，对应的套餐商品也需要禁用，当套餐商品全部区域价格没有开启，则要对套餐进行下线处理
        $list = PackageProduct::find()->where(['product_id' => $this->product_id])->asArray()->all();
        $ids = ArrayHelper::getColumn($list, 'package_id');
        //套餐区域价格禁用
        if(!empty($ids))
        {
            $cmd = Yii::$app->db->createCommand();
            $cmd->update(ProductPrice::tableName(),
                ['status' => ProductPrice::STATUS_DISABLED],
                ['and', ['in', 'product_id', $ids], ['district_id' => $this->district_id]]);
            $cmd->execute();
        }

        //套餐最后一个区域价格禁用时，套餐下线
        $packageProducts = Product::find()->where(['in', 'id', $ids])->all();
        foreach ($packageProducts as $packageProduct)
        {
            /** @var Product $packageProduct */
            if(!$this->areaPriceIsValidate($packageProduct))
            {
                $cmd = Yii::$app->db->createCommand();
                $cmd->update(Product::tableName(),
                    ['status' => Product::STATUS_OFFLINE],
                    ['id' =>$packageProduct->id]);
                $cmd->execute();
            }
        }
    }

    public function validateStatus()
    {
        if($this->status == ProductPrice::STATUS_ENABLED)
        {
            $packageProduct = Product::findOne($this->product_id);

            if(null == $packageProduct)
            {
                $this->addError('status', '操作有误。');
            }
            else
            {
                if($packageProduct->isPackage() && !$this->packageAreaPriceIsValidate($packageProduct))
                {
                    $this->addError('status', '该套餐下的商品不包含该区域或未启用。');
                }
            }
        }
    }
    /**
     * @param Product $product
     * @return bool
     */
    public function areaPriceIsValidate($product)
    {
        if($product->is_area_price == 1)
        {
            if(empty($product->productPrices))
            {
                return false;
            }
            foreach($product->productPrices as $productPrice)
            {
                if($productPrice->isEnabled())
                {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * @param $package Product
     * @return bool
     */
    public function packageAreaPriceIsValidate($package)
    {
        if($package->isPackage())
        {
            foreach($package->packageProducts as $product)
            {
                if($product->isAreaPrice())
                {
                    $pp = $product->getProductPriceByDistrict($this->district_id);
                    if(null == $pp || !$pp->isEnabled())
                    {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function getRegionFullName()
    {
        return $this->province_name.' '.$this->city_name.' '.$this->district_name;
    }

    public function getRemitAmount()
    {
        return BC::sub($this->original_price, $this->price) > 0 ? BC::sub($this->original_price, $this->price) : 0;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'price' => 'Price',
            'tax' => 'Tax',
            'status' => 'Status',
            'original_price' => 'Original Price',
            'price_detail' => 'Price Detail',
        ];
    }

    public static function statusList()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }

    public function getStatusName()
    {
        $stautsList = ProductPrice::statusList();
        return $stautsList[$this->status];
    }

    public function isEnabled()
    {
        return $this->status == self::STATUS_ENABLED;
    }

    public function getProvince()
    {
        return static::hasOne(Province::className(), ['id' => 'province_id']);
    }

    public function getCity()
    {
        return static::hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getDistrict()
    {
        return static::hasOne(District::className(), ['id' => 'district_id']);
    }
}
