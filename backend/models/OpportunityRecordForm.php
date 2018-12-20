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
use common\models\CrmOpportunityRecord;
use yii\base\Model;

class OpportunityRecordForm extends Model
{
    public $opportunity_id;
    public $content;
    public $next_follow_time;
    public $progress;

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    public function rules()
    {
        return [
            [['opportunity_id', 'content', 'next_follow_time','progress'], 'required'],
            [['content'], 'string', 'max' => 200],
            [['opportunity_id'], 'validateOpportunityId'],
            [['next_follow_time'], 'date', 'format' => 'yyyy-MM-dd HH:mm'],
            [['next_follow_time'], 'validateNextFollowTime'],
            [['progress'],'validateProgress']
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
        else if(!$this->opportunity->is_receive)
        {
            $this->addError('opportunity_id', '该商机暂未确认转入');
        }
    }

    public function validateProgress(){
        if ($this->progress < 20){
            $this->addError('progress', '请选择最新商机状态');
        }
    }

    public function validateNextFollowTime()
    {
        if(strtotime($this->next_follow_time.':00') < time())
        {
            $this->addError('next_follow_time', '下次跟进时间不能小于当前时间');
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return null;
        }
        /** @var Administrator $admin */
        $admin = \Yii::$app->user->identity;
        $model = new CrmOpportunityRecord();
        $model->opportunity_id = $this->opportunity_id;
        $model->customer_id = $this->opportunity->customer_id;
        $model->department_id = $admin->department_id;
        $model->department_name = $admin->department ? $admin->department->name : '';
        $model->content = $this->content;
        $model->next_follow_time = strtotime($this->next_follow_time.':00');
        $model->creator_id = $admin->id;
        $model->creator_name = $admin->name;
        $model->created_at = time();
        $model->save(false);

        $this->opportunity->next_follow_time = $model->next_follow_time;
        $this->opportunity->last_record = $model->created_at;
        $this->opportunity->last_record_creator_id = $admin->id;
        $this->opportunity->last_record_creator_name = $admin->name;
        $this->opportunity->save(false);

        CrmCustomerLog::add('添加跟进记录', $this->opportunity->customer_id, $this->opportunity->id,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);

        return $model;
    }

    public function attributeLabels()
    {
        return [
            'content' => '跟进记录',
            'next_follow_time' => '下次跟进时间',
            'progress' => '最新商机状态',
        ];
    }
}