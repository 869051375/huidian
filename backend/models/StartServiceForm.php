<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderSms;
use common\models\OrderStatusStatistics;
use common\models\Property;
use common\models\Remind;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Model;

/** @var Remind[] $remind */

class StartServiceForm extends Model
{
    public $order_id;
    public $is_send_sms = 1;
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
            ['is_send_sms', 'boolean'],
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

        // 检查订单状态，必须是待服务状态
        if(!$this->order->isPendingService())
        {
            $this->addError('order_id', '当前订单状态不能进行该操作。');
        }

        if($this->order->isRefundAudit() || $this->order->isRefundApply())
        {
            $this->addError('order_id', '当前订单尚未完成退款操作。');
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'is_send_sms' => '发送短信（若不需要发送短信给客户，可选择不勾选）',
        ];
    }

    /**
     * 开始服务的同时新增一条站内提醒
     * @return bool
     * @var Administrator $admin
     */
    public function startService()
    {
        if(!$this->validate()) return false;
        if($this->order->startService())
        {
            //新增订单记录
            $admin = Yii::$app->user->identity;
            OrderRecord::create($this->order->id, '开始服务', '', $admin);

            //新增消息提醒
            Remind::create(Remind::CATEGORY_3, '您的订单有新进度：开始服务', null, null, $this->order);

            OrderStatusStatistics::totalStatusNum($this->order->product_id,$this->order->district_id,'in_service_no');//统计订单状态-服务中

            //新增后台操作日志
            AdministratorLog::logStartService($this->order);

            //服务人员点击开始服务时短信发送通知客户
            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            $start_service_sms_id = Property::get('start_service_sms_id');
            if($this->is_send_sms == 1 && $queue && $start_service_sms_id)
            {
                 //【掘金企服】尊敬的客户，您购买的商品：{1}，已开始服务，如需帮助，请联系您的专属客服：{2}（{3}），如遇服务质量问题请联系嘟嘟妹（{4}）第一时间解决您的困扰，掘金企服全体人员竭诚为您服务！
                $queue->pushOn(new SendSmsJob(),[
                    'phone' => $this->order->user->phone,
                    'sms_id' => $start_service_sms_id,
                    'data' => [
                        $this->order->product_name,
                        $this->order->customer_service_name,
                        $this->order->customerService ? $this->order->customerService->phone : '',
                        $this->order->supervisor ? $this->order->supervisor->phone : '',
                    ]
                ], 'sms');

                // 短信记录
                OrderSms::startServiceOrderSms($this->order);
            }
            return true;
        }
        return false;
    }
}