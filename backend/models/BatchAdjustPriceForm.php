<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Order;
use common\models\OrderRecord;
use common\utils\BC;
use common\models\Receipt;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Model;
use yii\log\Logger;

class BatchAdjustPriceForm extends Model
{
    public $order_id;
    public $origin_price;
    public $price;
    public $adjust_price;
    public $adjust_price_reason;

    /**
     * @var Order[]
     */
    public $orders;

    public function rules()
    {
        return [
            ['order_id', 'required','message' => '请至少选择一个子订单！'],
            [['adjust_price', 'adjust_price_reason'], 'trim'],
            [['adjust_price', 'adjust_price_reason'], 'required'],
            [['adjust_price'], 'match', 'pattern'=>'/^[+-]{0,1}[0-9]*\.?[0-9]{0,2}$/', 'message'=>'请输入正确的变动金额。'],
            ['order_id', 'validateOrderId'],
            ['adjust_price', 'validateAdjustPrice'],
            [['adjust_price_reason'], 'default', 'value' => ''],
            [['adjust_price_reason'], 'string', 'max' => 80],
        ];
    }

    public function validateOrderId()
    {
        $order_ids = explode(',',rtrim($this->order_id,','));
        $this->orders = Order::find()->where(['in','id',$order_ids])->all();

        if(count($order_ids) != count($this->orders))
        {
            $this->addError('order_id','多选中有无效的订单id存在');
        }
        else
        {
            foreach($this->orders as $order)
            {
                if(!$order->virtualOrder->isPendingPayment())
                {
                    $this->addError('order_id', '订单必须是未付款状态。');
                }
                if(empty($order->salesman_aid))
                {
                    $this->addError('order_id', '该订单无负责人不可修改价格。');
                }
                // 如果存在正在申请中的 或者 审核通过的 则不允许保存
                if($order->isAdjustStatusPending())
                {
                    $this->addError('order_id', '该订单存在待审核的价格修改申请，请审核通过之后再修改。');
                }

                $receipt = Receipt::find()->where(['virtual_order_id' => $order->virtual_order_id,'status' => Receipt::SEPARATE_MONEY_DISABLED])->limit(1)->one();
                if($receipt)
                {
                    $this->addError('order_id', '订单存在待审核回款，不能修改价格');
                }
            }
        }

    }

    public function validateAdjustPrice()
    {
        if($this->orders)
        {
            foreach($this->orders as $order)
            {
                $maxConfirmPaymentAmount = $order->virtualOrder->getPendingPayAmount();
                if($this->adjust_price == 0)
                {
                    $this->addError('adjust_price', '请输入变动金额');
                }
                if($maxConfirmPaymentAmount + $this->adjust_price <= 0)
                {
                    $this->addError('adjust_price', '变动金额超出范围');
                }
            }
        }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            $salesmanIds = [];
            $virtualOrderSn = '';
            foreach($this->orders as $order)
            {
                $model = null;
                if($order->isAdjustStatusReject()) // 如果存在审核不通过的则修改不通过的数据
                {
                    /** @var AdjustOrderPrice $model */
                    $model = AdjustOrderPrice::findOne(['order_id' => $order->id]);
                }
                else
                {
                    $model = new AdjustOrderPrice();
                }
                $model->order_id = $order->id;
                $model->virtual_order_id = $order->virtual_order_id;
                $model->status = AdjustOrderPrice::STATUS_PENDING;
                $model->creator_id = $administrator->id;
                $model->creator_name = $administrator->name;
                $model->created_at = time();
                $model->adjust_price = BC::add($this->adjust_price, 0);
                $model->adjust_price_reason = $this->adjust_price_reason;
                $order->adjust_status = AdjustOrderPrice::STATUS_PENDING;
                $model->save(false);
                $order->save(false);
                OrderRecord::create($order->id, '申请金额变动', '订单金额：'.$order->price.'元，变动金额：'.
                    ($model->adjust_price > 0 ? '+' : '').$model->adjust_price.'元，应付金额：'.
                    (BC::add($order->price,$model->adjust_price)).'元，修改说明：'.$model->adjust_price_reason, $administrator, 0, 1);
                //新增后台操作日志
                AdministratorLog::logAdjustOrderPrice($order);
                $salesmanIds[] = $order->salesman_aid;//获取订单业务员
                $virtualOrderSn = $order->virtualOrder->sn;
            }

            if(!empty(array_unique($salesmanIds)))
            {
                foreach (array_unique($salesmanIds) as $salesmanId)
                {
                    /** @var Administrator $salesman */
                    $salesman = Administrator::findOne($salesmanId);
                    if($salesman)
                    {
                        //修改价格-提交审核
                        try {
                            //订单业务员所在部门主管电话
                            $phone = $salesman->department ? ($salesman->department->leader ? $salesman->department->leader->phone : null) : null;
                            // 发送短信（加入短信队列）
                            /** @var Queue $queue */
                            $queue = \Yii::$app->get('queue', false);
                            if($queue && $phone)
                            {
                                // 业务员：{1}，订单号：{2}
                                $queue->pushOn(new SendSmsJob(),[
                                    'phone' => $phone,
                                    'sms_id' => '258411',//订单价格变动审核 模板id：258411
                                    'data' => [$salesman->name, $virtualOrderSn]
                                ], 'sms');
                            }
                        }catch (\Exception $e){
                            Yii::getLogger()->log($e, Logger::LEVEL_INFO);
                        }
                    }
                }
            }
            $t->commit();
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
        return true;
    }

    public function attributeLabels()
    {
        return [
            'origin_price' => '原订单金额总价',
            'adjust_price' => '固定变动金额',
            'price' => '变动后订单金额总价',
            'adjust_price_reason' => '修改说明',
        ];
    }
}