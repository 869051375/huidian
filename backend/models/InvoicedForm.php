<?php
namespace backend\models;

use common\models\Invoice;
use yii\base\Model;

/**
 * Class InvoicedForm
 * @package backend\models
 */
class InvoicedForm extends Model
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
        if($invoiceModel->status != Invoice::STATUS_CONFIRMED)
        {
            $this->addError('id', '该订单暂未确认发票。');
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
        $this->invoice->status = Invoice::STATUS_INVOICED;
        $this->invoice->invoice_time = time();
        if(!$this->invoice->save(false)) return false;
        return true;
    }
}