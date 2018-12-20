<?php

namespace common\models;

use common\jobs\SendSmsJob;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Exception;
use yii\log\Logger;

/**
 * This is the model class for table "{{%refund_record}}".
 *
 * @property integer $id
 * @property string $sn
 * @property integer $pay_record_id
 * @property integer $user_id
 * @property integer $pay_platform
 * @property string $pay_trade_no
 * @property string $refund_trade_no
 * @property integer $virtual_order_id
 * @property integer $order_id
 * @property string $order_sn
 * @property string $refund_amount
 * @property integer $status
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $refund_time
 *
 * @property PayRecord $payRecord
 * @property Order $order
 */
class RefundRecord extends \yii\db\ActiveRecord
{
    // 0:未退款，1:退款成功，2:退款失败
    const STATUS_NOT_REFUND = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%refund_record}}';
    }

    public static function findBySn($sn)
    {
        return static::find()->where(['sn' => $sn])->one();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pay_record_id', 'user_id', 'pay_platform', 'virtual_order_id', 'order_id', 'status', 'creator_id', 'refund_time'], 'integer'],
            [['refund_amount'], 'number'],
            [['order_sn'], 'string', 'max' => 16],
            [['pay_trade_no', 'refund_trade_no'], 'string', 'max' => 255],
            [['creator_name'], 'string', 'max' => 10],
            [['sn'], 'string', 'max' => 17],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pay_record_id' => 'Pay Record ID',
            'sn' => 'Sn',
            'user_id' => 'User ID',
            'pay_platform' => 'Pay Platform',
            'pay_trade_no' => 'Pay Trade No',
            'refund_trade_no' => 'Refund Trade No',
            'virtual_order_id' => 'Virtual Order ID',
            'order_id' => 'Order ID',
            'order_sn' => 'Order Sn',
            'refund_amount' => 'Refund Amount',
            'status' => 'Status',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'refund_time' => 'Refund Time',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                $this->sn = static::generateSn();
            }
            return true;
        }
        return false;
    }

    public static function refundSuccess($refund_id, $refund_time, $trade_no)
    {
        Yii::getLogger()->log('退款成功，正在处理交易状态，退款单id：'.$refund_id, Logger::LEVEL_INFO);
        $record = RefundRecord::findOne($refund_id);
        if(null == $record)
        {
            throw new Exception('找不到退款单。');
        }
        if(!$record->isRefundSuccess())
        {
            Yii::getLogger()->log('退款成功，正在处理交易状态，平台：'.$record->pay_platform, Logger::LEVEL_INFO);
            $t = Yii::$app->db->beginTransaction();
            try
            {
                $record->refund_time = $refund_time;
                $record->status = static::STATUS_SUCCESS;
                $record->refund_trade_no = $trade_no;
                if(false === $record->update(false) || !$record->payRecord->refundSuccess($record))
                {
                    throw new Exception('退款通知处理失败');
                }
                $virtualOrder = $record->payRecord->virtualOrder;
//                $virtualOrder->refund_amount = BC::add($virtualOrder->refund_amount, $record->refund_amount);
                if($record->order)
                {
                    if(0 >= RefundRecord::find()->where(['order_id' => $record->order_id])->andWhere(['in', 'status', [RefundRecord::STATUS_FAIL, RefundRecord::STATUS_NOT_REFUND]])->count())
                    {
                        $record->order->refund_status = Order::REFUND_STATUS_REFUNDED;
                        $record->order->save(false);
                    }

                    //新增订单记录
                    /** @var Administrator $admin */
                    $admin = Yii::$app->user->identity;
                    if($record->order->isCancel())
                    {
                        $title = '订单已取消';
                        $remark = '取消原因:'. Order::getRefundReason($record->order->refund_reason).'。退款金额'. $record->refund_amount.'元。';
                    }
                    else
                    {
                        $title = '订单退款成功';
                        $remark = '退款原因:'. Order::getRefundReason($record->order->refund_reason).'。退款金额'. $record->refund_amount .'元。';
                    }
                    OrderRecord::create($record->order->id, $title, $remark, $admin);

                    //有退款实际订单id时，生成的系统提醒消息
                    Remind::create(Remind::CATEGORY_8, '订单退款成功', null, $virtualOrder->user_id, $record->order);
                }
                else
                {
                    //没有退款实际订单id时，循环生成虚拟订单下订单的系统提醒消息
                    foreach($virtualOrder->orders as $order)
                    {
                        Remind::create(Remind::CATEGORY_8, '订单退款成功', null, $virtualOrder->user_id, $order);
                    }
                }

                // 记录资金流水
                static::recordFlow($record);

                // 更新虚拟订单退款状态
                if($virtualOrder->isCanceled() && $virtualOrder->isRefunded())
                {
                    $virtualOrder->refund_status = VirtualOrder::REFUND_STATUS_REFUNDED;
                }
                $virtualOrder->save(false);

                $t->commit();

                //新增后台操作日志
                AdministratorLog::logRefundSuccess($record);

                Yii::getLogger()->log('退款成功，交易状态处理完成，平台：'.$record->pay_platform, Logger::LEVEL_INFO);
                /** @var Queue $queue */
                $queue = \Yii::$app->get('queue', false);
                $refund_record_sms_id = Property::get('refund_record_sms_id');
                if($queue && $refund_record_sms_id)
                {
                    // 您的订单：{1}
                    $queue->pushOn(new SendSmsJob(),[
                        'phone' => $virtualOrder->user->phone,
                        'sms_id' => $refund_record_sms_id,
                        'data' => $record->order ? [$record->order_sn] : (count($virtualOrder->orders) > 1 ? [$virtualOrder->orders[0]->sn.'等'.count($virtualOrder->orders).'个'] : [$virtualOrder->orders[0]->sn]),
                    ], 'sms');
                }
            }
            catch (Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
        }
    }

    /**
     * @param RefundRecord $record
     */
    private static function recordFlow($record)
    {
        $fundsRecord = new FundsRecord();
        $fundsRecord->refund_record_id = $record->id;
        $fundsRecord->user_id = $record->payRecord->virtualOrder->user_id;
        $fundsRecord->virtual_order_id = $record->payRecord->virtualOrder->id;
        $fundsRecord->virtual_sn = $record->payRecord->virtualOrder->sn;
        $fundsRecord->sn = $record->sn;
        $fundsRecord->orientation = FundsRecord::PAY_MONEY;
        $fundsRecord->pay_platform = $record->pay_platform;
        $fundsRecord->amount = '-'.$record->refund_amount;
        $fundsRecord->trade_no = $record->refund_trade_no;
        $fundsRecord->trade_time = $record->refund_time;
        $snList = [];
        $idList = [];
        if($record->order)
        {
            $snList[] = $record->order_sn;
            $idList[] = $record->order_id;
            $fundsRecord->order_id = $record->order_id;
        }
        else
        {
            foreach($record->payRecord->virtualOrder->orders as $order)
            {
                $snList[] = $order->sn;
                $idList[] = $order->id;
            }
        }
        $fundsRecord->setOrderSnList($snList);
        $fundsRecord->setOrderIdList($idList);
        $fundsRecord->save(false);
    }

    public function isRefundSuccess()
    {
        return $this->status == static::STATUS_SUCCESS;
    }

    public function getPayRecord()
    {
        return static::hasOne(PayRecord::className(), ['id' => 'pay_record_id']);
    }

    public function getOrder()
    {
        return static::hasOne(Order::className(), ['id' => 'order_id']);
    }

    public static function generateSn()
    {
        list($year, $month, $day, $h, $i, $s) = explode('-', date('y-m-d-H-i-s'));
        // 2分+2日+2月+2时+2年+2秒+4数
        return 'R'.$i.$day.$month.$h.$year.$s.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }
}
