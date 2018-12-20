<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/8/23
 * Time: 下午1:59
 */

namespace console\record;


use common\models\DailyStatistics;
use common\models\ShoppingCartItem;
use common\models\VirtualOrder;
use Yii;
use yii\redis\Connection;

class DailyRecord
{
    public function record($date)
    {
        $time = empty($date) ? mktime(0, 0 , 0,date("m"),date("d")-1,date("Y")) : strtotime($date);
        $date = empty($date) ? date('Y-m-d',strtotime("-1 day")) : $date;
        $virtualOrder = new VirtualOrder();
        $model = new DailyStatistics();
        $model->date = $time;
        $model->visitors_no = $this->getRedisVal('visitors_no',$date) ;
        $model->order_price = $this->getRedisVal('order_price',$date);
        $model->vx_order_no = $this->getRedisVal('vx_order_no',$date);
        $model->pc_order_no = $this->getRedisVal('pc_order_no',$date);
        $model->m_order_no = $this->getRedisVal('m_order_no',$date);
        $model->order_no = $this->getRedisVal('order_no',$date);
        $model->order_user_no = $this->getRedisVal('order_user_no',$date);
        $model->pay_user_no = $this->getRedisVal('pay_user_no',$date);
        $model->pay_price = $this->getRedisVal('pay_price',$date);
        $model->pay_success_no = $this->getRedisVal('pay_success_no',$date);
        $model->refunds_order_no = $this->getRedisVal('refunds_order_no',$date);
        $model->refunds_price = $this->getRedisVal('refunds_price',$date);
        $model->browse_no = $this->getRedisVal('browse_no',$date);
        $model->shopping_cart_user_no = $this->getRedisVal('shopping_cart_user_no',$date);
        $model->renewal_order_no  = $this->getRedisVal('renewal_order_no',$date);
        $model->twice_no  = $virtualOrder->twiceNo($time);
        $model->repeatedly_no  = $virtualOrder->repeatedlyNo($time);
        if($model->save(false))
        {
            $this->delRedis($date);
        }
    }

    /**
     * @param $time
     * 获取keys并删除
     */
    private function delRedis($time)
    {
        $model = new DailyStatistics();
        $attributeLabels = array_keys($model->attributeLabels());
        foreach($attributeLabels as $attributeLabel)
        {
            $key = $time.'-'.$attributeLabel;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $redis->del($key);
        }
    }

    /**
     * @param $attribute
     * @param $time
     * @return mixed
     * 获取redis中的值
     */
    private function getRedisVal($attribute,$time)
    {
        $key = $time.'-'.$attribute;
        /** @var Connection $redis */
        $redis = Yii::$app->get('redis');
        if($redis->get($key))
        {
            return $redis->get($key);
        }
        return 0;
    }
}