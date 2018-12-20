<?php
namespace backend\models;

use common\models\Order;
use common\models\OrderRecord;
use common\models\RefundRecord;
use yii\base\Exception;
use yii\base\Model;

class RefundOrderForm extends Model
{
    public $order_id;
    public $refund_reason = '';//退款原因
    public $refund_amount;//退款金额
    public $is_cancel;//订单是否取消
    public $refund_explain;//退款说明
    public $refund_remark;//退款备注

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
            [['refund_reason', 'refund_amount', 'refund_explain', 'refund_remark'], 'trim'],
            [['refund_reason', 'refund_amount'], 'required'],
            ['order_id', 'required'],
            ['order_id', 'validateOrderId'],
            ['refund_amount', 'number'],
            ['refund_amount', 'validateRefundAmount'],
            ['is_cancel', 'boolean'],
            ['refund_reason', 'in', 'range' => array_keys(static::getRefundReasonList())],
            [['refund_explain'], 'string', 'max'=>80],
            [['refund_remark'], 'string', 'max'=>80],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('refund_amount', '找不到订单信息。');
            return ;
        }
    }

    public function validateRefundAmount()
    {
        if(null == $this->order) return ;
        if(!$this->order->virtualOrder->isAlreadyPayment() && !$this->order->is_installment)
        {
            if($this->order->virtualOrder->isUnpaid())
            {
                $this->addError('refund_amount', '该订单无法单个商品退款。');
            }
            else
            {
                $this->addError('refund_amount', '该订单无法退款。');
            }
        }
        $maxRefundAmount = $this->order->canRefundAmount();
        if($this->order->virtualOrder->canRefundAmount() < $this->refund_amount)
        {
            $this->addError('refund_amount', '退款金额不能超出订单可退款金额。');
        }
        if($this->order->canRefundAmount() < $this->refund_amount)
        {
            $this->addError('refund_amount', '退款金额不能超出最大可退款金额：'.$maxRefundAmount);
        }
        $refundRecord = RefundRecord::find()->where(['order_id' => $this->order_id])->one();
        if(null != $refundRecord)
        {
            $this->addError('refund_amount', '该订单只能退款一次。');
        }
    }

    public function attributeLabels()
    {
        return [
            'refund_reason' => '退款原因',
            'refund_amount' => '退款金额',
            'is_cancel' => '订单停止服务',
            'refund_explain' => '退款说明',
            'refund_remark' => '退款备注',
        ];
    }

    public static function getRefundReasonList()
    {
        return Order::getRefundReasonList();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            $this->order->refund($this->refund_amount, $this->is_cancel, $this->refund_reason,
                $this->refund_explain, $this->refund_remark, $this->order->virtual_order_id, Order::BREAK_REASON_REFUND_AND_CANCEL);
            $t->commit();
            OrderRecord::create($this->order->id, $this->is_cancel ? '订单退款取消服务' : '订单退款继续服务', '退款原因:'.$this->order->getRefundReasonText().'，退款金额：'.$this->refund_amount.'元', \Yii::$app->user->identity);
            return true;
        }
        catch (Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}