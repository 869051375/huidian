<?php
namespace backend\models;

use common\models\Flow;
use yii\base\Model;

class FlowPublishForm extends Model
{
    /**
     * @var Flow
     */
    public $flow;

    public function rules()
    {
        return [
            [['flow'], 'validateFlow'],
        ];
    }

    public function validateFlow()
    {
        if($this->flow->isPublished())
        {
            $this->addError('flow', '已经发布了');
        }
        else if(0 >= count($this->flow->nodes))
        {
            $this->addError('flow', '没有节点');
        }
        else
        {
            foreach ($this->flow->nodes as $node)
            {
                if(count($node->actions) <= 0){
                    $this->addError('flow', '没有节点操作');
                }
            }
        }
    }

    public function publish()
    {
        return $this->flow->publish();
    }
}
