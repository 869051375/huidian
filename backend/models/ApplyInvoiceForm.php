<?php
namespace backend\models;

use common\models\Invoice;
use common\models\Order;
use common\models\User;
use common\validators\TelPhoneValidator;
use yii\base\Model;
use yii\web\NotFoundHttpException;

class ApplyInvoiceForm extends Model
{
    public $invoice_title;
    public $invoice_addressee;
    public $invoice_phone;
    public $invoice_address;
    public $order_ids;
    public $tax_number;
    public $type = 1;
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id'], 'integer'],
            [['type'], 'required', 'message' => '请选择开票类型。'],
            [['order_ids'], 'required', 'message' => '请填选择需要开票的订单。'],
            [['order_ids'], 'validateOrderIds'],
            [['invoice_title', 'invoice_addressee', 'invoice_phone', 'invoice_address', 'tax_number'], 'trim'],
            [['invoice_title'], 'required', 'message' => '请填写发票抬头。'],
            [['invoice_addressee'], 'required', 'message' => '请填写收件人。'],
            [['invoice_phone'], 'required', 'message' => '请填写联系电话。'],
            [['invoice_address'], 'required', 'message' => '请填写收件地址。'],
            [['invoice_phone'], TelPhoneValidator::className()],
            ['invoice_title', 'string', 'min' => 1, 'max' => 30],
//            ['invoice_title', 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}（）\(\)]+$/u', 'message' => '发票抬头只能为中文', 'on' => 'need_invoice'],
            [['order_ids'], 'validateOrders'],

//            [['tax_number'], 'required', 'message' => '请填写税号。'],
            ['tax_number', 'string', 'min' => 1, 'max' => 18],
            [['tax_number'], 'match', 'pattern' => '/^[0-9A-Z]+$/', 'message' => '税号只能为数字或大写字母。'],
            [['tax_number'], 'validateTaxNumber', 'skipOnEmpty' => false, 'skipOnError' => false],

        ];
    }

    public function validateTaxNumber()
    {
        if($this->type == '1')
        {
            if(empty($this->tax_number))
            {
                $this->addError('tax_number', '请填写税号。');
            }
        }
    }

    public function validateOrderIds()
    {
        if(empty($this->order_ids))
        {
            $this->addError('order_ids', '您还没有选定需要申请发票的订单。');
        }
    }

    public function validateOrders()
    {
        $orders = $this->getOrders();
        if(empty($orders))
        {
            $this->addError('order_ids', '您的发票申请提交无效。');
        }
        foreach($orders as $order)
        {
            /** @var Order $order */
            if($order->isBreakService() || $order->isPendingPay() || $order->isInvoiced())
            {
                $this->addError('order_ids', '您的操作有误。');
                return ;
            }
        }
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return Order::find()
            ->where(['in', 'id', $this->order_ids])
            ->andWhere(['user_id' => $this->user_id, 'is_invoice' => Order::INVOICE_DISABLED])
            ->andWhere(['in', 'status', [
                Order::STATUS_PENDING_ALLOT,
                Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE,
                Order::STATUS_COMPLETE_SERVICE,
            ]])
            ->andWhere(['in', 'refund_status', [
                Order::REFUND_STATUS_NO,
                Order::REFUND_STATUS_REFUNDED,
            ]])
            ->andWhere(['is_cancel' => Order::CANCEL_DISABLED])
            ->andWhere(['or', ['complete_service_time' => 0], ['>', 'complete_service_time', time()-90*86400]])
            ->all();
    }

    public function attributeLabels()
    {
        return [
            'invoice_title' => '发票抬头：',
            'invoice_addressee' => '收件人：',
            'invoice_phone' => '联系电话：',
            'invoice_address' => '收件地址：',
            'order_ids' => '订单选项',
            'tax_number' => '税号：',
            'type' => '开票类型：'
        ];
    }

    public function save()
    {
        if(!$this->validate()) return null;
        $invoiceOrders = $this->getOrders();
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            foreach($invoiceOrders as $order)
            {
                $invoice = new Invoice();
                $invoice->user_id = $order->user_id;
                $invoice->order_id = $order->id;
                $invoice->order_sn = $order->sn;
                $invoice->virtual_order_id = $order->virtualOrder->id;
                $invoice->invoice_amount = 0;
                $invoice->invoice_title = $this->invoice_title;
                $invoice->addressee = $this->invoice_addressee;
                $invoice->phone = $this->invoice_phone;
                $invoice->address = $this->invoice_address;
                $invoice->status = Invoice::STATUS_SUBMITTED;
                $invoice->created_at = time();
                if($this->type == '1')
                {
                    $invoice->tax_number = $this->tax_number;
                }
                else
                {
                    $invoice->tax_number = '';
                }
                $invoice->save(false);
                //修改订单发票状态
                $order->is_invoice = Order::INVOICE_ACTIVE;
                $order->save(false);
            }
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}
