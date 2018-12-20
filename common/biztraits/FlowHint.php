<?php
namespace common\biztraits;
use yii\helpers\Json;

trait FlowHint
{
    public function getHintCustomer()
    {
        $hint = ['title' => '', 'content' => ''];
        if(empty($this->hint_customer)) return $hint;
        $hint = Json::decode($this->hint_customer);
        return $hint;
    }

    public function setHintCustomer($title, $content)
    {
        $saveData = ['title' => $title, 'content' => $content];
        $this->hint_customer = Json::encode($saveData);
    }

    public function getHintOperator()
    {
        $hint = ['title' => '', 'content' => ''];
        if(empty($this->hint_operator)) return $hint;
        $hint = Json::decode($this->hint_operator);
        return $hint;
    }

    public function setHintOperator($title, $content)
    {
        $saveData = ['title' => $title, 'content' => $content];
        $this->hint_operator = Json::encode($saveData);
    }

    public function getInputList()
    {
        $result['input_list'] = [];
        if(!empty($this->input_list))
            $result = Json::decode($this->input_list);
        return $result;
    }

    public function hasInputList()
    {
        $inputList = $this->getInputList();
        return !empty($inputList['input_list']);
    }

    public function setInputList($input_list, $typeList)
    {
        if(empty($input_list)) return ;
        $saveData['input_list'] = [];
        foreach ($input_list as $k => $label)
        {
            $label = trim($label);
            if(empty($label)) continue;
            $saveData['input_list'][] = [
                'label' => $label,
                'type' => $typeList[$k],
                'is_company' => (isset($typeList[$k]) && $typeList[$k] == 1) ? 1 : 0,
                'is_trademark_apply_no' => (isset($typeList[$k]) && $typeList[$k] == 2) ? 1 : 0,
            ];
        }
        $this->input_list = Json::encode($saveData);
    }
}