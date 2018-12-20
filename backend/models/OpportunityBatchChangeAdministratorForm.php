<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/15
 * Time: 上午10:47
 */

namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\MessageRemind;
use Yii;
use yii\base\Model;


class OpportunityBatchChangeAdministratorForm extends Model
{
    public $opportunity_ids;
    public $administrator_id;

    /**
     * @var CrmOpportunity[]
     */
    public $opportunities = [];

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['administrator_id'], 'required'],
            ['opportunity_ids', 'each', 'rule' => ['integer']],
            ['opportunity_ids', 'validateOpportunityIds'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateOpportunityIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        $this->opportunities = CrmOpportunity::find()->where(['in', 'id', $this->opportunity_ids])->all();

        if(empty($this->opportunities))
        {
            $this->addError('opportunity_ids', '请选择商机');
        }

        foreach($this->opportunities as $opportunity)
        {
            if($opportunity->administrator_id != $administrator->id && !$opportunity->isSubFor($administrator))
            {
                $this->addError('opportunity_id', '您没有修改该商机的权限');
            }

            if(in_array($opportunity->status, [CrmOpportunity::STATUS_DEAL, CrmOpportunity::STATUS_FAIL]))
            {
                $this->addError('opportunity_id', '对不起，当前商机中存在不允许更换负责人的商机。');
            }

            if($opportunity->is_receive == CrmOpportunity::RECEIVE_DISABLED)
            {
                $this->addError('opportunity_id', '对不起，当前商机中存在未转入商机，不允许更换负责人。');
            }
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id', '人员信息不存在');
        }
        else if($this->administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('administrator_id', '该账号非业务人员');
        }
    }

    public function change()
    {
        if(!$this->validate())
        {
            return false;
        }
        foreach($this->opportunities as $opportunity)
        {
            // 先把原负责人设置为分享人
            $oldAdministratorId = $opportunity->send_administrator_id = $opportunity->administrator_id;
            //分配时间
            $opportunity->send_time = time();

            // 再把新负责人设置为负责人
            $opportunity->administrator_id = $this->administrator->id;
            $opportunity->administrator_name = $this->administrator->name;
            $opportunity->department_id = $this->administrator->department_id;
            CrmCustomerCombine::addTeam($this->administrator, $opportunity->customer);
            CrmCustomerLog::add('更换负责人为：'.$opportunity->administrator_name, $opportunity->customer_id, $opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
            $opportunity->save(false);
            if($oldAdministratorId != $this->administrator->id)
            {
                /** @var Administrator $administrator */
                $administrator = Yii::$app->user->identity;
                $message = '恭喜您成为商机：'. $opportunity->name .'的新负责人，请前往查看！';
                $popup_message = $message;
                $type = MessageRemind::TYPE_COMMON;
                $type_url = MessageRemind::TYPE_URL_OPPORTUNITY_DETAIL;
                $receive_id = $this->administrator->id;
                $customer_id = $opportunity->customer_id;
                $opportunity_id = $opportunity->id;
                $sign ='h-'.$oldAdministratorId.'-'.$receive_id.'-'.$opportunity_id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if(null == $messageRemind)
                {
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, $opportunity_id, $administrator);
                }
            }
        }

        return true;
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '负责人'
        ];
    }
}