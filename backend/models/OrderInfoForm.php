<?php
namespace backend\models;

use common\models\Order;
use yii\base\Model;

/**
 * Class OrderInfoForm
 * @package backend\models
 *
 */
class OrderInfoForm extends Model
{
    public $product_name;
    public $district_name;
    public $price;
    public $sn;
    public $created_at;
    public $payment_time;
    public $begin_service_time;
    public $service_cycle;
    public $begin_service_cycle;
    public $end_service_cycle;
    public $renewal_warn_time;
    public $estimate_service_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['begin_service_cycle', 'end_service_cycle', 'renewal_warn_time', 'estimate_service_time'], 'required'],
            [['begin_service_cycle', 'end_service_cycle', 'renewal_warn_time', 'estimate_service_time'], 'date', 'format' => 'yyyy-MM-dd'],
            ['begin_service_cycle', 'string', 'max' => 10],
            ['end_service_cycle', 'string', 'max' => 10],
            ['renewal_warn_time', 'string', 'max' => 10],
            ['estimate_service_time', 'string', 'max' => 10],
            ['begin_service_cycle', 'validateTimes'],

        ];
    }

    public function validateTimes()
    {
        if($this->begin_service_cycle > $this->end_service_cycle && $this->end_service_cycle)
        {
            $this->addError('begin_service_cycle', '服务开始时间不能大于服务结束时间！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_name' => '服务名称',
            'district_name' => '所属地区',
            'price' => '支付金额',
            'sn' => '订单号',
            'created_at' => '下单时间',
            'payment_time' => '付款时间',
            'begin_service_time' => '服务开始时间',
            'service_cycle' => '服务周期',
            'begin_service_cycle' => '服务开始时间',
            'end_service_cycle' => '服务结束时间',
            'renewal_warn_time' => '续费报警开始时间',
            'estimate_service_time' => '预估服务结束时间',
        ];
    }

    /**
     * @param Order $order
     * @return null
     */
    public function save($order)
    {
        $begin_service_cycle = strtotime($this->begin_service_cycle. '00:00:00');
        $end_service_cycle = strtotime($this->end_service_cycle. '23:59:59');
        $renewal_warn_time = strtotime($this->renewal_warn_time. '00:00:00');
        $estimate_service_time = strtotime($this->estimate_service_time. '23:59:59');
        $order->begin_service_cycle = $begin_service_cycle > 0 ? $begin_service_cycle : 0;
        $order->end_service_cycle = $end_service_cycle > 0 ? $end_service_cycle : 0;
        $order->renewal_warn_time = $renewal_warn_time > 0 ? $renewal_warn_time : 0;
        $order->estimate_service_time = $estimate_service_time > 0 ? $estimate_service_time : 0;
        if(!$order->update(false))return null;
        return $order;
    }
}
