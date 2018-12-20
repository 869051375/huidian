<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%customer_custom_field}}".
 *
 * @property integer $id
 * @property integer $administrator_id
 * @property string $fields
 */
class CustomerCustomField extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_custom_field}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id'], 'integer'],
            [['fields'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'administrator_id' => 'Administrator ID',
            'fields' => 'Fields',
        ];
    }

    /**
     * @param CustomerCustomField $model
     * @return mixed
     */
    public function fieldSave($model)
    {
        if(!$this->validate()) return false;
        $model->fields = $this->fields;
        return $model->save(false);
    }

    public function checkField()
    {
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $fields = $this->allFields();
        $customField = CustomerCustomField::find()->where(['administrator_id' => $admin->id])->one();
        if(null == $customField)
        {
            //不存时同步自定义字段
            $model = new CustomerCustomField();
            $arr = [];
            $fieldArr = [];
            foreach ($fields as $field)
            {
                $arr['name'] = $field;
                $arr['show'] = 1;
                array_push($fieldArr,$arr);
            }
            $model->administrator_id = $admin->id;
            $model->fields = json_encode($fieldArr);
            $model->save(false);
            $model->refresh();
            unset($arr);
            unset($fieldArr);
            return $model ? $model->fields ? $model->fields: '': '';
        }
        else
        {
            $fieldArr = json_decode($customField->fields);
            if(count($fieldArr) == 0)
            {
                $arr = [];
                $fieldArr = [];
                foreach ($fields as $field)
                {
                    $arr['name'] = $field;
                    $arr['show'] = 1;
                    array_push($fieldArr,$arr);
                }
                $customField->administrator_id = $admin->id;
                $customField->fields = json_encode($fieldArr);
                $customField->save(false);
                $customField->refresh();
                unset($arr);
                unset($fieldArr);
            }
            else
            {
                if(count($fields) != count($fieldArr))
                {
                    $arr = [];
                    foreach ($fieldArr as $v)
                    {
                        $arr[] = $v->name;
                    }
                    $diff = array_diff($fields, $arr);
                    if(!empty($diff))
                    {
                        $arr1 = [];
                        foreach ($diff as $value)
                        {
                            $arr1['name'] = $value;
                            $arr1['show'] = 1;
                            array_push($fieldArr, $arr1);
                        }

                        $customField->fields = json_encode($fieldArr);
                        $customField->save(false);
                        $customField->refresh();
                    }
                }
            }
            return $customField ? $customField->fields ? $customField->fields: '': '';
        }
    }

    //客户列表的所有字段
    private function allFields()
    {
        return ['created_at','administrator_id','is_receive','source','tag_id','last_operation_creator_name', 'operation_time','opportunity_deal',
            'opportunity_not_deal','opportunity_apply','opportunity_failed','opportunity_no_receive','opportunity_no_extract','paid_order', 'pending_order','get_way'];
    }


    public function getFields($fields = '')
    {
        $fieldsList = static::getFieldsList();
        $fields = json_decode($fields);
        if(!empty($fields))
        {
            foreach ($fields as $field)
            {
                foreach ($fieldsList as $k => $v)
                {
                    if($field->name == $k)
                    {
                        $field->zh_name = $v;
                    }
                }
            }
        }
        return $fields;
    }

    public static function getFieldsList()
    {
        return [
            'created_at' => '创建时间',
            'administrator_id' => '负责人',
            'is_receive' => '负责人跟进状态',
            'source' => '客户来源',
            'tag_id' => '标签',
            'last_operation_creator_name' => '最后维护人',
            'operation_time' => '最后维护时间',
            'opportunity_deal' => '已成交商机数',
            'opportunity_not_deal' => '跟进中商机数',
            'opportunity_apply' => '申请中商机数',
            'opportunity_failed' => '已失败商机数',
            'opportunity_no_receive' => '待确认商机数',
            'opportunity_no_extract' => '待提取商机数',
            'paid_order' => '已付款订单数',
            'pending_order' => '未付款订单数',
            'get_way' => '获取方式',
        ];
    }
}
