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

class CustomerConfirmClaimNewForm extends Model
{
    public $customer_id;
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
            [['id'], 'required'],
            ['id', 'validateCustomerId'],
        ];
    }

    public function validateCustomerId()
    {
        $dataStr = date('Y-m-d', time());
        $startTime = strtotime($dataStr);
        $endTime = strtotime($dataStr) + 86400;
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $id = explode(',', $this->id);

        if ($administrator->type == 1) {
            $department_id = [$administrator->department_id];
        } else {
            if($administrator->isLeader() ||  $administrator -> isDepartmentManager()){
                $department_id = $department_id = $administrator->getTreeDepartmentId(true);;
            }else{
                $department_id = [$administrator->department_id];
            }
        }

        foreach ($id as $key => $val) {
            $this->customer = CrmCustomer::findOne($val);
            $this->business = BusinessSubject::find()->where(['customer_id' => $val])->one();
            if (null == $this->customer) {
                return $this->addError('id', '您所选择的客户不存在，请重新选择！');
            }
            if ($this->customer-> customer_public_id ==  0) {
                return $this->addError('id', '您所选择的客户已经被提取，不能进行该操作，请重新选择！');
            }
            if (!in_array($administrator->department_id, $department_id)) {
                return $this->addError('id', '您所选择的客户您无权操作，请重新选择！');
            }
            if (!$this->customer->canRelease()) {
                $company_name = isset($this->business->company_name) ? $this->business->company_name : '';
                    $this->addError('customer_id',$company_name . '：所选客户仍在限制提取时间内，请重新选择。');
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
            /** @var CustomerPublic $customerPublic */
            $customerPublic = CustomerPublic::find()
                ->alias('c')
                ->leftJoin(['d' => CustomerDepartmentPublic::tableName()], 'c.id=d.customer_public_id')
                ->where(['d.customer_department_id' => $administrator->department_id])
                ->andWhere(['in', 'c.customer_type', $where])
                ->one();

            if (null == $customerPublic) {
                return $this->addError('id', '您所选择的客户公海不存在，请重新选择！');
            } else {
                if ($customerPublic->status != 1) {
                    return $this->addError('id', '您所选择的客户 客户公海未启用，请重新选择！');
                }
            }

            if (null == $administrator->department || !$administrator->isBelongCompany()) {
                return $this->addError('id', '您所选择的客户您无权操作，请重新选择！');
            }

            if ($administrator->company_id != $customerPublic->company_id) {
                return $this->addError('id', '您所选择的客户您无权提取，请重新选择！');
            }

            //当前业务员拥有的最大客户数
            if ($customerPublic->big_customer > 0) {
                $customer_query = CrmCustomer::find()
                    ->alias('c')
                    ->leftJoin(['b' => BusinessSubject::tableName()], 'c.id=b.customer_id')
                    ->leftJoin(['ccc' => CrmCustomerCombine::tableName()],'c.id=ccc.customer_id')
                    ->where(['ccc.administrator_id' => $administrator->id])
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
                    $customer_count = $customer_query->andWhere(['<>', 'c.creator_id', $administrator->id])->count();
                }
                if ($customer_count >= $customerPublic->big_customer) {
                    return $this->addError('id', '对不起，您的最大客户数已达到上限，不能提取！');
                }
            }
            //当前客户对应的客户公海保护的数量
            if ($customerPublic->extract_number_limit > 0) {
                //判断当前客户对应的客户公海是否有最大提取限制（24小时之内）,今天已经提取到客户的数量，利用提取时间大于0，并且部门属于客户公海部门或者是客户公海对应部门的下属部门
                $count = CrmCustomer::find()
                    ->where(['administrator_id' => $administrator->id])
                    ->andWhere(['>=', 'extract_time', $startTime])
                    ->andWhere(['<=', 'extract_time', $endTime])
                    ->andWhere(['>', 'extract_time', 0])
                    ->andWhere(['company_id' => $customerPublic->company_id])
                    ->count();
                if ($count >= $customerPublic->extract_number_limit) {
                    return $this->addError('id', '对不起，今日提取客户数量已达到上限，请耐心等待明日再次提取哦！');
                }
            }
        }

    }
}