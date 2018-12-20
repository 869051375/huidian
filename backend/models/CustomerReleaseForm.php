<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use common\models\CustomerPublic;
use yii\base\Model;

class CustomerReleaseForm extends Model
{
    public $id;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var CustomerPublic
     */
    public $customerPublic;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['id'], 'required'],
            ['id', 'validateId'],
        ];
    }

    public function validateId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer = CrmCustomer::findOne($this->id);
        if(null == $this->customer)
        {
            $this->addError('id', '客户不存在');
            return ;
        }
        else
        {
            $this->customerPublic = CustomerPublic::find()->where(['company_id' => $this->customer->company_id])->one();
            if(null == $this->customerPublic)
            {
                $this->addError('id', '当前没有客户公海，不允许不存在');
            }
            else
            {
                if($this->customer->isPublic())
                {
                    $this->addError('id', '该客户已经被释放，不能进行该操作');
                }
                else if($this->customer->administrator_id != $administrator->id)
                {
                    $this->addError('id', '您不能释放其他人的客户');
                }
                else if(!$this->customer->canRelease())
                {
                    $this->addError('id', '释放限制时间未到，您不能执行此操作');
                }
            }
        }
    }

    /**
     * 确认释放客户
     * @return bool
     */
    public function confirm()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!$this->validate())
        {
            return false;
        }
//        CrmCustomerCombine::addTeam($this->customer->administrator, $this->customer);
        $this->customer->customer_public_id = $this->customerPublic->id;
        $this->customer->administrator_id = 0;
        $this->customer->is_receive = CrmCustomer::RECEIVE_DISABLED;
        $this->customer->level = CrmCustomer::CUSTOMER_LEVEL_ACTIVE;
        $this->customer->is_protect = CrmCustomer::PROTECT_DISABLED;
        $this->customer->move_public_time = time();
        CrmCustomerLog::add('客户移入"'. $this->customerPublic->name .'"客户公海', $this->customer->id, 0,$administrator,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        return $this->customer->save(false) && $this->deleteCombine($this->customer);
    }

    /**
     * @param CrmCustomer $customer
     * @return bool
     */
    private function deleteCombine($customer)
    {
        if(null != $customer->crmCustomerCombine)
        {
            foreach ($customer->crmCustomerCombine as $crmCustomerCombine)
            {
                if(!$crmCustomerCombine->delete()) return false;
            }
        }
        return true;
    }
}