<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/18
 * Time: 下午5:57
 */

namespace backend\models;


use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\VirtualOrder;
use yii\base\Model;

class OpportunityApplyDealForm extends Model
{
    public $opportunity_id;
    public $virtual_order_id;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    /**
     * @var VirtualOrder
     */
    public $virtualOrder;

    public function rules()
    {
        return [
            [['opportunity_id', 'virtual_order_id'], 'required', 'on' => ['apply-deal']],
            [['opportunity_id'], 'validateOpportunityId', 'on' => ['apply-deal']],
            [['virtual_order_id'], 'validateVirtualOrderId', 'on' => ['apply-deal']],

            [['opportunity_id'], 'required', 'on' => ['forced-deal']],
            [['opportunity_id'], 'validateForcedOpportunityId', 'on' => ['forced-deal']],
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
            $this->addError('opportunity_id', '申请成交前必须关联主体信息');
        }
    }

    public function validateVirtualOrderId()
    {
        if($this->hasErrors()) return ;
        $this->virtualOrder = VirtualOrder::findOne($this->virtual_order_id);
        if(null == $this->virtualOrder)
        {
            $this->addError('virtual_order_id', '找不到订单');
            return ;
        }
        else if($this->virtualOrder->user_id != $this->opportunity->customer->user_id)
        {
            $this->addError('virtual_order_id', '您不能选择该订单作为成交订单');
            return ;
        }
        $has = false;
        foreach($this->virtualOrder->orders as $order)
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

    public function validateForcedOpportunityId()
    {
        $this->opportunity = CrmOpportunity::findOne($this->opportunity_id);
        if(null == $this->opportunity)
        {
            $this->addError('opportunity_id', '找不到商机信息');
        }
        else if($this->opportunity->administrator_id != \Yii::$app->user->id)
        {
            $this->addError('opportunity_id', '您无法强制成交该商机');
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        CrmCustomerLog::add('商机申请成交', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        if($this->virtualOrder->isAlreadyPayment())
        {
            $this->opportunity->status = CrmOpportunity::STATUS_DEAL;
            $this->opportunity->deal_time = $this->virtualOrder->payment_time;
            $this->opportunity->is_protect = CrmOpportunity::PROTECT_DISABLED;
            CrmCustomerLog::add('商机成交', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        }
        else
        {
            $this->opportunity->status = CrmOpportunity::STATUS_APPLY;
        }
        $this->opportunity->virtual_order_id = $this->virtualOrder->id;
        $this->opportunity->user_id = $this->virtualOrder->user_id;
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
            'virtual_order_id' => '关联订单',
        ];
    }
}