<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerApi;
use common\models\CrmDepartment;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerPublic;
use Symfony\Component\Yaml\Tests\A;
use yii\base\Model;

class CustomerReleaseNewForm extends Model
{
    public $customer_id;
    public $abandon_reason;
    public $id;
    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var CustomerPublic
     */
    public $customerPublic;

    /**
     * @var BusinessSubject
     */
    public $business_subject;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['customer_id', 'abandon_reason'], 'required'],
            ['customer_id', 'validateId'],
        ];
    }


    public function validateId()
    {

        $customer_id = explode(',', $this->customer_id);

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if($administrator->type == 1){
            $administrator_id = [$administrator->id];
        }else{
            if($administrator->isLeader() ||  $administrator -> isDepartmentManager()){
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
            }else{
                $administrator_id = [$administrator->id];
            }
        }
        foreach ($customer_id as $key => $val) {
            $this->customer = CrmCustomer::findOne($val);
            $this->business_subject = BusinessSubject::find()->where(['customer_id' => $val])->one();
            $company_name = isset($this->business_subject->company_name) ? $this->business_subject->company_name : '';
            if (null == $this->customer) {
                $this->addError('customer_id',$company_name . '：客户不存在');
            } else {
                if (!in_array($this->customer->administrator_id, $administrator_id)) {
                    return $this->addError('customer_id', '所选客户中存在您没有放弃权限的客户，请重新选择。');
                }

                if ($this->customer->customer_public_id != 0) {
                    $this->addError('customer_id',$company_name . '：所选客户中存在已经被放弃的客户，请重新选择。');
                }

                if ($this->customer->is_protect ==1){
                    $this->addError('customer_id', $company_name . '：所选客户为保护客户，不能放弃，请重新选择。');
                }

                $where = [];

                //判断当前客户是企业还是个人客户 查询对应的公海
                if (!$this->business_subject){
                    $where = [0, 1];
                } else if ($this->business_subject->subject_type == 1 || $this->business_subject->subject_type === null) {
                    $where = [0, 1];
                } else if ($this->business_subject->subject_type == 0) {
                    $where = [0, 2];
                }

                /** @var CustomerPublic $customerPublic */
                $customerPublic = CustomerPublic::find()
                    ->alias('c')
                    ->leftJoin(['d' => CustomerDepartmentPublic::tableName()], 'c.id=d.customer_public_id')
                    ->where(['d.customer_department_id' => $administrator->department_id])
                    ->andWhere(['in', 'c.customer_type', $where])
                    ->one();

                if (null == $customerPublic) {
                    if($this->business_subject->subject_type != 0 || $this->business_subject->subject_type===null){
                        $this -> addError('customer_id',$company_name . '：客户没有个人公海，请重新选择。');
                    }
                    if($this->business_subject->subject_type == 0){
                        $this -> addError('customer_id',$company_name . '：客户没有企业公海，请重新选择。');
                    }
                }
            }
        }
    }

}