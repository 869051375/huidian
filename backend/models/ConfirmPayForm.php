<?php
namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\ContractRecord;
use common\models\CustomerService;
use common\models\MessageRemind;
use common\models\Order;
use common\models\OrderRecord;
use common\models\PayRecord;
use common\models\Receipt;
use common\models\VirtualOrder;
use common\utils\BC;
use Exception;
use Yii;
use yii\base\Model;

class ConfirmPayForm extends Model
{
    public $virtual_order_id;
    public $pay_method; //付款方式
    public $password;
    public $confirm_payment_amount;//确认付款金额
    public $receipt_id;
    public $is_separate_money;

    public $order_id;//消息提醒时需要子订单id
    public $audit_note;
    /**
     * @var VirtualOrder
     */
    public $vo;

    /**
     * @var Receipt
     */
    public $receipt;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['confirm_payment_amount'], 'trim'],
            [['confirm_payment_amount','pay_method'], 'required'],
            [['pay_method'], 'integer'],
            ['confirm_payment_amount', 'number'],
            [['confirm_payment_amount'], 'match', 'pattern'=>'/^[0-9]*\.?[0-9]{0,2}$/', 'message'=>'请输入正确的金额。'],
            [['virtual_order_id', 'password','audit_note'], 'required'],
            ['virtual_order_id', 'validateVirtualOrderId'],
            ['confirm_payment_amount', 'validateConfirmPaymentAmount','on' => 'review'],
            ['password', 'validatePassword'],
            [['receipt_id'], 'integer'],
            ['receipt_id', 'validateReceiptId'],
            [['order_id'], 'integer'],
            [['audit_note'], 'string', 'max' => 80],
        ];
    }

    public function validateVirtualOrderId()
    {
        $this->vo = VirtualOrder::findOne($this->virtual_order_id);
        if(!$this->vo)
        {
            $this->addError('virtual_order_id', '找不到订单。');
        }
        else
        {
            if(!($this->vo->isUnpaid() || $this->vo->isPendingPayment()))
            {
                $this->addError('virtual_order_id', '订单必须是未付款和未付清。');
            }
            if($this->vo->hasPendingAdjustPriceOrder())
            {
                $this->addError('virtual_order_id', '订单必须是未付款和未付清。');
            }
        }
    }

    public function validateReceiptId()
    {
        $this->receipt = Receipt::findOne($this->receipt_id);

        if(null == $this->receipt)
        {
            $this->addError('receipt_id', '找不到指定的回款。');
        }
        $this->receipt->receipt_date = date('Y-m-d',$this->receipt->receipt_date);
    }

    public function validateConfirmPaymentAmount()
    {
        if(null == $this->vo) return ;
        $maxConfirmPaymentAmount = $this->vo->getPendingPayAmount();
        if($this->confirm_payment_amount < 0)
        {
            $this->addError('confirm_payment_amount', '确认付款金额不能小于0。');
        }

        /** @var Receipt[] $receipts */
        $query = Receipt::find()->where(['virtual_order_id' => $this->vo->id, 'status' => Receipt::STATUS_NO]);
        if($this->receipt_id)
        {
            $query->andWhere(['!=', 'id', $this->receipt_id]);
        }
        $receipts = $query->all();
        if(!empty($receipts))
        {
            $totalAmount = 0;
            /** @var Receipt $receipt */
            foreach ($receipts as $receipt)
            {
                $totalAmount = BC::add($totalAmount, $receipt->payment_amount);
            }
            $paymentAmount = BC::sub($maxConfirmPaymentAmount, $totalAmount);
            if($paymentAmount < $this->confirm_payment_amount)
            {
                $this->addError('confirm_payment_amount', '已提交过新建回款还未确认，目前确认付款金额最多为'.$paymentAmount.'元');
            }
        }
        else
        {
            if($maxConfirmPaymentAmount < $this->confirm_payment_amount)
            {
                $this->addError('confirm_payment_amount', '确认付款金额不可超过'.$maxConfirmPaymentAmount.'元');
            }
        }
    }

    public function validatePassword()
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        if(!$administrator->validatePassword($this->password))
        {
            $this->addError('password', '密码不正确！');
        }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
