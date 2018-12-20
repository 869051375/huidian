<?php

namespace backend\models;

use common\models\ClueCustomField;
use yii\base\Model;
use yii\helpers\ArrayHelper;


class ClueCustomList extends Model
{

    public $fields;
    /**
     * 类型 (0:跟进中列表，1：已转换列表 2：公海列表)
     * @SWG\Property(example = 2)
     * @var integer
     */
    public $type;
    public $field_all;

    public $administrator;

    const TYPE_0 = 0;   //跟进中列表
    const TYPE_1 = 1;   //已转换列表
    const TYPE_2 = 2;   //公海列表


    public function rules()
    {
        return [
            [['type'],'required','message'=>'所属类型提交错误！'],
            [['fields'],'validateField'],

        ];
    }

    public function load($data, $formName = '')
    {
        if ($data['type'] == self::TYPE_0)
        {
            $this->field_all = self::$field_default0;
        }
        elseif ($data['type'] == self::TYPE_1)
        {
            $this->field_all = self::$field_default1;
        }
        elseif ($data['type'] == self::TYPE_2)
        {
            $this->field_all = self::$field_default2;
        }
        if (empty($data['fields'])){
            $data['fields'] = $this->field_all;
        }
        return parent::load($data, $formName);

    }

    public static $field_default0 = array(
        ['field'=>'id','field_name'=>'线索ID','sort'=>'1','status'=>1,'is_update'=>0],
        ['field'=>'name','field_name'=>'姓名','sort'=>'2','status'=>1,'is_update'=>0],
        ['field'=>'company_name','field_name'=>'公司名称','sort'=>'3','status'=>1,'is_update'=>0],
        ['field'=>'next_follow_time','field_name'=>'下次跟进时间','sort'=>'4','status'=>1,'is_update'=>1],
        ['field'=>'created_at','field_name'=>'创建时间','sort'=>'5','status'=>1,'is_update'=>1],
        ['field'=>'source_name','field_name'=>'线索来源','sort'=>'6','status'=>1,'is_update'=>1],
        ['field'=>'department_name','field_name'=>'所属部门','sort'=>'7','status'=>1,'is_update'=>1],
        ['field'=>'creator_name','field_name'=>'创建人','sort'=>'8','status'=>1,'is_update'=>1],
        ['field'=>'administrator_name','field_name'=>'负责人','sort'=>'9','status'=>1,'is_update'=>1],
        ['field'=>'updater_name','field_name'=>'最后维护人','sort'=>'10','status'=>1,'is_update'=>1],
        ['field'=>'updated_at','field_name'=>'最后维护时间','sort'=>'11','status'=>1,'is_update'=>1],
        ['field'=>'follow_status','field_name'=>'跟进状态','sort'=>'12','status'=>1,'is_update'=>1],
        ['field'=>'channel_name','field_name'=>'来源渠道','sort'=>'13','status'=>1,'is_update'=>1],
        ['field'=>'label_name','field_name'=>'标签','sort'=>'14','status'=>1,'is_update'=>1],
    );
    public static $field_default1 = array(
        ['field'=>'id','field_name'=>'线索ID','sort'=>'1','status'=>1,'is_update'=>0],
        ['field'=>'name','field_name'=>'姓名','sort'=>'2','status'=>1,'is_update'=>0],
        ['field'=>'company_name','field_name'=>'公司名称','sort'=>'3','status'=>1,'is_update'=>0],
        ['field'=>'created_at','field_name'=>'创建时间','sort'=>'4','status'=>1,'is_update'=>1],
        ['field'=>'source_name','field_name'=>'线索来源','sort'=>'5','status'=>1,'is_update'=>1],
        ['field'=>'department_name','field_name'=>'所属部门','sort'=>'6','status'=>1,'is_update'=>1],
        ['field'=>'creator_name','field_name'=>'创建人','sort'=>'7','status'=>1,'is_update'=>1],
        ['field'=>'administrator_name','field_name'=>'负责人','sort'=>'8','status'=>1,'is_update'=>1],
        ['field'=>'updater_name','field_name'=>'最后维护人','sort'=>'9','status'=>1,'is_update'=>1],
        ['field'=>'updated_at','field_name'=>'最后维护时间','sort'=>'10','status'=>1,'is_update'=>1],
        ['field'=>'follow_status','field_name'=>'跟进状态','sort'=>'11','status'=>1,'is_update'=>1],
        ['field'=>'channel_name','field_name'=>'来源渠道','sort'=>'12','status'=>1,'is_update'=>1],
        ['field'=>'label_name','field_name'=>'标签','sort'=>'13','status'=>1,'is_update'=>1],
        ['field'=>'transfer_at','field_name'=>'转换时间','sort'=>'14','status'=>1,'is_update'=>1],
    );
    public static $field_default2 = array(
        ['field'=>'id','field_name'=>'线索ID','sort'=>'1','status'=>1,'is_update'=>0],
        ['field'=>'name','field_name'=>'姓名','sort'=>'2','status'=>1,'is_update'=>0],
        ['field'=>'company_name','field_name'=>'公司名称','sort'=>'3','status'=>1,'is_update'=>0],
        ['field'=>'created_at','field_name'=>'创建时间','sort'=>'4','status'=>1,'is_update'=>1],
        ['field'=>'source_name','field_name'=>'线索来源','sort'=>'5','status'=>1,'is_update'=>1],
        ['field'=>'clue_public_name','field_name'=>'所属公海','sort'=>'6','status'=>1,'is_update'=>1],
        ['field'=>'creator_name','field_name'=>'创建人','sort'=>'7','status'=>1,'is_update'=>1],
        ['field'=>'updater_name','field_name'=>'最后维护人','sort'=>'8','status'=>1,'is_update'=>1],
        ['field'=>'updated_at','field_name'=>'最后维护时间','sort'=>'9','status'=>1,'is_update'=>1],
        ['field'=>'follow_status','field_name'=>'跟进状态','sort'=>'10','status'=>1,'is_update'=>1],
        ['field'=>'channel_name','field_name'=>'来源渠道','sort'=>'11','status'=>1,'is_update'=>1],
        ['field'=>'label_name','field_name'=>'标签','sort'=>'12','status'=>1,'is_update'=>1],
        ['field'=>'recovery_at','field_name'=>'回收时间','sort'=>'13','status'=>1,'is_update'=>1],
    );


