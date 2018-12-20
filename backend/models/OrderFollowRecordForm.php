<?php
namespace backend\models;

use common\models\Order;
use common\models\OrderFollowRecord;
use common\models\Product;
use yii\base\Model;

class OrderFollowRecordForm extends Model
{
    public $is_follow = 1;
    public $follow_remark;
    public $next_follow_time;
    public $order_id;
    public $product_id;

    /**
     * @var Order
     */
    private $order;

    public function rules()
    {
        return [
            [['follow_remark'], 'trim'],
            [['follow_remark', 'order_id'], 'required'],
//            [['product_id'], 'required', 'on'=> 'product'],
            ['is_follow', 'boolean'],
            [['follow_remark'], 'string', 'max'=>80],
            [['next_follow_time'], 'validateNextFollowTime'],
            [['product_id'], 'validateProductId'],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateNextFollowTime()
    {
    }

    public function validateProductId()
    {
        if($this->is_follow == 1)
        {
            $product = Product::findOne($this->product_id);
            if(null == $product)
            {
                $this->addError('product_id', '找不到续费关联商品。');
            }else
            {
                if(!$product->isRenewal())
                {
                    $this->addError('product_id', '此商品不是续费关联商品。');
                }
            }
        }
    }

    /**
     * 校验订单下的状态是否为待付款状态
     */
    public function validateOrderId()
    {
        if(!$this->is_follow)
        {
            $this->next_follow_time = 0;
        }
        /** @var Order $order */
        $order = Order::findOne($this->order_id);
        if(null == $order)
        {
            $this->addError('order_id', '找不到订单信息。');
        }
        if($order->is_renewal != Order::RENEWAL_ACTIVE)
        {
            $this->addError('order_id', '该订单不是续费订单，无法进行该操作。');
        }
        //已经取消跟进订单不可再次跟进
        if(!empty($order->lastOrderFollowRecord))
        {
            if($order->lastOrderFollowRecord['is_follow'] != OrderFollowRecord::FOLLOW_ACTIVE)
            {
                $this->addError('order_id', '该订单已取消跟进，无法进行该操作。');
            }
        }

        if($order->renewal_order_id > 0)
        {
            $this->addError('order_id', '已续费订单，无法再跟进。');
        }
        $this->order = $order;
    }

    public function attributeLabels()
    {
        return [
            'next_follow_time' => '下次跟进时间',
            'is_follow' => '继续跟进',
            'follow_remark' => '跟进备注',
            'product_id' => '意向商品',
        ];
    }

    public function attributeHints()
    {
        return [
            'is_follow' => '(选中代表客户有意向，继续跟进订单，不选中代表客户无意向，订单进入无意向列表)',
        ];
    }

    /**
     * @return null|OrderFollowRecord
     */
    public function save()
    {
        if(!$this->validate())
        {
            return null;
        }

        if($this->is_follow == 1)
        {
            $this->order->next_follow_time = strtotime($this->next_follow_time);
            $this->order->save(false, ['next_follow_time']);
            //新增后台操作日志
            //AdministratorLog::logFollowRecord($this->order);
        }
        else
        {
            $this->order->renewal_status = Order::RENEWAL_STATUS_NO;
            $this->order->save(false);
            //新增后台操作日志
            //AdministratorLog::logCancelVirtualOrder($this->order->virtualOrder);
        }
        $orderFollowRecordModel = new OrderFollowRecord();
        $orderFollowRecordModel->order_id = $this->order_id;
        $orderFollowRecordModel->is_follow = $this->is_follow;
        $orderFollowRecordModel->next_follow_time = strtotime($this->next_follow_time);
        $orderFollowRecordModel->follow_remark = $this->follow_remark;
        if($orderFollowRecordModel->save(false)) return $orderFollowRecordModel;
        return null;
    }
}