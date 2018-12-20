<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_sms}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $flow_id
 * @property integer $flow_node_id
 * @property integer $flow_action_id
 * @property string $content
 * @property string $phone
 * @property integer $clerk_id
 * @property string $clerk_name
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $is_internal
 * @property integer $created_at
 */
class OrderSms extends ActiveRecord
{
    const INTERNAL_ACTIVE = 1;//仅内部后台查看

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_sms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'flow_id', 'flow_node_id', 'flow_action_id', 'clerk_id', 'creator_id', 'created_at'], 'integer'],
            [['content'], 'string', 'max' => 200],
            [['phone'], 'string', 'max' => 11],
            [['clerk_name', 'creator_name'], 'string'],
            ['is_internal', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'order_id' => '订单id',
            'flow_id' => '流程id',
            'flow_node_id' => '流程节点id',
            'flow_action_id' => '操作id',
            'content' => '短信内容',
            'phone' => '手机号',
            'clerk_id' => '服务人员id',
            'clerk_name' => '服务人员名字',
            'creator_id' => '操作人id',
            'creator_name' => '操作人名字',
            'created_at' => '上传时间',
        ];
    }

    /**
     * 派单之后给服务人员发送的短信记录
     * @param Order $order
     */
    public static function assignClerkOrderSms($order)
    {
        if(null == $order) return;
        $sms = Property::get('assign_clerk_sms_preview') ? Property::get('assign_clerk_sms_preview') : '';
        $smsData = [];
        $smsData[] = $order->sn;
        $smsData[] = $order->user->name;
        $smsData[] = $order->user->phone;
        $smsData[] = $order->product_name;
        $smsData[] = $order->customer_service_name;
        $phone = $order->clerk->phone;
        OrderSms::create($order, $phone, $smsData, $sms, OrderSms::INTERNAL_ACTIVE);
    }

    /**
     * 开始服务给客户发送的短信记录
     * @param Order $order
     */
    public static function startServiceOrderSms($order)
    {
        if(null == $order) return;
        $sms = Property::get('start_service_sms_preview') ? Property::get('start_service_sms_preview') : '';
        $smsData = [];
        $smsData[] = $order->product_name;
        $smsData[] = $order->customer_service_name;
        $smsData[] = $order->customerService ? $order->customerService->phone : '';
        $smsData[] = $order->supervisor ? $order->supervisor->phone : '';
        $phone = $order->user->phone;
        OrderSms::create($order, $phone, $smsData , $sms, 0);
    }

    /**
     * @param Order $order
     * @param int $phone
     * @param array $smsData
     * @param string $sms
     * @param int $isInternal
     * @return bool
     */
    private static function create($order, $phone, $smsData, $sms, $isInternal)
    {

        $orderSms = new OrderSms();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $orderSms->is_internal = $isInternal;
        $orderSms->order_id = $order->id;
        $orderSms->phone = $phone;
        $orderSms->creator_id = $admin->id;
        $orderSms->creator_name = $admin->name;
        $orderSms->content = OrderSms::getPreviewSms($smsData, $sms);
        $orderSms->created_at = time();
        if(!$orderSms->save(false)) return false;
        return true;
    }

    /**
     * 返回预览短信内容
     * @param array $smsData
     * @param string $sms
     * @return mixed|null|string
     */
    private static function getPreviewSms($smsData, $sms)
    {
       if(!empty($sms))
        {
            foreach($smsData as $k => $data)
            {
                $sms = str_replace('{'.(1+$k).'}', $data, $sms);
            }
        }
        return $sms;
    }
}
