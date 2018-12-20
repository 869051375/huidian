<?php

namespace common\models;

use common\biztraits\PriceDetail;
use common\utils\BC;
use common\utils\Decimal;
use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%product}}".
 *
 * @property integer $id
 * @property integer $is_package
 * @property integer $is_confirm
 * @property string $name
 * @property string $alias
 * @property string $spec_name
 * @property string $spec_explain
 * @property integer $top_category_id
 * @property integer $category_id
 * @property string $slug
 * @property string $keywords
 * @property string $explain
 * @property integer $type
 * @property integer $traded
 * @property integer $traded_init
 * @property integer $buy_limit
 * @property string $tax
 * @property string $industries
 * @property integer $flow_id
 * @property integer $form_id
 * @property integer $is_home
 * @property integer $home_sort
 * @property integer $is_home_nav
 * @property integer $home_nav_sort
 * @property integer $is_hot
 * @property integer $is_pay_after_service
 * @property integer $is_experience
 * @property integer $is_trademark
 * @property integer $is_show_list
 * @property integer $show_list_sort
 * @property string $tags
 * @property integer $is_bargain
 * @property integer $status
 * @property string $price
 * @property string $wx_remit_amount
 * @property string $deposit
 * @property string $service_area
 * @property string $original_price
 * @property string $price_detail
 * @property integer $is_area_price
 * @property string $address_list
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $is_renewal
 * @property integer $is_installment
 * @property integer $service_cycle
 * @property integer $is_inventory_limit
 * @property integer $inventory_qty
 * @property integer $platform_id
 *
 * @property Flow $flow
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property ProductSeo $productSeo
 * @property ProductPrice[] $productPrices
 * @property ProductPrice[] $prices
 * @property Collocation[] $collocations
 * @property OpportunityAssignDepartment[] $opportunityAssignDepartments
 *
 * @property PackageProduct[] $packageProductList
 * @property Product[] $packageProducts
 * @property Product[] $packages
 * @property PackageRelated[] $relatedPackages
 * @property ProductCategory $productCategory
 */
class Product extends ActiveRecord
{
    use PriceDetail;
    const TYPE_SERVICE = 1;
    const TYPE_ADDRESS = 2;

    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 0;

    //是否套餐商品
    const PACKAGE_ACTIVE = 1;//是
    const PACKAGE_DISABLED = 0;//否

    //是否确认套餐具体商品
    const CONFIRM_ACTIVE = 1;//是
    const CONFIRM_DISABLED = 0;//否

    //是否支持续费
    const RENEWAL_ACTIVE = 1;//是
    const RENEWAL_DISABLED = 0;//否
    //是否显示在首页
    const SHOW_LIST_ACTIVE = 1;//是
    const SHOW_LIST_DISABLED = 0;//是

    //是否库存限制商品
    const INVENTORY_LIMIT_ACTIVE = 1;//是
    const INVENTORY_LIMIT_DISABLED = 0;//否

    //商品来源
    const CRM_FROM=1;//crm来源
    const ONLINE_FROM=2;//电商来源

    public $platform_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @param $top_category_id
     * @return Product[]
     */
    public static function getProductList($top_category_id)
    {
        /** @var Product[] $list */
        $list = Product::findOnline()->andWhere(['top_category_id' => $top_category_id])
            ->orderBy(['home_sort' => SORT_ASC])->all();
        $data = [];
        foreach ($list as $product)
        {
            if(!isset($data[$product->category_id]))
            {
                $data[$product->category_id]['category'] = $product->category;
            }
            $data[$product->category_id]['products'][] = $product;
        }
        return $data;
    }

    /**
     * @param $top_category_id
     * @param $limit
     * @return Product[]
     */
    public static function getNavHotList($top_category_id, $limit = 0)
    {
        $query = Product::findOnline()
                ->andWhere(['is_home_nav'=>1,'top_category_id' => $top_category_id])
                ->orderBy(['home_nav_sort' => SORT_ASC]);
        if($limit > 0)
        {
            $query->limit($limit);
        }
        return $query->all();
    }

    /**
     * @param $category_id
     * @return Product[]
     */
    public static function getHomeProduct($category_id)
    {
        /** @var Product[] $list */
        return Product::findOnline()->andWhere(['is_home' => 1, 'category_id' => $category_id])
            ->orderBy(['home_sort' => SORT_ASC])->limit(6)->all();
    }

