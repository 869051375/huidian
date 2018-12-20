<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\Invoice;
use common\models\Order;
use common\models\Property;
use common\models\Remind;
use shmilyzxt\queue\base\Queue;
use yii\base\Exception;
use yii\base\Model;

class SendInvoiceForm extends Model
{
    public $id;
    public $express;
    public $express_no;
    public $send_time;


    /**
     * @var Invoice
     */
    public $invoice;

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
            [['express', 'express_no', 'send_time'], 'trim'],
            [['id', 'express', 'express_no', 'send_time'], 'required'],
            ['id', 'validateId'],
            ['send_time', 'validateSendTime'],
            [['express'], 'string', 'max' => 100],
            [['express_no'], 'string', 'max' => 24],
        ];
    }

    public function validateId()
    {
        $invoiceModel = Invoice::findOne($this->id);
        if(null == $invoiceModel)
        {
            $this->addError('id', '找不到指定订单的发票。');
            return ;
        }
        if($invoiceModel->status != Invoice::STATUS_INVOICED)
        {
            $this->addError('id', '该订单暂未开发票。');
            return ;
        }

        $this->invoice = $invoiceModel;
    }

    public function validateSendTime()
    {

        if(strtotime($this->send_time) < strtotime(date('Y-m-d', time())))
        {
            $this->addError('send_time', '预计到达时间不能低于当前时间。');
            return ;
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => '',
            'express' => '快递公司',
            'express_no' => '快递单号',
            'send_time' => '预计到达时间',
        ];
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->invoice->express = $this->express;
        $this->invoice->express_no = $this->express_no;
        $this->invoice->status = Invoice::STATUS_SEND;
        $this->invoice->send_time = strtotime($this->send_time);
        if($this->invoice->save(false))
        {
            //进度提醒
            $this->order = Order::findOne($this->invoice->order_id);
            Remind::create(Remind::CATEGORY_6, '您的订单有新进度：已寄送发票', null, null, $this->order);

            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            $send_invoice_sms_id = Property::get('send_invoice_sms_id');
            if($queue && $send_invoice_sms_id)
            {
                // 快递公司：{1}，快递单号：{2}，预计到达时间：{3}
                $queue->pushOn(new SendSmsJob(),[
                    'phone' => $this->invoice->user->phone,
                    'sms_id' => $send_invoice_sms_id,
                    'data' => [$this->express, $this->express_no, $this->send_time]
                ], 'sms');
            }
            return true;
        }
        return false;
    }
}