<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerPublic;
use yii\base\Model;

class CustomerConfirmDistributionForm extends Model
{
    public $administrator_id;
    public $id;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var BusinessSubject
     */
    public $business;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['id'], 'string'],
            [['administrator_id'], 'integer'],
            [['id','administrator_id'], 'required'],
            ['id', 'validateCustomerId'],
        ];
    }

    public function validateCustomerId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $id = explode(',', $this->id);

        if($administrator->type == 1){
            $department_id = [$administrator->department_id];
        }else{
            if($administrator->isLeader() ||  $administrator -> isDepartmentManager()){
                $department_id = $administrator->getTreeDepartmentId(true);
            }else{
                $department_id = [$administrator->department_id];
            }
        }

        foreach ($id as $key => $val) {
            $this->customer = CrmCustomer::findOne($val);
            $this->business = BusinessSubject::find()->where(['customer_id' => $val])->one();

            if (null == $this->customer) {
                return  $this->addError('id','您所选择的客户不存在，请重新选择！');
            }
            if ($this->customer->customer_public_id == 0) {
                return $this->addError('id', '您所选择的客户已经被分配，不能进行该操作，请重新选择！');
            }

            if(!in_array($administrator->department_id,$department_id)){
                return $this->addError('id', '您所选择的客户您无权操作，请重新选择！');
            }

            $where = [];

            //判断当前客户是企业还是个人客户 查询对应的公海
            if (!$this->business){
                $where = [0, 1];
            } else if ($this->business->subject_type == 1 || $this->business->subject_type === null) {
                $where = [0, 1];
            } else if ($this->business->subject_type == 0) {
                $where = [0, 2];
            }
            /** @var Administrator $admin_id */
            $admin_id = Administrator::find()->where(['id'=>$this->administrator_id])->one();

            /** @var CustomerPublic $customerPublic */
            $customerPublic = CustomerPublic::find()
                ->alias('c')
                ->leftJoin(['d' => CustomerDepartmentPublic::tableName()], 'c.id=d.customer_public_id')
                ->where(['d.customer_department_id' => $admin_id->department_id])
                ->andWhere(['in', 'c.customer_type', $where])
                ->one();


            if (null == $customerPublic) {
                return $this->addError('id', '您所选择的客户公海不存在，请重新选择！');

            }else{
                if($customerPublic->status != 1){
                    return $this->addError('id', '您所选择的客户 客户公海未启用，请重新选择！');
                }
            }

            if (null == $administrator->department || !$administrator->isBelongCompany()) {
                return $this->addError('id', '您所选择的客户您无权操作，请重新选择！');
            }

            if ($administrator->company_id != $customerPublic->company_id) {
                return $this->addError('id', '您所选择的客户您无权分配，请重新选择！');
            }

            //当前业务员拥有的最大客户数
            if ($customerPublic->big_customer > 0) {

                $customer_query = CrmCustomer::find()->alias('c')
                    ->leftJoin(['b' => BusinessSubject::tableName()], 'c.id=b.customer_id')
                    ->leftJoin(['ccc' => CrmCustomerCombine::tableName()],'c.id=ccc.customer_id')
                    ->where(['ccc.administrator_id' => $this->administrator_id])
                    ->andWhere(['<>','c.administrator_id',0]);

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
                    $customer_count = $customer_query->andWhere(['<>', 'c.creator_id', $this->administrator_id])->count();
                }
                if ($customer_count >= $customerPublic->big_customer) {
                    return $this->addError('id', '对不起，该业务员的最大客户数已达到上限，不能分配！');
                }
            }
        }
    }
}