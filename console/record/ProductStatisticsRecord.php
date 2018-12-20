<?php

namespace console\record;

use common\models\Product;
use common\models\ProductStatistics;
use common\models\ProductStatisticsDetailed;
use Yii;
use yii\redis\Connection;

class ProductStatisticsRecord
{
    public function record($date)
    {
        $time = empty($date) ? mktime(0, 0 , 0,date("m"),date("d")-1,date("Y")) : strtotime($date);
        $date = empty($date) ? date('Y-m-d',strtotime("-1 day")) : $date;
        $products = Product::find()
                ->select(['id','name','top_category_id','category_id'])
                ->orderBy(['id'=> SORT_ASC])
                ->all();
        foreach($products as $product)
        {
            /**@var $product Product * */
            $model = new ProductStatistics();
            $model->date = $time;
            $model->product_id = $product->id;
            $model->product_name = $product->name;
            $model->top_category_id = $product->top_category_id;
            $model->top_category_name = empty($product->topCategory) ? '' : $product->topCategory->name;
            $model->category_id = $product->category_id;
            $model->category_name = empty($product->category) ? '' : $product->category->name;
            $model->product_order_no = $this->sumAttribute($product->id, 'product_order_num',$time);
            $model->pay_success_no = $this->sumAttribute($product->id, 'pay_success_num',$time);
            $model->product_pv = $this->getRedisVal($product->id, 'product_pv',$date);
            $model->product_visitors = $this->getRedisVal($product->id, 'product_visitors',$date);
            $model->total_amount = $this->sumAttribute($product->id, 'total_price',$time);
            $model->pay_success_no = $this->sumAttribute($product->id, 'pay_success_num',$time);
            if ($model->product_order_no || $model->pay_success_no || $model->product_pv || $model->product_visitors || $model->total_amount || $model->pay_success_no)
            {
                if ($model->save(false))
                {
                    $this->delRedis($product->id,$date);
                }
            }
        }
    }

    private function sumAttribute($product_id,$field_name,$time)
    {
        $start_time = mktime(0, 0 , 0,date("m",$time),date("d",$time),date("Y",$time));
        $closure_time = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        $products = ProductStatisticsDetailed::find()
            ->andWhere(['product_id' => $product_id])
            ->andWhere('date >= :start_time', [':start_time' => $start_time])
            ->andWhere('date <= :end_time', [':end_time' => $closure_time])
            ->sum($field_name);
        if(empty($products))
        {
            return 0;
        }
        return $products;
    }


    /**
     * @param $product_id
     * @param $date
     * 删除redis
     */
    private function delRedis($product_id,$date)
    {
        $model = new ProductStatistics();
        $attributeLabels = array_keys($model->attributeLabels());
        foreach($attributeLabels as $attributeLabel)
        {
            $key = $date.'-'.$product_id.'-'.$attributeLabel;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $redis->del($key);
        }
    }

    /**
     * @param $product_id
     * @param $attribute
     * @param $date
     * @return mixed
     * 获取redis中的值
     */
    private function getRedisVal($product_id,$attribute,$date)
    {
        $key = $date.'-'.$product_id.'-'.$attribute;
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        if($redis->get($key))
        {
            return $redis->get($key);
        }
        return 0;
    }
}
