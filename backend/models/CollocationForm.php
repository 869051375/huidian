<?php
namespace backend\models;

use common\models\Collocation;
use common\models\Product;
use yii\base\Model;

/**
 * Class ProductForm
 * @package backend\models
 *
 */
class CollocationForm extends Model
{
    public $collocation_product_id;
    public $product_id;
    public $desc;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['desc'], 'trim'],
            [['product_id', 'collocation_product_id', 'desc'], 'required'],
            ['collocation_product_id', 'required'],
            ['collocation_product_id', 'validateProductId'],
            ['collocation_product_id', 'validateRelatedProductId'],
            ['desc', 'string', 'max' => 30],
        ];
    }

    public function validateRelatedProductId()
    {
        $model = Product::findOne($this->product_id);
        if(null != $model)
        {
            if($model->isPayAfterService())
            {
                $this->addError('collocation_product_id', '先服务后付费商品不允许添加关联搭配商品！');
            }
        }
        if($this->collocation_product_id == $this->product_id)
        {
            $this->addError('collocation_product_id', '不能关联自己！');
        }
    }

    public function validateProductId()
    {
        $r_id = $this->collocation_product_id;
        $p_id = $this->product_id;
        $data = Collocation::find()
            ->andWhere(['=','collocation_product_id',$r_id])
            ->andWhere(['=','product_id',$p_id])
            ->All();
        if(!empty($data))
        {
            $this->addError('collocation_product_id', '您已经添加过了！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'collocation_product_id' => '搭配商品',
            'desc' => '搭配商品简介',
        ];
    }

    /**
     * @return Collocation
     */
    public function save()
    {
        $product_related = new Collocation();
        $product_related->product_id = $this->product_id;
        $product_related->collocation_product_id = $this->collocation_product_id;
        $product_related->desc = $this->desc;
        return $product_related->save() ? $product_related : null;
    }
}