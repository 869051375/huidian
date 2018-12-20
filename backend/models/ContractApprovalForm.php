<?php

namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\Contract;
use common\models\ContractRecord;
use yii\base\Model;

class ContractApprovalForm extends Model
{
    public $contract_id;
    public $type;
    public $remark;

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
            [['contract_id','type'], 'integer'],
            [['contract_id','remark'], 'required'],
            [['contract_id'], 'validateContractId'],
            [['remark'], 'string'],
        ];
    }

    public function validateContractId()
    {
        $this->contract = Contract::findOne($this->contract_id);
        if(null == $this->contract)
        {
            $this->addError('contract_id','找不到指定的合同');
        }

        if(!($this->contract->status == Contract::STATUS_CONTRACT && $this->contract->sign_status == Contract::SIGN_PENDING_REVIEW))
        {
            $this->addError('contract_id','合同状态必须是待签约状态');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'remark' => '审核备注',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return null;
        $this->contract->status = Contract::STATUS_CONTRACT;
        /** @var Administrator $admin */
        $admin = \Yii::$app->user->identity;
        if($this->type == 1)
        {
            foreach ($this->contract->virtualOrder->orders as $order)
            {
                /** @var AdjustOrderPrice $adjust */
                $adjust = AdjustOrderPrice::find()->where(['order_id' => $order->id,'status' => AdjustOrderPrice::STATUS_PENDING])->limit(1)->one();
                if($adjust)
                {
                    $review = new ReviewAdjustPriceForm();
                    $review->order_id = $order->id;
                    $review->status = AdjustOrderPrice::STATUS_PASS;
                    $review->adjust_price = $adjust->adjust_price;
                    $review->adjust_price_reason = $adjust->adjust_price_reason;
                    $review->origin_price = $order->original_price;
                    $review->save();
                }
                $order->is_contract_show = 1;
                $order->save(false);
            }
            $this->contract->sign_status = Contract::SIGN_ALREADY_REVIEW;
            $this->contract->save(false);
            ContractRecord::CreateRecord($this->contract->id,$admin->name.'通过了合同签约审批，审批备注为：'.$this->remark,$admin);
            return true;
        }
        else
        {
            $this->contract->sign_status = Contract::SIGN_NO_REVIEW;
            $this->contract->save(false);
            ContractRecord::CreateRecord($this->contract->id,$admin->name.'驳回了合同签约审批，审批备注为：'.$this->remark,$admin);
            return true;
        }
    }
}
