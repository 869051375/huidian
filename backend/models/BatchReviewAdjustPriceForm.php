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
use yii\db\Exception;

class BatchReviewAdjustPriceForm extends Model
{
    public $order_id;
    public $status;

    /**
     * @var Order[]
     */
    public $orders;

    public function rules()
    {
        return [
            [['order_id', 'status'], 'trim'],
            ['order_id', 'required','message' => '请至少选择一个子订单！'],
            ['status', 'required'],
            ['status', 'in', 'range' => [AdjustOrderPrice::STATUS_PASS, AdjustOrderPrice::STATUS_REJECT]],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateOrderId()
    {
        $order_ids = explode(',',rtrim($this->order_id,','));
        $this->orders = Order::find()->where(['in','id',$order_ids])->all();
        if(count($order_ids) != count($this->orders))
        {
            $this->addError('order_id','多选中有无效的订单存在');
        }
        else
        {
            foreach($this->orders as $order)
            {
                if(!$order->virtualOrder->isPendingPayment())
                {
                    $this->addError('order_id', '订单必须是未付款状态。');
                }
                // 如果 审核通过的 则不允许再次审核
                if($order->isAdjustStatusPass() || $order->isAdjustStatusNotAdjust())
                {
                    $this->addError('order_id', '该状态不能进行操作');
                }
            }
        }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try{
            foreach($this->orders as $order)
            {
                $adjust = AdjustOrderPrice::findOne(['order_id' => $order->id, 'status' => AdjustOrderPrice::STATUS_PENDING]);
                if(null == $adjust)
                {
                    $this->addError('order_id', '操作失败');
                }
                $review = new ReviewAdjustPriceForm();
                $review->order_id = $order->id;
                $review->status = $this->status;
                //$review->status_reason = '';
                $review->adjust_price = $adjust->adjust_price;
                $review->adjust_price_reason = $adjust->adjust_price_reason;
                $review->origin_price = $order->original_price;
                //$review->price = ;
                if(!$review->save()){
                    $this->addError('order_id', '操作失败');
                }
            }
            $t->commit();
            return true;
        }
        catch (Exception $e)
        {
            $t->rollBack();
            return $this->hasErrors();
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => 'Order Id',
            'status' => '状态',
        ];
    }
}