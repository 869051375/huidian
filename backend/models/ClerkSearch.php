<?php

namespace backend\models;

use common\models\Administrator;
use common\models\City;
use common\models\Clerk;
use common\models\District;
use common\models\Order;
use common\models\ProductCategory;
use common\models\Province;
use common\models\VirtualOrder;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * Class ClerkSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 */

class ClerkSearch extends Model
{
    public $top_category_id;
    public $category_id;
    public $keyword;
    public $type = 6;
    public $province_id;
    public $city_id;
    public $district_id;

    const TYPE_CLERK_NAME = 1;//服务人员姓名
    const TYPE_CLERK_PHONE = 2;//服务人员联系方式



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['top_category_id', 'category_id', 'type', 'province_id', 'city_id', 'district_id'], 'integer'],
            ['keyword', 'safe'],
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
            'category_id' => '',
            'city_id' => '',
            'district_id' => '',
            'keyword' => '关键词',
            'type' => '类型',
            'province_id' => '地区',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Clerk::find()->alias('c');
        $query->joinWith(['clerkItems ci']);
        $query->joinWith(['clerkArea ca']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'ci.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'ci.category_id' => $this->category_id <= 0 ? null : $this->category_id,
        ]);

       if ($this->type == self::TYPE_CLERK_NAME){
            //服务人员姓名
            $query->andFilterWhere(['like', 'c.name', $this->keyword]);

        }elseif ($this->type == self::TYPE_CLERK_PHONE){
            //服务人员联系方式
            $query->andFilterWhere(['like', 'c.phone', $this->keyword]);
        }

        $query->andFilterWhere([
            'ca.province_id' => $this->province_id <= 0 ? null : $this->province_id,
            'ca.city_id' => $this->city_id <= 0 ? null : $this->city_id,
            'ca.district_id' => $this->district_id <= 0 ? null : $this->district_id,
        ]);
        $query->orderBy(['c.created_at' => SORT_DESC]);
        $query->groupBy('c.id');
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

    public static function getTypes()
    {
        return [
            self::TYPE_CLERK_NAME => '服务人员姓名',
            self::TYPE_CLERK_PHONE => '服务人员手机号',
        ];
    }


}
