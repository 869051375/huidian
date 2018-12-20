<?php

namespace backend\models;

use common\models\Administrator;
use common\models\Contract;
use common\models\ContractRecord;
use common\models\CrmOpportunity;
use common\models\Niche;
use common\models\Order;
use yii\base\Model;

class ContractInvalidForm extends Model
{
    public $contract_id;
    public $cause_id;
    public $remark;
    public $invalid_type;

    /**
     * @var Contract
     */
    public $contract;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contract_id','cause_id','invalid_type'], 'integer'],
            [['contract_id', 'cause_id','remark'], 'required'],
            [['contract_id'], 'validateContractId'],
            [['remark'], 'string'],
        ];
    }

    public function validateContractId()
    {
        $this->contract = Contract::findOne($this->contract_id);

        if (null == $this->contract) {
            $this->addError('contract_id', '找不到指定的合同');
        }

        if ((!$this->contract->virtualOrder->isCanceled() || !$this->contract->virtualOrder->isRefunded())
            && $this->contract->virtualOrder->isRefundedTwo() == 0 && $this->contract->virtualOrder->isPaymentAmount()) {
            $this->addError('contract_id', '对不起，当前合同已经回款，不可取消。请财务确认退款后，再次作废.');

        }

        if ($this->invalid_type == 1 || $this->invalid_type == 2) {
            if ($this->contract->status != Contract::STATUS_INVALID) {
                $this->addError('contract_id', '合同状态必须是作废的状态');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cause_id' => '作废原因',
            'remark' => '作废备注',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return null;
        $reasonList = Order::getRefundReasonList();
        /** @var Administrator $admin */
        $admin = \Yii::$app->user->identity;
        if($this->invalid_type == 1)
        {
//            foreach($this->contract->virtualOrder->orders as $order)
//            {
//                $order->is_contract_show = 0;
//                $order->save(false);
//            }
            $this->contract->status = Contract::STATUS_INVALID;
            $this->contract->invalid_status = Contract::INVALID_ALREADY_REVIEW;
            foreach($this->contract->opportunity as $opportunity)
            {
                $opportunity->niche->status = Niche::STATUS_FAIL;
                $opportunity->niche->progress = 0;
                $opportunity->save(false);
            }
            $this->contract->save(false);
            ContractRecord::CreateRecord($this->contract->id,$admin->name.'通过了合同作废审批，作废原因为：'.$reasonList[$this->cause_id].'，作废备注为：'.$this->remark,$admin);
        }
        else if($this->invalid_type == 2)
        {
            $this->contract->status = Contract::STATUS_CONTRACT;
            $this->contract->invalid_status = Contract::SIGN_ALREADY_REVIEW;
            $this->contract->save(false);
            ContractRecord::CreateRecord($this->contract->id,$admin->name.'驳回了合同作废审批，作废原因为：'.$reasonList[$this->cause_id].'，作废备注为：'.$this->remark,$admin);
        }
        else
        {
            $this->contract->status = Contract::STATUS_INVALID;
            $this->contract->invalid_status = Contract::INVALID_PENDING_REVIEW;
            $this->contract->invalid_cause = $this->cause_id;
            $this->contract->invalid_remark = $this->remark;
            $this->contract->save(false);
            ContractRecord::CreateRecord($this->contract->id,$admin->name.'提交了合同作废审批，作废原因为：'.$reasonList[$this->cause_id].'，作废备注为：'.$this->remark,$admin);
        }

        return true;
    }
}
