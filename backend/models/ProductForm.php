<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\CrmDepartment;
use common\models\Flow;
use common\models\Product;
use common\models\ProductCategory;
use yii\base\Model;

/**
 * Class ProductForm
 * @package backend\models
 *
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property CrmDepartment $department
 * @property Flow $flow
 */
class ProductForm extends Model
{
    /**
     * @var Product
     */
    protected $_product;
    public $name = '';
    public $alias = '';
    public $spec_name = '';
    public $spec_explain = '';
    public $top_category_id = 0;
    public $category_id = 0;
//    public $slug = '';
    public $explain = '';
    public $type = 0;
    public $traded_init = 0;
    public $buy_limit = 0;
    public $industries = '';
    public $flow_id = 0;
    public $form_id = 0;
    public $is_home = 0;
    public $home_sort = 0;
    public $is_home_nav = 0;
    public $home_nav_sort = 0;
    public $is_show_list = 1;
    public $show_list_sort = 0;
    public $is_hot=0;
    public $is_pay_after_service = 0;
    public $is_trademark = 0;
    public $is_experience = 0;
    public $tags = '';
    public $address_list = '';
    public $is_renewal = 0;
    public $service_cycle = 0;
    public $keywords = '';
//    public $department_id = 0;

    public $is_inventory_limit = 0;
    public $inventory_qty = 0;
    public  $platform_id=1;
    public  $is_package=0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'alias','top_category_id',  'category_id',  'explain', 'type', 'is_experience','flow_id'], 'required'],
//            [['name', 'top_category_id',  'category_id', 'slug', 'explain', 'type', 'is_experience', 'department_id'], 'required'],
            [['top_category_id', 'category_id', 'type', 'traded_init','is_trademark','flow_id', 'form_id', 'is_home', 'is_home_nav', 'home_nav_sort', 'is_hot', 'is_pay_after_service', 'is_experience', 'is_renewal', 'service_cycle','is_show_list','show_list_sort','is_inventory_limit','inventory_qty'], 'integer'],
//            [['slug'], 'validateSlug'],
            [['name', 'spec_name',  'tags'], 'string', 'max' => 15],
            [['keywords'], 'string', 'max' => 200],
            [['alias'], 'string', 'max' => 40],
            [['spec_explain'], 'string', 'max' => 100],
            [['explain'], 'string', 'max' => 150],
            [['name', 'alias', 'spec_name', 'spec_explain',  'explain', 'traded_init', 'home_sort', 'home_nav_sort', 'tags', 'inventory_qty'], 'trim'],
//            ['slug', 'match', 'pattern' => '/^[a-zA-Z]+[\w-]*$/', 'message' => '只能包含字母、数字、中横线（-）、下划线（_）并且必须以字母开头'],
            ['address_list', 'validateAddressList'],
            ['category_id', 'validateCategoryId'],
            ['industries', 'safe'],
            [['home_sort', 'home_nav_sort', 'buy_limit', 'inventory_qty'], 'integer', 'skipOnEmpty'=>false],
            [['buy_limit'], 'integer', 'min' => 0, 'max' => 99],
            [['traded_init', 'buy_limit'], 'default', 'value' => '0'],
            ['flow_id', 'default', 'value' => '0'],
            [['traded_init'], 'integer', 'min' => '0', 'skipOnEmpty'=>false],
        ];
    }

