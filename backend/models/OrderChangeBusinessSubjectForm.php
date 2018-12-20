<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/15
 * Time: 上午10:47
 */

namespace backend\models;

use common\models\BusinessSubject;
use common\models\Order;
use yii\base\Model;


class OrderChangeBusinessSubjectForm extends Model
{
    public $order_id;
    public $business_subject_id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var BusinessSubject
     */
    public $businessSubject;

    public function rules()
    {
        return [
            [['order_id', 'business_subject_id'], 'required'],
            ['business_subject_id', 'validateBusinessSubjectId'],
            ['order_id', 'validateOrderId'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(empty($this->order))
        {
            $this->addError('order_id','找不到指定的订单');
        }
    }

    public function validateBusinessSubjectId()
    {
       $this->businessSubject = BusinessSubject::findOne($this->business_subject_id);
        if(empty($this->businessSubject))
        {
            $this->addError('business_subject_id','找不到指定的主体信息');
        }
    }

    public function change()
    {
        if(!$this->validate())
        {
            return false;
        }
        $this->order->business_subject_id = $this->businessSubject->id;
        if(empty($this->businessSubject->subject_type))
        {
            $this->order->company_name = $this->businessSubject->company_name;
        }
        else
        {
            $this->order->company_name = $this->businessSubject->region;
        }
        return $this->order->save(false);
    }

    public function attributeLabels()
    {
        return [
            'business_subject_id' => '业务主体'
        ];
    }
}