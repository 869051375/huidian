<?php

namespace backend\models;

use common\models\City;
use common\models\DailyStatistics;
use common\models\District;
use common\models\OrderStatusStatistics;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductStatistics;
use common\models\ProductStatisticsDetailed;
use common\models\Province;
use common\utils\BC;
use yii\base\Model;
use yii\helpers\Json;

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

class SummarySearch extends Model
{

    public $starting_time;
    public $end_time;

    public $top_category_id;
    public $category_id;
    public $product_id;

    public $province_id;
    public $city_id;
    public $district_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['starting_time', 'end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
            [['top_category_id', 'category_id','product_id', 'province_id', 'city_id', 'district_id', ], 'integer'],
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
            return null;
        }
    }

    //区域交易量
    public function areaTrading($status = 2,$attribute)
    {
        $start_time = null;
        $closure_time = null;
        $start_time = strtotime($this->starting_time);
        $closure_time = strtotime($this->end_time);
        if(empty($start_time))
        {
            $start_time = $this->setStartTime($status);
            $closure_time = $this->setEndTime($status);
        }
        $data = ProductStatisticsDetailed::find()
                ->select('district_id,any_value(district_name) as district_name,sum('.$attribute.') as num')
                ->andFilterWhere(['top_category_id' => $this->top_category_id])
                ->andFilterWhere(['category_id' => $this->category_id])
                ->andFilterWhere(['province_id' => $this->province_id])
                ->andFilterWhere(['city_id' => $this->city_id])
                ->andFilterWhere(['district_id' => $this->district_id])
                ->andFilterWhere(['product_id' => $this->product_id])
                ->andWhere('district_id > :district', [':district' => 0])
                ->andWhere('date >= :start_time', [':start_time' => $start_time])
                ->andWhere('date <= :end_time', [':end_time' => $closure_time])
                ->groupBy('district_id')->orderBy(['num' => SORT_DESC])->asArray()->all();
        return $data;
    }

    //上一周（昨天，月，年）的数据
    public function beforeData($status = 2)
    {
        $start_time = null;
        $closure_time = null;
        if($status==1)
        {   //昨天
            $start_time = mktime(0, 0 , 0,date("m"),date("d")-2,date("Y"));
            $closure_time = mktime(23,59,59,date("m"),date("d")-2,date("Y"));
        }elseif ($status==2)
        {   //上一周
            $start_time = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"));
            $closure_time = mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"));
        }elseif ($status==3)
        {   //上一月
            $start_time = mktime(0, 0 , 0,date("m")-1,1,date("Y"));
            $closure_time = mktime(23,59,59,date("m"),0,date("Y"));
        }elseif ($status==4)
        {   //上一年
            $start_time = mktime(0, 0 , 0,1,1,date("Y")-1);
            $closure_time = mktime(23,59,59,12,31,date("Y")-1);
        }
        return  ProductStatistics::find()
            ->select('product_id,any_value(product_name) as product_name,sum(product_visitors) as product_visitors,sum(product_pv) as product_pv,sum(pay_success_no) as pay_success_no,sum(product_order_no) as product_order_no,sum(total_amount) as total_amount')
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['product_id' => $this->product_id])
            ->andWhere('date >= :start_time', [':start_time' => $start_time])
            ->andWhere('date <= :end_time', [':end_time' => $closure_time])
            ->orderBy(['product_id'=>SORT_ASC])
            ->groupBy('product_id')
            ->asArray()
            ->all();
    }

    //主表的数据
    public function productStatisticsData($status = 2)
    {
        $start_time = null;
        $closure_time = null;
        $start_time = strtotime($this->starting_time);
        $closure_time = strtotime($this->end_time);
        if(empty($start_time))
        {
            $start_time = $this->setStartTime($status);
            $closure_time = $this->setEndTime($status);
        }
        return  ProductStatistics::find()
            ->select('product_id,any_value(product_name) as product_name,sum(product_visitors) as product_visitors,sum(product_pv) as product_pv,sum(pay_success_no) as pay_success_no,sum(product_order_no) as product_order_no,sum(total_amount) as total_amount')
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['product_id' => $this->product_id])
            ->andWhere('date >= :start_time', [':start_time' => $start_time])
            ->andWhere('date <= :end_time', [':end_time' => $closure_time])
            ->orderBy(['product_id'=>SORT_ASC])
            ->groupBy('product_id')
            ->asArray()
            ->all();
    }

    //top排行主表的数据
    public function productTopData($status = 2,$attribute)
    {
        $start_time = null;
        $closure_time = null;
        $start_time = strtotime($this->starting_time);
        $closure_time = strtotime($this->end_time);
        if(empty($start_time))
        {
            $start_time = $this->setStartTime($status);
            $closure_time = $this->setEndTime($status);
        }
        return  ProductStatistics::find()
            ->select('product_id,any_value(product_name) as product_name,sum(product_visitors) as product_visitors,sum(product_pv) as product_pv,sum(pay_success_no) as pay_success_no,sum(product_order_no) as product_order_no,sum(product_order_no) as product_order_no,sum(total_amount) as total_amount')
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['product_id' => $this->product_id])
            ->andWhere('date >= :start_time', [':start_time' => $start_time])
            ->andWhere('date <= :end_time', [':end_time' => $closure_time])
            ->groupBy('product_id')
            ->orderBy([$attribute => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();
    }

    //---------------------------------------折线图数据---------------------------------------
    public function chartData($status=2, $field, $data_type, $mark = 0)
    {
        $data = [];
        if($status==2)
        {
            //按一周内的
            for($i=1;$i<=date('w');$i++)
            {
                $time = mktime(0 , 0, 0,date("m"),date("d")-date("w")+$i,date("Y"));
                //数据or时间
                if($mark)
                {
                    if($data_type ==1 )
                    {
                        //商品统计表
                        $data[] =  $this->sumData($time,$field);
                    }
                    else if($data_type == 2)
                    {
                        //商品统计明细表
                        $data[] =  $this->sumDetailData($time,$field);
                    }
                    else if($data_type == 3)
                    {
                        //商品统计表-种类统计
                        $data[] =  $this->sumDataSpecies($time,$field);
                    }
                    else if($data_type == 4)
                    {
                        //商品统计表-下单转化率
                        $data[] =  $this->orderRate($this->sumDailyStatistics($time,'order_user_no'),$this->sumDailyStatistics($time,'visitors_no'));
                    }
                    else if($data_type == 5)
                    {
                        //每日统计表-支付转化率
                        $data[] =  $this->orderRate($this->sumDailyStatistics($time,'pay_user_no'),$this->sumDailyStatistics($time,'visitors_no'));
                    }
                    else if($data_type == 6)
                    {
                        //每日统计表-下单支付转化率
                        $data[] =  $this->orderRate($this->sumDailyStatistics($time,'pay_user_no'),$this->sumDailyStatistics($time,'order_user_no'));
                    }
                }
                else
                {
                    $data[] = date('Y/m/d',$time);
                }
            }
        }
        elseif($status==3)
        {
            //按一月内的
            for($i=1;$i<=date('d');$i++)
            {
                $time = mktime(0, 0 , 0,date("m"),$i,date("Y"));
                if($mark)
                {
                    //商品统计表
                    if($data_type == 1)
                    {
                        $data[] =  $this->sumData($time,$field);
                    }
                    else if($data_type == 2)
                    {
                        //商品统计明细表
                        $data[] =  $this->sumDetailData($time,$field);
                    }
                    else if($data_type == 3)
                    {
                        //商品统计表-种类统计
                        $data[] =  $this->sumDataSpecies($time,$field);
                    }
                    else if($data_type == 4)
                    {
                        //商品统计表-下单转化率
                        $data[] =  $this->orderRate($this->sumDailyStatistics($time,'order_user_no'),$this->sumDailyStatistics($time,'visitors_no'));
                    }
                    else if($data_type == 5)
                    {
                        //每日统计表-支付转化率
                        $data[] =  $this->orderRate($this->sumDailyStatistics($time,'pay_user_no'),$this->sumDailyStatistics($time,'visitors_no'));
                    }
                    else if($data_type == 6)
                    {
                        //每日统计表-下单支付转化率
                        $data[] =  $this->orderRate($this->sumDailyStatistics($time,'pay_user_no'),$this->sumDailyStatistics($time,'order_user_no'));
                    }
                }
                else
                {
                    $data[] = date('Y/m/d',$time);
                }
            }
        }
        return Json::encode($data);
    }

    //明细表的数据和
    private function sumDetailData($time,$field)
    {
        $sum_no =  ProductStatisticsDetailed::find()
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['province_id' => $this->province_id])
            ->andFilterWhere(['city_id' => $this->city_id])
            ->andFilterWhere(['district_id' => $this->district_id])
            ->andFilterWhere(['product_id' => $this->product_id])
            ->andFilterWhere(['date' => $time])
            ->sum($field);
        if($sum_no)
        {
            return $sum_no;
        }
        return 0;
    }

    //主表数据的和
    private function sumData($time,$field)
    {
        $sum_no =  ProductStatistics::find()
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['date' => $time])
            ->sum($field);
        if($sum_no)
        {
            return $sum_no;
        }
        return 0;
    }

    //被访问的商品种类数
    private function sumDataSpecies($time,$field)
    {
        $count_no =  ProductStatistics::find()
            ->andFilterWhere(['top_category_id' => $this->top_category_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andWhere($field.' >= :no', [':no' => 0])
            ->andFilterWhere(['date' => $time])
            ->count();
        if($count_no)
        {
            return $count_no;
        }
        return 0;
    }

    private function sumDailyStatistics($time,$field)
    {
        $sum_no = DailyStatistics::find()
            ->andFilterWhere(['date' => $time])
            ->sum($field);
        if($sum_no)
        {
            return $sum_no;
        }
        return 0;
    }
    //下单率转化
    private function orderRate($data_one,$data_two)
    {
        if($data_one&&$data_two)
        {
            return BC::mul(BC::div($data_one,$data_two,4),100,2);
        }
        return 0;
    }
    //----------------------------------折线图数据end-----------------------------------

    //访问的种类数
    public function handleData($data,$field)
    {
        $no = [];
        foreach($data as $item)
        {
            if($item[$field] > 0 && !in_array($item['product_id'],$no))
            {
                $no[] = $item['product_id'];
            }
        }
        return count($no);
    }

    //-----------------------订单状态的饼图-----------------------------
    public function getOrderStatus($status)
    {
        $start_time = null;
        $closure_time = null;
        $start_time = strtotime($this->starting_time);
        $closure_time = strtotime($this->end_time);
        if(empty($start_time))
        {
            $start_time = $this->setStartTime($status);
            $closure_time = $this->setEndTime($status);
        }
        return OrderStatusStatistics::find()
                    ->andWhere('date >= :start_time', [':start_time' => $start_time])
                    ->andWhere('date <= :end_time', [':end_time' => $closure_time])
                    ->andFilterWhere(['top_category_id' => $this->top_category_id])
                    ->andFilterWhere(['category_id' => $this->category_id])
                    ->andFilterWhere(['province_id' => $this->province_id])
                    ->andFilterWhere(['city_id' => $this->city_id])
                    ->andFilterWhere(['district_id' => $this->district_id])
                    ->andFilterWhere(['product_id' => $this->product_id])
                    ->asArray()
                    ->all();
    }

    //开始时间
    public function setStartTime($status)
    {
        if(empty($this->starting_time) && empty($this->end_time))
        {
            if ($status == 1) {   //昨天
                return mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
            } elseif ($status == 2) {   //本周
                return mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
            } elseif ($status == 3) {   //本月
                return mktime(0, 0, 0, date("m"), 1, date("Y"));
            } elseif ($status == 4) {   //本年
                return mktime(0, 0, 0, 1, 1, date("Y"));
            }
        }
        return null;
    }

    //结束时间
    public function setEndTime($status)
    {
        if(empty($this->starting_time) && empty($this->end_time))
        {
            if ($status == 1) {   //昨天
                return mktime(23, 59, 59, date("m"), date("d")-1, date("Y"));
            } elseif ($status == 2) {   //本周
                return mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));
            } elseif ($status == 3) {   //本月
                return mktime(23,59,59,date("m") ,date("t"),date("Y"));
            } elseif ($status == 4) {   //本年
                return mktime(23, 59, 59, 12, 31, date("Y"));
            }
        }
        return null;
    }

    public function orderStatus($status)
    {
        $pending_pay_no = [];
        $unpaid_no = [];
        $pending_allot_no = [];
        $pending_service_no = [];
        $in_service_no = [];
        $complete_service_no = [];
        $data = [];
        $pending_pay_no['label'] = '待付款';
        $pending_pay_no['data'] = $this->countData($status,'pending_pay_no');
        $pending_pay_no['color'] = '#4f81bd';
        $data[] = $pending_pay_no;
        $unpaid_no['label'] = '未付清';
        $unpaid_no['data'] = $this->countData($status,'unpaid_no');
        $unpaid_no['color'] = '#c0504d';
        $data[] = $unpaid_no;
        $pending_allot_no['label'] = '待分配';
        $pending_allot_no['data'] = $this->countData($status,'pending_allot_no');
        $pending_allot_no['color'] = '#9bbb59';
        $data[] = $pending_allot_no;
        $pending_service_no['label'] = '待服务';
        $pending_service_no['data'] =  $this->countData($status,'pending_service_no');
        $pending_service_no['color'] = '#8064a2';
        $data[] = $pending_service_no;
        $in_service_no['label'] = '服务中';
        $in_service_no['data'] =  $this->countData($status,'in_service_no');
        $in_service_no['color'] = '#4bacc6';
        $data[] = $in_service_no;
        $complete_service_no['label'] = '服务完成';
        $complete_service_no['data'] =  $this->countData($status,'complete_service_no');
        $complete_service_no['color'] = '#f79646';
        $data[] = $complete_service_no;
        return $data;
    }

    public function countData($status,$field)
    {
        $data = $this->getOrderStatus($status);
        if(is_array($data))
        {
            return array_sum(array_column($data,$field));
        }
        return 0;
    }
    //--------获取饼图数据结束---------




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

    public function getProduct()
    {
        return Product::find()->where(['id' => $this->product_id])->one();
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id = :city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])
            ->one();
    }


    
}
