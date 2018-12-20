<?php
namespace backend\models;

use common\models\Product;
use yii\base\Model;

/** @var Product $product */

class FlowRelationForm extends Model
{
    public $flow_id;
    public $product_id;

    /**
     * @var Product
     */
    private $product;

    public function rules()
    {
        return [
            [['flow_id', 'product_id'], 'required'],
            ['product_id', 'validateProductId']
        ];
    }

    public function validateProductId()
    {
        $this->product = Product::find()->where(['id' => $this->product_id, 'flow_id' => $this->flow_id])->one();
        if(null == $this->product)
        {
            $this->addError('product_id', '关联不存在。');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'flow_id' => '流程ID',
            'product_id' => '商品ID',
        ];
    }

    public function remove()
    {
        if(!$this->validate()) return false;
        $this->product->flow_id = 0;
        return $this->product->save(false);
    }
}