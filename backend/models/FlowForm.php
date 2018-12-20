<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\Flow;
use yii\base\Model;

class FlowForm extends Model
{
    public $name;
    public $can_disable_sms;

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['name'], 'trim'],
            [['name'], 'string', 'max' => 15],
            ['can_disable_sms', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '流程名称',
            'can_disable_sms' => '勾选之后流程在进度更新时可选择是否发送短信',
        ];
    }

    /**
     * @return Flow|null
     */
    public function save()
    {
        $model = new Flow();
        $model->load($this->attributes, '');
        if(!$model->save(false))
        {
            return null;
        }
        return $model;
    }

    /**
     * @param Flow $flow
     * @return bool
     */
    public function update($flow)
    {
        $oldName = $flow->getOldAttribute('name');
        if(!$this->validate()) return false;
        $flow->name = $this->name;
        $flow->can_disable_sms = $this->can_disable_sms;
        if(false === $flow->save(false))
        {
            return false;
        }
        //新增后台操作日志
        AdministratorLog::logFlowUpdate($flow, $oldName);
        return true;
    }

}
