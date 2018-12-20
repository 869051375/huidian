<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use yii\base\Model;

class OpportunityConfirmReceiveForm extends Model
{
    public $opportunity_id;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['opportunity_id'], 'required'],
            ['opportunity_id', 'validateOpportunityId'],
        ];
    }

    public function validateOpportunityId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->opportunity = CrmOpportunity::findOne($this->opportunity_id);
        if(null == $this->opportunity)
        {
            $this->addError('opportunity_id', '商机不存在');
        }
        else if($this->opportunity->is_receive)
        {
            $this->addError('opportunity_id', '该商机已经被确认，不能进行该操作');
        }
        else if($this->opportunity->administrator_id != $administrator->id)
        {
            $this->addError('opportunity_id', '您不能确认其他人的商机');
        }
    }

    public function confirm()
    {
        if(!$this->validate())
        {
            return false;
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->opportunity->is_receive = 1;
        CrmCustomerLog::add('商机确认转入', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        return $this->opportunity->save(false) && CrmCustomerCombine::addTeam($administrator, $this->opportunity->customer);
    }
}