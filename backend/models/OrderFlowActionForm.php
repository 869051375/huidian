<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\Administrator;
use common\models\Clerk;
use common\models\FlowNode;
use common\models\FlowNodeAction;
use common\models\Holidays;
use common\models\MessageRemind;
use common\models\Order;
use common\models\OrderFile;
use common\models\OrderFlowRecord;
use common\models\OrderRecord;
use common\models\OrderSms;
use common\models\OrderStatusStatistics;
use common\models\Remind;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Model;
use yii\log\Logger;

class OrderFlowActionForm extends Model
{
    public $order_id;
    public $flow_id;
    public $node_id;
    public $action_id;
    public $input_text;
    public $input_date;
    public $file_id;
    public $remark;

    public $is_send_sms =1;

    /**
     * @var FlowNodeAction
     */
    public $action;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var FlowNode
     */
    public $node;

    /**
     * @var OrderFile
     */
    public $file;

    /**
     * @var Clerk
     */
    public $clerk;

    /**
     * @var array
     */
    public $inputTextList = [];

    public $input_company_name;

    public $input_trademark_apply_no;

    public function rules()
    {
        return [
            ['remark', 'trim'],
            [['order_id', 'flow_id', 'node_id', 'action_id'], 'required'],

            [['order_id'], 'validateOrderId'],
            [['flow_id'], 'validateFlowId'],
            [['node_id'], 'validateNodeId'],
            [['action_id'], 'validateActionId'],

            [['input_text', 'input_date', 'file_id'], 'trim'],

            [['input_date'], 'required', 'on' => 'input_date', 'message' => '必须选择日期'],
            [['file_id'], 'required', 'on' => 'upload', 'message' => '请上传文件'],
            //[['remark'], 'required', 'on' => 'upload', 'message' => '请输入文件说明'],
            [['remark'], 'safe', 'on' => 'upload'],
            [['input_date'], 'required', 'on' => 'input_date_text', 'message' => '必须选择日期'],
            [['input_text'], 'required', 'on' => 'input_date_text', 'message' => '该项必须填写'],
            [['file_id'], 'required', 'on' => 'upload_text', 'message' => '请上传文件'],
            [['input_text'], 'required', 'on' => 'upload_text', 'message' => '该项必须填写'],
            [['input_text'], 'required', 'on' => 'input_text', 'message' => '该项必须填写'],

            [['input_text'], 'validateInputText'],
            [['input_date'], 'validateInputDate'],
            [['file_id'], 'validateFileId'],

            ['is_send_sms', 'boolean'],
        ];
    }

