<?php
namespace backend\models;

use common\models\Invoice;
use common\models\Order;
use yii\base\Model;

/**
 * Class ConfirmInvoiceForm
 * @package backend\models
 */
class ConfirmInvoiceForm extends Model
{
    public $id;

    /**
     * @var Invoice
     */
    private $invoice;

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            ['id', 'validateId'],
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
        if($invoiceModel->status != Invoice::STATUS_SUBMITTED)
        {
            $this->addError('id', '该订单暂未提交申请开具发票。');
            return ;
        }
        if($invoiceModel->order->status != Order::STATUS_COMPLETE_SERVICE && $invoiceModel->order->status != Order::STATUS_IN_SERVICE)
        {
            $this->addError('id', '该订单未完成，无法开具发票。');
            return ;
        }
        if(!$invoiceModel->order->virtualOrder->isAlreadyPayment())
        {
            $this->addError('id', '该订单尚未完成付款，无法开具发票。');
            return ;
        }

        $this->invoice = $invoiceModel;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '发票',
        ];
    }


    /**
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->invoice->invoice_amount = $this->invoice->order->getInvoiceAmount();
        $this->invoice->status = Invoice::STATUS_CONFIRMED;
        $this->invoice->confirm_time = time();
        if(!$this->invoice->save(false)) return false;
        return true;
    }
}