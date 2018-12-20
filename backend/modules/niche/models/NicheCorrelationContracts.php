<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Contract;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NichePublicDepartment;
use Yii;
use yii\base\Model;


/**
 * 商机关联合同接口
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCorrelationContracts"))
 */
class NicheCorrelationContracts extends Model
{

    /**
     * 合同ID
     * @SWG\Property(example = "1,2,3")
     * @var integer
     */
    public $contract_ids;

    public $creator_name;

    /** @var $currentAdministrator */
    public $currentAdministrator;

    /**
     * 商机ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $niche_id;



    public function rules()
    {
        return [
            [['niche_id','contract_ids'], 'required'],
            [['contract_ids'], 'string'],
            [['niche_id'], 'validateNicheId'],
            [['contract_ids'], 'validateContractIds'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validateNicheId()
    {
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
        if (empty($niche_one))
        {
            return $this->addError('niche_ids','商机ID不存在');
        }
        return true;
    }

    public function validateContractIds()
    {
        $ids = explode(',', $this->contract_ids);
        $count = Contract::find()->where(['in','id',$ids])->count();
        if ((int)$count != count($ids))
        {
            return $this->addError('contract_ids','合同ID不存在');
        }
        return true;
    }



    public function save()
    {
        $ids = explode(',', $this->contract_ids);
        foreach ($ids as $contract_id)
        {
            /** @var Contract $contract */
            $contract = Contract::find()->where(['id'=>$contract_id])->one();
            $model = new NicheContract();
            $model->contract_id = $contract_id;
            $model->niche_id = $this->niche_id;
            $model->save(false);
            //添加操作记录
            NicheOperationRecord::create($this->niche_id,'关联合同','关联了合同，合同编码为：'.$contract->contract_no);
        }
        return true;
    }
}
