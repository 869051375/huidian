<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderVoucher;
use Yii;
use yii\base\Model;

class OrderReceiveForm extends Model
{
//    public $customer_name;
    public $phone;
    public $order_id; //领取人
    public $order_voucher;    //订单领取凭证

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Administrator
     */
    public $administrator;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            ['order_voucher', 'filter', 'filter' => 'trim'],
            [['order_id', 'phone','order_voucher'], 'required'],
            [['phone'],'match','pattern'=>'/^1\d{10}$/','message'=>'手机号为11位数字'],
            [['order_voucher'], 'string'],
            [['order_id'], 'validateOrderId'],
//            [['customer_name'], 'validateCustomer'],
            [['phone'], 'validatePhone'],
        ];
    }

    public function validateOrderId()
    {
        $this->administrator = Yii::$app->user->identity;
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id','订单不存在！');
        }
        if($this->administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('order_id','订单认领人员必须是业务员！');
        }

    }

//    public function validateCustomer()
//    {
//        if($this->order->user->name != $this->customer_name)
//        {
//            $this->addError('customer_name','请完整精确输入当前订单的客户昵称！');
//        }
//    }

    public function validatePhone()
    {
        if($this->order->user->phone != $this->phone)
        {
            $this->addError('phone','请完整精确输入当前订单的关联客户下单手机号！');
        }
    }

    public function attributeLabels()
    {
        return [
            'customer_name' => '客户昵称校验',
            'phone' => '客户手机号校验',
            'order_voucher' => '认领凭证',
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function receive()
    {
        if(!$this->validate()) return false;
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            $this->order->company_id = $this->administrator->company_id;
            $this->order->salesman_department_id = $this->administrator->department_id;
            $this->order->salesman_aid = $this->administrator->id;
            $this->order->salesman_name = $this->administrator->name;
            $this->order->save(false);
            $this->order->autoAssignCustomerService($this->administrator->company_id);//自动分配该公司下的客服
            OrderVoucher::createVoucher($this->order->id,$this->order_voucher);
            OrderRecord::create($this->order->id, '订单认领', $this->administrator->name.'成功认领订单！',$this->administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}