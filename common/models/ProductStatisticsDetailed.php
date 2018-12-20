<?php

namespace common\models;

use Yii;
use yii\log\Logger;
use yii\redis\Connection;

/**
 * This is the model class for table "{{%product_statistics_detailed}}".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $product_id
 * @property string  $product_name
 * @property integer $province_id
 * @property string  $province_name
 * @property integer $city_id
 * @property string  $city_name
 * @property integer $district_id
 * @property string  $district_name
 * @property integer $pay_success_num
 * @property integer $top_category_id
 * @property integer $category_id
 * @property integer $product_order_num
 * @property integer $total_price
 *
 *
 */
class ProductStatisticsDetailed extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_statistics_detailed}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date', 'product_id', 'product_order_num','pay_success_num','province_id','city_id','district_id','top_category_id','category_id'], 'integer'],
            [['total_price'], 'number'],
            [['product_name'], 'string','max'=>'32'],
            [['province_name'], 'string','max'=>'10'],
            [['city_name'], 'string','max'=>'10'],
            [['district_name'], 'string','max'=>'10'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => '商品id',
            'product_name' => '商品名称',
            'province_id' => '省ID',
            'city_id' => '城市ID',
            'district_id' => '区县ID',
            'province_name' => '省名称',
            'city_name' => '城市名称',
            'district_name' => '区县名称',
            'product_order_num' => '下单商品数',
            'pay_success_num' => '支付成功数',
            'total_price' => '金额',
        ];
    }

    //数量统计(下单商品数，)
    public function total($product_id,$service_id = 0,$attribute)
    {
        $service_id = $service_id ? $service_id : 0 ;
        try
        {
            $order_key = date('Y-m-d').'-'.$product_id.'-'.$service_id.'-'.$attribute;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $order_no = $redis->get($order_key);
            if($order_no)
            {
                $redis->incrby($order_key,1);
            }
            else
            {
                $redis->set($order_key,1);
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log($product_id.'-'.$service_id.'-'.$attribute.'-统计出现错误', Logger::LEVEL_TRACE);
        }
    }

    //金额统计
    public function sumPrice($product_id, $service_id = 0, $attribute, $price)
    {
        $service_id = $service_id ? $service_id : 0;
        try
        {
            $key = date('Y-m-d').'-'.$product_id.'-'.$service_id.'-'.$attribute;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $product_price = $redis->get($key);
            if($product_price)
            {
                $redis->incrbyfloat($key,$price);
            }
            else
            {
                $redis->set($key,$price);
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log($product_id.'-'.$service_id.'-'.$attribute.'-统计出现错误', Logger::LEVEL_TRACE);
        }

    }

    //访客数量的统计前10
    public function visitorNo($limit = 10,$status = 0)
    {
        $visitors = self::find()
                ->select(['sum(product_visitors) AS num','product_name'])
                ->groupBy('product_name')
                ->orderBy(['num'=> SORT_DESC])
                ->limit($limit)
                ->asArray()
                ->all();
        $resultData = $this->handleData($visitors,$status);
        return $resultData;
    }
    //处理数据
    public function handleData($visitors,$status)
    {
        $name = [];
        $visitor_num = [];
        foreach($visitors as $visitor)
        {
            $name[] = $visitor['product_name'];
            $visitor_num[] = $visitor['num'];
        }
        if($status)
        {
            return $name;
        }
        return $visitor_num;
    }

}
