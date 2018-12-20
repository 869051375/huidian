<?php
/**
 * Created by PhpStorm.
 * User: jiayongbo
 * Date: 2018/07/09
 * Time: 下午13:34
 */

namespace backend\models;


use common\models\Contract;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use yii\base\Model;

class OpportunityContractDealForm extends Model
{
    public $opportunity_id;
    public $contract_id;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    /**
     * @var Contract
     */
    public $contract;

    public function rules()
    {
        return [
            [['opportunity_id', 'contract_id'], 'required'],
            [['opportunity_id'], 'validateOpportunityId'],
            [['contract_id'], 'validateContractId'],
        ];
    }

    public function validateOpportunityId()
    {
        $this->opportunity = CrmOpportunity::findOne($this->opportunity_id);
        if(null == $this->opportunity)
        {
            $this->addError('opportunity_id', '找不到商机信息');
        }
        else if($this->opportunity->administrator_id != \Yii::$app->user->id)
        {
            $this->addError('opportunity_id', '您无法申请成交该商机');
        }
        else if(empty($this->opportunity->business_subject_id))
        {
            $this->addError('opportunity_id', '申请成交前必须录入主体信息');
        }
    }

    public function validateContractId()
    {
        if($this->hasErrors()) return ;
        $this->contract = Contract::findOne($this->contract_id);
        if(null == $this->contract)
        {
            $this->addError('contract_id', '找不到指定的合同');
            return ;
        }
        else if($this->contract->customer_id != $this->opportunity->customer->id)
        {
            $this->addError('contract_id', '您不能选择该订单作为成交订单');
            return ;
        }
        $has = false;
        foreach($this->contract->virtualOrder->orders as $order)
        {
            foreach($this->opportunity->opportunityProducts as $opportunityProduct)
            {
                if($order->product_id == $opportunityProduct->product_id)
                {
                    $has = true;
                }
            }
        }
        if(!$has)
        {
            $this->addError('virtual_order_id', '该订单不包含商机中的商品，不能申请成交');
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        CrmCustomerLog::add('商机申请成交', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        if($this->contract->virtualOrder->isAlreadyPayment())
        {
            $this->opportunity->status = CrmOpportunity::STATUS_DEAL;
            $this->opportunity->deal_time = $this->contract->virtualOrder->payment_time;
            $this->opportunity->is_protect = CrmOpportunity::PROTECT_DISABLED;
            CrmCustomerLog::add('商机成交', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        }
        else
        {
            $this->opportunity->status = CrmOpportunity::STATUS_APPLY;
        }
        $this->opportunity->contract_id = $this->contract->id;
        $this->opportunity->user_id = $this->contract->customer->user_id;
        $this->opportunity->progress = 100;
        $this->opportunity->is_protect = CrmOpportunity::PROTECT_DISABLED;
        return $this->opportunity->save(false);
    }

    public function forcedSave()
    {
        if(!$this->validate())
        {
            return false;
        }
        $this->opportunity->status = CrmOpportunity::STATUS_DEAL;
        $this->opportunity->deal_time = time();
        $this->opportunity->is_protect = CrmOpportunity::PROTECT_DISABLED;
        $this->opportunity->progress = 100;
        CrmCustomerLog::add('商机强制成交', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        return $this->opportunity->save(false);
    }

    public function attributeLabels()
    {
        return [
            'contract_id' => '关联合同',
            'opportunity_id' => '关联合同',
        ];
    }
}