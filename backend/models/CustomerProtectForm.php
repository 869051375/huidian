<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerPublic;
use yii\base\Model;

class CustomerProtectForm extends Model
{
    public $id;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var BusinessSubject
     */
    public $business_subject;

    public $customer_id;
    public $is_protect;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['customer_id'], 'required'],
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

            if (!in_array($this->customer->administrator_id, $administrator_id)) {
                $this->addError('customer_id', $company_name . '：所选客户中存在您没有保护权限的客户，请重新选择。');
            }
            if (null == $this->customer) {
                $this->addError('customer_id',  '所选客户不存在，请重新选择。');
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

            if($customerPublic != null){
                if($customerPublic->status ==0){
                    return $this->addError('customer_id', '对不起，您当前所在公海未启用，不能进行保护操作！');
                }
                $customer_query = CrmCustomer::find()->alias('c')
                    ->leftJoin(['b' => BusinessSubject::tableName()], 'c.id=b.customer_id')
                    ->where(['c.administrator_id' => $administrator->id])
                    ->andWhere(['c.is_protect' => 1]);

                //判断当前公海是企业还是个人 或者是全部
                if ($customerPublic->customer_type == 1) {
                    $customer_query->andWhere("b.subject_type = 1 OR ISNULL(b.subject_type)");
                } else if ($customerPublic->customer_type == 2) {
                    $customer_query->andWhere(['b.subject_type' => 0]);
                }
                if ($customerPublic->big_customer_status == 1) {
                    //包含我新增的客户
                    $customer_count = $customer_query->count();
                } else {
                    //不包含我新增的客户
                    $customer_count = $customer_query->andWhere(['<>', 'c.creator_id', $administrator->id])->count();
                }
                if ($customer_count >= $customerPublic->protect_number_limit) {
                    return $this->addError('customer_id', '对不起，您可以保护的最大客户数已达到上限！');
                }
            }

        }
    }
}