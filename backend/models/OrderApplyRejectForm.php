<?php
namespace backend\models;

use common\models\Order;
use common\models\OrderBalanceRecord;
use yii\base\Model;

/**
 * Class OrderApplyRejectForm
 * @package backend\models
 *
 */
class OrderApplyRejectForm extends Model
{
    public $remark;
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
            [['order_id','remark'], 'required'],
            [['order_id'], 'integer'],
            [['order_id'], 'validateOrderId'],
            [['remark'], 'string','max' => 50],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id', '订单不存在！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'remark' => '备注',
            'order_id' => '订单id',
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->order->is_apply = Order::APPLY_DISABLE;
        if($this->order->save(false))
        {
            OrderBalanceRecord::createRecord('订单计算业绩失败',OrderBalanceRecord::STATUS_REJECT,$this->order->id,'订单申请业绩提成被驳回，驳回理由是：'.$this->remark);
            return true;
        }
        return false;
    }
}
