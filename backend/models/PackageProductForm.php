<?php
namespace backend\models;

use common\models\PackageProduct;
use common\models\Product;
use yii\base\Model;

class PackageProductForm extends Model
{
    public $package_id;
    public $product_id;
    public $sort;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_id', 'product_id'], 'safe'],
            ['product_id','required'],
            //['product_id','validateProductId'],
            ['product_id','validatePackageProductId'],
        ];
    }

    public function validatePackageProductId()
    {
        if($this->product_id == $this->package_id)
        {
            $this->addError('product_id', '不能添加自己!');
        }
        $product = Product::findOne($this->product_id);
        if($product->isBargain())
        {
            $this->addError('product_id', '不能添加议价商品!');
        }
        $package= Product::findOne($this->package_id);
        if($package->isConfirmed())
        {
            $this->addError('product_id', '套餐商品已确认，不能再添加!');
        }
    }

    public function validateProductId()
    {
//        $p_id = $this->product_id;
//        $pp_id = $this->package_id;
//        $data = PackageProduct::find()
//            ->andWhere(['=','product_id',$p_id])
//            ->andWhere(['=','package_id',$pp_id])
//            ->All();
//        if(!empty($data))
//        {
//            $this->addError('product_id', '您已经添加过了!');
//        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => '添加商品',
        ];
    }

    /**
     * @return PackageProduct|null
     */
    public function save()
    {
        if(!$this->validate()) return null;
        $model = new PackageProduct();
        $model->package_id = $this->package_id;
        $model->product_id = $this->product_id;
        return $model->save() ? $model : null;
    }
}
