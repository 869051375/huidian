<?php
namespace backend\models;

use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\FollowRecord;
use common\models\Order;
use common\models\OrderRecord;
use common\models\VirtualOrder;
use Yii;
use yii\base\Model;

class FollowRecordForm extends Model
{
    public $is_follow = 1;
    public $follow_remark;
    public $next_follow_time;
    public $order_id;
    public $virtual_order_id;

    /**
     * @var VirtualOrder
     */
    private $vo;

    /**
     * @var Order
     */
    private $order;

    public function rules()
    {
        return [
            [['follow_remark'], 'trim'],
            [['follow_remark', 'virtual_order_id','order_id'], 'required'],
            ['is_follow', 'boolean'],
            [['follow_remark'], 'string', 'max'=>80],
            [['next_follow_time'], 'validateNextFollowTime'],
            ['virtual_order_id', 'validateVirtualOrderId'],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateNextFollowTime()
    {
    }

    /**
     * 校验次订单下的状态是否为待付款状态
     */
    public function validateVirtualOrderId()
    {
        if(!$this->is_follow)
        {
            $this->next_follow_time = 0;
        }
        /** @var VirtualOrder $vo */
        $vo = VirtualOrder::findOne($this->virtual_order_id);
        if(null == $vo)
        {
            $this->addError('virtual_order_id', '找不到订单信息。');
        }
        if(!($vo->status == VirtualOrder::STATUS_UNPAID || $vo->status == VirtualOrder::STATUS_PENDING_PAYMENT))
        {
            $this->addError('virtual_order_id', '该订单不是待付款订单，无法进行该操作。');
        }
        $this->vo = $vo;
    }

    /**
     * 校验子订单的状态
     */
    public function validateOrderId()
    {
        if(!$this->is_follow)
        {
            /** @var Order $order */
            $this->order = Order::findOne($this->order_id);
            if(null == $this->order)
            {
                $this->addError('order_id', '找不到订单信息。');
            }
            if(floatval($this->vo->payment_amount) && floatval($this->order->payment_amount == 0))
            {
                $this->addError('order_id','对不起，当前虚拟订单下有回款未分配，请前往处理。');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'next_follow_time' => '下次跟进时间',
            'is_follow' => '继续跟进',
            'follow_remark' => '跟进备注',
        ];
    }

    public function attributeHints()
    {
        return [
            'is_follow' => '(选中代表继续跟进订单，不选中代表不再跟进，取消订单)',
        ];
    }

    /**
     * @return null|FollowRecord
     */
    public function save()
    {
        if(!$this->validate())
        {
            return null;
        }
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        //如果不跟进，则必须订单是未支付的状态
        if($this->is_follow == 1){
            foreach ($this->vo->orders as $order)
            {
                $order->next_follow_time = strtotime($this->next_follow_time);
                $order->save(false, ['next_follow_time']);
                //新增后台操作日志
                AdministratorLog::logFollowRecord($order);
            }
        }
        else
        {
            $not_follow = '';
            foreach ($this->vo->receipt as $k=>$v){
                if ($v->status == 0){
                    $not_follow = 1;
                }
            }
            if ($not_follow == 1){
                $this->addError('order_id','此订单有待审核的回款，不能取消');
                return null;
            }

            $this->vo->cancel(Order::BREAK_REASON_NOT_FOLLOW); // 取消
            $this->vo->refund(); // 退款
            //新增后台操作日志
            foreach($this->vo->orders as $order)
            {
                OrderRecord::create($order->id, '订单已取消', '跟进记录取消',$administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
            }
            AdministratorLog::logCancelVirtualOrder($this->vo);
        }
        $followRecordModel = new FollowRecord();
        $followRecordModel->virtual_order_id = $this->virtual_order_id;
        $followRecordModel->is_follow = $this->is_follow;
        $followRecordModel->next_follow_time = strtotime($this->next_follow_time);
        $followRecordModel->follow_remark = $this->follow_remark;
        if($followRecordModel->save(false)) return $followRecordModel;
        return null;
    }
}