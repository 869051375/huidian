<?php

namespace backend\models;

use common\models\City;
use common\models\District;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductStatistics;
use common\models\Province;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class SummarySearch
 * @package backend\models
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 * @property Product $product
 */

class RankingSearch extends Model
{

    public $starting_time;
    public $end_time;

    public $top_category_id;
    public $category_id;
    public $product_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['starting_time', 'end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
            [['top_category_id', 'category_id','product_id' ], 'integer'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '开始时间不能大于结束时间！');
        }
    }


    public function formName()
    {
        return '';
    }



    public function attributeLabels()
    {
        return [
            'starting_time' => '开始时间',
            'end_time' => '结束时间',
            'district_id' => '',
            'city_id' => '',
            'province_id' => '地区',
            'top_category_id' => '商品类目',
            'category_id' => '',
            'product_id' => '',
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

    public function search($params)
    {
        $this->load($params);
        if(!$this->validate())
        {
            $this->starting_time = null;
        }
    }


    //top排行的数据
    public function productTopData($status = 2,$attribute)
    {
        $query = ProductStatistics::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $start_time = null;
        $closure_time = null;
        $start_time = strtotime($this->starting_time);
        $closure_time = strtotime($this->end_time)+86400;
        if(empty($this->starting_time) && empty($this->end_time))
        {
            if($status==1)
            {   //今天
                $start_time = mktime(0, 0 , 0,date("m"),date("d"),date("Y"));
                $closure_time = mktime(23,59,59,date("m"),date("d"),date("Y"));
            }elseif ($status==2)
            {   //本周
                $start_time = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
                $closure_time = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
            }elseif ($status==3)
            {   //本月
                $start_time = mktime(0, 0 , 0,date("m"),1,date("Y"));
                $closure_time = mktime(23,59,59,date("m") ,31,date("Y"));
            }elseif ($status==4)
            {   //本年
                $start_time = mktime(0, 0 , 0,1,1,date("Y"));
                $closure_time = mktime(23,59,59,12,31,date("Y"));
            }
        }
        $query->select('product_id,any_value(product_name) as product_name,sum(product_visitors) as product_visitors,sum(product_pv) as product_pv,sum(pay_success_no) as pay_success_no,sum(product_order_no) as product_order_no,sum(total_amount) as total_amount')
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['product_id' => $this->product_id])
            ->andWhere('date >= :start_time', [':start_time' => $start_time])
            ->andWhere('date <= :end_time', [':end_time' => $closure_time])
            ->groupBy('product_id')
            ->orderBy([$attribute => SORT_DESC])
            ->all();
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

    public function getProduct()
    {
        return Product::find()->where(['id' => $this->product_id])->one();
    }
    
}