//    public function validateSlug()
//    {
//        /** @var Product $product */
//        $product = Product::find()->where(['slug' => $this->slug])->one();
//        if($product && (null == $this->_product || $this->_product->id != $product->id))
//        {
//            $this->addError('slug', $this->getAttributeLabel('slug').'不能重复。');
//        }
//    }

    public function validateCategoryId()
    {
        $category = ProductCategory::findOne($this->category_id);
        if(null == $category || $category->parent_id == 0)
        {
            $this->addError('category_id', '请选择商品分类');
        }
        else
        {
            $this->top_category_id = $category->parent_id;
        }
    }

    public function getCategory()
    {
        return ProductCategory::find()->where(['id' => $this->category_id])
            ->andWhere('parent_id!=:parent_id', [':parent_id' => '0'])->one();
    }

    public function getTopCategory()
    {
        return ProductCategory::find()->where(['id' => $this->top_category_id, 'parent_id' => '0'])->one();
    }

    public function getDepartment()
    {
//        return CrmDepartment::find()->andWhere(['id' => $this->department_id])->one();
    }

    public function validateAddressList()
    {
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '商品名称',
            'alias' => '商品别名',
            'spec_name' => '关联商品命名',
            'spec_explain' => '提示说明',
            'top_category_id' => '所属一级分类',
            'category_id' => '所属分类',
//            'slug' => 'URL关键字',
            'keywords' => '搜索关键词',
            'explain' => '概要说明',
            'is_show_list' => '显示在列表页',
            'type' => '商品类型',
            'traded_init' => '初始交易量',
            'buy_limit' => '限购数量',
            'show_list_sort' => '列表页排序值',
            'industries' => '行业分类',
            'flow_id' => '关联商品流程',
            'form_id' => '关联订单表单',
            'is_home' => '显示在首页',
            'home_sort' => '首页排序值',
            'is_home_nav' => '首页顶部导航热门商品',
            'home_nav_sort' => '首页顶部导航热门商品排序值',
            'is_hot' => '首页热销',
            'is_pay_after_service' => '参与先服务后收费',
            'is_experience' => '参与服务体验馆',
            'is_trademark' => '显示商标信息',
            'tags' => '商品详情页标签',
            'address_list' => '关联地址',
            'is_renewal' => '支持续费',
            'service_cycle' => '服务周期(月)',
//            'department_id' => '商机分配部门',
            'is_inventory_limit' => '库存限制',
            'inventory_qty' => '库存量',
        ];
    }

    public function attributeHints()
    {
        return [
            'name' => '在首页显示的商品名称',
//            'alias' => '在商品详情页显示的商品名称，不填默认为商品名称',
            'spec_name' => '该商品被关联后，在商品详情页【类型】处显示的名称，不填默认为商品名称',
//            'slug' => '用于网站前台商品页链接地址后缀，请慎重填写，保存后不可编辑',
            'keywords' => '最多200个字符，每个关键词之间用中文逗号隔开，如：公司注册，办执照，代办执照',
            'explain' => '商品详情页商品名称下方的说明',
            'home_sort' => '请输入数字，数字大小决定该商品所在二级分类下的所有商品的排列顺序，从小到大依次为左上，右上，左下，右下',
            'home_nav_sort' => '请输入数字，数字大小决定该商品所在一级分类下的热门商品的排列顺序，从小到大依次为左上，右上，左下，右下',
            'show_list_sort' => '请输入数字，数字大小决定该商品所在列表页排列顺序，从小到大依次排序',
            'buy_limit' => '为0时表示不限购，大于0时表示一个用户限购的数量。',
            'service_cycle' => '月度服务选择1，季度选择3，半年度选择6，一年度选择12',
            'is_show_list' => '同时控制搜索结果页，商品详情页，新建商机，创建订单时添加的商品的范围，勾选代表显示，不勾选不显示',
            'inventory_qty' => '卖完该数量后商品自动下线（以支付全款为标准）',
        ];
    }

    /**
     * @return Product cui
     */
    public function save()
    {
        $product = new Product();
        //表明来源 platform_id 1crm 2电商
        $product->platform_id=1;
        $product->load($this->attributes, '');
        $product->setAddressIds($this->address_list);
        $product->setIndustryIds($this->industries);

        $product->keywords = ','.trim(str_replace('，', ',', $this->keywords), ',').',';
        if($product->alias == ''){
            $product->alias = $product->name;
        }
        if($product->spec_name == ''){
            $product->spec_name = $product->name;
        }
        if($product->is_renewal == Product::RENEWAL_DISABLED)
        {
            $product->service_cycle = Product::RENEWAL_DISABLED;
        }
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
        $oldFlow = Flow::findOne($product->flow_id);
        $this->_product = $product;
        if(!$this->validate()) return false;
        $product->load($this->attributes, '');
        $product->setAddressIds($this->address_list);
        $product->setIndustryIds($this->industries);
        $product->keywords = ','.trim(str_replace('，', ',', $this->keywords), ',').',';
        if($product->spec_name == ''){
            $product->spec_name = $product->name;
        }
        if($product->is_renewal == Product::RENEWAL_DISABLED)
        {
            $product->service_cycle = Product::RENEWAL_DISABLED;
        }
        if($product->update(false))
        {
            //新增后台操作日志
            AdministratorLog::logProductUpdate($product, $oldFlow);
            return true;
        }
        return false;
    }

    public function getFlow()
    {
        return Flow::findOne($this->flow_id);
    }
}
