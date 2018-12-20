<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderRemark;
use Yii;
use yii\base\Model;

class OrderRemarkForm extends Model
{
    public $order_id;
    public $remark;

    /**
     * @var Order
     */
    public $order;

    public function rules()
    {
        return [
            [['remark'], 'trim'],
            [['remark'], 'required'],
            [['remark'], 'string', 'max' => 200],
            [['order_id'], 'required', 'message' => '订单号错误'],
            [['order_id'], 'validateOrderId'],
        ];
    }

    public function validateOrderId()
    {
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id', '该订单不存在或不是您服务的订单。');
        }
        if(!$this->order->isBelongs($admin))
        {
            $this->addError('order_id', '该订单不是您服务的订单。');
        }
    }


    public function save()
    {
        $orderRemark = new OrderRemark();
        if(!$this->validate()) return null;
        $orderRemark->remark = $this->remark;
        $orderRemark->order_id = $this->order_id;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $orderRemark->creator_id = $admin->id;
        $orderRemark->creator_name = $admin->name;
        $orderRemark->created_at = time();
        if($orderRemark->save(false))
        {
            //新增订单记录
            OrderRecord::create($orderRemark->order_id, '添加备注', $orderRemark->remark, $admin, 0, OrderRecord::INTERNAL_ACTIVE);
            return $orderRemark;
        }
        return null;
    }

    public function attributeLabels()
    {
        return [
            'remark' => '请输入备注内容',
        ];
    }
}