<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clue_operation_record".
 *
 * @property integer $id
 * @property integer $clue_id
 * @property string $content
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property string $item
 */
class ClueOperationRecord extends \yii\db\ActiveRecord
{


    //操作项
    const UPDATE_CLUE_TYPE = '更新线索状态';//更新线索状态
    const CREATE_CLUE = '创建线索';//创建线索
    const EDIT_CLUE = '编辑线索';//编辑线索
    const DISTRIBUTION_CLUE = '分配线索';//分配线索
    const UPDATE_GROUPING = '更换分组';//更换分组
    const EXTRACT_CLUE = '提取线索';//提取线索
    const GIVE_UP_CLUE = '放弃线索';//放弃线索
    const DISCARDED_CLUE = '废弃线索';//废弃线索
    const FOLLOW_UP_CLUE = '跟进线索';//跟进线索
    const CREATE_CUSTOMER = '转换客户';//转换客户
    const RECOVERY_CLUE = '回收线索';//回收线索
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clue_operation_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clue_id', 'creator_id', 'created_at'], 'integer'],
            [['content'], 'required'],
            [['content','item'], 'string'],
            [['creator_name'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clue_id' => 'Clue ID',
            'content' => 'Content',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'item' => '操作项',
        ];
    }

    //添加操作记录
    public function Create($clue_id,$item,$content,$creator_id,$creator_name)
    {
        if (is_array($clue_id)){
            foreach ($clue_id as  $v){
                //添加操作线索
                $operation = new ClueOperationRecord();
                $operation->clue_id = $v;
                $operation->content = $content;
                $operation->creator_id = $creator_id;
                $operation->creator_name = $creator_name;
                $operation->item = $item;
                $operation->created_at = time();
                $operation->save(false);
            }
        }else{
            //添加操作线索
            $operation = new ClueOperationRecord();
            $operation->clue_id = $clue_id;
            $operation->content = $content;
            $operation->creator_id = $creator_id;
            $operation->creator_name = $creator_name;
            $operation->item = $item;
            $operation->created_at = time();
            $operation->save(false);
        }
        return true;
    }



}
