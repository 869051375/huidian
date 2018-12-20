<?php

namespace backend\models;

use common\models\Administrator;
use common\models\CrmOpportunity;
use common\models\CrmOpportunityProduct;
use common\models\Product;
use common\models\ProductPrice;
use Yii;
use yii\base\Model;

class OpportunityProductForm extends Model
{
    public $update_id;
    public $product_id;
    public $product_price_id;
    public $price;

    public $category_name;
    public $product_name;
    public $original_price;

    public $qty = 1;
    public $subtotal_price;
    /**
     * @var CrmOpportunityProduct
     */
    public $updateModel;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var ProductPrice
     */
    public $productPrice;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['product_id', 'product_price_id', 'qty'], 'required'],
            [['product_id'], 'validateProductId'],
            [['update_id', 'qty'], 'integer'],
            [['product_price_id'], 'validateProductPriceId'],
            [['price'], 'number', 'min' => 0],
            [['category_name', 'product_name', 'original_price', 'subtotal_price'], 'safe'],
            [['qty'], "validateQty", 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function validateQty()
    {
        if(empty($this->qty))
        {
            $this->addError('qty', '数量不能为空');
        }
    }

    public function validateProductId()
    {
        $this->product = Product::findOne($this->product_id);
        if(null == $this->product)
        {
            $this->addError('product_id', '您所选择的商品不存在');
        }
//        if(null == $this->product->department)
//        {
//            $this->addError('product_id', '商品 '.$this->product->name.' 尚未设置商机分配部门');
//        }
        $opportunityAssignDepartments = $this->product->opportunityAssignDepartments;
        if(null == $opportunityAssignDepartments)
        {
            //未设置商机分配部门
            $this->addError('product_id', '商品 '.$this->product->name.' 尚未设置商机分配部门');
        }
        else
        {
            $companies = [];
            foreach ($opportunityAssignDepartments as $opportunityAssignDepartment)
            {
                $companies[] = $opportunityAssignDepartment->company_id;
            }
            /** @var \common\models\Administrator $administrator */
            //客户商机一起创建时选择商机负责人（业务员）
            $administrator = $this->administrator ? $this->administrator : Yii::$app->user->identity;
            //如果业务员启用了公司与部门功能
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
                if(!in_array($administrator->company_id, $companies))
                {
                    //该商品不属于业务员所在公司
                    $this->addError('product_id', '商品 '.$this->product->name.' 该商品不属于您所在公司');
                }
            }
        }
        if($this->product->isPackage())
        {
            $this->addError('product_id', '不能选择套餐作为商机商品');
        }
    }

    public function validateProductPriceId()
    {
        if($this->product->isAreaPrice())
        {
            $this->productPrice = ProductPrice::findOne($this->product_price_id);
            if(null == $this->productPrice || $this->productPrice->product_id != $this->product_id)
            {
                $this->addError('product_price_id', '您所选择的商品不存在');
            }
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }

        if($this->updateModel)
        {
            return $this->update();
        }

        $model = new CrmOpportunityProduct();
        $model->product_id = $this->product_id;
        $model->product_name = $this->product->alias;
        $model->opportunity_id = $this->opportunity->id;
        $model->top_category_id = $this->product->top_category_id;
        $model->category_id = $this->product->category_id;
        $model->category_name = $this->product->category->name;
        $model->top_category_name = $this->product->topCategory->name;
        if($this->product->isAreaPrice())
        {
            $model->province_id = $this->productPrice->province_id;
            $model->province_name = $this->productPrice->province_name;
            $model->city_id = $this->productPrice->city_id;
            $model->city_name = $this->productPrice->city_name;
            $model->district_id = $this->productPrice->district_id;
            $model->district_name = $this->productPrice->district_name;
        }
        $model->price = $this->price;
        $model->qty = $this->qty;
        $model->save(false);
        return $model;
    }

    private function update()
    {
        $model = $this->updateModel;
        $model->price = $this->price;
        $model->qty = $this->qty;
        $model->save(false);
        return $model;
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
        ];
    }
}