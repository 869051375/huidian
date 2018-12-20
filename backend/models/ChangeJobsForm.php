<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\Order;
use Yii;
use yii\base\Model;

class ChangeJobsForm extends Model
{
    public $administrator_id;
    public $company_id;
    public $department_id;
    public $title;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var Company
     */
    public $company;

    /**
     * @var CrmDepartment
     */
    public $department;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['administrator_id', 'company_id', 'department_id'], 'required'],
            [['title'], 'string', 'max' => 6],
            [['administrator_id'], 'validateAdministratorId'],
            [['company_id'], 'validateCompanyId'],
            [['department_id'], 'validateDepartment'],
        ];
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id','账号不存在！');
        }
    }

    public function validateCompanyId()
    {
        $this->company = Company::findOne($this->company_id);
        if(null == $this->company)
        {
            $this->addError('company_id','公司不存在！');
        }
    }

    public function validateDepartment()
    {
        $this->department = CrmDepartment::findOne($this->department_id);
        if(null == $this->department)
        {
            $this->addError('department_id','部门不存在！');
        }
    }

    public function attributeLabels()
    {
        return [
            'company_id' => '所属公司',
            'department_id' => '所属部门',
            'title' => '职位',
        ];
    }

    //更新客户的公司与部门字段
    public function updateCustomer()
    {
        /** @var CrmCustomer[] $customers */
        $customers = CrmCustomer::find()->where(['administrator_id' => $this->administrator->id])->all();
        foreach($customers as $customer)
        {
            if($customer->department_id != $this->department_id)
            {
                $customer->company_id = $this->company_id;
                $customer->department_id = $this->department_id;
                $customer->save(false);
            }
        }
    }

    //更新客户关系表公司部门字段
    public function updateCustomerCombine()
    {
        /** @var CrmCustomerCombine[] $customerCombines */
        $customerCombines = CrmCustomerCombine::find()->where(['administrator_id' => $this->administrator->id])->all();
        foreach($customerCombines as $customerCombine)
        {
            if($customerCombine->department_id != $this->department_id)
            {
                $customerCombine->company_id = $this->company_id;
                $customerCombine->department_id = $this->department_id;
                $customerCombine->save(false);
            }
        }
    }

    //更新商机表表公司部门字段
    public function updateOpportunity()
    {
        /** @var CrmOpportunity[] $crmOpportunitys */
        $crmOpportunitys = CrmOpportunity::find()->where(['administrator_id' => $this->administrator->id])->all();
        foreach($crmOpportunitys as $crmOpportunity)
        {
            if($crmOpportunity->department_id != $this->department_id)
            {
                $crmOpportunity->company_id = $this->company_id;
                $crmOpportunity->department_id = $this->department_id;
                $crmOpportunity->save(false);
            }
        }
    }

    //客服类型的调岗
    public function updateOrderCustomerService()
    {
        /** @var Order[] $orders */
        $orders = Order::find()->where(['customer_service_id' => $this->administrator->id])->all();
        foreach($orders as $order)
        {
            $order->customer_service_department_id = $this->department_id;
            $order->save(false);
        }
    }

    //督导类型的调岗
    public function updateOrderSupervisor()
    {
        /** @var Order[] $orders */
        $orders = Order::find()->where(['supervisor_id' => $this->administrator->id])->all();
        foreach($orders as $order)
        {
            $order->supervisor_department_id = $this->department_id;
            $order->save(false);
        }
    }

    //服务人员类型的调岗
    public function updateOrderClerk()
    {
        /** @var Order[] $orders */
        $orders = Order::find()->where(['clerk_id' => $this->administrator->id])->all();
        foreach($orders as $order)
        {
            $order->clerk_department_id = $this->department_id;
            $order->save(false);
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function changeJobs()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            if($this->administrator->type == Administrator::TYPE_CUSTOMER_SERVICE)
            {
                $this->updateOrderCustomerService();
            }elseif ($this->administrator->type == Administrator::TYPE_SUPERVISOR)
            {
                $this->updateOrderSupervisor();
            }elseif ($this->administrator->type == Administrator::TYPE_CLERK)
            {
                $this->updateOrderClerk();
            }elseif ($this->administrator->type == Administrator::TYPE_SALESMAN
                || $this->administrator->type == Administrator::TYPE_ADMIN)
            {
                $this->updateCustomer();
                $this->updateCustomerCombine();
                $this->updateOpportunity();
            }
            if($this->administrator->department && $this->administrator->department->assign_administrator_id == $this->administrator->id)
            {
                $this->administrator->department->assign_administrator_id = 0;
                $this->administrator->department->save(false);
            }
            if($this->administrator->department && $this->administrator->department->leader_id == $this->administrator->id)
            {
                $this->administrator->department->leader_id = 0;
                $this->administrator->department->save(false);
            }
            if($this->administrator->isDepartmentManager())
            {
                $this->administrator->is_department_manager = Administrator::DEPARTMENT_MANAGER_DISABLED;
            }
            $this->administrator->company_id = $this->company->id;
            $this->administrator->department_id = $this->department->id;
            if($this->title)
            {
                $this->administrator->title = $this->title;
            }
            $this->administrator->save(false);
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