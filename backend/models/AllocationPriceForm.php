<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\Order;
use common\models\OrderRecord;
use common\models\PerformanceRecord;
use common\utils\BC;
use Yii;
use yii\base\Model;

class AllocationPriceForm extends Model
{
    public $total_amount;  //虚拟订单总价
    public $payment_amount;//虚拟订单已付金额
    public $order_id;   //子订单id
    public $allot_payment_amount;//变动后（子）订单已付金额
    public $order_payment_amount;//本（子）订单已付金额
    public $allot_price;       //分配金额
    public $allot_price_reason;//修改说明

    /**
     * @var Order
     */
    public $order;

    public function rules()
    {
        return [
            [['allot_price', 'allot_price_reason'], 'trim'],
            [['allot_price', 'allot_price_reason', 'order_id'], 'required'],
            [['allot_price'], 'match', 'pattern'=>'/^[+-]{0,1}[0-9]*\.?[0-9]{0,2}$/', 'message'=>'请输入正确的变动金额。'],
            [['order_id'], 'validateOrderId'],
            [['allot_price'], 'validateAllotPrice'],
            [['allot_price_reason'], 'default', 'value' => ''],
            [['allot_price_reason'], 'string', 'max' => 80],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(!$this->order)
        {
            $this->addError('order_id', '找不到订单。');
        }
//        else
//        {
//            if(!$this->order->virtualOrder->isPendingPayment())
//            {
//                $this->addError('order_id', '订单必须是未付款状态。');
//            }
//            if(empty($this->order->salesman_aid))
//            {
//                $this->addError('order_id', '该订单无负责人不可修改价格。');
//            }
//            // 如果存在正在申请中的 或者 审核通过的 则不允许保存
//            if($this->order->isAdjustStatusPass() || $this->order->isAdjustStatusPending())
//            {
//                $this->addError('order_id', '一个订单只能申请一次修改价格');
//            }
//        }
    }

    public function validateAllotPrice()
    {
        if(null == $this->order) return ;
        if($this->allot_price > 0)
        {
            $payment_amount = $this->order->getTotalAmount();//子订单已付总和
            $maxPaymentAmount = BC::sub($this->order->virtualOrder->payment_amount,$payment_amount);//最大可分配数
            $orderMaxAmount = BC::sub($this->order->price,$this->order->payment_amount);//子订单剩下可分配数
            if(floatval($maxPaymentAmount) == 0)//虚拟订单有分配值
            {
                $this->addError('allot_price','对不起，当前虚拟订单可分配回款金额不足，请检查后重新提交。');
            }
            else
            {
                if($this->allot_price > $orderMaxAmount)//分配值大于子订单可分配值
                {
                    $this->addError('allot_price', '可分配金额不能超过'.$orderMaxAmount);
                }
                if($maxPaymentAmount < $orderMaxAmount)//虚拟订单分配值小于子订单可分配值
                {
                    if($this->allot_price > $maxPaymentAmount)
                    {
                        $this->addError('allot_price', '可分配金额不能超过'.$maxPaymentAmount);
                    }
                }
            }
        }
        else
        {
            $minPaymentAmount = BC::add($this->order->payment_amount,$this->allot_price);//最小可分配数
            if($minPaymentAmount < 0)
            {
                $this->addError('allot_price', '可分配金额不能小于0');
            }
        }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $administrator = Yii::$app->user->identity;
        $this->order->payment_amount = BC::add($this->order->payment_amount,$this->allot_price);
        $t = Yii::$app->db->beginTransaction();
        try
        {
            $this->order->save(false);
            $pending_pay = BC::sub($this->order->price,$this->order->payment_amount);
            PerformanceRecord::createRecord($this->order->virtual_order_id,$this->order->id,$this->allot_price,$pending_pay,0,0);
            OrderRecord::create($this->order->id, '分配回款', '订单已付金额调整：'.$this->allot_price.'元，变动备注：'.$this->allot_price_reason, $administrator, 0, 1);
            //新增后台操作日志
            AdministratorLog::logAdjustOrderPrice($this->order);
            $t->commit();
            return true;
        }
        catch(\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    public function attributeLabels()
    {
        return [
            'total_amount' => '订单回款总金额',
            'payment_amount' => '可分配回款金额',
            'order_payment_amount' => '本（子）订单已付金额',
            'allot_payment_amount' => '变动后（子）订单已付金额',
            'allot_price' => '分配金额',
            'allot_price_reason' => '变动备注',
        ];
    }

}