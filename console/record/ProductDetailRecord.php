<?php

namespace console\record;

use common\models\Product;
use common\models\ProductPrice;
use common\models\ProductStatisticsDetailed;
use Yii;
use yii\redis\Connection;

class ProductDetailRecord
{
    public function record($date)
    {
        /**@var $product Product **/
        $products = Product::find()
            ->select(['id','name','is_area_price','top_category_id','category_id'])
            ->orderBy(['id'=> SORT_ASC])
            ->all();
        $time = empty($date) ? mktime(0, 0 , 0,date("m"),date("d")-1,date("Y")) : strtotime($date);
        $date = empty($date) ? date('Y-m-d',strtotime("-1 day")) : $date;
        foreach($products as $product)
        {
            if($product->is_area_price)
            {
                $this->areaPrice($product,$time,$date);
            }
            else
            {
                $model = new ProductStatisticsDetailed();
                $model->date = $time;
                $model->product_id = $product->id;
                $model->product_name = $product->name;
                $model->top_category_id = $product->top_category_id;
                $model->category_id = $product->category_id;
                $model->product_order_num = $this->getRedisVal($product->id, 0, 'product_order_num',$date);
                $model->pay_success_num = $this->getRedisVal($product->id, 0, 'pay_success_num',$date);
                $model->total_price = $this->getRedisVal($product->id, 0, 'total_price',$date);
                if($model->product_order_num || $model->pay_success_num || $model->total_price || $model->pay_success_num )
                {
                    if($model->save(false))
                    {
                        $this->delRedis($product->id,0,$date);
                    }
                }
            }
        }
    }

    private function areaPrice($product,$time,$date)
    {
        /**@var $item ProductPrice **/
        foreach($product->productPrices as $item)
        {
            $model = new ProductStatisticsDetailed();
            $model->date = $time;
            $model->product_id = $product->id;
            $model->product_name = $product->name;
            $model->province_id = $item->province_id;
            $model->province_name = $item->province_name;
            $model->city_id = $item->city_id;
            $model->city_name = $item->city_name;
            $model->district_id = $item->district_id;
            $model->district_name = $item->district_name;
            $model->top_category_id = $product->top_category_id;
            $model->category_id = $product->category_id;
            $model->product_order_num = $this->getRedisVal($product->id, $item->district_id, 'product_order_num',$date);
            $model->pay_success_num = $this->getRedisVal($product->id, $item->district_id, 'pay_success_num',$date);
            $model->total_price = $this->getRedisVal($product->id, $item->district_id, 'total_price',$date);
            if($model->product_order_num || $model->pay_success_num || $model->total_price || $model->pay_success_num )
            {
                if($model->save(false))
                {
                    $this->delRedis($product->id,$item->district_id,$date);
                }
            }
        }
    }

    /**
     * @param $product_id
     * @param $district_id
     * @param $date
     * 删除redis
     */
    private function delRedis($product_id,$district_id = 0,$date)
    {
        $model = new ProductStatisticsDetailed();
        $attributeLabels = array_keys($model->attributeLabels());
        foreach($attributeLabels as $attributeLabel)
        {
            $key = $date.'-'.$product_id.'-'.$district_id.'-'.$attributeLabel;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $redis->del($key);
        }
    }

    /**
     * @param $product_id
     * @param $service_id
     * @param $attribute
     * @param $date
     * @return mixed
     * 获取redis中的值
     */
    private function getRedisVal($product_id,$service_id = 0,$attribute,$date)
    {
        $key = $date.'-'.$product_id.'-'.$service_id.'-'.$attribute;
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        if($redis->get($key))
        {
            return $redis->get($key);
        }
        return 0;
    }
}
