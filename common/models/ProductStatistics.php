<?php

namespace common\models;

use common\utils\BC;
use Yii;
use yii\log\Logger;
use yii\redis\Connection;

/**
 * This is the model class for table "{{%product_statistics}}".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $product_id
 * @property string  $product_name
 * @property integer $top_category_id
 * @property string  $top_category_name
 * @property integer $category_id
 * @property string  $category_name
 * @property integer $product_order_no
 * @property integer $pay_success_no
 * @property integer $product_pv
 * @property integer $product_visitors
 * @property integer $total_amount
 *
 */
class ProductStatistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_statistics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date', 'product_id', 'product_order_no','pay_success_no','product_pv'
                ,'product_visitors','top_category_id','category_id'], 'integer'],
            [['total_amount'], 'number'],
            [['product_name'], 'string','max'=>'32'],
            [['top_category_name'], 'string','max'=>'10'],
            [['category_name'], 'string','max'=>'10'],
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
            'top_category_id' => '一级分类ID',
            'top_category_name' => '一级分类名称',
            'category_id' => '二级分类ID',
            'category_name' => '二级分类名称',
            'product_order_no' => '下单商品数',
            'pay_success_no' => '支付成功数',
            'product_pv' => '浏览量',
            'product_visitors' => '访客数',
            'total_amount' => '金额',
        ];
    }

    /**
     * @param $product_id
     * @param $attribute
     * 商品访问量pv统计
     */
    public function ProductUv($product_id, $attribute)
    {
        try
        {
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $key = date('Y-m-d').'-'.$product_id.'-'.$attribute;
            $cache = Yii::$app->cache;
            $sid = Yii::$app->session->getId();
            if(empty($cache->get($key.$sid)))
            {
                $visitors = $redis->get($key);
                if($visitors)
                {
                    $redis->incrby($key,1);
                }
                else
                {
                    $redis->set($key,1);
                }
                $cache->set($key.$sid,time(),86400);
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log($attribute.'-统计出现错误', Logger::LEVEL_TRACE);
        }
    }

    //数量统计(下单商品数，)
    public function total($product_id,$attribute)
    {
        try
        {
            $order_key = date('Y-m-d').'-'.$product_id.'-'.$attribute;
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
            Yii::getLogger()->log($product_id.'-'.$attribute.'-统计出现错误', Logger::LEVEL_TRACE);
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

    //相比较率
    public function comparison($parameter_one,$parameter_two,$decimal,$action)
    {
        $cycle = null;
        if($action == 'statistics/yesterday-summary'){
            $cycle = '较昨天';
        } elseif ($action == 'statistics/week-summary'){
            $cycle = '较上周';
        }elseif ($action == 'statistics/month-summary'){
            $cycle = '较上月';
        }
        if($parameter_one && $parameter_two)
        {
            $result = BC::sub($parameter_one,$parameter_two);
            if($result > 0)
            {
                return $cycle.'↑'.BC::mul(BC::div($result,$parameter_two,$decimal),100,$decimal).'%';
            }
            return $cycle.'↓'.BC::mul(BC::div($result,$parameter_two,$decimal),100,$decimal).'%';
        }
        return '0.00%';
    }





}
