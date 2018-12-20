<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%opportunity_custom_field}}".
 *
 * @property integer $id
 * @property integer $administrator_id
 * @property string $fields
 */
class OpportunityCustomField extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%opportunity_custom_field}}';
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
     * @param OpportunityCustomField $model
     * @return bool
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
        $customField = OpportunityCustomField::find()->where(['administrator_id' => $admin->id])->one();
        if(null == $customField)
        {
            //不存时同步自定义字段
            $model = new OpportunityCustomField();
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

    //商机列表的所有字段
    private function allFields()
    {
        return ['created_at','creator_name','tag_id','administrator_id','next_follow_time','product_name','business_subject',
            'customer_id','customer_name','source','last_record_creator_name','last_record','status','total_amount','send_administrator_id',
            'send_time','customer_created_at'];
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
            'creator_name'=>'创建人',
            'tag_id' => '标签',
            'administrator_id' => '跟进人',
            'next_follow_time' => '下次跟进时间',
            'product_name' =>'商品信息',
            'business_subject' => '关联业务主体',
            'customer_id' => '客户ID',
            'customer_name' => '客户名称',
            'source' => '客户来源',
            'last_record_creator_name' => '最后跟进人',
            'last_record' => '最后跟进时间',
            'status' => '商机状态',
            'total_amount' => '商机金额',
            'send_administrator_id' => '分配人员',
            'send_time' => '分配时间',
            'customer_created_at' => '客户创建时间'
        ];
    }
}
