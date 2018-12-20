<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use yii\base\Model;

class CustomerConfirmReceiveForm extends Model
{
    public $id;

    /**
     * @var CrmCustomer
     */
    public $customer;

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
        }
        else if($this->customer->is_receive)
        {
            $this->addError('id', '该客户已经被转入，不能进行该操作');
        }
        else if($this->customer->administrator_id != $administrator->id)
        {
            $this->addError('id', '您不能转入其他人的客户');
        }
    }

    /**
     * 确认转入客户
     * @return bool
     */
    public function confirm()
    {
        if(!$this->validate())
        {
            return false;
        }
        CrmCustomerCombine::addTeam($this->customer->administrator, $this->customer);
        $this->customer->is_receive = 1;
        return $this->customer->save(false);
    }
}