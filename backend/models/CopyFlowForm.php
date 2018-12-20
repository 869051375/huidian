<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/19
 * Time: 上午11:36
 */

namespace backend\models;

use common\models\Flow;
use common\models\FlowNode;
use common\models\FlowNodeAction;
use yii\base\Model;

class CopyFlowForm extends Model
{
    public $flow_id;

    public $name;

    /**
     * @var Flow
     */
    public $flow;

    public function rules()
    {
        return [
            [['flow_id', 'name'], 'required'],
            [['name'], 'string', 'max' => 15],
            [['flow_id'], 'validateFlowId'],
        ];
    }

    public function validateFlowId()
    {
        $this->flow = Flow::findOne($this->flow_id);
        if(null === $this->flow)
        {
            $this->addError('flow_id', '找不到要复制的流程');
        }
    }

    public function attributeLabels()
    {
        return [
            'name' => '新流程名称',
        ];
    }

    public function copy()
    {
        if(!$this->validate()) return false;
        $flow = new Flow();
        foreach($this->flow->attributes as $attribute => $value)
        {
            $flow->$attribute = $value;
        }
        $flow->is_publish = 0;
        $flow->status = Flow::STATUS_DISABLED;
        $flow->name = $this->name;
        $flow->id = null;
        $flow->save(false);
        foreach ($this->flow->nodes as $node)
        {
            $flowNode = new FlowNode();
            foreach($node->attributes as $attribute => $value)
            {
                $flowNode->$attribute = $value;
            }
            $flowNode->flow_id = $flow->id;
            $flowNode->id = null;
            $flowNode->save(false);
            foreach ($node->actions as $action)
            {
                $flowNodeAction = new FlowNodeAction();
                foreach($action->attributes as $attribute => $value)
                {
                    $flowNodeAction->$attribute = $value;
                }
                $flowNodeAction->flow_id = $flow->id;
                $flowNodeAction->flow_node_id = $flowNode->id;
                $flowNodeAction->id = null;
                $flowNodeAction->save(false);
            }
        }
        return $flow;
    }
}