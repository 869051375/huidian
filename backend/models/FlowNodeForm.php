<?php
namespace backend\models;

use common\models\Flow;
use common\models\FlowNode;
use yii\base\Exception;
use yii\base\Model;

class FlowNodeForm extends Model
{
    public $flow_id; // 该字段要注意是需要的，不管新增还是编辑都必须提交这个字段，
    public $node_id; // 该字段要注意是需要的，如果是新增，则这个字段不需要提交，如果是编辑是需要提交这个字段
    public $name = ''; // 节点名字
    public $is_limit_time;
    public $limit_work_days = 0;
    public $hint_customer_title = '';
    public $hint_customer_content = '';
    public $hint_operator_title = '';
    public $hint_operator_content = '';

    /**
     * @var Flow
     */
    public $flow;

    /**
     * @var FlowNode
     */
    private $nodeModel;

    public function setFlow($flow)
    {
        $this->flow = $flow;
        $this->flow_id = $flow->id;
    }

    public function getFlow()
    {
        if(null == $this->flow) throw new Exception('请设置Flow对象');
        return $this->flow;
    }

    public function rules()
    {
        return [
            [['name', 'hint_customer_title', 'hint_customer_content',
                'hint_operator_title', 'hint_operator_content'], 'trim'],
            ['name', 'required'],
            [['name'], 'string', 'max'=>8],
            ['is_limit_time', 'boolean'],
            ['is_limit_time', 'validateIsLimitTime'],
            [['limit_work_days', 'name'], 'required'],
            [['hint_operator_content'], 'string', 'max'=>60],
            [['hint_customer_title'], 'string', 'max'=>30],
            [['hint_customer_content'], 'string', 'max'=>80],
            [['limit_work_days'], 'integer', 'min'=>0, 'max'=>60000],
            // 注意要校验已经发布的（is_publish）不能编辑和新增node。
            ['flow_id', 'validateFlow'],
            ['limit_work_days', 'default', 'value' => 0],
        ];
    }

    public function validateIsLimitTime()
    {
        if(0 == $this->is_limit_time)
        {
            $this->limit_work_days = 0;
        }
    }

    public function validateFlow()
    {
//        if($this->getFlow()->isPublished())
//        {
//            $this->addError('name', '该流程已经发布无法操作!');
//        }
    }

    public function attributeLabels()
    {
        return [
            'name' => '流程节点名称',
            'limit_work_days' => '控制周期',
            'is_limit_time' => '需要时间控制',
            'hint_customer_title' => '前台提示信息',
            'hint_customer_content' => '',
            'hint_operator_title' => '',
            'hint_operator_content' => '后台提示信息',
        ];
    }

    //todo
    public function attributeHints()
    {
        return [
            'limit_work_days' => '工作日',
            'hint_customer_content' => '此处输入流程进行到该节点时管理后台订单详情页订单状态处的提示内容，可用提示变量:%日期%,%输入框的文字提醒%,%邮寄地址%',
            'hint_operator_title' => '此处输入流程进行到该节点时前台用户中心订单详情页订单状态处的提示内容标题，可用提示变量:%日期%,%输入框的文字提醒%,%邮寄地址%',
            'hint_operator_content' => '此处输入流程进行到该节点时前台用户中心订单详情页订单状态处的提示内容，可用提示变量:%日期%,%输入框的文字提醒%,%邮寄地址%',
        ];
    }

    /**
     * @return FlowNode | null 创建或保存成功则返回对应的FlowNode对象，如果失败，则返回null
     */
    public function save()
    {
        $model = null;
        if($this->nodeModel)
        {
            $model = $this->nodeModel;
        }
        else
        {
            $model = new FlowNode();
            $model->flow_id = $this->flow_id;
        }

        $model->name = $this->name;
        $model->is_limit_time = $this->is_limit_time;
        $model->limit_work_days = $this->limit_work_days;
        $model->setHintCustomer($this->hint_customer_title, $this->hint_customer_content);
        $model->setHintOperator($this->hint_operator_title, $this->hint_operator_content);
        if($model->save(false)) return $model;
        return null;
    }

    /**
     * @param FlowNode $nodeModel
     */
    public function setModel($nodeModel)
    {
        // 这里用于编辑FlowNode时，从这里初始化表单模型的数据
        $this->nodeModel = $nodeModel;
        $this->flow_id = $nodeModel->flow_id;
        $this->node_id = $nodeModel->id;
        $this->name = $nodeModel->name;
        $this->is_limit_time = $nodeModel->is_limit_time;
        $this->limit_work_days = $nodeModel->limit_work_days;
        $hintCustomer = $nodeModel->getHintCustomer();
        $hintOperator = $nodeModel->getHintOperator();
        $this->hint_customer_title = $hintCustomer['title'];
        $this->hint_customer_content = $hintCustomer['content'];
        $this->hint_operator_title = $hintOperator['title'];
        $this->hint_operator_content = $hintOperator['content'];
    }

}