<?php

namespace console\record;

use common\models\Order;
use common\models\OrderStatusStatistics;
use common\models\Product;
use common\models\ProductPrice;
use common\models\VirtualOrder;
use Yii;
use yii\redis\Connection;

class OrderStatusRecord
{
    public function record($date)
    {
        /**@var $product Product **/
        $products = Product::find()->orderBy(['id'=> SORT_ASC])->all();
        $date = empty($date) ? date('Y-m-d',strtotime("-1 day")) : $date;
        $time = empty($date) ? mktime(0, 0 , 0,date("m"),date("d")-1,date("Y")) : strtotime($date);
        foreach($products as $product)
        {
            if($product->is_area_price)
            {
                $this->areaPrice($product,$time,$date);
            }
            else
            {
                $model = new OrderStatusStatistics();
                $model->date = $time;
                $model->pending_pay_no = $this->getRedisVal($product->id,0,'pending_pay_no',$date);
                $model->unpaid_no = $this->getRedisVal($product->id,0,'unpaid_no',$date);
                $model->pending_allot_no = $this->getRedisVal($product->id,0,'pending_allot_no',$date);
                $model->pending_service_no = $this->getRedisVal($product->id,0,'pending_service_no',$date);
                $model->in_service_no = $this->getRedisVal($product->id,0,'in_service_no',$date);
                $model->complete_service_no = $this->getRedisVal($product->id,0,'complete_service_no',$date);
                if($model->pending_pay_no||$model->unpaid_no||$model->pending_allot_no||$model->pending_service_no||$model->in_service_no||$model->complete_service_no)
                {
                    $model->save(false);
                }
            }
        }
    }

    private function areaPrice($product,$time,$date)
    {
        /**@var Product $product **/
        $productPrices = $product->productPrices;
        /**@var ProductPrice[] $productPrices**/
        foreach($productPrices as $item)
        {
            $model = new OrderStatusStatistics();
            $model->date = $time;
            $model->pending_pay_no = $this->getRedisVal($item->product_id,$item->district_id,'pending_pay_no',$date);
            $model->unpaid_no = $this->getRedisVal($item->product_id,$item->district_id,'unpaid_no',$date);
            $model->pending_allot_no = $this->getRedisVal($item->product_id,$item->district_id,'pending_allot_no',$date);
            $model->pending_service_no = $this->getRedisVal($item->product_id,$item->district_id,'pending_service_no',$date);
            $model->in_service_no = $this->getRedisVal($item->product_id,$item->district_id,'in_service_no',$date);
            $model->complete_service_no = $this->getRedisVal($item->product_id,$item->district_id,'complete_service_no',$date);
            $model->top_category_id = $product->top_category_id;
            $model->category_id =  $product->category_id;
            $model->product_id = $item->product_id;
            $model->province_id = $item->province_id;
            $model->city_id = $item->city_id;
            $model->district_id = $item->district_id;
            if($model->pending_pay_no||$model->unpaid_no||$model->pending_allot_no||$model->pending_service_no||$model->in_service_no||$model->complete_service_no)
            {
                $model->save(false);
            }
        }
    }

    /**
     * @param $product_id
     * @param $region
     * @param $attribute
     * @param $date
     * @return mixed
     * 获取redis中的值
     */
    private function getRedisVal($product_id,$region = 0,$attribute,$date)
    {
        $key = $date.'-'.$product_id.'-'.$region.'-'.$attribute;
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        $val = $redis->get($key);
        if($val)
        {
            $redis->del($key);
            return $val;
        }
        return 0;
    }

}