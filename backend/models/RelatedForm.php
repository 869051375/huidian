<?php
namespace backend\models;

use common\models\ProductRelated;
use yii\base\Model;

/**
 * Class ProductForm
 * @package backend\models
 *
 */
class RelatedForm extends Model
{
    public $related_product_id;
    public $product_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'related_product_id'], 'safe'],
            ['related_product_id', 'required'],
            ['related_product_id', 'validateProductId'],
            ['related_product_id', 'validateRelatedProductId'],
        ];
    }

    public function validateRelatedProductId()
    {
        if($this->related_product_id == $this->product_id)
        {
            $this->addError('related_product_id', '不能关联自己！');
        }
    }

    public function validateProductId()
    {
        $r_id = $this->related_product_id;
        $p_id = $this->product_id;
        $data = ProductRelated::find()
            ->andWhere(['=','related_product_id',$r_id])
            ->andWhere(['=','product_id',$p_id])
            ->All();
        if(!empty($data))
        {
            $this->addError('related_product_id', '您已经添加过了！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'related_product_id' => '关联商品',
        ];
    }

    /**
     * @return ProductRelated
     */
    private function createProductRelated()
    {
        $model = new ProductRelated();
        $model->product_id = $this->related_product_id;
        $model->related_product_id = $this->product_id;
        $model->save(false);
        return $model;
    }

    /**
     * @return ProductRelated
     */
    public function save()
    {
        $this->createProductRelated();
        $product_related = new ProductRelated();
        $product_related->product_id = $this->product_id;
        $product_related->related_product_id = $this->related_product_id;
        return $product_related->save() ? $product_related : null;
    }

}
