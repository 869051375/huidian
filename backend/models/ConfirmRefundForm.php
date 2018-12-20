<?php
namespace backend\models;

use common\models\Administrator;
use common\models\DailyStatistics;
use common\models\PayRecord;
use common\models\RefundRecord;
use imxiangli\alipay\Alipay;
use imxiangli\wxpay\WxPay;
use Yii;
use yii\base\Model;

class ConfirmRefundForm extends Model
{
    public $refund_sn;
    public $refund_price;
    public $password;

    /**
     * @var RefundRecord
     */
    public $refundRecord;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['refund_sn','refund_price' ,'password'], 'required'],
            ['refund_sn', 'validateRefundRecord'],
            ['password', 'validatePassword'],
        ];
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

    public function validateRefundRecord()
    {
        $this->refundRecord = RefundRecord::find()->where(['sn' => $this->refund_sn])->one();
        if(null == $this->refundRecord)
        {
            $this->addError('password', '找不到退款信息!');
        }
        return $this->refundRecord;
    }

    public function refund()
    {
        if(!$this->validate()) return false;
        $dailyStatistics = new DailyStatistics();
        if($this->refundRecord->pay_platform == PayRecord::PAY_PLATFORM_ALIPAY)
        {
            $dailyStatistics->total('refunds_order_no');
            $dailyStatistics->sumPrice('refunds_price',$this->refund_price);
            return $this->alipay();
        }
        else if($this->refundRecord->pay_platform == PayRecord::PAY_PLATFORM_WX)
        {
            $dailyStatistics->total('refunds_order_no');
            $dailyStatistics->sumPrice('refunds_price',$this->refund_price);
            return $this->wx();
        }
        else if($this->refundRecord->pay_platform == PayRecord::PAY_PLATFORM_UNIONPAY)
        {
            $dailyStatistics->total('refunds_order_no');
            $dailyStatistics->sumPrice('refunds_price',$this->refund_price);
            return $this->unionpay();
        }
        else if($this->refundRecord->pay_platform == PayRecord::PAY_PLATFORM_CASH)
        {
            $dailyStatistics->total('refunds_order_no');
            $dailyStatistics->sumPrice('refunds_price',$this->refund_price);
            return $this->cash();
        }
        $this->addError('password', '不支持的退款方式!');
        return false;
    }

    public function cashRefund()
    {
        if(!$this->validate()) return false;
        $dailyStatistics = new DailyStatistics();
        $dailyStatistics->total('refunds_order_no');
        $dailyStatistics->sumPrice('refunds_price',$this->refund_price);
        if($this->cash())
        {
            return true;
        }
        $this->addError('password', '不支持的退款方式!');
        return false;
    }

    private function alipay()
    {
        /** @var Alipay $alipay */
        $alipay = \Yii::$app->get('alipay');
        return $alipay->buildRefundRequestForm(date('Y-m-d H:i:s'), date('Ymd').str_pad($this->refundRecord->id, 8, '0',STR_PAD_LEFT), $this->refundRecord->pay_trade_no.'^'.$this->refundRecord->refund_amount.'^退款');
    }

    private function wx()
    {
        /** @var WxPay $wxpay */
        $wxpay = \Yii::$app->get('wxpay');
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($this->refundRecord->payRecord->pay_sn);
        $input->SetTotal_fee(bcmul($this->refundRecord->payRecord->payment_amount, 100));
        $input->SetRefund_fee(bcmul($this->refundRecord->refund_amount, 100));
        $input->SetOut_refund_no($this->refundRecord->sn);
        $input->SetOp_user_id(\WxPayConfig::MCHID());
        $data = $wxpay->refund($input);
        if($data && $data['result_code'] == 'SUCCESS')
        {
            //站内系统提醒
            RefundRecord::refundSuccess($this->refundRecord->id, time(), $data['refund_id']);
            return true;
        }
        $this->addError('password', '退款失败!原因：('.$data['err_code'].')'.$data['err_code_des'].var_export($data, true));
        return false;
    }

    private function unionpay()
    {
        $this->addError('password', '不支持的退款方式!');
        return false;
    }

    private function cash()
    {
        RefundRecord::refundSuccess($this->refundRecord->id, time(), '');
        return true;
    }

    public function attributeLabels()
    {
        return [
            'password' => '请输入密码'
        ];
    }
}