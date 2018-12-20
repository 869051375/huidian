<?php
namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Order;
use common\models\OrderRecord;
use common\utils\BC;
use Yii;
use yii\base\Model;

class ReviewAdjustPriceForm extends Model
{
    public $order_id;
    public $status;
    public $status_reason;
    public $adjust_price;
    public $adjust_price_reason;
    public $origin_price;
    public $price;

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
            [['adjust_price', 'adjust_price_reason'], 'trim'],
            [['adjust_price', 'adjust_price_reason', 'order_id', 'status'], 'required'],
            [['adjust_price'], 'match', 'pattern'=>'/^[+-]{0,1}[0-9]*\.?[0-9]{0,2}$/', 'message'=>'请输入正确的变动金额。'],
            [['order_id'], 'validateOrderId'],
            [['adjust_price'], 'validateAdjustPrice'],
            [['status'], 'in', 'range' => [AdjustOrderPrice::STATUS_PASS, AdjustOrderPrice::STATUS_REJECT]],
            [['status_reason', 'adjust_price_reason'], 'default', 'value' => ''],
            [['status_reason', 'adjust_price_reason'], 'string', 'max' => 80],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(!$this->order)
        {
            $this->addError('order_id', '找不到订单。');
        }
        else
        {
            if(!$this->order->virtualOrder->isPendingPayment())
            {
                $this->addError('order_id', '订单必须是未付款状态。');
            }
            // 如果 审核通过的 则不允许再次审核
            if($this->order->isAdjustStatusPass() || $this->order->isAdjustStatusNotAdjust())
            {
                $this->addError('order_id', '该状态不能进行操作');
            }
        }
    }

    public function validateAdjustPrice()
    {
        if(null == $this->order) return ;
        $maxConfirmPaymentAmount = $this->order->virtualOrder->getPendingPayAmount();
        if($this->adjust_price == 0)
        {
            $this->addError('adjust_price', '请输入变动金额');
        }
        if($maxConfirmPaymentAmount + $this->adjust_price < 0)
        {
            $this->addError('adjust_price', '变动金额超出范围');
        }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $adjust = AdjustOrderPrice::findOne(['order_id' => $this->order->id, 'status' => AdjustOrderPrice::STATUS_PENDING]);
        if(null == $adjust)
        {
            $this->addError('order_id', '操作失败');
        }
        if($this->status == AdjustOrderPrice::STATUS_PASS) // 通过
        {
            $adjust->status = AdjustOrderPrice::STATUS_PASS;
            $adjust->status_reason = $this->status_reason;
            $adjust->adjust_price = BC::add($this->adjust_price, 0);
            $adjust->adjust_price_reason = $this->adjust_price_reason;
            $adjust->confirm_id = $administrator->id;
            $adjust->confirm_name = $administrator->name;
            $adjust->confirm_time = time();
            // 修改订单中的金额
            $oldOrderPrice = $this->order->price;
            $this->order->price = BC::add($this->order->price, $this->adjust_price);
            $this->order->adjust_amount = BC::add($this->adjust_price, $this->order->adjust_amount);
            $this->order->adjust_status = AdjustOrderPrice::STATUS_PASS;
            $this->order->virtualOrder->refresh();
            $this->order->virtualOrder->adjust_amount = BC::add($this->order->virtualOrder->adjust_amount, $this->adjust_price);
            $this->order->virtualOrder->total_amount = BC::add($this->order->virtualOrder->total_amount, $this->adjust_price);

            $t = Yii::$app->db->beginTransaction();
            try
            {
                $adjust->save(false);
                $this->order->save(false);
                $this->order->virtualOrder->save(false);
                $t->commit();
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
            OrderRecord::create($this->order->id, '金额变动审核通过', '订单金额：'.$oldOrderPrice.'元，变动金额：'.
                ($adjust->adjust_price > 0 ? '+' : '').$adjust->adjust_price.'元，应付金额：'.
                ($this->order->price).'元，修改说明：'.$adjust->adjust_price_reason, $administrator, 0, 1);
            //新增后台操作日志
            AdministratorLog::logAdjustOrderPriceReview($this->order, $adjust);
            return true;
        }
        else // 不通过
        {
            $adjust->status = AdjustOrderPrice::STATUS_REJECT;
            $this->order->adjust_status = AdjustOrderPrice::STATUS_REJECT;
            $this->order->save(false);
            $adjust->save(false);
            OrderRecord::create($this->order->id, '金额变动未通过', '申请变动金额：'.
                ($adjust->adjust_price > 0 ? '+' : '').$adjust->adjust_price.'元，'.
                $adjust->adjust_price_reason, $administrator, 0, 1);
            return true;
        }
    }

    public function attributeLabels()
    {
        return [
            'origin_price' => '原金额',
            'price' => '变动后金额',
            'adjust_price' => '变动金额',
            'adjust_price_reason' => '修改说明',
            'status_reason' => '审核说明'
        ];
    }
}