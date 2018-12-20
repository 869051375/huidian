<?php
namespace backend\models;

use common\models\Order;
use common\models\OrderEvaluate;
use yii\base\Model;

/**
 * Class OrderEvaluateForm
 * @package backend\models
 */

class OrderEvaluateForm extends Model
{
    public $reply_content;
    public $order_id;
    public $evaluate_content;


    public function rules()
    {
        return [
            [['reply_content', 'order_id'], 'required'],
            ['order_id', 'validateOrderId'],
        ];
    }


    public function validateOrderId()
    {
        /** @var  OrderEvaluate $orderEvaluate */
        $orderEvaluate = OrderEvaluate::find()->where(['order_id'=>$this->order_id])->one();
        if(null == $orderEvaluate)
        {
            $this->addError('order_id', '找不到订单评价。');
        }
        else
        {
            if($orderEvaluate->is_reply)
            {
                $this->addError('order_id', '只能回复一次。');
            }

            if(!$orderEvaluate->is_audit)
            {
                $this->addError('order_id', '未审核评价无法回复。');
            }
        }

    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'evaluate_content' => '客户评价',
            'reply_content' => '回复评价',
        ];
    }

    /**
     * @param $orderEvaluate OrderEvaluate
     * @return bool
     */
    public function save($orderEvaluate)
    {
        $orderModel = Order::findOne($this->order_id);
        if(!$this->validate()) return false;
        $orderEvaluate->reply_time = time();
        $orderEvaluate->reply_content = $this->reply_content;
        $orderEvaluate->customer_service_id = $orderModel->customer_service_id;
        $orderEvaluate->customer_service_name = $orderModel->customer_service_name;
        $orderEvaluate->is_reply = OrderEvaluate::REPLY_ACTIVE;

        if($orderEvaluate->save(false))
        {
            return true;
        }
        return false;
    }

}