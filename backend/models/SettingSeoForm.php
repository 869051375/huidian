<?php
namespace backend\models;

use common\models\Property;
use yii\base\Model;

class SettingSeoForm extends Model
{
    public $home_seo_title; // 首页SEO标题
    public $home_seo_keywords; // 首页SEO关键词
    public $home_seo_description; // 首页SEO描述
    public $head_meta; // head meta
    public $stats_code; // 统计代码（电脑网页）
    public $stats_code_m; // 统计代码(移动网页)

    private $_attributeLabels;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['home_seo_title', 'home_seo_keywords', 'home_seo_description'], 'string'],
            [['head_meta', 'stats_code', 'stats_code_m'], 'string'],
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        /** @var Property[] $properties */
        $properties = Property::find()
                    ->where(['in', 'key',['home_seo_title', 'home_seo_keywords', 'home_seo_description', 'head_meta', 'stats_code', 'stats_code_m']])
                    ->all();
        foreach ($properties as $property)
        {
            $key = $property->key;
            $this->$key = $property->getValue();
            $this->_attributeLabels[$key] = $property->desc;
        }
    }

    public function attributeLabels()
    {

        if(empty($this->_attributeLabels['home_seo_title']))
        {
            $this->_attributeLabels['home_seo_title'] = '首页SEO标题';
        }
        if(empty($this->_attributeLabels['home_seo_keywords']))
        {
            $this->_attributeLabels['home_seo_keywords'] = '首页SEO关键词';
        }
        if(empty($this->_attributeLabels['home_seo_description']))
        {
            $this->_attributeLabels['home_seo_description'] = '首页SEO描述';
        }
        if(empty($this->_attributeLabels['head_meta']))
        {
            $this->_attributeLabels['head_meta'] = 'Head Meta';
        }
        if(empty($this->_attributeLabels['stats_code']))
        {
            $this->_attributeLabels['stats_code'] = '统计代码（PC）';
        }
        if(empty($this->_attributeLabels['stats_code_m']))
        {
            $this->_attributeLabels['stats_code_m'] = '统计代码（移动）';
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