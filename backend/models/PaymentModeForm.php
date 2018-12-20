<?php
namespace backend\models;

use common\models\Document;
use common\models\Order;
use common\models\OrderRecord;
use yii\base\Model;
/**
 * Class PaymentModeForm
 * @package backend\models
 *
 */
class PaymentModeForm extends Model
{
    public $order_id;
    public $is_installment = 0;
    public $content;
    public $status;

    /** @var Order[] */
    public  $orders;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_installment','content'], 'required'],
            [['order_id'], 'required','message' => '请至少选择一个子订单！'],
            ['is_installment', 'in','range' => ['0','1']],
            ['status', 'in','range' => ['0','1']],
            ['content', 'string','max' => '50'],
            ['order_id', 'validateOrderId'],
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
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'is_installment' => '选择付款方式',
            'content' => '修改原因',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $admin = \Yii::$app->user->identity;
        foreach ($this->orders as $order)
        {
            $old = $order->is_installment ? '分期付款' : '一次付款';
            $now = $this->is_installment ? '分期付款' : '一次付款';
            $content = $old.'"修改为"'.$now.',备注:'.$this->content;
            $order->is_installment = $this->is_installment;
            if($this->status)
            {
                $order->is_pay_after_service = $order->is_installment ? Order::PAY_AFTER_SERVICE_ACTIVE : Order::PAY_AFTER_SERVICE_DISABLED;
            }
            if($order->save(false))
            {
                OrderRecord::create($order->id, '修改付款方式', $content,$admin, 0, OrderRecord::INTERNAL_ACTIVE,0);
            }
        }
        return true;
    }
}
