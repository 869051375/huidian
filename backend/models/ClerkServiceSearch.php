<?php

namespace backend\models;

use common\models\City;
use common\models\Clerk;
use common\models\ClerkServicePause;
use common\models\District;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductPrice;
use common\models\Province;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class ClerkServiceSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 *
 * @package backend\models
 */

class ClerkServiceSearch extends Model
{
    const STATUS_IN_SERVICE = 1;
    const STATUS_PAUSE_SERVICE = 2;

    public $top_category_id;
    public $category_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['top_category_id', 'category_id', 'province_id', 'city_id', 'district_id', 'status'],
                'filter', 'filter' => 'trim'],
        ];
    }


    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'top_category_id' => '商品类目',
            'province_id' => '服务区域',
            'status' => '状态',
            'category_id' => '',
            'city_id' => '',
            'district_id' => '',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param Clerk $clerk
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($clerk, $params)
    {
        $product_ids = [];
        foreach($clerk->clerkItems as $clerkItem)
        {
            $product_ids = ArrayHelper::merge($product_ids, $clerkItem->getProductIds());
        }

        $district_ids = [];
        foreach($clerk->clerkArea as $clerkArea)
        {
            $district_ids[] = $clerkArea->district_id;
        }

        /**
         *  SELECT `product`.id, product.name, pp.price, product.price, product.`is_area_price`, product.category_id, product.`is_bargain`, c.name, top_c.name, pp.district_name, pp.city_name
            FROM `product` `product`
            LEFT JOIN `product_price` `pp` ON `product`.`id` = `pp`.`product_id`
            LEFT JOIN `product_category` `top_c` ON `product`.`top_category_id` = `top_c`.`id`
            LEFT JOIN `product_category` `c` ON `product`.`category_id` = `c`.`id`
            WHERE (`product`.`id` IN ('1', '11', '57')) AND ((`pp`.`district_id` IN (516, 517) AND pp.status=1) OR `product`.`is_area_price` = 0)
            ORDER BY `id`
         */

        $query = new Query();
        $query->select(['p.id', 'p.name', 'region_price' => 'pp.price', 'price' => 'p.price', 'p.is_area_price', 'pp.district_name', 'pp.city_name', 'pp.district_id',
            'p.category_id', 'p.is_bargain', 'category_name' => 'c.name', 'top_category_name' => 'top.name', 'service_area' => 'p.service_area'])
            ->from(Product::tableName().' p')->leftJoin(ProductPrice::tableName().' pp', '`p`.`id` = `pp`.`product_id`')
            ->leftJoin(ProductCategory::tableName().' top', '`p`.`top_category_id` = `top`.`id`')
            ->leftJoin(ProductCategory::tableName().' c', '`p`.`category_id` = `c`.`id`')
            ->orderBy(['id' => SORT_ASC]);
        $query->andWhere(['in', 'p.id', $product_ids]);
        $query->andWhere(['or', '`p`.`is_area_price` = 0', ['in', 'pp.district_id', $district_ids]]);

        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if($this->top_category_id)
        {
            $query->andWhere('p.top_category_id=:top_category_id', [':top_category_id' => $this->top_category_id]);
        }
        if($this->category_id)
        {
            $query->andWhere('p.category_id=:category_id', [':category_id' => $this->category_id]);
        }
        if($this->province_id)
        {
            $query->andWhere('pp.province_id=:province_id', [':province_id' => $this->province_id]);
        }
        if($this->city_id)
        {
            $query->andWhere('pp.city_id=:city_id', [':city_id' => $this->city_id]);
        }
        if($this->district_id)
        {
            $query->andWhere('pp.district_id=:district_id', [':district_id' => $this->district_id]);
        }
        if($this->status == 1) // 只查询正在服务中的
        {

        }
        if($this->status == 2) // 只查询已经暂停的
        {

        }
        return $dataProvider;
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

    public function getProvince()
    {
        return Province::find()->where('id=:id', [':id' => $this->province_id])->one();
    }

    public function getCity()
    {
        return City::find()->where(['id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])->one();
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id = :city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])
            ->one();
    }

    public static function getStatusList()
    {
        return [
            self::STATUS_IN_SERVICE => '正常服务',
            self::STATUS_PAUSE_SERVICE => '暂停服务',
        ];
    }
}
