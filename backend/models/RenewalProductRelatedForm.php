<?php
namespace backend\models;

use common\models\Product;
use common\models\RenewalProductRelated;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class RenewalProductRelatedForm
 */
class RenewalProductRelatedForm extends Model
{
    public $id;
    public $product_id;

    /**
     * @var RenewalProductRelated
     */
    public $renewalProductRelated;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'validateId'],
            ['product_id','required'],
            [['product_id'], 'validateProductId'],
        ];
    }

    public function validateId()
    {
        $this->renewalProductRelated = RenewalProductRelated::findOne($this->id);
        if(null == $this->renewalProductRelated)
        {
            $this->addError('id', '您的操作有误。');
        }
    }

    public function validateProductId()
    {
        if(null != $this->renewalProductRelated)
        {
            $ids = $this->renewalProductRelated->getProductIds();
            if(in_array($this->product_id, $ids))
            {
                $this->addError('product_id', '您已添加过此商品。');
            }

            //暂时product_ids最大限制在240个字符（暂定）
            if(strlen($this->renewalProductRelated->product_ids) > 240)
            {
                $this->addError('product_id', '添加的商品已达最大上限。');
            }
        }
        $product = Product::findOne($this->product_id);
        if(null == $product)
        {
            $this->addError('product_id', '您的操作有误。');
        }
        else
        {
            if($product->isPackage())
            {
                $this->addError('product_id', '套餐商品不能被添加为关联续费商品。');
            }
            elseif(!$product->isRenewal())
            {
                $this->addError('product_id', '非续费商品不能被添加为关联续费商品。');
            }
        }
        //每个商品只能被添加在某个包含组（不能被同时添加再多个包含组中）
        $renewalProduct = RenewalProductRelated::find()->andWhere(['like', 'product_ids', ','.$this->product_id.','])->orderBy(['id' => SORT_ASC])->one();
        if(null != $renewalProduct)
        {
            $this->addError('product_id', '此商品已经被添加到别的关联续费商品组中，不能再被添加。');
        }

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'product_id' => '添加商品',
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $ids = $this->renewalProductRelated->getProductIds();
        $product_id = [];
        $product_id[] = $this->product_id;
        $this->renewalProductRelated->setProductIds(ArrayHelper::merge($ids, $product_id));
        return $this->renewalProductRelated->save(false);
    }
}

