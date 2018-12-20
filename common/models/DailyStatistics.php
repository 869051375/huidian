<?php

namespace common\models;

use common\utils\BC;
use Yii;
use yii\helpers\Json;
use yii\log\Logger;
use yii\redis\Connection;
use yii\web\Cookie;

/**
 * This is the model class for table "{{%daily_statistics}}".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $visitors_no
 * @property integer $order_no
 * @property integer $pc_order_no
 * @property integer $m_order_no
 * @property integer $vx_order_no
 * @property integer $order_user_no
 * @property integer $order_price
 * @property integer $pay_success_no
 * @property integer $pay_user_no
 * @property integer $pay_price
 * @property integer $refunds_order_no
 * @property integer $browse_no
 * @property integer $shopping_cart_user_no
 * @property integer $renewal_order_no
 * @property integer $refunds_price
 * @property integer $twice_no
 * @property integer $repeatedly_no
 */
class DailyStatistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%daily_statistics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date', 'visitors_no', 'order_no','pc_order_no','m_order_no'
                ,'vx_order_no','order_user_no','pay_success_no', 'pay_user_no'
                ,'refunds_order_no','twice_no','repeatedly_no','shopping_cart_user_no'], 'integer'],
            [['order_price','pay_price','refunds_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'visitors_no' => '网站日访问量',
            'order_no' => '网站下单数',
            'browse_no' => '浏览商品用户数',
            'pc_order_no' => 'pc端下单数',
            'm_order_no' => '移动端下单数',
            'order_user_no' => '下单用户数',
            'pay_user_no' => '支付用户数',
            'shopping_cart_user_no' => '加入购物车用户数',
            'vx_order_no' => '微信端下单数',
            'pay_success_no' => '付款成功数',
            'pay_price' => '付款金额',
            'refunds_order_no' => '退款订单数',
            'order_price' => '订单金额',
            'refunds_price' => '退款金额',
        ];
    }

    /**
     * @param $attribute
     * 日UV统计
     */
    public function DayUv($attribute)
    {
        try
        {
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $key = date('Y-m-d').'-'.$attribute;
            if(empty(Yii::$app->request->cookies->get('dayUv')))
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
                $cookies = Yii::$app->response->cookies;
                $cookies->add(new Cookie([
                    'name' => 'dayUv',
                    'value' => 'cookieId',
                    'expire'=>time()+(3600*24)
                ]));
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log('日UV统计出现错误', Logger::LEVEL_TRACE);
        }
    }

    /**
     * @param $type
     * 统计各端下单数（pc，移动，微信）
     */
    public function countOrder($type)
    {
        try
        {
            $attribute = $this->getAttribute($type);
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $key = date('Y-m-d').'-'.$attribute;
            $other_order_no  = $redis->get($key);
            if($other_order_no)
            {
                $this->orderNo();
                $redis->incrby($key,1);
            }
            else
            {
                $this->orderNo();
                $redis->set($key,1);
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log($type.'-'.'统计出现错误', Logger::LEVEL_TRACE);
        }
    }
    //各平台总下单数
    static function orderNo()
    {
        try
        {
            $order_key = date('Y-m-d').'-order_no';
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
            Yii::getLogger()->log('order_no-统计出现错误', Logger::LEVEL_TRACE);
        }

    }

    //区分下单来源
    public function getAttribute($source)
    {
        if($source == Order::SOURCE_APP_PC){
            return 'pc_order_no';
        }
        else if($source == Order::SOURCE_APP_WAP){
            return 'm_order_no';
        }
        else if($source == Order::SOURCE_APP_WX){
            return 'vx_order_no';
        }
        return null;
    }

    //金额统计(下单金额，支付金额，退款金额)
    public function sumPrice($attribute, $price)
    {
        try
        {
            $key = date('Y-m-d').'-'.$attribute;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $order_price = $redis->get($key);
            if($order_price)
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
            Yii::getLogger()->log($attribute.'-'.'统计出现错误',Logger::LEVEL_TRACE);
        }

    }

    //数量统计(支付成功数，退款成功数)
    public function total($attribute)
    {
        try
        {
            $order_key = date('Y-m-d') . '-' . $attribute;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $order_no = $redis->get($order_key);
            if ($order_no) {
                $redis->incrby($order_key, 1);
            } else {
                $redis->set($order_key, 1);
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log($attribute.'-'.'统计出现错误',Logger::LEVEL_TRACE);
        }
    }

    /**
     * @param $field_name
     * @return integer
     * 统计总和
     */
    public function sumTotal($field_name)
    {
       return self::find()->sum($field_name);
    }

    /**
     * @param $parameter_one
     * @param $parameter_two
     * @return string
     */
    public function getPercentage($parameter_one,$parameter_two)
    {
        if($parameter_one && $parameter_two)
        {
            $data = BC::mul(BC::div($parameter_one,$parameter_two,5),100,2);
            return $data.'%';
        }
        return '0.00%';
    }

    //计算列的总和
    public function calculation($value,$field)
    {
        if(is_array($value))
        {
            return array_sum(array_column($value,$field));
        }
        return null;
    }

    //相比较率
    public function comparison($parameter_one,$parameter_two,$decimal,$action)
    {
        $cycle = null;
        if($action == 'statistics/yesterday'){
            $cycle = '较昨天';
        } elseif ($action == 'statistics/this-week'){
            $cycle = '较上周';
        }elseif ($action == 'statistics/this-month'){
            $cycle = '较上月';
        }elseif ($action == 'statistics/this-year'){
            $cycle = '较上年';
        }
        if($parameter_one && $parameter_two)
        {
            //增长率 = (本周-上周)/上周*100%
            $result = BC::div(BC::sub($parameter_one,$parameter_two,$decimal),$parameter_two,$decimal);
            if($result > 0)
            {
                return $cycle.'↑'.BC::mul($result,100,$decimal).'%';
            }
            return $cycle.'↓'.BC::mul($result,100,$decimal).'%';
        }
        return '0.00%';
    }

    //处理时间戳
    public function getTime($array)
    {
        $new_array = [];
        foreach($array as $item)
        {
            $new_array[] = date('Y/m/d',$item);
        }
        return $new_array;
    }

    //每月的统计
    public function perMonthData($attributes)
    {
        $data = [];
        for($i=1;$i<=date('m');$i++)
        {
            $data[] = empty($this->countYear($i,$attributes)) ? 0 : $this->countYear($i,$attributes) ;
        }
        return $data;
    }

    //每月的统计
    public function countYear($date,$attributes)
    {
                $start_time = mktime(0, 0 , 0,$date,1,date("Y"));
                $closure_time = mktime(23,59,59,$date,31,date("Y"));
        return  self::find()
                ->andWhere('date >= :start_time', [':start_time' => $start_time])
                ->andWhere('date <= :end_time', [':end_time' => $closure_time])
                ->orderBy(['date'=>SORT_DESC])
                ->sum($attributes);
    }
    //月份
    public function getMouth()
    {
        $data = [];
        for($i=1;$i<=date('m');$i++)
        {
            $data[] = $i.'月份';
        }
        return $data;
    }

    //下单来源饼图
    public function getOrderSource($vx,$m,$pc)
    {
        $wx = [];
        $mobile = [];
        $computer = [];
        $data = [];
        $wx['label'] = '微信';
        $wx['data'] = $vx;
        $wx['color'] = '#9bbb59';
        $data[] = $wx;
        $mobile['label'] = '移动端';
        $mobile['data'] = $m;
        $mobile['color'] = '#c0504d';
        $data[] = $mobile;
        $computer['label'] = 'PC端';
        $computer['data'] = $pc;
        $computer['color'] = '#4f81bd';
        $data[] = $computer;
        return Json::encode($data);
    }


}
