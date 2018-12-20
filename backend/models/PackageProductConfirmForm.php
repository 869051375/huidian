<?php
namespace backend\models;

use common\models\PackageProduct;
use common\models\Product;
use yii\base\Model;

class PackageProductConfirmForm extends Model
{
    /**
     * @var Product
     */
    public $product;

    public function rules()
    {
        return [
            [['product'], 'validateProduct'],
        ];
    }

    public function validateProduct()
    {
        $model = PackageProduct::find()->where(['package_id' => $this->product->id])->one();
        if($this->product->isConfirmed())
        {
            $this->addError('product', '套餐商品已经确认提交了!');
        }
        if(null == $model)
        {
            $this->addError('product', '没有添加套餐商品，无法操作!');
        }
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function confirm($product)
    {
        //如果套餐下的商品只要有一个商品是区分区域，本套餐就区分区域
        if(PackageProduct::isAreaPrice($product->id))
        {
            $product->is_area_price = 1;
            if(!$product->save(false))
            {
                $this->addError('product', '套餐商品区分区域保存失败!');
                return false;
            }
        }
        if(!$this->product->confirmed())
        {
            $this->addError('product', '确认套餐商品失败!');
            return false;
        }
        return true;
    }
}