//            $this->vo->cash = BC::sub($this->vo->total_amount, $this->vo->payment_amount);
            $is_separate_money = $this->is_separate_money ? '是' : '否';
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            if($this->receipt)
            {
                foreach($this->vo->orders as $order)
                {
                    if ($this->receipt->invoice == 1){
                        $invoice = '是';
                    }else{
                        $invoice = '否';
                    }
                    $images = null;
                    if($this->receipt->getImage())
                    {
                        $images .= '回款图片：';
                        foreach ($this->receipt->getImage() as $item)
                        {
                            $images .= "<a href=".$this->receipt->getImageUrl($item)." target='_blank'>{$item}</a>";
                        }
                    }
                    OrderRecord::create($order->id, '回款审核', "回款金额：{$this->confirm_payment_amount}元；收款日期：{$this->receipt->receipt_date}；收款公司：{$this->receipt->receipt_company}；回款方式：{$this->receipt->getPayMethodName()}，打款账户：{$this->receipt->pay_account}；是否开票：{$invoice}；回款备注：{$this->receipt->remark}；审核备注：{$this->audit_note}；{$images}", $administrator, 0, 1);
                }
            }
            if($this->vo->opportunities)
            {
                foreach($this->vo->opportunities as $opportunity)
                {
                    $images = null;
                    if($this->receipt->getImage())
                    {
                        $images .= '回款图片：';
                        foreach ($this->receipt->getImage() as $item)
                        {
                            $images .= "<a href=".$this->receipt->getImageUrl($item)." target='_blank'>{$item}</a>";
                        }
                    }
                    if($opportunity->contract)
                    {
                        contractRecord::CreateRecord($opportunity->contract->id,"{$administrator->name}通过了回款审核，明细为：回款金额：{$this->confirm_payment_amount}元；回款日期：{$this->receipt->receipt_date}；回款方式：{$this->receipt->getPayMethodName()}，打款账户为：{$this->receipt->pay_account}; 收款公司：{$this->receipt->receipt_company};回款备注：{$this->receipt->remark};{$images}审核备注：{$this->audit_note}", $administrator);
                    }
                }
            }

            $this->vo->cash = $this->confirm_payment_amount;
            $repayRecord = PayRecord::createCashPayRecord($this->vo, $this->vo->cash,$this->pay_method);
            $this->receipt->pay_record_id = $repayRecord->id;
            $this->receipt->save(false, ['pay_record_id']);
            $paySuccess = PayRecord::paySuccess(BC::mul($this->vo->cash, 100), $repayRecord->pay_sn, PayRecord::PAY_PLATFORM_CASH, time(), '',1);// todo is_auto必须自动分款
            $this->vo->save(false);
            $t->commit();
            //新增后台操作日志
            AdministratorLog::logConfirmPay($this->vo);
            //新增消息提醒
            $this->messageRemind($paySuccess->id, $paySuccess->pay_time);
            return true;
        }
        catch (Exception $e)
        {
            $t->rollBack();
            throw $e;
        }

    }

    public function attributeLabels()
    {
        return [
            'password' => '密码',
            'confirm_payment_amount' => '确认付款金额',
            'pay_method' => '线下付款方式',
            'audit_note' => '审核备注',
        ];
    }

    //生成消息提醒
    private function messageRemind($payId, $payTime)
    {
        $order = Order::findOne($this->order_id);
        if(null != $order)
        {
            //后台消息提醒
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '订单新分配提醒-订单号：'. $order->sn. $order->product_name. $order->province_name .'-'. $order->city_name.'-'.$order->district_name;
            $popup_message = '您有一条新订单（'. $order->sn .'）需处理，请查看！';
            $type = MessageRemind::TYPE_EMAILS;
            $type_url = MessageRemind::TYPE_URL_ORDER_LIST;
            $receive_id = $order->customerService ? $order->customerService->administrator->id : 0;
            $email = $order->customerService ? $order->customerService->administrator->email : "";
            $order_id = $order->id;
            $sign = 'k-'.$receive_id.'-'.$order->id.'-'.$payId.'-'.$payTime.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind)
            {
                MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, $administrator, $email);
            }
        }
    }

    public function receiptAuditFailedSave()
    {
        if(!$this->validate()) return false;
        //新增订单记录
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->vo->orders as $order)
            {
                OrderRecord::create($order->id, '订单回款审核失败', '本次未审核通过的金额：'.$this->confirm_payment_amount. '元；原因：'.$this->audit_note, $admin, 0, 1, 0, $this->receipt_id);
            }
            if($this->vo->opportunities)
            {
                foreach($this->vo->opportunities as $opportunity)
                {
                    $images = null;
                    if($this->receipt->getImage())
                    {
                        $images .= '回款图片：';
                        foreach ($this->receipt->getImage() as $item)
                        {
                            $images .= "<a href=".$this->receipt->getImageUrl($item)." target='_blank'>{$item}</a>";
                        }
                    }
                    if($opportunity->contract)
                    {
                        contractRecord::CreateRecord($opportunity->contract->id,"{$admin->name}驳回了回款审核，明细为：回款金额：{$this->confirm_payment_amount}元；回款日期：{$this->receipt->receipt_date}；回款方式：{$this->receipt->getPayMethodName()}，打款账户为：{$this->receipt->pay_account}; 收款公司：{$this->receipt->receipt_company};回款备注：{$this->receipt->remark};{$images}审核备注：{$this->audit_note}", $admin);
                    }
                }
            }
            $t->commit();
            return true;
        }
        catch (Exception $e)
        {
            $t->rollBack();
            return false;
        }
    }
}