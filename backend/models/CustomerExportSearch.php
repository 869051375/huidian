<?php
namespace backend\models;

use yii\helpers\ArrayHelper;

class CustomerExportSearch extends CrmCustomerSearch
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['start_date', 'end_date', 'source', 'get_way', 'company_id', 'department_id', 'start_last_record_date', 'end_last_record_date',
                'collaborator_id', 'level_id', 'leader_id', 'keyword'], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false],
            [['start_date', 'end_date'],"validateOutTime"],
        ]);
    }

    public function validateOutTime()
    {
        $day = strtotime($this->start_date)+31*86400;//创建时间+31天
        if(strtotime($this->end_date) > $day)
        {
            $this->addError('start_date', '导出的客户时间间隔不能超过30天！');
        }
    }

    public function requiredBySpecial($attribute)
    {
        if(empty($this->start_date) && empty($this->end_date) &&
            empty($this->source) && empty($this->get_way) &&
            empty($this->company_id) && empty($this->department_id) &&
            empty($this->start_last_record_date) && empty($this->end_last_record_date) &&
            empty($this->collaborator_id) && empty($this->leader_id) &&
            empty($this->level_id) && empty($this->keyword))
        {
            $this->addError($attribute, '请选择任意一项搜索才能导出！');
        }
    }
}
