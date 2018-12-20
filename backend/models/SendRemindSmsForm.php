<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\Administrator;
use common\models\Order;
use common\models\Property;
use shmilyzxt\queue\base\Queue;
use yii\base\Model;

class SendRemindSmsForm extends Model
{
    public $order_id;
    /**
     * @var Order
     */
    public $order;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            ['order_id', 'required'],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('refund_amount', '订单不存在。');
            return ;
        }

        if(!$this->order->isRenewal())
        {
            $this->addError('order_id', '非续费订单不能发送提醒。');
        }
        else
        {
            if($this->order->renewal_warn_time > time())
            {
                $this->addError('order_id', '该订单不能发送续费提醒。');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
        ];
    }

    /**
     * @return bool
     * @var Administrator $admin
     */
    public function save()
    {
        if(!$this->validate()) return false;
        //点击发送短信时短信发送通知客户续费
        /** @var Queue $queue */
        $queue = \Yii::$app->get('queue', false);
        $send_renewal_remind_sms_id = Property::get('send_renewal_remind_sms_id');
        if($queue && $send_renewal_remind_sms_id)
        {
            //[掘金企服】亲爱的{1}您好，您的{2}服务即将到期，以免对您的公司造成影响，请尽快续费，如需帮助，请联系客服人员或致电400-6060-999
            $queue->pushOn(new SendSmsJob(),[
                'phone' => $this->order->user->phone,
                'sms_id' => $send_renewal_remind_sms_id,
                'data' => [
                    $this->order->user->name,
                    $this->order->product_name,
                ]
            ], 'sms');
            // 短信记录(待定)
//        OrderSms::startServiceOrderSms($this->order);
            return true;
        }
        return false;
    }
}