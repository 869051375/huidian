<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/18
 * Time: 下午5:57
 */

namespace backend\models;


use common\models\Administrator;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use yii\base\Model;

class OpportunityVoidForm extends Model
{
    public $opportunity_id;
    public $reason;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    public function rules()
    {
        return [
            [['opportunity_id', 'reason'], 'required'],
            [['reason'], 'string', 'max' => 200],
            [['opportunity_id'], 'validateOpportunityId'],
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
            $this->addError('opportunity_id', '您无法跟进该商机');
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        /** @var Administrator $admin */
        $this->opportunity->invalid_reason = $this->reason;
        $this->opportunity->invalid_time = time();
        $this->opportunity->status = CrmOpportunity::STATUS_FAIL;
        $this->opportunity->progress = 0;
        $this->opportunity->next_follow_time = null;
        $this->opportunity->is_protect = CrmOpportunity::PROTECT_DISABLED;
        CrmCustomerLog::add('商机作废', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        return $this->opportunity->save(false);
    }

    public function attributeLabels()
    {
        return [
            'reason' => '作废原因',
        ];
    }
}