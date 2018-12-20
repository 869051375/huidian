<?php
namespace backend\models;

use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\CustomerService;
use common\models\MessageRemind;
use common\models\Order;
use common\models\OrderRecord;
use Yii;
use yii\base\Exception;
use yii\base\Model;

class ChangeOrderCustomerServiceForm extends Model
{
    public $order_id;
    public $customer_service_id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var CustomerService
     */
    public $customerService;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['customer_service_id', 'order_id'], 'required'],
            ['order_id', 'validateOrderId'],
            ['customer_service_id', 'validateCustomerServiceId'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id', '订单不存在。');
            return ;
        }
        if($this->order->isBreakService() || $this->order->isCompleteService())
        {
            $this->addError('order_id', '当前订单状态不能进行该操作。');
        }
    }

    public function validateCustomerServiceId()
    {
        $this->customerService = CustomerService::findOne($this->customer_service_id);
        if(null == $this->customerService)
        {
            $this->addError('customer_service_id', '客服不存在。');
            return ;
        }
        if(!$this->customerService->isActive())
        {
            $this->addError('customer_service_id', '该客服未开通服务。');
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'customer_service_id' => '客服',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $oldCustomerServiceId = 0;
        if($this->customer_service_id)
        {
            $customerServiceModel = CustomerService::findOne($this->customer_service_id);

            if(null != $customerServiceModel)
            {
                $department_name = isset($customerServiceModel->administrator->department->name) ? $customerServiceModel->administrator->department->name : '--部门';
                $oldCustomerServiceId = $customerServiceModel ? $customerServiceModel->administrator->id : 0;
                OrderRecord::create($this->order->id, '修改客服', "修改客服人员为：{$department_name}的{$customerServiceModel->name}；", Yii::$app->user->identity, 0, 1);
            }
        }

        $t = \Yii::$app->db->beginTransaction();
        try{
            $this->order->assignCustomerService($this->customerService);
            $t->commit();
            //新增后台操作日志
            AdministratorLog::logChangeCustomerService($this->order);

            //生成消息提醒
            $order_id = $this->order->id;
            $type = MessageRemind::TYPE_EMAILS;
            $type_url = MessageRemind::TYPE_URL_ORDER_DETAIL;
            $receive_id = $this->customerService->administrator_id;
            $email = $this->customerService->administrator->email;
            $sign = 'i-'.$oldCustomerServiceId.'-'.$receive_id.'-'.$order_id.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind && $oldCustomerServiceId != $receive_id)
            {
                $this->messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email);
            }

            return true;
        }
        catch (Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    //消息提醒
    private function messageRemind($sign, $order_id, $type, $type_url, $receive_id, $email)
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $message = '订单新分配提醒-订单号：'. $this->order->sn .','. $this->order->product_name. $this->order->province_name.'-'.$this->order->city_name.'-'.$this->order->district_name;
        $popup_message = '您有一条新订单（'. $this->order->sn .'）需处理，请查看！';
        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, 0, $order_id, 0, $administrator, $email);
    }
}