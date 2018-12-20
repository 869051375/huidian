<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\Contract;
use common\models\ContractRecord;
use Yii;
use yii\base\Model;

/**
 * Class ContractChangeAdministratorForm
 * @package backend\models
 *
 * @property Company $company
 * @property Administrator $administrator
 */
class ContractChangeAdministratorForm extends Model
{
    public $contract_id;
    public $administrator_id;
    public $department_id;
    public $company_id;
    /**
     * @var Contract
     */
    public $contract;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['administrator_id'], 'required'],
            [['contract_id'], 'required','message' => '请选择业务员'],
            [['contract_id', 'administrator_id', 'department_id', 'company_id'], 'integer'],
            ['contract_id', 'validateContractId'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateContractId()
    {
        $this->contract = Contract::findOne($this->contract_id);
        if(null == $this->contract)
        {
            $this->addError('contract_id', '客户不存在');
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

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        ContractRecord::createRecord($this->contract->id,$administrator->name.'更换了合同负责人：'.$this->administrator->name,$administrator);
        $this->contract->administrator_id = $this->administrator->id;
        $this->contract->company_id = $this->administrator->company_id;
        $this->contract->department_id = $this->administrator->department_id;
        return $this->contract->save(false);
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '负责人',
            'contract_id' => '合同id',
            'department_id' => '所属部门',
            'company_id' => '所属公司'
        ];
    }

}