<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/15
 * Time: 上午10:47
 */

namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\MessageRemind;
use Yii;
use yii\base\Model;


class OpportunityChangeAdministratorForm extends Model
{
    public $opportunity_id;
    public $administrator_id;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['opportunity_id', 'administrator_id'], 'required'],
            ['opportunity_id', 'validateOpportunityId'],
            ['administrator_id', 'validateAdministratorId'],
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
        else if(!$this->opportunity->isSubFor($administrator))
        {
            $this->addError('opportunity_id', '您没有修改该商机的权限');
        }
    }

    public function validateAdministratorId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id', '人员信息不存在');
        }
        else if($this->administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('administrator_id', '该账号非业务人员');
        }
        else if(!$administrator->isDepartmentManager() && !$administrator->isLeader())
        {
            $this->addError('administrator_id', '该账号非部门主管');
        }
    }

    public function change()
    {
        if(!$this->validate())
        {
            return false;
        }
        $oldAdministratorId = $this->opportunity->administrator_id;
        $this->opportunity->administrator_id = $this->administrator->id;
        $this->opportunity->administrator_name = $this->administrator->name;
        $this->opportunity->department_id = $this->administrator->department_id;
        CrmCustomerLog::add('更换负责人为：'.$this->opportunity->administrator_name, $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);

        //消息提醒
        if($this->opportunity->save(false) && $oldAdministratorId != $this->administrator->id)
        {
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '恭喜您成为商机：'. $this->opportunity->name .'的新负责人，请前往查看！';
            $popup_message = $message;
            $type = MessageRemind::TYPE_COMMON;
            $type_url = MessageRemind::TYPE_URL_OPPORTUNITY_DETAIL;
            $receive_id = $this->administrator->id;
            $customer_id = $this->opportunity->customer_id;
            $opportunity_id = $this->opportunity->id;
            $sign ='h-'.$oldAdministratorId.'-'.$receive_id.'-'.$opportunity_id.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind)
            {
                MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, $opportunity_id, $administrator);
            }
        }
        return $this->opportunity->save(false);
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '负责人'
        ];
    }
}