<?php

namespace backend\models;

use yii\helpers\ArrayHelper;

class OrderExportSearch extends OrderSearch
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['starting_time', 'end_time', 'begin_service_start_time', 'begin_service_end_time', 'end_service_start_time', 'end_service_end_time','first_pay_start_time','first_pay_end_time'], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false],
            [['starting_time', 'end_time', 'begin_service_start_time', 'begin_service_end_time', 'end_service_start_time', 'end_service_end_time','first_pay_start_time','first_pay_end_time'], 'validateOutTime'],
        ]);
    }

    public function requiredBySpecial($attribute)
    {
        if((empty($this->starting_time) || empty($this->end_time)) &&
            (empty($this->begin_service_start_time) || empty($this->begin_service_end_time)) &&
            (empty($this->end_service_start_time) || empty($this->end_service_end_time)) &&
            (empty($this->first_pay_start_time) || empty($this->first_pay_end_time)))
        {
            $this->addError($attribute, '请选择任意一项时间(开始时间、结束时间必填)！');
        }
    }

    public function validateOutTime()
    {
        $day = strtotime($this->starting_time)+120*86400;//下单时间+31天
        $service_time = strtotime($this->begin_service_start_time)+120*86400;//服务开始时间+31天
        $end_service_time = strtotime($this->end_service_start_time)+120*86400;//服务结束时间+31天
        $first_pay_time = strtotime($this->first_pay_start_time)+120*86400;//首次付款时间+31天
        if(strtotime($this->end_time) > $day ||
           strtotime($this->begin_service_start_time) > $service_time ||
           strtotime($this->end_service_start_time) > $end_service_time ||
           strtotime($this->first_pay_start_time) > $first_pay_time)
        {
            $this->addError('starting_time', '导出的订单记录时间间隔不能超过120天！');
        }
    }
}