    public function beforeValidate()
    {
        if(parent::beforeValidate())
        {
            $this->validateOrderId();
            $this->validateFlowId();
            $this->validateNodeId();
            $this->validateActionId();
            if($this->action)
            {
                if($this->action->isTypeUpload())
                {
                    if($this->action->hasInputList())
                    {
                        $this->setScenario('upload_text');
                    }
                    else
                        $this->setScenario('upload');
                }
                else if($this->action->isTypeDate())
                {
                    if($this->action->hasInputList())
                    {
                        $this->setScenario('input_date_text');
                    }
                    else
                        $this->setScenario('input_date');
                }
                else
                {
                    if($this->action->hasInputList())
                    {
                        $this->setScenario('input_text');
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function validateOrderId()
    {
        if($this->order) return ;
        $this->order = Order::find()->where(['id' => $this->order_id, 'clerk_id' => $this->clerk->id])->one();
        if(null == $this->order)
        {
            $this->addError('order_id', '该订单不存在或不是您服务的订单。');
        }
    }

    public function validateFlowId()
    {
        if($this->order->flow_id != $this->flow_id)
        {
            $this->addError('flow_id', '订单流程不正确。');
        }
    }

    public function validateNodeId()
    {
        if($this->node) return ;
        $this->node = FlowNode::find()->where(['id' => $this->node_id, 'flow_id' => $this->flow_id])->one();
        if(null == $this->node)
        {
            $this->addError('node_id', '当前流程节点错误。');
        }
        if(null == $this->node->nextNode)
        {
            if($this->order->isRenewal() && (!($this->order->begin_service_cycle > 0) || !($this->order->end_service_cycle > 0)))
            {
                $this->addError('node_id', '您无法操作，请先设置服务周期时间。');
            }
        }
    }

    public function validateActionId()
    {
        if($this->action) return ;
        $this->action = FlowNodeAction::find()->where(['id' => $this->action_id, 'flow_node_id' => $this->node_id, 'flow_id' => $this->flow_id])->one();
        if(null == $this->action)
        {
            $this->addError('action_id', '流程操作错误。');
        }
    }

    public function validateInputText()
    {
        $data = [];
        $list = $this->action->getInputList();
        foreach ($list['input_list'] as $item)
        {
            $k = md5($item['label']);
            if(!isset($this->input_text[$k])) break;
            
            $v = $this->input_text[$k];
            if($item['is_company'])
            {
                $this->input_company_name = $v;
            }
            else if((isset($item['is_trademark_apply_no']) && $item['is_trademark_apply_no']) || (isset($item['type']) && $item['type'] == 2))
            {
                $this->input_trademark_apply_no = $v;
            }
            if(md5($item['label']) == $k)
            {
                $data[] = [
                    'label' => $item['label'],
                    'text' => $v,
                ];
            }
        }
        $this->inputTextList = $data;
    }

    public function validateInputDate()
    {
        if($this->action->isTypeDate())
        {
            if(empty($this->input_date))
            {
                $this->addError('input_date', '请选择日期：'.$this->action->action_hint);
            }
            else
            {
                $time = strtotime($this->input_date);
                if(date('Y-m-d', $time) != $this->input_date)
                {
                    $this->addError('input_date', '您的日期格式不正确。');
                }
            }
        }
    }

    public function validateFileId()
    {
        $this->file = OrderFile::find()->where(['id' => $this->file_id, 'order_id' => $this->order_id, 'flow_id' => $this->flow_id,
            'flow_node_id' => $this->node_id, 'flow_action_id' => $this->action_id])->one();
        if(null == $this->file && $this->action->isTypeUpload())
        {
            $this->addError('file_id', '请上传文件：'.$this->action->action_hint);
        }
    }

    public function doAction()
    {
        if(!$this->validate()) return null;

        // 记录操作记录
        $record = new OrderFlowRecord();
        $record->order_id = $this->order_id;
        $record->flow_id = $this->flow_id;
        $record->flow_node_id = $this->node_id;
        $record->flow_action_id = $this->action_id;
        $record->flow_action_name = $this->action->action_label;
        $record->limited_time = $this->order->next_node_limited_time;
        //$record->clerk_id = $this->clerk->id;
        //$record->clerk_name = $this->clerk->name;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $record->creator_id = $admin->id;
        $record->creator_name = $admin->name;
        $record->ignore_limited_time = $this->action->isStay() ? 1 : 0;
        $record->setInputText($this->inputTextList);
        $record->input_date = $this->input_date;
        if($this->file)
        {
            $record->file_id = $this->file->id;
            if(!empty($this->remark))
            {
                $this->file->remark = $this->remark;
                $this->file->save(false);
            }
        }
        else
        {
            $record->file_id = 0;
        }
        $record->created_at = time();
        if($record->save(false))
        {
            $message = '您的订单有新进度：'.$this->action->action_label;
            Remind::create(Remind::CATEGORY_5, $message, null, null, $this->order);
        }

        // 修改订单信息
        $this->order->last_node_id = $this->node_id;
        $this->order->last_action_id = $this->action_id;
        $this->order->next_node_id = 0;
        $this->order->next_node_limited_time = 0;
        $this->order->next_node_warn_time = 0;
        $this->order->flow_is_finish = 0;
        if($this->input_company_name)
        {
            $this->order->company_name = $this->input_company_name;
        }
        if($this->input_trademark_apply_no)
        {
            $this->order->trademark_apply_no = $this->input_trademark_apply_no;
        }
        if($this->action->isStay())
        {
            $this->order->next_node_id = $this->node_id;
        }
        else
        {
            if($this->node->nextNode)
            {
                $this->order->next_node_id = $this->node->nextNode->id;
                if($this->action->isTypeDate())
                {
                    // 如果是日期选择，则需要在日期选择的截止日期完成
                    $nextLimitedTime = strtotime($this->input_date)+86400;
                    $this->order->next_node_limited_time = $nextLimitedTime;
                    $this->order->next_node_warn_time = Holidays::getPreWorkDay($this->input_date);
                }
                else if($this->node->nextNode->is_limit_time && $this->node->nextNode->limit_work_days > 0)
                {
                    // 直接根据节点中的工作日限制时间
                    $this->order->next_node_limited_time = Holidays::getEndTimeByDays($this->node->nextNode->limit_work_days);
                    $this->order->next_node_warn_time = Holidays::getEndTimeByDays($this->node->nextNode->limit_work_days-1);
                }
            }
        }
        // 没有下一个节点了，结束流程。
        if(null == $this->node->nextNode)
        {
            OrderStatusStatistics::totalStatusNum($this->order->product_id,$this->order->district_id,'complete_service_no');//统计订单状态-服务完成
            $this->order->status = Order::STATUS_COMPLETE_SERVICE;
            $this->order->flow_is_finish = 1;
            $this->order->complete_service_time = time();

            //生成消息提醒
            $order_id = $this->order->id;
            $type = MessageRemind::TYPE_EMAILS;
            $type_url = MessageRemind::TYPE_URL_ORDER_DETAIL;
            $receive_id = $this->order->customerService ? $this->order->customerService->administrator->id : 0;
            $email = $this->order->customerService ? $this->order->customerService->administrator->email : '';
            $nodeId = $this->node->id;
            $sign = 'c-'.$receive_id.'-'.$nodeId.'-'.$order_id.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind)
            {
                $this->messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email);
            }
        }
        $this->order->save(false);
        try {
            // 发送短信（加入短信队列并记录短信记录）
            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            if($queue && $this->action->sms_id && ($this->is_send_sms || !$this->order->flow->can_disable_sms))
            {
                $queue->pushOn(new SendSmsJob(),['phone' => $this->order->user->phone,
                    'sms_id' => $this->action->sms_id, 'data' => $this->getSmsData()
                ], 'sms');
                // 短信记录
                $sms = new OrderSms();
                $sms->order_id = $this->order_id;
                $sms->flow_id = $this->flow_id;
                $sms->flow_node_id = $this->node_id;
                $sms->flow_action_id = $this->action_id;
                $sms->content = $this->getPreviewSms();
                $sms->phone = $this->order->user->phone;
                //$sms->clerk_id = $record->clerk_id;
                //$sms->clerk_name = $record->clerk_name;
                $sms->creator_id = $record->creator_id;
                $sms->creator_name = $record->creator_name;
                $sms->created_at = $record->created_at;
                $sms->save(false);
            }
        }catch (\Exception $e){
            Yii::getLogger()->log($e, Logger::LEVEL_INFO);
        }
        //新增订单记录
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        OrderRecord::create($this->order_id, '', '', $admin, $record->id);
        return $record;
    }

    /**
     * 返回预览短信内容
     * @return mixed|string
     */
    private function getPreviewSms()
    {
        $smsData = $this->getSmsData();
        $sms = $this->action->sms_preview;
        foreach($smsData as $k => $data)
        {
            $sms = str_replace('{'.(1+$k).'}', $data, $sms);
        }
        return $sms;
    }

    /**
     * 获得短信变量参数
     * @return array
     */
    private function getSmsData()
    {
        // 短信模板变量
        $smsData = [];
        if($this->action->isHasSendVar())
        {
            $smsData[] = $this->clerk->address;
            $smsData[] = $this->clerk->name;
            $smsData[] = $this->clerk->phone;
        }
        if($this->action->isTypeDate())
        {
            $smsData[] = $this->input_date;
        }
        // 所有的类型都有可能存在input输入框（多个）
        foreach($this->inputTextList as $text)
        {
            $smsData[] = $text['text'];
        }
        return $smsData;
    }

    public function attributeLabels()
    {
        return [
            'remark' => '文件说明',
            'is_send_sms' => '勾选之后将给客户发送以下短信内容(不勾选则不发送)',
        ];
    }

    private function messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email)
    {
        //后台消息提醒
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $message = '订单完成提醒-订单号：'. $this->order->sn .','. $this->order->product_name.' -'.$this->order->province_name.'-'.$this->order->city_name.'-'.$this->order->district_name;
        $popup_message = '您有一条新订单服务已完成，请查看！';
        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, $administrator, $email);
    }
}