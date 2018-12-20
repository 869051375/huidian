<?php

use backend\models\OrderExpectedCost;
use common\models\Order;
use common\models\VirtualOrder;
use common\utils\BC;
use yii\data\ActiveDataProvider;
use yii\db\Migration;

class m180712_090010_update_scheme extends Migration
{
    public function safeUp()
    {
        //成本录入
        $query = Order::find()->where(['total_cost' => 0]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $batchNum = 100;
        $count = $dataProvider->totalCount;
        $batch = ceil($count / $batchNum);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var Order[] $models */
            $models = $dataProvider->query->offset($i * $batchNum)->limit($batchNum)->all();
            foreach ($models as $order)
            {
                $cost = OrderExpectedCost::find()->where(['order_id' => $order->id])->limit(1)->one();
                if(null == $cost)
                {
                    $order->total_cost = 1;
                    $order->save(false);
                }
            }
        }

        //子订单已付款分配
        $query = VirtualOrder::find()->where(['in','status',[1,2]]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $batchNum = 100;
        $count = $dataProvider->totalCount;
        $batch = ceil($count / $batchNum);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var VirtualOrder[] $virtualModels */
            $virtualModels = $dataProvider->query->offset($i * $batchNum)->limit($batchNum)->all();
            foreach ($virtualModels as $virtualModel)
            {
                //最后一次付款 || 一次付清的
                $remainder = BC::sub($virtualModel->total_amount,$virtualModel->payment_amount,2);
                if(floatval($remainder) == 0 || $virtualModel->total_amount == $virtualModel->payment_amount)
                {
                    foreach($virtualModel->orders as $i => $order)
                    {
                        if($order->payment_amount <= 0)
                        {
                            $order->payment_amount = $order->price;
                            $order->save(false);
                        }
                    }
                }
                else
                {
                    $total = 0;
                    $count = count($virtualModel->orders);
                    if($count > 1)
                    {
                        //未付清并且不止一个订单的
                        $rate = [];
                        foreach($virtualModel->orders as $i => $order)
                        {
                            if($count > 1 && $i+1 != $count)
                            {
                                //按照子订单应付金额/虚拟订单应付金额
                                $rate[$order->id] = BC::div(BC::sub($order->price,$order->payment_amount),BC::sub($virtualModel->total_amount,$virtualModel->payment_amount),5);
                            }
                        }
                        foreach($virtualModel->orders as $i => $order)
                        {
                            if($order->payment_amount <= 0)
                            {
                                if($count > 1 && $i+1 == $count)
                                {
                                    $pay_price = BC::sub($virtualModel->payment_amount,$total);
                                    $order->payment_amount = BC::add($order->payment_amount,$pay_price);
                                    $order->save(false);
                                }
                                else
                                {
                                    $pay_price = round(BC::mul($virtualModel->payment_amount,$rate[$order->id],5),2);
                                    $total += $pay_price;
                                    $order->payment_amount = BC::add($order->payment_amount,$pay_price);
                                    $order->save(false);
                                }
                            }
                        }
                    }
                    elseif($count == 1)
                    {
                        //未付清只有一个订单未付清的情况
                        foreach($virtualModel->orders as $i => $order)
                        {
                            if($order->payment_amount <= 0)
                            {
                                $order->payment_amount = BC::add($order->payment_amount, $virtualModel->payment_amount);
                                $order->save(false);
                            }
                        }
                    }
                }
            }
        }
    }

    public function safeDown()
    {
        echo "m180712_090010_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180712_090010_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
