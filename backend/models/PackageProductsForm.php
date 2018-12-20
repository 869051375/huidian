<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\Flow;
use common\models\Product;
use common\models\ProductCategory;

/**
 * Class PackageProductsForm
 * @package backend\models
 *
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Flow $flow
 */
class PackageProductsForm extends ProductForm
{
    public  $is_package=1;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'top_category_id',  'category_id','alias', 'explain', 'is_experience'], 'required'],
            [['top_category_id', 'category_id', 'traded_init', 'form_id', 'is_home', 'is_home_nav', 'home_nav_sort', 'is_hot', 'is_experience','is_show_list','show_list_sort', 'is_inventory_limit', 'inventory_qty'], 'integer'],
//            [['slug'], 'validateSlug'],
            [['name'], 'string', 'max' => 15],
            [['keywords'], 'string', 'max' => 80],
            [['spec_name'], 'string', 'max' => 15],
            [['alias'], 'string', 'max' => 40],
            [['explain'], 'string', 'max' => 150],
            [['name', 'alias', 'spec_name',  'explain', 'traded_init', 'home_sort', 'home_nav_sort', 'inventory_qty'], 'trim'],
//            ['slug', 'match', 'pattern' => '/^[a-zA-Z]+[\w-]*$/', 'message' => '只能包含字母、数字、中横线（-）、下划线（_）并且必须以字母开头'],
            ['category_id', 'validateCategoryId'],
            [['home_sort', 'home_nav_sort', 'inventory_qty'], 'integer', 'skipOnEmpty'=>false],
            [['traded_init'], 'default', 'value' => '0'],
            [['traded_init'], 'integer', 'min' => '0', 'skipOnEmpty'=>false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '套餐名称',
            'alias' => '套餐别名',
            'spec_name' => '关联套餐命名',
            'top_category_id' => '所属一级分类',
            'category_id' => '所属分类',
//            'slug' => 'URL关键字',
            'keywords' => '搜索关键词',
            'explain' => '概要说明',
            'traded_init' => '初始交易量',
            'form_id' => '关联订单表单',
            'is_home' => '显示在首页',
            'home_sort' => '首页排序值',
            'is_home_nav' => '首页顶部导航热门商品',
            'home_nav_sort' => '首页顶部导航热门商品排序值',
            'is_hot' => '首页热销',
            'is_experience' => '参与服务体验馆',
            'is_show_list' => '显示在列表页',
            'show_list_sort' => '列表页排序值',
            'is_inventory_limit' => '库存限制',
            'inventory_qty' => '库存量',
        ];
    }

    public function attributeHints()
    {
        return [
            'name' => '在首页显示的套餐名称',
//            'alias' => '在商品详情页显示的套餐名称，不填默认为套餐名称',
            'spec_name' => '该套餐被关联后，在商品详情页【选择套餐】处显示的名称，不填默认为套餐名称',
//            'slug' => '用于网站前台商品页链接地址后缀，请慎重填写，保存后不可编辑',
            'keywords' => '最多80个字符，每个关键词之间用中文逗号隔开，如：公司注册，办执照，代办执照',
            'explain' => '商品详情页套餐名称下方的说明',
            'home_sort' => '请输入数字，数字大小决定该套餐所在二级分类下的所有商品的排列顺序，从小到大依次为左上，右上，左下，右下',
            'home_nav_sort' => '请输入数字，数字大小决定该套餐所在一级分类下的热门商品的排列顺序，从小到大依次为左上，右上，左下，右下',
            'show_list_sort' => '请输入数字，数字大小决定该商品所在列表页排列顺序，从小到大依次排序',
            'inventory_qty' => '卖完该数量后商品自动下线（以支付全款为标准）',
        ];
    }

    /**
     * @return Product feng
     */
    public function save()
    {
        $product = new Product();
        $product->load($this->attributes, '');
        $product->platform_id=1;
        if($product->alias == ''){
            $product->alias = $product->name;
        }
        if($product->spec_name == ''){
            $product->spec_name = $product->name;
        }
        $product->keywords = '，'.trim($this->keywords, '，').'，';
        $product->is_package = Product::PACKAGE_ACTIVE;
        if(!$product->save(false))
        {
            return null;
        }
        return $product;
    }

    /**
     * @param Product $product
     * @return null
     */
    public function update($product)
    {
        $this->_product = $product;
        if(!$this->validate()) return false;
        $product->load($this->attributes, '');
        if($product->spec_name == ''){
            $product->spec_name = $product->name;
        }
        $product->keywords = '，'.trim($this->keywords, '，').'，';
        if($product->update(false))
        {
            //新增后台操作日志
            AdministratorLog::logPackageProductUpdate($product);
            return true;
        }
        return false;
    }
}