    /**
     * @return Query
     */
    public static function findOnline()
    {
        return self::find()->where(['status' => self::STATUS_ONLINE]);
    }

    /**
     * @param $id
     * @param $online
     * @return null|Product
     */
    public static function findById($id, $online = true)
    {
        if($online)
            $query = static::findOnline();
        else
            $query = static::find();
        /** @var Product $model */
        $model = $query->andWhere(['id' => $id])->one();
        return $model;
    }

    /**
     * @param $slug
     * @param $online
     * @return null|Product
     */
    public static function findBySlug($slug, $online = true)
    {
        if($online)
            $query = static::findOnline();
        else
            $query = static::find();
        /** @var Product $model */
        $model = $query->andWhere(['slug' => $slug])->one();
        return $model;
    }

    /**
     * @param $category_id
     * @return Product[]
     */
    public static function findAllByCategory($category_id)
    {
        return Product::findOnline()
            ->andWhere(['category_id' => $category_id,'is_show_list'=>self::SHOW_LIST_ACTIVE])
            ->orderBy(['show_list_sort'=>SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    public static function findAllNavByCategory($category_id)
    {
        return Product::findOnline()
            ->andWhere(['category_id' => $category_id, 'is_home' => 1])
            ->orderBy(['home_sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /**
     * @inheritdoc
     * @property
     */
    public function rules()
    {
        return [
            [['top_category_id', 'category_id', 'type', 'traded', 'traded_init', 'buy_limit', 'flow_id', 'form_id',
                'is_home', 'home_sort', 'is_home_nav', 'home_nav_sort', 'is_hot', 'is_pay_after_service',
                'is_experience','is_trademark', 'is_bargain', 'status', 'is_area_price', 'is_renewal', 'service_cycle',
                'is_inventory_limit', 'inventory_qty', 'creator_id', 'updater_id', 'created_at', 'updated_at', 'is_package', 'is_confirm','is_show_list',
                'show_list_sort','is_installment','platform_id'], 'integer'],
            [['price', 'wx_remit_amount', 'original_price', 'tax', 'deposit'], 'number'],
            [['price_detail'], 'string'],
            [['name', 'spec_name', 'slug', 'tags'], 'string', 'max' => 15],
            [['alias'], 'string', 'max' => 40],
            [['keywords'], 'string', 'max' => 200],
            [['spec_explain'], 'string', 'max' => 100],
            [['explain'], 'string', 'max' => 150],
            [['industries', 'address_list'], 'string', 'max' => 255],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],

            // 下面是确认要用的规则
            ['status', 'in', 'range' => [self::STATUS_ONLINE, self::STATUS_OFFLINE], 'message' => '状态不正确'],
            ['status', 'validateStatus'],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if(!$this->isOnline() && !$insert)
        {
            /** @var array $list */
            $list = PackageProduct::find()->where(['product_id' => $this->id])->asArray()->all();
            $ids = ArrayHelper::getColumn($list, 'package_id');
            if(!empty($ids))
            {
                $cmd = Yii::$app->db->createCommand();
                $cmd->update(Product::tableName(),
                    ['status' => Product::STATUS_OFFLINE],
                    ['in', 'id', $ids]);
                $cmd->execute();
            }
        }
    }

    public function validateStatus()
    {
        if($this->status == Product::STATUS_ONLINE)
        {
            if(!$this->areaPriceIsValidate())
            {
                $this->addError('status', '商品区域价格信息设置不完整。');
            }
            if($this->isPackage())
            {
                if(null == $this->packageProducts)
                {
                    $this->addError('status', '套餐必须有商品才能上线。');
                }
                else
                {
                    if(!$this->isConfirmed())
                    {
                        $this->addError('status', '套餐商品必选先确认之后才能上线。');
                    }
                    foreach ($this->packageProducts as $product)
                    {
                        if(!$product->canOnline()) $this->addError('status', '套餐下的商品必须先上线。');
                    }
                }
            }
            else
            {
                if(!$this->flowIsValidate())
                {
                    $this->addError('status', '商品尚未关联流程或者流程未生效。');
                }

                if(!$this->is_bargain && $this->is_installment)
                {
                    $this->addError('status', '非议价商品不能设置为分期付款。');
                }
            }
        }
    }

    public function canOnline()
    {
        if(!$this->isPackage())
        {
            if(!$this->flowIsValidate() || !$this->areaPriceIsValidate())
            {
                return false;
            }
        }
        else
        {
            if(empty($this->packageProducts))
            {
                return false;
            }
            else
            {
                if(!$this->isConfirmed())
                {
                    return false;
                }
                foreach ($this->packageProducts as $product)
                {
                    if(!$product->isOnline())
                    {
                        return false;
                    }
                }
                //判断如果是区域价格，价格要设置并且需要启用
                if(!$this->areaPriceIsValidate())
                {
                    return false;
                }
            }
        }
        return true;
    }

    public function flowIsValidate()
    {
        //如果是套餐商品
        if($this->isPackage())
        {
            if(empty($this->packageProducts))
            {
                return false;
            }
            else
            {
                if(!$this->isConfirmed())
                {
                    return false;
                }
                foreach ($this->packageProducts as $product)
                {
                    if($product->flowIsValidate())
                    {
                        return false;
                    }
                }
            }
        }
        else
        {
            if(null == $this->flow || !$this->flow->isActive())
            {
                return false;
            }
        }

        return true;
    }

    public function areaPriceIsValidate()
    {
        if($this->isAreaPrice())
        {
            if(empty($this->productPrices))
            {
                return false;
            }
            foreach($this->productPrices as $productPrice)
            {
                if($productPrice->isEnabled())
                {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],

            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            if($insert){
                $this->creator_id = $user->id;
                $this->creator_name = $user->name;
            }else if (isset($user->id)){
                $this->updater_id = $user->id;
                $this->updater_name = $user->name;
            }
            if(!$this->canOnline()) // 没有达到上线条件
            {
                $this->status = Product::STATUS_OFFLINE;
            }
            return true;
        }
        return false;
    }

    public function setIndustryIds($industries)
    {
        $this->industries = '';
        if(!empty($industries)) {
            $this->industries = implode(',', $industries);
        }
    }

    public function getIndustryIds()
    {
        if (empty($this->industries)) return [];
        return explode(',', $this->industries);
    }

    public function getIndustries()
    {
        return Industry::find()->where(['in', 'id', $this->getIndustryIds()])->all();
    }

    /**
     * @return Product[]
     */
    public function getAddresses()
    {
        return Product::find()->where(['in', 'id', $this->getAddressIds()])->andWhere(['status' => Product::STATUS_ONLINE])->all();
    }

    public function setAddressIds($address_list)
    {
        if(!empty($address_list)){
            $this->address_list = $address_list;
        }
    }

    public function getAddressIds()
    {
        if (empty($this->address_list)) return [];
        return explode(',', $this->address_list);
    }

    /**
     * @return Product[]
     * 关联商品
     */
    public  function getRelatedList()
    {
        $data = ProductRelated::find()->select('related_product_id')->where(['product_id'=>$this->id])->asArray()->all();
        //$products[]= self::find()->where(['id'=>$this->id])->one();
        $ids = [];
        $ids[] = $this->id;
        foreach ($data as $key=>$id)
        {
            $ids[] = $id['related_product_id'];
        }
        $products = self::findOnline()->andWhere(['in', 'id', $ids])->all();
        return $products;
    }

    /**
     * @param int $limit
     * @return ProductFaq[]
     */
    public function getFaqList($limit = 0)
    {
        $query = ProductFaq::find()->where(['product_id'=>$this->id]);
        if($limit > 0)
        {
            $query->limit($limit);
        }
        return $query->all();
    }

    /**
     * @return ProductPrice[]
     */
    public function getDistrictsPrice()
    {
        return ProductPrice::find()->where(['pp.product_id' => $this->id, 'pp.status' => ProductPrice::STATUS_ENABLED])
            ->alias('pp')
            ->orderBy(['pp.sort' => SORT_ASC])->all();
    }

    public function getProductPrices()
    {
        return $this->hasMany(ProductPrice::className(), ['product_id' => 'id'])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getPrices()
    {
        return $this->hasMany(ProductPrice::className(), ['product_id' => 'id']);
    }

    public function getPackageProductList()
    {
        return $this->hasMany(PackageProduct::className(), ['package_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }

    public function getPackageProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'product_id'])
            ->viaTable(PackageProduct::tableName(), ['package_id' => 'id']);
    }

    public function getPackages()
    {
        return $this->hasMany(Product::className(), ['id' => 'package_id'])
            ->viaTable(PackageProduct::tableName(), ['product_id' => 'id']);
    }
    public static function getTypes()
    {
        return [
            self::TYPE_SERVICE => '服务类型',
            self::TYPE_ADDRESS => '地址类型',
        ];
    }

    public static function getServiceCycles()
    {
        $num = [];
        for ($i=1; $i<=20; $i++)
        {
            $num[$i] = $i;
        }
        return $num;
    }

    public function isOnline()
    {
        return $this->status == self::STATUS_ONLINE;
    }

    public function getStatusName()
    {
        $statusList = static::getStatusList();
        return $statusList[$this->status];
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_OFFLINE => '下线',
            self::STATUS_ONLINE => '上线',
        ];
    }

    public static function getProductFromList()
    {
        return [
            self::CRM_FROM => 'CRM平台',
            self::ONLINE_FROM => '电商平台',
        ];
    }

    public function isPackage()
    {
        return $this->is_package == self::PACKAGE_ACTIVE;
    }

    public function isConfirmed()
    {
        return $this->is_confirm == self::CONFIRM_ACTIVE;
    }

    public function isRenewal()
    {
        return $this->is_renewal == self::RENEWAL_ACTIVE;
    }

    public function confirmed()
    {
        $this->is_confirm = self::CONFIRM_ACTIVE;
        return $this->save(false);
    }

    public function isInventoryLimit()
    {
        return $this->is_inventory_limit == self::INVENTORY_LIMIT_ACTIVE;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_package' => 'Is Package',
            'is_confirm' => 'Is Confirm',
            'name' => 'Name',
            'alias' => 'Alias',
            'spec_name' => 'Spec Name',
            'spec_explain' => 'Spec Explain',
            'top_category_id' => 'Top Category ID',
            'category_id' => 'Category ID',
            'slug' => 'Slug',
            'explain' => 'Explain',
            'type' => 'Type',
            'traded' => 'Traded',
            'traded_init' => 'Traded Init',
            'tax' => 'Tax',
            'industries' => 'Industries',
            'flow_id' => 'Flow ID',
            'form_id' => 'Form ID',
            'is_home' => 'Is Home',
            'home_sort' => 'Home Sort',
            'is_home_nav' => 'Is Home Nav',
            'home_nav_sort' => 'Home Nav Sort',
            'is_hot' => 'Is Hot',
            'is_pay_after_service' => 'Is Pay After Service',
            'is_experience' => 'Is Experience',
            'tags' => 'Tags',
            'is_bargain' => 'Is Bargain',
            'status' => 'Status',
            'price' => 'Price',
            'original_price' => 'Original Price',
            'price_detail' => 'Price Detail',
            'is_area_price' => 'Is Area Price',
            'is_renewal' => 'Is Renewal',
            'service_cycle' => 'Service Cycle',
            'is_inventory_limit' => 'Is Inventory Limit',
            'inventory_qty' => 'Inventory Qty',
            'address_list' => 'Address List',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getFlow()
    {
        return static::hasOne(Flow::className(), ['id' => 'flow_id']);
    }

    public function getTopCategory()
    {
        return static::hasOne(ProductCategory::className(), ['id' => 'top_category_id']);
    }

    public function getDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'department_id']);
    }

    public function getCategory()
    {
        return static::hasOne(ProductCategory::className(), ['id' => 'category_id']);
    }

    public function getProductSeo()
    {
        return static::hasOne(ProductSeo::className(), ['product_id' => 'id']);
    }

    public function getCollocations()
    {
        return static::hasMany(Collocation::className(), ['product_id' => 'id']);
    }

    public function getProductCategory()
    {
        return static::hasOne(ProductCategory::className(), ['id' => 'top_category_id']);
    }

    public function getOpportunityAssignDepartments()
    {
        return static::hasMany(OpportunityAssignDepartment::className(), ['product_id' => 'id']);
    }

    /**
     * @return Collocation[]
     */
    public function getOnlineCollocations()
    {
        $collocations = [];
        foreach($this->collocations as $collocation)
        {
            if($collocation->collocationProduct->isOnline())
            {
                $collocations[] = $collocation;
            }
        }
        return $collocations;
    }

    //标准商品关联套餐
    public function getRelatedPackages()
    {
        return static::hasMany(PackageRelated::className(), ['package_id' => 'id']);
    }

    /**
     * @return Product[]
     */
    public function getOnlineRelatedPackages()
    {
        $data = PackageRelated::find()->select('package_related_id')->where(['package_id'=>$this->id])->asArray()->all();
        //$products[]= self::find()->where(['id'=>$this->id])->one();
        $ids = [];
        $ids[] = $this->id;
        foreach ($data as $key=>$id)
        {
            $ids[] = $id['package_related_id'];
        }
        $products = self::findOnline()->andWhere(['in', 'id', $ids])->andWhere(['is_package' => '1'])->all();
        return $products;
    }

    public function getImageUrl($width, $height, $type = ProductImage::TYPE_DETAIL)
    {
        $id = $this->id;
        /** @var ProductImage $model */
        $model = ProductImage::find()->where(['type'=>$type,'product_id'=>$id])->one();
        /** @var ImageStorageInterface $is */
        if(null == $model)
        {
            return $this->category ? $this->category->getImageUrl($width, $height) : null;
        }
        $is = Yii::$app->get('imageStorage');
        return $is->getImageUrl($model->image, ['width' => $width, 'height' => $height, 'mode' => 0]);
    }

    public function getViewImageUrl($width, $height, $type = ProductImage::TYPE_DETAIL)
    {
        $id = $this->id;
        /** @var ProductImage $model */
        $model = ProductImage::find()->where(['type'=>$type,'product_id'=>$id])->one();
        /** @var ImageStorageInterface $is */
        $is = Yii::$app->get('imageStorage');
        return $model ? $is->getImageUrl($model->image, ['width' => $width, 'height' => $height, 'mode' => 0]) : null;
    }

    /**
     * @param int $type
     * @return ProductImage
     */
    public function getImage($type = ProductImage::TYPE_DETAIL)
    {
        /** @var ProductImage $image */
        $image = ProductImage::find()->where(['type' => $type, 'product_id' => $this->id])->one();
        return $image;
    }

    //服务体验馆

    /**
     * @param int $limit
     * @return Product[]
     */
    public static function getExperienceList($limit=5)
    {
        return Product::findOnline()
            ->andWhere(['is_experience' => 1])
            ->limit($limit)
            ->all();
    }

    public function isAreaPrice()
    {
        return $this->is_area_price == 1 && !$this->isBargain();
    }

    public function isBargain()
    {
        return $this->is_bargain == 1;
    }

    public function isPayAfterService()
    {
        return $this->is_pay_after_service == 1;
    }

    public function isInstallment()
    {
        return $this->is_installment == 1;
    }

    public function getDefaultProductPrice($district_id = null, $sort = 'price')
    {
        //从价格表中读取最便宜的一个
        if($this->isAreaPrice())
        {
            $price = null;
            if($district_id)
            {
                $price = $this->getProductPriceByDistrict($district_id);
            }
            if(null == $price)
            {
                $query = ProductPrice::find()->where(['product_id' => $this->id,
                    'status' => ProductPrice::STATUS_ENABLED]);
                if($sort == 'default')
                {
                    $query->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
                }
                else
                {
                    $query->orderBy(['price' => SORT_ASC, 'id' => SORT_ASC]);
                }
                /** @var ProductPrice $price */
                $price = $query->one();
            }
            return $price;
        }
        return null;
    }

    public function getDefaultPrice($sort = 'price')
    {
        if($this->isBargain())
        {
            return null;
        }
        if($this->isAreaPrice())
        {
            /** @var ProductPrice $price */
            $price = $this->getDefaultProductPrice(null, $sort);
            if(!$price) return null;
            return $price->price;
        }
        else
        {
            return $this->price;
        }
    }

    public function getDefaultPriceDetail($sort = 'price')
    {
        if($this->isBargain())
        {
            return [];
        }
        if($this->isAreaPrice())
        {
            //从价格表中读取最便宜的一个
            /** @var ProductPrice $price */
            $price = $this->getDefaultProductPrice(null, $sort);
            if(null == $price) return [];
            return $price->getPriceDetail();
        }
        else
        {
            return $this->getPriceDetail();
        }
    }

    /**
     * @param $product_price_id
     * @return ProductPrice
     */
    public function getProductPrice($product_price_id)
    {
        /** @var ProductPrice $pp */
        $pp = ProductPrice::find()->where(['id' => $product_price_id, 'product_id' => $this->id])->one();
        return $pp;
    }

    public function getDisplayPrice($is_wx = false)
    {
        if ($this->isBargain())
        {
            return '面议';
        }
        else
        {
            $price = $this->getDefaultPrice();
            if ($this->isAreaPrice())
            {
                return Decimal::formatYenCurrentFrom($is_wx ? BC::sub($price, $this->wx_remit_amount) : $price);
            }
            else
            {
                return Decimal::formatYenCurrent($is_wx ? BC::sub($price, $this->wx_remit_amount) : $price);
            }
        }
    }

    /**
     * @param int $district_id
     * @param boolean $needEnabled
     * @return ProductPrice|null
     */
    public function getProductPriceByDistrict($district_id, $needEnabled = true)
    {
        /** @var ProductPrice $pp */
        $pp = ProductPrice::find()->where(['product_id' => $this->id, 'district_id' => $district_id])->one();
        if($pp && $needEnabled && $pp->isEnabled())
        {
            return $pp;
        }
        return null;
    }

    /**
     * @return array|ProductIntroduce
     */
    public function getProductIntroduce()
    {
        return ProductIntroduce::find()->where(['product_id'=>$this->id])->one();
    }

    /**
     * 综合评分
     */
    public function getComplexScore()
    {
        $score = OrderEvaluate::calculateComplexScore($this->getAttitudeScore(), $this->getProScore(), $this->getEfficiencyScore());
        $score = Yii::$app->formatter->asDecimal($score, 2);
        return $score;
    }

    /**
     * 专业程度评分
     */
    public function getProScore()
    {
        // todo 做缓存
        $query = OrderEvaluate::find()
            ->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE]);
        if($this->isPackage())
        {
            $query->andWhere(['package_id' => $this->id]);
        }
        else
        {
            $query->andWhere(['product_id' => $this->id]);
        }
        $score = $query->average('pro_score');
        if(null == $score) $score = '5.00';
        $score = Yii::$app->formatter->asDecimal($score, 2);
        return $score;
    }

    /**
     * 效率评分
     */
    public function getEfficiencyScore()
    {
        // todo 做缓存
        $query = OrderEvaluate::find()
            ->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE]);
            if($this->isPackage())
            {
                $query->andWhere(['package_id' => $this->id]);
            }
            else
            {
                $query->andWhere(['product_id' => $this->id]);
            }
        $score = $query->average('efficiency_score');
        if(null == $score) $score = '5.00';
        $score = Yii::$app->formatter->asDecimal($score, 2);
        return $score;
    }

    /**
     * 服务态度评分
     */
    public function getAttitudeScore()
    {
        // todo 做缓存
        $query = OrderEvaluate::find()
            ->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE]);
            if($this->isPackage())
            {
                $query->andWhere(['package_id' => $this->id]);
            }
            else
            {
                $query->andWhere(['product_id' => $this->id]);
            }
        $score = $query->average('attitude_score');
        if(null == $score) $score = '5.00';
        $score = Yii::$app->formatter->asDecimal($score, 2);
        return $score;
    }

    /**
     * 评价总数
     */
    public function getEvaluateCount()
    {
        // todo 做缓存
        $query = OrderEvaluate::find()
            ->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE]);
        if($this->isPackage())
        {
            $query->andwhere(['package_id' => $this->id]);
        }
        else
        {
            $query->andwhere(['product_id' => $this->id]);
        }
        $count = $query->count();
        return $count;
    }

    /**
     * 好评数
     */
    public function getBestEvaluateCount()
    {
        // todo 做缓存
        $query =  OrderEvaluate::find()->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->andWhere('complex_score >= 4');
        if($this->isPackage())
        {
            $query->andwhere(['package_id' => $this->id]);
        }
        else
        {
            $query->andwhere(['product_id' => $this->id]);
        }
        $count = $query->count();
        return $count;
    }

    /**
     * 中评数
     */
    public function getNeutralEvaluateCount()
    {
        // todo 做缓存
        $query =  OrderEvaluate::find()->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->andWhere('complex_score >= 3 and complex_score < 4');
        if($this->isPackage())
        {
            $query->andwhere(['package_id' => $this->id]);
        }
        else
        {
            $query->andwhere(['product_id' => $this->id]);
        }
        $count = $query->count();
        return $count;
    }

    /**
     * 差评数
     */
    public function getBadEvaluateCount()
    {
        // todo 做缓存
        $query =  OrderEvaluate::find()->where(['is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->andWhere('complex_score < 3');
        if($this->isPackage())
        {
            $query->andwhere(['package_id' => $this->id]);
        }
        else
        {
            $query->andwhere(['product_id' => $this->id]);
        }
        $count = $query->count();
        return $count;
    }

    /**
     * @param int $limit
     * @return ProductTag[]
     */
    public function getProductTags($limit = 4)
    {
        /** @var ProductTag[] $models */
        $models = ProductTag::find()->where(['product_id' => $this->id])
            ->orderBy(['count' => SORT_DESC])->limit($limit)->all();
        return $models;
    }

    /**
     * 计算商品的销售价格（当该商品属于套餐商品时，销售价格根据套餐销售价比例算出）
     * @param $product Product
     * @param $packagePp ProductPrice
     * @param boolean $isWeiXin
     * @return string
     */
    public function getPackageProductPrice($product, $packagePp, $isWeiXin = false)
    {
        $total = 0;
        if($this->isAreaPrice())
        {
            if($product->isAreaPrice())
            {
                $pp = $product->getProductPriceByDistrict($packagePp->district_id);
                $productPrice = $product->getPrice($pp);
            }
            else
            {
                $productPrice = $product->getPrice(null);
            }

            $price = $this->getPrice($packagePp, $isWeiXin);
        }
        else
        {
            $price = $this->getPrice($packagePp, $isWeiXin);
            $productPrice = $product->getPrice(null);
        }

        foreach($this->packageProducts as $packageProduct)
        {
            if($packageProduct->isAreaPrice())
            {
                $pp = $packageProduct->getProductPriceByDistrict($packagePp->district_id);
                $total = BC::add($total, $packageProduct->getPrice($pp));
            }
            else
            {
                $total =BC::add($total, $packageProduct->getPrice(null));
            }
        }
        return BC::mul($productPrice, BC::div($price, $total, 6));
    }

    /**
     * @param ProductPrice $productPrice
     * @param boolean $isWeiXin
     * @return string
     */
    public function getPrice($productPrice, $isWeiXin = false)
    {
        if($this->isBargain())
        {
            return 0;
        }

        $price = $this->isAreaPrice() ? $productPrice->price : $this->price;
        $price = BC::add($price, $this->deposit);

        if($isWeiXin)
        {
            $price = BC::sub($price, $this->wx_remit_amount);
        }
        return $price;
    }

    /**
     * @param ProductPrice $productPrice
     * @param boolean $isWeiXin
     * @param string $originalPrice
     * @return string
     */
    public function getSavePrice($productPrice, $originalPrice, $isWeiXin = false)
    {
        if($this->isAreaPrice())
        {
            $price = $this->getPrice($productPrice, $isWeiXin);
            $save = BC::sub($originalPrice, $price);
        }
        else
        {
            $save = BC::sub($originalPrice, $this->getPrice(null, $isWeiXin));
        }
        if($save > 0) return $save;
        return 0;
    }

    //该商品是否在推荐位
    public function isFeatured()
    {
        $featuredItem = FeaturedItem::find()->where(['product_id' => $this->id])->limit(1)->one();
        return $featuredItem ? true : false;
    }

    //列表展示 商品来源改为汉子
    public static function getSource($param){
        if($param==1){
            return $param='CRM平台';
        }
        if($param==2){
            return $param='电商平台';
        }
    }

    //列表展示 状态
    public static function getStatus($param){
        if($param==0){
            return $param='已下线';
        }
        if($param==1){
            return $param='已上线';
        }
    }
}