    public function validateField()
    {
        if (!in_array($this->type,[self::TYPE_0,self::TYPE_1,self::TYPE_2]))
        {
            return $this->addError('type','提交的字段有误');
        }

        if ($this->type == self::TYPE_0)
        {
            $field = array_column(self::$field_default0,'field');
        }
        elseif ($this->type == self::TYPE_1)
        {
            $field = array_column(self::$field_default1,'field');
        }
        elseif ($this->type == self::TYPE_2)
        {
            $field = array_column(self::$field_default2,'field');
        }

        if (is_array($this->fields))
        {
            $field_new = array_column($this->fields,'field');

            //先判断长度是否相等，不相等直接抛错
            if (count($field_new) != count($field))
            {
                return $this->addError('field','提交的字段有误');
            }

            //再判断提交的字段是否全部一致，不一致直接抛错
            for($i=0;$i<count($field_new);$i++)
            {
                if(!in_array($field_new[$i],$field))
                {
                    return $this->addError('field','提交的字段有误');
                }
            }

            //不可移动的字段如果被移动，直接抛错
            foreach ($this->field_all as $k=>$v)
            {
                if ($v['is_update'] == 0)
                {
                    //在判断不可以移动的是否被移动
                    if ($this->fields[$k] != $this->field_all[$k])
                    {
                        return $this->addError('field','提交的字段有误');
                    }
                }
            }

        }
        else
        {
            $this->fields = $this->field_all;
        }
        return true;
    }

    public function save()
    {
        /** @var ClueCustomField $model */
        $model = ClueCustomField::find()->where(['administrator_id'=>$this->administrator->id])->andWhere(['type'=>$this->type])->one();
        if($model){
            $model->updated_at = time();
        }else{
            $model = new ClueCustomField();
            $model->created_at = time();
        }
        $model->administrator_id = $this->administrator->id;
        $model->field = json_encode($this->fields);
        $model->type = $this->type;
        $model->save(false);
    }
    
    //获取列表
    public function select()
    {
        $model = ClueCustomField::find()->where(['administrator_id'=>$this->administrator->id])->andWhere(['type'=>$this->type])->one();
        if (empty($model))
        {
            if ($this->type == self::TYPE_0)
            {
                $fields = self::$field_default0;
            }
            elseif ($this->type == self::TYPE_1)
            {
                $fields = self::$field_default1;
            }
            elseif ($this->type == self::TYPE_2)
            {
                $fields = self::$field_default2;
            }
            $model = new ClueCustomField();
            $model->created_at = time();
            $model->administrator_id = $this->administrator->id;
            $model->field = json_encode($fields);
            $model->type = $this->type;
            $model->save(false);
        }

        return $model;

    }



}