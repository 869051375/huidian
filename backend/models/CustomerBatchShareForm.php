<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use yii\base\Model;

/**
 * Class CustomerBatchShareForm
 * @package backend\models
 *
 * @property Company $company
 * @property Administrator $administrator
 */
class CustomerBatchShareForm extends Model
{
    public $customer_ids;
    public $administrator_id;
    public $company_id;

    /**
     * @var CrmCustomer[]
     */
    public $customers = [];

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['administrator_id', 'company_id'], 'integer'],
            [['administrator_id'], 'required'],
            ['customer_ids', 'each', 'rule' => ['integer']],
            ['customer_ids', 'validateCustomerIds'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateCustomerIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        $this->customers = CrmCustomer::find()->where(['in', 'id', $this->customer_ids])->all();

        if(empty($this->customers))
        {
            $this->addError('customer_ids', '请选择客户');
        }

        foreach($this->customers as $customer)
        {
            if($customer->administrator_id != $administrator->id && !$customer->isSubFor($administrator))
            {
                $this->addError('customer_ids', '您没有分享该客户的权限');
            }
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id', '人员信息不存在');
        }
        else if($this->administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('administrator_id', '该账号非业务人员');
        }
    }

    public function batchShare()
    {
        if(!$this->validate())
        {
            return false;
        }
        foreach($this->customers as $customer)
        {
            CrmCustomerCombine::addTeam($this->administrator, $customer);

            /** @var Administrator $user */
            $user = \Yii::$app->user->identity;
            $share = $user->department ? $user->department->name : '';
            $receiveShare = $this->administrator->department ? $this->administrator->department->name : '';
            $remark = '由'.$share.'的'. $user->name.'添加'.$receiveShare.'的'. $this->administrator->name.'为本客户合伙人';
            CrmCustomerLog::add($remark, $customer->id,false,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        }
        return true;
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '合伙人',
            'company_id' => '所属公司'
        ];
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()->where(['id' => $this->administrator_id])->one();
    }
}