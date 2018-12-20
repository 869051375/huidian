<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use yii\base\Model;

class CustomerCombineDeleteForm extends Model
{
    public $customer_id;
    public $administrator_id;

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
            [['customer_id', 'administrator_id'], 'required'],
            [['customer_id', 'administrator_id'], 'integer'],
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
        if(!$this->customer->isPrincipal($administrator) && !$this->customer->isCombine($administrator))
        {
            $this->addError('customer_id', '您不是客户的负责人，没有删除该客户的权限');
        }
        if($this->customerCombine->hasOrder())
        {
            $this->addError('customer_id', '当前客户存在订单，无法删除');
        }
        if($this->customerCombine->hasOpportunity())
        {
            $this->addError('customer_id', '当前客户存在商机，无法删除');
        }
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '合伙人',
            'customer_id' => '客户id',
        ];
    }

    public function delete()
    {
        if(!$this->validate())
        {
            return false;
        }
        return $this->customerCombine->delete();
    }
}