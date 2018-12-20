<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CustomerPublic;
use yii\base\Model;

class CustomerConfirmClaimForm extends Model
{
    public $customer_id;

    /**
     * @var CrmCustomer
     */
    public $customer;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['customer_id'], 'integer'],
            [['customer_id'], 'required'],
            ['customer_id', 'validateCustomerId'],
        ];
    }

    public function validateCustomerId()
    {
        $dataStr = date('Y-m-d', time());
        $startTime = strtotime($dataStr);
        $endTime = strtotime($dataStr) + 86400;
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer = CrmCustomer::findOne($this->customer_id);
        if(null == $this->customer)
        {
            $this->addError('customer_id', '客户不存在');
            return ;
        }
        /** @var CustomerPublic $customerPublic */
        $customerPublic = $this->customer->customerPublic;
        if(null == $customerPublic)
        {
            $this->addError('customer_id', '客户公海不存在！');
            return ;
        }

        if(null == $administrator->department || !$administrator->isBelongCompany())
        {
            $this->addError('customer_id', '您无权操作！');
        }

        if($administrator->company_id != $customerPublic->company_id)
        {
            $this->addError('customer_id', '此客户您无权提取！');
        }

        if(!$administrator->isBelongCompany())
        {
            $this->addError('customer_id', '此客户您无权提取！');
        }

        if($this->customer->is_receive && $this->customer->administrator_id > 0)
        {
            $this->addError('customer_id', '该客户已经被提取，不能进行该操作！');
        }

        //当前客户对应的客户公海保护的数量
        if($customerPublic->extract_number_limit > 0)
        {
            //判断当前客户对应的客户公海是否有最大提取限制（24小时之内）,今天已经提取到客户的数量，利用提取时间大于0，并且部门属于客户公海部门或者是客户公海对应部门的下属部门
            $count = CrmCustomer::find()
                ->where(['administrator_id' => $administrator->id])
                ->andWhere(['>=', 'extract_time', $startTime])
                ->andWhere(['<=', 'extract_time', $endTime])
                ->andWhere(['>', 'extract_time', 0])
                ->andWhere(['company_id' => $customerPublic->company_id])
                ->count();
            if($count >= $customerPublic->extract_number_limit)
            {
                $this->addError('customer_id', '对不起，今日提取客户数量以达到上限，请耐心等待明日再次提取哦！');
            }
        }
    }

    public function confirmClaim()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer->is_receive = 1;
        $this->customer->level = 1;
        $this->customer->administrator_id = $administrator->id;
        $this->customer->company_id = $administrator->company_id;
        $this->customer->department_id = $administrator->department_id;
        $this->customer->customer_public_id = 0;
        $this->customer->extract_time  = time();

        $c = CrmCustomerCombine::find()->where(['customer_id' => $this->customer->id,
            'administrator_id' => $administrator->id])->one();
        if(null != $c)
        {
            $c->level = CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE;
            return $this->customer->save(false) && $c->save(false);
        }
        CrmCustomerLog::add('公海客户提取成功', $this->customer->id, 0,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        return $this->customer->save(false) && CrmCustomerCombine::addTeam($administrator, $this->customer);
    }
}