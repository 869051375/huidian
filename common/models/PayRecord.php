<?php

namespace common\models;

use common\utils\BC;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\log\Logger;

/**
 * This is the model class for table "{{%pay_record}}".
 *
 * @property integer $id
 * @property integer $virtual_order_id
 * @property integer $user_id
 * @property string $virtual_sn
 * @property string $pay_sn
 * @property integer $pay_time
 * @property integer $pay_platform
 * @property integer $pay_method
 * @property string $payment_amount
 * @property integer $pay_status
 * @property string $trade_no
 * @property integer $is_refund
 * @property string $refund_amount
 * @property integer $refund_time
 *
 * @property VirtualOrder $virtualOrder
 * @property User $user
 */
class PayRecord extends \yii\db\ActiveRecord
{
    const PAY_STATUS_NOT_PAY = 0;
    const PAY_STATUS_SUCCESS = 1;
    const PAY_STATUS_FAIL = 2;

    const PAY_PLATFORM_ALIPAY = 1;
    const PAY_PLATFORM_WX = 2;
    const PAY_PLATFORM_UNIONPAY = 3;
    const PAY_PLATFORM_CASH = -1;//线下支付

    const TYPE_ALI_PAY = 1; //线下支付宝
    const TYPE_WX_PAY = 2; //线下微信
    const TYPE_LINE_MONEY = 3; //线下现金
    const TYPE_PUBLIC_TRANSFER = 4; //线下对公转账
    const TYPE_PRIVATE_TRANSFER = 5; //线下对私转账
    const TYPE_PRIVATE_ALIYUN = 6; //阿里云平台付款
    const TYPE_PRIVATE_TENCENT = 7; //腾讯众创平台付款
    const TYPE_OTHER_COLLECTION = 8; //其他分公司代收

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pay_record}}';
    }

    public static function findBySn($pay_sn)
    {
        return static::find()->where(['pay_sn' => $pay_sn])->one();
    }


    public static function paySuccess($total_fee, $out_trade_no, $pay_platform, $time_end, $trade_no,$is_auto = 1)
    {
        /** @var PayRecord $record */
        $record = PayRecord::find()->where(['pay_sn' => $out_trade_no])->one();
        if(null == $record)
        {
            throw new Exception('支付通知找不到记录:'.$out_trade_no.'，平台：'.$pay_platform);
        }
        if($total_fee != bcmul($record->payment_amount, 100))
        {
            throw new Exception('支付通知支付金额不一致，平台：'.$pay_platform);
        }
        if(!$record->isPaySuccess())
        {
            $t = Yii::$app->db->beginTransaction();
            try
            {
                Yii::getLogger()->log('支付宝通知，处理交易状态', Logger::LEVEL_INFO);
                $record->pay_status = PayRecord::PAY_STATUS_SUCCESS;
                $record->pay_time = $time_end;
                $record->pay_platform = $pay_platform;
                $record->trade_no = $trade_no;
                $record->virtualOrder->payment($record->payment_amount,$is_auto);
                if (false === $record->update(false)) {
                    throw new Exception('支付通知处理失败，平台：' . $pay_platform . ',:' . reset($record->getFirstErrors()));
                }
                static::recordFlow($record);
                $t->commit();
            }
            catch (Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
        }
        Yii::getLogger()->log('处理交易状态完成，平台：'.$pay_platform, Logger::LEVEL_INFO);

        //生成消息提醒(前台用户下单支付成功后)
        if($pay_platform == PayRecord::PAY_PLATFORM_CASH)
        {
            foreach ($record->virtualOrder->orders as $order)
            {
                if(null != $order && $order->customerService && $order->isPendingAllot())
                {
                    $message = '订单新分配提醒-订单号：'. $order->sn. $order->product_name. ' -'.$order->province_name .'-'. $order->city_name.'-'.$order->district_name;
                    $popup_message = '您有一条新订单需分配处理，请查看！';
                    $type = MessageRemind::TYPE_EMAILS;
                    $type_url = MessageRemind::TYPE_URL_ORDER_DETAIL;
                    $receive_id = $order->customerService ? $order->customerService->administrator->id : 0;
                    $email = $order->customerService ? $order->customerService->administrator->email : '';
                    $order_id = $order->id;
                    $sign = 'j-'.$receive_id.'-'.$order->id.'-'.$record->id.'-'.$record->pay_time.'-'.$type.'-'.$type_url;
                    $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                    if(null == $messageRemind)
                    {
                        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, null, $email);
                    }
                }
            }
        }

        return $record;
    }

    /**
     * @param PayRecord $record
     */
    private static function recordFlow($record)
    {
        /** @var Receipt $receipt */
        $receipt = Receipt::find()->where(['pay_record_id' => $record->id])->one();

        $fundsRecord = new FundsRecord();
        if(null != $receipt) {
            $fundsRecord->receipt_id = $receipt->id;
        }
        $fundsRecord->pay_record_id = $record->id;
        $fundsRecord->user_id = $record->virtualOrder->user_id;
        $fundsRecord->virtual_order_id = $record->virtualOrder->id;
        $fundsRecord->virtual_sn = $record->virtualOrder->sn;
        $fundsRecord->sn = $record->pay_sn;
        $fundsRecord->orientation = FundsRecord::MONEY_COLLECTION;
        $fundsRecord->pay_platform = $record->pay_platform;
        $fundsRecord->pay_method = $record->pay_method;
        $fundsRecord->amount = $record->payment_amount;
        $fundsRecord->trade_no = $record->trade_no;
        $fundsRecord->trade_time = $record->pay_time;
        $snList = [];
        $idList = [];
        foreach($record->virtualOrder->orders as $order)
        {
            $snList[] = $order->sn;
            $idList[] = $order->id;
        }

        $fundsRecord->setOrderSnList($snList);
        $fundsRecord->setOrderIdList($idList);
        $fundsRecord->save(false);
    }

    public function isPaySuccess()
    {
        return $this->pay_status == PayRecord::PAY_STATUS_SUCCESS;
    }

    public function isRefund()
    {
        return $this->is_refund == 1;
    }

    public function canRefundAmount()
    {
        return $this->isPaySuccess() ? BC::sub($this->payment_amount, $this->refund_amount) : 0;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['pay_time'],
                ],

            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['virtual_order_id', 'pay_time', 'pay_platform', 'pay_status', 'is_refund', 'refund_time','pay_method'], 'integer'],
            [['payment_amount', 'refund_amount'], 'number'],
            [['virtual_sn', 'pay_sn'], 'string', 'max' => 17],
            [['trade_no'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'virtual_order_id' => 'Virtual Order ID',
            'virtual_sn' => 'Virtual Sn',
            'pay_sn' => 'Pay Sn',
            'pay_time' => 'Pay Time',
            'pay_platform' => 'Pay Platform',
            'payment_amount' => 'Payment Amount',
            'pay_status' => 'Pay Status',
            'trade_no' => 'Trade No',
            'is_refund' => 'Is Refund',
            'refund_amount' => 'Refund Amount',
            'refund_time' => 'Refund Time',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                $this->pay_sn = static::generateSn();
            }
            return true;
        }
        return false;
    }

    // 生成退款单（预退款）
    /**
     * @param $refund_amount
     * @param Order $order 当为空时，不指定退款实际订单，为整个虚拟订单取消时退款。
     * @throws ErrorException
     */
    public function preRefund($refund_amount, $order = null)
    {
        if($refund_amount <= 0)
        {
            throw new ErrorException('退款金额必须大于0');
        }
        if($this->canRefundAmount() < $refund_amount)
        {
            throw new ErrorException('该订单生成退款单金额不能超过支付金额');
        }
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $refundRecord = new RefundRecord();
        $refundRecord->pay_record_id = $this->id;
        $refundRecord->user_id = $this->virtualOrder->user_id;
        $refundRecord->pay_platform = $this->pay_platform;
        $refundRecord->pay_trade_no = $this->trade_no;
        $refundRecord->virtual_order_id = $this->virtual_order_id;
        if(null != $order)
        {
            $refundRecord->order_id = $order->id;
            $refundRecord->order_sn = $order->sn;
        }
        $refundRecord->refund_amount = $refund_amount;
        $refundRecord->status = RefundRecord::STATUS_NOT_REFUND;
        $refundRecord->creator_id = $admin->id;
        $refundRecord->creator_name = $admin->name;
        $this->refund_amount = BC::add($this->refund_amount, $refund_amount);
        $this->save(false);
        $refundRecord->save(false);
    }

    /**
     * @param RefundRecord $refundRecord
     * @return bool
     */
    public function refundSuccess($refundRecord)
    {
        $this->is_refund = 1;
        $this->refund_time = $refundRecord->refund_time;
        $this->refund_amount = $refundRecord->refund_amount;
        return false !== $this->update(false);
    }

    public function getVirtualOrder()
    {
        return self::hasOne(VirtualOrder::className(), ['id' => 'virtual_order_id']);
    }

    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public static function generateSn()
    {
        list($year, $month, $day, $h, $i, $s) = explode('-', date('y-m-d-H-i-s'));
        // 2分+2日+2月+2时+2年+2秒+4数
        return 'P'.$i.$day.$month.$h.$year.$s.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    /**
     * 生成一条支付记录，等待用户付款操作（微信、支付、银联等）
     * @param VirtualOrder $virtual_order
     * @param $payment_amount
     * @return PayRecord|null
     */
    public static function createPayRecord($virtual_order, $payment_amount)
    {
        $payRecord = new PayRecord();
        $payRecord->virtual_order_id = $virtual_order->id;
        $payRecord->user_id = $virtual_order->user_id;
        $payRecord->virtual_sn = $virtual_order->sn;
        $payRecord->pay_sn = $payRecord->generateSn();
        $payRecord->payment_amount = $payment_amount;
        $payRecord->pay_status = PayRecord::PAY_STATUS_NOT_PAY;
        if($payRecord->save(false)) return $payRecord;
        return null;
    }

    /**
     * 生成一条支付记录，现金支付，表示已经完成线下付款。
     * @param VirtualOrder $virtual_order
     * @param $payment_amount
     * @param $pay_method
     * @return PayRecord|null
     */
    public static function createCashPayRecord($virtual_order, $payment_amount,$pay_method)
    {
        $payRecord = new PayRecord();
        $payRecord->virtual_order_id = $virtual_order->id;
        $payRecord->user_id = $virtual_order->user_id;
        $payRecord->virtual_sn = $virtual_order->sn;
        $payRecord->pay_sn = $payRecord->generateSn();
        $payRecord->pay_platform = PayRecord::PAY_PLATFORM_CASH;
        $payRecord->payment_amount = $payment_amount;
        $payRecord->pay_status = PayRecord::PAY_STATUS_NOT_PAY;
        $payRecord->pay_method = $pay_method;
        if($payRecord->save(false)) return $payRecord;
        return null;
    }

    public function getPayPlatformName()
    {
        if($this->pay_platform == self::PAY_PLATFORM_WX)
        {
            return '微信支付';
        }
        else if($this->pay_platform == self::PAY_PLATFORM_ALIPAY)
        {
            return '支付宝';
        }
        else if($this->pay_platform == self::PAY_PLATFORM_UNIONPAY)
        {
            return '银联支付';
        }
        else if($this->pay_platform == self::PAY_PLATFORM_CASH)
        {
            return '现金支付';
        }
        return null;
    }
    public static function getPayMethod()
    {
        return[
            self::TYPE_ALI_PAY => '线下支付宝',
            self::TYPE_WX_PAY => '线下微信',
            self::TYPE_LINE_MONEY => '线下现金',
            self::TYPE_PUBLIC_TRANSFER => '线下对公转账',
            self::TYPE_PRIVATE_TRANSFER => '线下对私转账',
            self::TYPE_PRIVATE_ALIYUN => '阿里云平台付款',
            self::TYPE_PRIVATE_TENCENT => '腾讯众创平台付款',
            self::TYPE_OTHER_COLLECTION => '其他分公司代收',
        ];
    }

    public function getPayMethodName()
    {
        $names = self::getPayMethod();
        return isset($names[$this->pay_method]) ? $names[$this->pay_method] : '-';
    }
}
