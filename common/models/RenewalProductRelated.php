<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%renewal_product_related}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $remark
 * @property string $product_ids
 * @property integer $status
 *
 */
class RenewalProductRelated extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%renewal_product_related}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['status'], 'integer'],
            [['status'], 'validateStatus', 'on'=>'status'],
            [['name'], 'string', 'max' => 15],
            [['remark'], 'string', 'max' => 80],
            [['product_ids'], 'string', 'max' => 255],
        ];
    }

    public function validateStatus()
    {
        if(null == $this)
        {
            $this->addError('id', '您的操作有误。');
        }
        else
        {
            if(empty($this->getProductIds()))
            {
                $this->addError('id', '必须添加包含续费商品才能上线。');
            }
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'name' => '名称',
            'remark' => '备注说明',
            'product_ids' => '包含商品',
            'status' => 'Status',
        ];
    }

    /**
     * @return Product[]
     */
    public function getProductList()
    {
        $ids = $this->getProductIds();
        if(empty($ids)) return [];
        return Product::find()->where(['in', 'id', $this->getProductIds()])->all();
    }

    public function getProductIds()
    {
        $ids = trim($this->product_ids, ',');
        if(empty($ids)){
            return [];
        }
        return explode(',', trim($ids, ','));
    }

    public function addProductId($id)
    {
        $ids = $this->getProductIds();
        $ids[] = $id;
        $this->setProductIds($ids);
    }

    public function setProductIds($product_ids)
    {
        if(!empty($product_ids)){
            $product_ids = array_unique($product_ids);
            $this->product_ids = ','.implode(',', $product_ids).',';
        }
        else
        {
            $this->product_ids = '';
        }
    }

    /**
     * @param $product_id
     * @return bool 是否成功
     */
    public function removeProduct($product_id)
    {
        $ids = $this->getProductIds();
        ArrayHelper::removeValue($ids, $product_id);
        $this->setProductIds($ids);
        if($this->save(false))
        {
            if(empty($this->getProductIds()))
            {
                $this->status = RenewalProductRelated::STATUS_DISABLED;
                $this->save(false);
            }
        }
        return $this->save(false);
    }
}
