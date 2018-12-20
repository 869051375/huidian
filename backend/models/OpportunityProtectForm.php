<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmOpportunity;
use common\models\OpportunityPublic;
use yii\base\Model;

class OpportunityProtectForm extends Model
{
    public $opportunity_id;
    public $is_protect;

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
            [['opportunity_id'], 'integer'],
            [['opportunity_id', 'is_protect'], 'required'],
            [['is_protect'], 'boolean'],
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
        else if(!$this->opportunity->is_receive)
        {
            $this->addError('opportunity_id', '该商机未被确认，不能进行该操作');
        }
        else if($this->opportunity->administrator_id != $administrator->id)
        {
            $this->addError('opportunity_id', '您不能操作其他人的商机');
        }
        else if($this->opportunity->opportunityPublic)
        {
            $this->addError('opportunity_id', '您无权操作此商机');
        }
        else if(!$this->opportunity->isStatusNotDeal())
        {
            $this->addError('opportunity_id', '您的商机无法开启保护');
        }
        //查询商机公海所在部门是否是当前商机所谓部门或者所在上级部门(二级)，如果有，需要校验商机保护数量，暂时不考虑一级部门的商机
        if(!$this->opportunity->isProtect())
        {
            if($this->opportunity->department)
            {
                $op = OpportunityPublic::find()->where(['department_id' => $this->opportunity->department_id])->orWhere(['department_id' => $this->opportunity->department->parent_id])->one();
                if(null != $op)
                {
                    $opCount = CrmOpportunity::find()->where(['administrator_id' => \Yii::$app->user->id,'is_protect' => CrmOpportunity::PROTECT_ACTIVE])->count();
                    if(($op->protect_number_limit > 0 && $op->protect_number_limit <= $opCount) || $op->protect_number_limit <= 0)
                    {
                        $this->addError('opportunity_id', '您商机保护数量已达到最大值，无法再申请');
                    }
                }
            }
        }
    }

    public function confirm()
    {
        if(!$this->validate()) return false;
        $this->opportunity->is_protect = $this->is_protect;
        return $this->opportunity->save(false);
    }
}