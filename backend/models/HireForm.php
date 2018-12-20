<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use Yii;
use yii\base\Model;

class HireForm extends Model
{
    public $company_id;
    public $department_id;
    public $administrator_id;

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
            'company_id' => '所属公司',
            'department_id' => '所属部门',
        ];
    }

    //更新商机表表公司部门字段
    public function updateOpportunity()
    {
        /** @var CrmOpportunity[] $crmOpportunitys */
        $crmOpportunitys = CrmOpportunity::find()->where(['administrator_id' => $this->administrator->id])->all();
        foreach($crmOpportunitys as $crmOpportunity)
        {
            if($crmOpportunity->administrator_id != $this->administrator->id)
            {
                $crmOpportunity->company_id = $this->company_id;
                $crmOpportunity->department_id = $this->department_id;
                $crmOpportunity->save(false);
            }
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hire()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            if ($this->administrator->type == Administrator::TYPE_SALESMAN)
            {
                $this->updateOpportunity();
            }
            $this->administrator->company_id = $this->company_id;
            $this->administrator->department_id = $this->department_id;
            $this->administrator->is_dimission = Administrator::DIMISSION_DISABLED;
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