<?php

namespace console\controllers;

use common\models\Order;
use common\models\Property;
use common\models\Receipt;
use common\models\VirtualOrder;
use yii\console\Controller;

class OrderCancelController extends Controller
{
    // 取消超时订单
    public function actionRun($last = 0)
    {
        $order_pay_timeout = Property::get('order_pay_timeout', 0);
        if($order_pay_timeout > 0)
        {
            if($last == 0)
            {
                $last = time() - (2*($order_pay_timeout*3600));
            }
            /** @var \common\models\VirtualOrder[] $virtualOrders */
            $virtualOrders = VirtualOrder::find()->where(['status' => VirtualOrder::STATUS_PENDING_PAYMENT])
                ->andWhere('created_at < :time', [':time' => time() - ($order_pay_timeout * 3600)])
                ->andWhere('payment_amount<=0')
                ->andWhere('created_at > :last', [':last' => $last])
                ->orderBy(['created_at' => SORT_ASC])->limit(10)->all();
            foreach($virtualOrders as $virtualOrder)
            {
                if($virtualOrder->contract || $virtualOrder->isPendingCheckReceipt() || $virtualOrder->isUnpaid()) continue;//如果有未审核的新建回款或未付清，暂不取消订单
                foreach($virtualOrder->orders as $order)
                {
                    if($order->isPayAfterService()) continue;
                }
                $t = \Yii::$app->db->beginTransaction();
                try
                {
                    $virtualOrder->cancel(Order::BREAK_REASON_OVERTIME_CLOSE);
                    $virtualOrder->refund();
                    $t->commit();
                }
                catch (\Exception $e)
                {
                    $t->rollBack();
                    throw $e;
                }
            }
        }
    }
}
