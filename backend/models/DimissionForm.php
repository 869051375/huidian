<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\Order;
use common\models\Receipt;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class DimissionForm extends Model
{
    public $company_id;
    public $department_id;
    public $administrator_id;      //离职人员id
    public $take_administrator_id; //接手人员id

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var Administrator
     */
    public $takeAdministrator;

    /**
     * @var Company
     */
    public $company;

    /**
     * @var CrmDepartment
     */
    public $department;

    public function rules()
    {
        return [
            [['administrator_id', 'company_id', 'department_id','take_administrator_id'], 'required'],
            [['administrator_id'], 'validateAdministratorId'],
            [['take_administrator_id'], 'validateTakeAdministratorId'],
            [['company_id'], 'validateCompanyId'],
            [['department_id'], 'validateDepartment'],
        ];
    }

    public function validateTakeAdministratorId()
    {
        $this->takeAdministrator = Administrator::findOne($this->take_administrator_id);
        if(null == $this->takeAdministrator)
        {
            $this->addError('take_administrator_id','接收人员账号不存在！');
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id','人员账号不存在！');
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
            'take_administrator_id' => '接收人员',
            'company_id' => '所属公司',
            'department_id' => '所属部门',
        ];
    }

    //更新客户的负责人及公司与部门字段
    public function updateCustomer()
    {
        /** @var CrmCustomer[] $customers */
        $customers = CrmCustomer::find()->where(['administrator_id' => $this->administrator->id])->all();
        foreach($customers as $customer)
        {
            if($customer->administrator_id != $this->takeAdministrator->id)
            {
                $customer->administrator_id = $this->takeAdministrator->id;
                $customer->company_id = $this->takeAdministrator->id;
                $customer->department_id = $this->takeAdministrator->department_id;
                $customer->save(false);
            }
        }
    }

    //更新客户关系表合作人字段及公司部门字段
    public function updateCustomerCombine()
    {
        /** @var CrmCustomerCombine[] $customerCombines */
        $customerCombines = CrmCustomerCombine::find()->where(['administrator_id' => $this->administrator->id])->all();
        foreach($customerCombines as $customerCombine)
        {
            /** @var CrmCustomerCombine $crmCombine */
            $crmCombine = CrmCustomerCombine::find()->where(['customer_id' => $customerCombine->customer_id,'administrator_id' => $this->takeAdministrator->id])->limit(1)->one();
            if($crmCombine)
            {
                $customerCombine->delete();
            }
            else
            {
                $customerCombine->administrator_id = $this->takeAdministrator->id;
                $customerCombine->company_id = $this->takeAdministrator->company_id;
                $customerCombine->department_id = $this->takeAdministrator->department_id;
                $customerCombine->save(false);
            }
        }
    }

    //更新商机表表公司部门字段
    public function updateOpportunity()
    {
        $status = [
            CrmOpportunity::STATUS_APPLY,
            CrmOpportunity::STATUS_DEAL,
            CrmOpportunity::STATUS_FAIL,
        ];
        /** @var CrmOpportunity[] $crmOpportunitys */
        $crmOpportunitys = CrmOpportunity::find()->where(['administrator_id' => $this->administrator->id])
            ->andWhere(['not in','status',$status])->all();
        foreach($crmOpportunitys as $crmOpportunity)
        {
            if($crmOpportunity->administrator_id != $this->takeAdministrator->id)
            {
                $crmOpportunity->administrator_id = $this->takeAdministrator->id;
                $crmOpportunity->administrator_name = $this->takeAdministrator->name;
                $crmOpportunity->company_id = $this->takeAdministrator->company_id;
                $crmOpportunity->department_id = $this->takeAdministrator->department_id;
                $crmOpportunity->save(false);
            }
        }
    }

    //更新（未付款订单）业务负责人
    public function updateOrder()
    {
        /** @var Order[] $orders */
        $orders1 = Order::find()->where(['salesman_aid' => $this->administrator->id])
//            ->andWhere(['in','virtual_order_id',$virtual_order_ids])
            ->andWhere(['status' => Order::STATUS_PENDING_PAY])
            ->all();

        $receipt_order_id =  ArrayHelper::getColumn($orders1, 'virtual_order_id');

        $ids = [];
        foreach($receipt_order_id as $key => $val){
            $receipt = Receipt::find()->where(['creator_id' => $this->administrator->id]) -> andWhere(['virtual_order_id' => $val]) -> andWhere(['in','status',[Receipt::STATUS_NO,Receipt::STATUS_YES]])->asArray()->all();

            if(empty($receipt)){
                $ids[] = $val;
            }

        }

        $orders = Order::find()->where(['salesman_aid' => $this->administrator->id])
            ->andWhere(['in','virtual_order_id',$ids])
            ->andWhere(['status' => Order::STATUS_PENDING_PAY])
            ->all();


        foreach($orders as $order)
        {
            if($order->salesman_aid != $this->takeAdministrator->id)
            {
                $order->salesman_aid = $this->takeAdministrator->id;
                $order->salesman_name = $this->takeAdministrator->name;
                $order->company_id = $this->takeAdministrator->company_id;
                $order->salesman_department_id = $this->takeAdministrator->department_id;
                $order->save(false);
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function takeOver()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();

        try
        {
            if ($this->administrator->type == Administrator::TYPE_SALESMAN ||
                $this->administrator->type == Administrator::TYPE_ADMIN)
            {
                $this->updateCustomer();
                $this->updateCustomerCombine();
                $this->updateOpportunity();
                $this->updateOrder();
                $this->administrator->status = Administrator::STATUS_DISABLED; //账号置为禁用状态
                $this->administrator->is_dimission = Administrator::DIMISSION_ACTIVE; //账号置为离职状态
                $this->administrator->save(false);
            }
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