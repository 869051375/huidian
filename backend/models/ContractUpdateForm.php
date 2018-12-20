<?php

namespace backend\models;

use common\models\Administrator;
use common\models\Contract;
use common\models\ContractRecord;
use yii\base\Model;

class ContractUpdateForm extends Model
{
    public $contract_id;
    public $name;
    public $administrator_id;
    public $prior_administrator_id;
    public $signing_date;
    public $remark;
    public $file;
    public $file_name;

    /**
     * @var Contract
     */
    public $contract;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id','contract_id','prior_administrator_id'], 'integer'],
            [['name', 'signing_date', 'administrator_id','contract_id','prior_administrator_id'], 'required'],
            [['administrator_id'], 'validateAdministratorId'],
            [['contract_id'], 'validateContractId'],
            [['remark','file','file_name'], 'string'],
            [['name'], 'string', 'max' => 12],
            [['signing_date'],'date', 'format' => 'yyyy-MM-dd'],
        ];
    }

    public function validateContractId()
    {
        $this->contract = Contract::find()
            ->where(['id' => $this->contract_id,'administrator_id' => $this->administrator_id])
            ->limit(1)->one();
        if(null == $this->contract)
        {
            $this->addError('contract_id','找不到指定的合同');
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id','找不到指定的负责人');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '合同名称',
            'administrator_id' => '负责人',
            'status' => 'Status',
            'signing_date' => '签约时间',
            'remark' => '合同备注',
            'file' => '合同附件',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return null;
        if($this->file)
        {
            $this->contract->addFile($this->file,$this->file_name);
        }
        $this->contract->administrator_id = $this->administrator_id;
        $this->contract->name = $this->name;
        $this->contract->remark = $this->remark;
        $this->contract->signing_date = strtotime($this->signing_date);
        $this->contract->status = Contract::STATUS_MODIFY;
        $this->contract->correct_status = Contract::MODIFY_PENDING_REVIEW;
        $this->contract->save(false);

        foreach($this->contract->virtualOrder->orders as $order)
        {
            $order->salesman_aid = $this->administrator->id;
            $order->salesman_department_id = $this->administrator->department_id;
            $order->company_id = $this->administrator->company_id;
            $order->save(false);
        }
        /** @var Administrator $admin */
        $admin = \Yii::$app->user->identity;
        ContractRecord::CreateRecord($this->contract->id,$admin->name.'变更了合同',$admin);
        return true;
    }
}
