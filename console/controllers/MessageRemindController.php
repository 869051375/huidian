<?php

namespace console\controllers;

use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmOpportunity;
use common\models\MessageRemind;
use common\models\Order;
use Yii;
use yii\console\Controller;

class MessageRemindController extends Controller
{
    //生成提醒消息-商机的预计成交时间到期
    public function actionOpportunityPredictDealTimeout()
    {
        $time = time();
        $opportunities = CrmOpportunity::find()
            ->where(['in', 'status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]])
            ->andWhere('predict_deal_time < :time AND predict_deal_time > :time1', [':time' => $time, ':time1' => $time - 30*60])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit(10)->all();
        if(null != $opportunities)
        {
            /** @var CrmOpportunity $opportunity */
            foreach($opportunities as $opportunity)
            {
                $message = '你有商机：'. $opportunity->name .'，预计成交时间已到，请加倍努力哦！';
                $popup_message = '您的商机：'. $opportunity->name .'，即将超过跟进时间，请及时跟进！';
                $type = MessageRemind::TYPE_COMMON;
                $type_url = MessageRemind::TYPE_URL_OPPORTUNITY_DETAIL;
                $opportunity_id= $opportunity->id;
                $receive_id = $opportunity->administrator_id;
                $sign = 'm-'.$receive_id.'-'.$opportunity_id.'-'.$opportunity->predict_deal_time.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, 0, $opportunity_id);
                }
            }
        }
    }

    //生成提醒消息-商机的下次跟进时间到期
    public function actionOpportunityNextFollowTimeout()
    {
        $time = time();
        $opportunities = CrmOpportunity::find()
            ->where(['in', 'status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]])
            ->andWhere('next_follow_time < :time', [':time' => $time])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit(10)->all();
        if(null != $opportunities)
        {
            /** @var CrmOpportunity $opportunity */
            foreach($opportunities as $opportunity)
            {
                $message = '你有商机：'. $opportunity->name .'，跟进即将超时，请及时跟进处理！';
                $popup_message = '您的商机：'. $opportunity->name .'，即将超过成交时间，请及时跟进！';
                $type = MessageRemind::TYPE_COMMON;
                $type_url = MessageRemind::TYPE_URL_OPPORTUNITY_DETAIL;
                $opportunity_id= $opportunity->id;
                $receive_id = $opportunity->administrator_id;
                $sign = 'n-'.$receive_id.'-'.$opportunity_id.'-'.$opportunity->next_follow_time.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, 0, $opportunity_id);
                }
            }
        }
    }

    //生成提醒消息-业务主体成立日期满一年提醒
    public function actionBusinessYear(){
        $data = CrmCustomer::find()->alias('c')
            ->select('c.id as customer_id,c.administrator_id,b.operating_period_begin,b.company_name')
            ->leftJoin(['b'=>BusinessSubject::tableName()],'c.id = b.customer_id')
            ->where(['<>','b.operating_period_begin',0])
            ->andWhere(['b.subject_type' => 0])
            ->asArray()
            ->all();

        for($i=1;$i<=100;$i++){
            foreach($data as $key => $val){
                $a = date('Y-m-d',$val['operating_period_begin']);
                $times = strtotime("$a +$i year");
                $date = date("Y-m-d",$times);
                //var_dump($date);die;
                if($date == date("Y-m-d",time())){
                    $message = '您的客户：'. $val['company_name'] .'今天成立满'.$i.'周年，请及时跟进哦！';
                    $popup_message = '您的客户：'. $val['company_name'] .'今天成立满'.$i.'周年，请及时跟进！';
                    $type = MessageRemind::TYPE_COMMON;
                    $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
                    $customer_id= $val['customer_id'];
                    $receive_id = $val['administrator_id'];
                    $sign = 'n-'.$receive_id.'-'.$customer_id.'-'.$val['operating_period_begin'].'-'.$type.'-'.$type_url;
                    $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                    if(null == $messageRemind)
                    {
                        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, 0);
                    }
                }
            }
        }
    }

    //订单实施节点超时报警
    public function actionOrderTimeout()
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->andWhere(['or',
            'o.next_node_warn_time > 0 and o.next_node_warn_time < :current_time and o.status!=:status_complete_service',
            'o.next_follow_time > 0 and o.next_follow_time < :current_time'],
            [':current_time' => time(), ':status_complete_service' => Order::STATUS_COMPLETE_SERVICE]);
        $query->andWhere(['not in', 'o.status', [
            Order::STATUS_BREAK_SERVICE,
            Order::STATUS_COMPLETE_SERVICE,
            Order::STATUS_PENDING_SERVICE,
            Order::STATUS_PENDING_ALLOT,
        ]]);
        $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
        $query->orderBy(['vo.created_at' => SORT_ASC])->limit(10);
        $orders = $query->all();
        if(null != $orders)
        {
            /** @var Order $order */
            foreach($orders as $order)
            {
                $message = '订单服务超时提醒-订单号：'. $order->sn . $order->product_name . ' -'.$order->province_name .'-'.$order->city_name.'-'.$order->district_name;
                $popup_message = '您有一条新订单（'. $order->sn .'）服务超时，请查看！';
                $type = MessageRemind::TYPE_EMAILS;
                $type_url = MessageRemind::TYPE_URL_ORDER_DETAIL;
                $order_id = $order->id;

                //被派单客服
                $receive_id = $order->customerService ? $order->customerService->administrator->id : 0;
                $email = $order->customerService ? $order->customerService->administrator->email : '';
                $sign = 'o-'.$receive_id.'-'.$order_id.'-'.$order->flow_id.'-'.$order->next_node_warn_time.'-'.$order->next_follow_time.'-'.$order_id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, null, $email);
                }

                //被派单服务人员
                $receive_id = $order->clerk ?  $order->clerk->administrator->id : 0;
                $email = $order->clerk ? $order->clerk->administrator->email : '';
                $sign = 'p-'.$receive_id.'-'.$order_id.'-'.$order->flow_id.'-'.$order->next_node_warn_time.'-'.$order->next_follow_time.'-'.$order_id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, null, $email);
                }

                //被派单嘟嘟妹
                $receive_id = $order->supervisor ? $order->supervisor->administrator->id : 0;
                $sign = 'q-'.$receive_id.'-'.$order_id.'-'.$order->flow_id.'-'.$order->next_node_warn_time.'-'.$order->next_follow_time.'-'.$order_id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0);
                }
            }
        }
    }
}