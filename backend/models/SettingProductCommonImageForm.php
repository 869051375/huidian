<?php
namespace backend\models;

use common\models\Property;
use yii\base\Model;

class SettingProductCommonImageForm extends Model
{
    public $product_common_image1;
    public $product_common_image2;
    public $product_common_image3;
    public $product_common_image4;
    public $product_common_image5;

    public $product_common_m_image1;
    public $product_common_m_image2;
    public $product_common_m_image3;
    public $product_common_m_image4;
    public $product_common_m_image5;

    private $_attributeLabels;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_common_image1', 'product_common_image2',
                'product_common_image3', 'product_common_image4', 'product_common_image5'], 'string'],
            [['product_common_m_image1', 'product_common_m_image2',
                'product_common_m_image3', 'product_common_m_image4', 'product_common_m_image5'], 'string'],
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        /** @var Property[] $properties */
        $properties = Property::find()
                    ->where(['in', 'key',['product_common_image1', 'product_common_image2',
                        'product_common_image3', 'product_common_image4', 'product_common_image5', 'product_common_m_image1', 'product_common_m_image2',
                        'product_common_m_image3', 'product_common_m_image4', 'product_common_m_image5']])->all();
        foreach ($properties as $property)
        {
            $key = $property->key;
            $this->$key = $property->getValue();
            $this->_attributeLabels[$key] = $property->desc;
        }
    }

    public function attributeLabels()
    {
        if(empty($this->_attributeLabels['product_common_image1']))
        {
            $this->_attributeLabels['product_common_image1'] = '默认商品图片1';
        }
        if(empty($this->_attributeLabels['product_common_image2']))
        {
            $this->_attributeLabels['product_common_image2'] = '默认商品图片2';
        }
        if(empty($this->_attributeLabels['product_common_image3']))
        {
            $this->_attributeLabels['product_common_image3'] = '默认商品图片3';
        }
        if(empty($this->_attributeLabels['product_common_image4']))
        {
            $this->_attributeLabels['product_common_image4'] = '默认商品图片4';
        }
        if(empty($this->_attributeLabels['product_common_image5']))
        {
            $this->_attributeLabels['product_common_image5'] = '默认商品图片5';
        }
        if(empty($this->_attributeLabels['product_common_m_image1']))
        {
            $this->_attributeLabels['product_common_m_image1'] = '默认商品图片1';
        }
        if(empty($this->_attributeLabels['product_common_m_image2']))
        {
            $this->_attributeLabels['product_common_m_image2'] = '默认商品图片2';
        }
        if(empty($this->_attributeLabels['product_common_m_image3']))
        {
            $this->_attributeLabels['product_common_m_image3'] = '默认商品图片3';
        }
        if(empty($this->_attributeLabels['product_common_m_image4']))
        {
            $this->_attributeLabels['product_common_m_image4'] = '默认商品图片4';
        }
        if(empty($this->_attributeLabels['product_common_m_image5']))
        {
            $this->_attributeLabels['product_common_m_image5'] = '默认商品图片5';
        }
        return $this->_attributeLabels;
    }

    public function save()
    {
        foreach ($this->attributes as $attribute => $value)
        {
            Property::set($attribute, trim($value));
        }
        return true;
    }
}