<?php
namespace backend\models;

use common\models\DailyStatistics;
use common\models\Order;
use yii\base\Model;

/**
 * Class OrderRenewalForm
 * @package backend\models
 *
 */
class OrderRenewalForm extends Model
{
    public $renewal_order_id;
    public $order_id;

    /**
     * @var Order
     */
    public $order;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'renewal_order_id'], 'required'],
            ['renewal_order_id', 'validateRenewalOrderId'],
            ['renewal_order_id', 'validateId'],
        ];
    }

    public function validateRenewalOrderId()
    {
        /** @var Order $data */
        $data = Order::find()
            ->andWhere(['id' => $this->order_id])
            ->andWhere(['renewal_order_id' => $this->renewal_order_id])
            ->one();
        if(!empty($data) && $data->isAlreadyRenewal())
        {
            $this->addError('renewal_order_id', '您已经关联过此订单了，无法再次关联!');
        }
        if($this->renewal_order_id == $this->order_id)
        {
            $this->addError('renewal_order_id', '不能关联自己!');
        }
    }

    public function validateId()
    {
        //判断订单是否被使用过（关联其他订单）
        if($this->renewal_order_id > 0)
        {
            $data = Order::find()->andWhere(['renewal_order_id' => $this->renewal_order_id])->one();
        }
        else
        {
            $this->addError('renewal_order_id', '请选择有效的关联订单!');
        }
        if(!empty($data))
        {
            $this->addError('renewal_order_id', '此订单关联过其他订单，无法再次关联!');
        }
        //判断订单是否有其他订单关联（被其他订单关联）
        $order = Order::findOne($this->order_id);
        if(null != $order)
        {
            $this->order = $order;
            if($this->order->renewal_order_id > 0 && $order->isAlreadyRenewal())
            {
                $this->addError('renewal_order_id', '此订单已经被关联过了，无法再次被关联!');
            }
        }
        else
        {
            $this->addError('renewal_order_id', '您的操作有误!');
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'renewal_order_id' => '续费订单',
        ];
    }

    /**
     * @return Order
     */
    public function save()
    {
        if(!$this->validate()) return null;
        $this->order->renewal_order_id = $this->renewal_order_id;
        $this->order->renewal_status = Order::RENEWAL_STATUS_ALREADY;
        if($this->order->save())
        {
            $dailyStatistics = new DailyStatistics();
            $dailyStatistics->total('renewal_order_no');//统计续费订单数量
            $renewalOrder = Order::findOne($this->renewal_order_id);
            $renewalOrder->original_order_id = $this->order->id;
            $renewalOrder->save();
            return $this->order;
        }
        return null;
    }
}
