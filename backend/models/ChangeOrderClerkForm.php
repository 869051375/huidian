<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Clerk;
use common\models\MessageRemind;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderSms;
use common\models\OrderStatusStatistics;
use common\models\Property;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Model;

class ChangeOrderClerkForm extends Model
{
    public $order_id;
    public $clerk_id;

    /**
     * @var Clerk
     */
    public $clerk;

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
            [['clerk_id', 'order_id'], 'required'],
            ['clerk_id', 'validateClerkId'],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateClerkId()
    {
        $this->clerk = Clerk::findOne($this->clerk_id);
        if(null == $this->clerk)
        {
            $this->addError('clerk_id', '找不到服务人员。');
            return ;
        }
        if(!$this->clerk->isActive())
        {
            $this->addError('clerk_id', '该服务人员未开通服务。');
        }
        //todo 校验地区和服务的对应商品匹配
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('refund_amount', '订单不存在。');
            return ;
        }

        // 检查订单状态
        if(!($this->order->isPendingService() || $this->order->isInService() || $this->order->isPendingAllot()))
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
            'clerk_id' => '服务人员',
            'order_id' => '订单',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $oldClerkName = $this->order->clerk_name;
        $oldClerkId = 0;
        if($this->order->clerk_id)
        {
            $clerkModel = Clerk::findOne($this->order->clerk_id);
            if(null != $clerkModel)
            {
                $oldClerkId = $clerkModel ? $clerkModel->administrator->id : 0;
            }
        }
        if(empty($oldClerkName))
        {
            $this->order->status = Order::STATUS_PENDING_SERVICE;
        }
        $this->order->clerk_id = $this->clerk_id;
        $this->order->clerk_name = $this->clerk->name;
        $this->order->clerk_department_id = $this->clerk->administrator->department_id;
        $this->order->dispatch_time = time();
        $this->order->order_dispatch_time = time();
        if($this->order->save(false))
        {
            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            if(empty($oldClerkName))
            {
                // 客服派单给服务人员时，短信发送给服务人员
                $assign_clerk_sms_id = Property::get('assign_clerk_sms_id');
                if($queue && $assign_clerk_sms_id)
                {
                    // 订单号：{1}；客户昵称：{2}；客户手机号：{3}；所购商品：{4}；客服：{5}
                    $queue->pushOn(new SendSmsJob(),['phone' => $this->order->clerk->phone,
                        'sms_id' => $assign_clerk_sms_id, 'data' => [
                            $this->order->sn, $this->order->user->name, $this->order->user->phone,
                            $this->order->product_name, $this->order->customer_service_name
                        ] ], 'sms');
                }
                //新增订单记录
                $admin = Yii::$app->user->identity;
                OrderRecord::create($this->order->id, '派单成功', '', $admin, 0, OrderRecord::INTERNAL_ACTIVE);

                // 短信记录
                OrderSms::assignClerkOrderSms($this->order);

                OrderStatusStatistics::totalStatusNum($this->order->product_id,$this->order->district_id,'pending_service_no');//统计订单状态-待服务

                //后台消息提醒
                $order_id = $this->order->id;
                $type = MessageRemind::TYPE_EMAILS;
                $type_url = MessageRemind::TYPE_URL_ORDER_DETAIL;
                $receive_id = $this->order->clerk ? $this->order->clerk->administrator->id : 0;
                $email = $this->order->clerk ? $this->order->clerk->administrator->email : '';
                $sign = 'a-'.$receive_id.'-'.$order_id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    $this->messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email);
                }
            }
            else
            {
                // 客服修改服务人员时，短信发送给服务人员
                $re_assign_clerk_sms_id = Property::get('re_assign_clerk_sms_id');
                if($queue && $re_assign_clerk_sms_id)
                {
                    //上次服务人员是：{1}；订单号：{2}；客户昵称：{3}；客户手机号：{4}；所购商品：{5}；客服：{6}；客服电话：{7}
                    /** @var string $oldClerkName */
                    $queue->pushOn(new SendSmsJob(),['phone' => $this->order->clerk->phone,
                        'sms_id' => $re_assign_clerk_sms_id, 'data' => [
                            $oldClerkName, $this->order->sn, $this->order->user->name, $this->order->user->phone,
                            $this->order->product_name, $this->order->customer_service_name, $this->order->customerService->phone
                        ] ], 'sms');
                }

                //后台消息提醒
                $order_id = $this->order->id;
                $type = MessageRemind::TYPE_EMAILS;
                $type_url = MessageRemind::TYPE_URL_ORDER_DETAIL;
                $receive_id = $this->order->clerk ? $this->order->clerk->administrator->id : 0;
                $email = $this->order->clerk ? $this->order->clerk->administrator->email : '';
                $sign = 'b-'.$oldClerkId.'-'.$receive_id.'-'.$order_id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind && $oldClerkId != $receive_id)
                {
                    $this->messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email);
                }
                //添加操作记录
                OrderRecord::create($this->order->id, '修改服务人员', "更换服务人员为：{$this->order->clerk_name}", Yii::$app->user->identity, 0, 1);
            }
            //新增后台操作日志
            AdministratorLog::logChangeOrderClerk($this->order, $oldClerkName);
            return true;
        }
        return false;
    }

    //消息提醒
    private function messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email)
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $message = '订单服务提醒-订单号：'. $this->order->sn .','. $this->order->product_name. ' -'.$this->order->province_name.'-'.$this->order->city_name.'-'.$this->order->district_name;
        $popup_message = '您有一条新订单需要服务处理，请查看！';
        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, $administrator, $email);
    }
}