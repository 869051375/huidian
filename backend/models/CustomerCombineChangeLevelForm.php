<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use yii\base\Model;

class CustomerCombineChangeLevelForm extends Model
{
    public $customer_id;
    public $administrator_id;
    public $level;

    /**
     * @var CrmCustomerCombine
     */
    public $customerCombine;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['level', 'customer_id', 'administrator_id'], 'required'],
            [['level', 'customer_id', 'administrator_id'], 'integer'],
            ['customer_id', 'validateCustomerId'],
        ];
    }

    public function validateCustomerId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customerCombine = CrmCustomerCombine::find()->where(['customer_id' => $this->customer_id, 'administrator_id' => $this->administrator_id])->one();
        $this->customer = CrmCustomer::findOne($this->customer_id);
        if(null == $this->customerCombine || null == $this->customer)
        {
            $this->addError('customer_id', '客户不存在');
        }
        if(!$this->customer->isPrincipal($administrator) && !$this->customer->isCombine($administrator) && !$this->customer->isSubFor($administrator))
        {
            $this->addError('customer_id', '您没有修改该客户的权限');
        }
    }

    public function changeLevel()
    {
        if(!$this->validate())
        {
            return false;
        }
//        CrmCustomerLog::add('更换客户负责人为：'.$this->administrator->name, $this->customer_id);
        $this->customerCombine->level = $this->level;
        return $this->customerCombine->save(false);
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '合伙人',
            'customer_id' => '客户id',
            'level' => '客户级别',
        ];
    }
}