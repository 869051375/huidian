<?php

namespace common\models;

use yii\helpers\Json;

/**
 * This is the model class for table "{{%order_flow_record}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $flow_id
 * @property integer $flow_node_id
 * @property integer $flow_action_id
 * @property string $flow_action_name
 * @property integer $limited_time
 * @property integer $clerk_id
 * @property string $clerk_name
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $ignore_limited_time
 * @property string $input_text
 * @property string $input_date
 * @property integer $file_id
 * @property integer $created_at
 *
 * @property FlowNodeAction $action
 * @property OrderFile $orderFile
 */
class OrderFlowRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_flow_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'flow_id', 'flow_node_id', 'flow_action_id', 'flow_action_name', 'limited_time', 'clerk_id', 'creator_id', 'ignore_limited_time', 'input_text', 'file_id', 'created_at'], 'integer'],
            [['input_date'], 'required'],
            [['input_date'], 'safe'],
            [['clerk_name', 'creator_name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'order_id' => '订单id',
            'flow_id' => '流程id',
            'flow_node_id' => '流程节点id',
            'flow_action_id' => '操作id',
            'flow_action_name' => '操作名称',
            'limited_time' => '限制完成时间',
            'clerk_id' => '服务人员id',
            'clerk_name' => '服务人员名字',
            'creator_id' => '操作人员id',
            'creator_name' => '操作人名字',
            'ignore_limited_time' => '是否忽略限制完成时间',
            'input_text' => '输入框内容，按照json格式保存',
            'input_date' => '日期输入内容',
            'file_id' => '当次操作文件上传id',
            'created_at' => '实际操作时间',
        ];
    }

    public function getAction()
    {
        return static::hasOne(FlowNodeAction::className(), ['id' => 'flow_action_id']);
    }

    public function getOrderFile()
    {
        return static::hasOne(OrderFile::className(), ['id' => 'file_id']);
    }

    /**
     * @return array
     */
    public function getInputText()
    {
        if(empty($this->input_text)) return [];
        return (array)Json::decode($this->input_text);
    }

    /**
     * @param array $inputTextList
     */
    public function setInputText($inputTextList)
    {
        $this->input_text = Json::encode($inputTextList);
    }
}
