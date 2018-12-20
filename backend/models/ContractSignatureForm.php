<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Contract;
use common\models\ContractRecord;
use Yii;
use yii\base\Model;

/**
 * Class ContractSignatureForm
 * @package backend\models
 *
 */
class ContractSignatureForm extends Model
{
    public $contract_id;
    public $signature_id;
    /**
     * @var Contract
     */
    public $contract;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['contract_id'], 'required'],
            [['signature_id'], 'required','message' => '请选择签章状态'],
            [['contract_id', 'signature_id'], 'integer'],
            ['contract_id', 'validateContractId'],
            ['signature_id', 'validateSignatureId'],
        ];
    }

    public function validateContractId()
    {
        $this->contract = Contract::findOne($this->contract_id);
        if(null == $this->contract)
        {
            $this->addError('contract_id', '客户不存在');
        }
    }

    public function validateSignatureId()
    {
        if($this->signature_id <= 0)
        {
            $this->addError('signature_id', '请选择签章状态');
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $signature = Contract::getSignature();
        ContractRecord::createRecord($this->contract->id,$administrator->name.'修改了合同签章状态：'.$signature[$this->signature_id],$administrator);
        $this->contract->signature = $this->signature_id;
        return $this->contract->save(false);
    }

    public function attributeLabels()
    {
        return [
            'contract_id' => '合同id',
            'signature_id' => '签章状态',
        ];
    }

}