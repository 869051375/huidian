<?php

namespace backend\models;


use yii\helpers\ArrayHelper;

class OpportunityExportSearch extends OpportunitySearch
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['start_date', 'end_date', 'keyword', 'status', 'customer_source', 'top_category_id', 'category_id', 'product_id',
                'start_last_record_date', 'end_last_record_date', 'amount', 'amount_keyword', 'company_id', 'department_id', 'administrator_id'], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false],
            [['start_date', 'end_date'],"validateOutTime"],
        ]);
    }

    public function validateOutTime()
    {
        $day = strtotime($this->start_date)+31*86400;//创建时间+31天
        if(strtotime($this->end_date) > $day)
        {
            $this->addError('start_date', '导出的商机时间间隔不能超过30天！');
        }
    }

    public function requiredBySpecial($attribute)
    {
        if(empty($this->start_date) && empty($this->end_date) &&
            empty($this->keyword) && empty($this->status) &&
            empty($this->customer_source) && empty($this->top_category_id) &&
            empty($this->category_id) && empty($this->product_id) &&
            empty($this->start_last_record_date) && empty($this->end_last_record_date) &&
            empty($this->amount) && empty($this->amount_keyword) &&
            empty($this->company_id) && empty($this->department_id) &&
            empty($this->administrator_id))
        {
            $this->addError($attribute, '请选择任意一项搜索才能导出！');
        }
    }
}
