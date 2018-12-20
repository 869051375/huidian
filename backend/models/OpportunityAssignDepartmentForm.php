<?php
namespace backend\models;

use common\models\Company;
use common\models\CrmDepartment;
use common\models\NichePublicDepartment;
use common\models\OpportunityAssignDepartment;
use yii\base\Model;

/**
 * Class OpportunityAssignDepartmentForm
 * @package backend\models
 *
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 */
class OpportunityAssignDepartmentForm extends Model
{
    public $company_id;
    public $department_id;
    public $product_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'company_id', 'department_id'], 'required'],
            [['product_id', 'company_id', 'department_id'], 'integer'],
            ['company_id', 'validateProductId'],
            ['department_id', 'validateDepartmentId'],
        ];
    }

    public function validateProductId()
    {
        $data = OpportunityAssignDepartment::find()
            ->andWhere(['=','company_id',$this->company_id])
            ->andWhere(['=','product_id',$this->product_id])
            ->one();
        if(!empty($data))
        {
            $this->addError('company_id', '一个商品只能分配一个公司下的一个部门，请检查后录入！');
        }
        if($this->company_id <= 0)
        {
            $this->addError('company_id', '关联公司能为空！');
        }
        if($this->department_id <= 0)
        {
            $this->addError('company_id', '关联部门能为空！');
        }
    }

    public function validateDepartmentId()
    {
        $department = NichePublicDepartment::find()->where(['department_id'=>$this->department_id])->one();
        if (empty($department))
        {
            $this->addError('department_id', '当前所选部门无商机公海，请重新选择！');
        }
        
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => '关联公司',
            'department_id' => '关联部门',
        ];
    }

    /**
     * @return OpportunityAssignDepartment
     */
    public function save()
    {
        $model= new OpportunityAssignDepartment();
        $model->load($this->attributes,'');
        return $model->save(false) ? $model : null;
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getCompanyDepartment()
    {
        return CrmDepartment::find()
            ->where(['id' => $this->department_id, 'company_id' => $this->company_id , 'status' => CrmDepartment::STATUS_ACTIVE])->one();
    }
}