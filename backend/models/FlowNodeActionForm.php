<?php
namespace backend\models;

use common\models\FlowNode;
use common\models\FlowNodeAction;
use yii\base\Model;
use yii\validators\StringValidator;

class FlowNodeActionForm extends Model
{
    public $action_id;
    public $flow_id;
    public $flow_node_id;
    public $type;
    public $action_label;
    public $action_hint;
    public $input_list;
    public $input_type_list; // is_company
    public $is_stay;
    public $hint_customer_title;
    public $hint_customer_content;
    public $hint_operator_title;
    public $hint_operator_content;
    public $sms_id;
    public $sms_preview;
    public $has_send_var;

    /**
     * @var FlowNodeAction
     */
    private $model;

    /**
     * @var FlowNode
     */
    private $node;

    public function rules()
    {
        return [
            [['action_label', 'action_hint', 'hint_customer_title', 'hint_customer_content', 'hint_operator_title',
                'hint_operator_content', 'sms_id', 'sms_preview'], 'trim'],
            [['flow_node_id', 'type', 'action_label', 'is_stay', ], 'required'],
            [['action_label'], 'string', 'max'=>8],
            [['action_hint'], 'string', 'max'=>15],
            [['hint_operator_content'], 'string', 'max'=>60],
            [['hint_customer_title'], 'string', 'max'=>30],
            [['hint_customer_content'], 'string', 'max'=>80],
            [['sms_id'], 'string', 'max'=>30],
            ['input_list', 'validateInputList'],
            [['has_send_var'], 'boolean'],
            ['input_type_list', 'validateInputType'],
            ['flow_node_id', 'validateFlowNodeId'],
            ['type', 'in', 'range' => array_keys(FlowNodeAction::getTypeList())],
            ['type', 'validateType'],
            ['is_stay', 'validateIsStay'],
            ['flow_node_id', 'validateFlowNodeId'],
        ];
    }

    public function validateType()
    {
        if($this->type == FlowNodeAction::TYPE_BUTTON)
        {
            $this->action_hint = '';
        }
    }

    public function validateIsStay()
    {
        if((int)$this->is_stay == 0)
        {
            $this->hint_customer_content = '';
            $this->hint_customer_title = '';
            $this->hint_operator_content = '';
            $this->hint_operator_title = '';
        }
    }

    public function validateFlow()
    {
        if($this->node->flow->isPublished())
        {
            $this->addError('name', '该流程已经发布无法操作。');
        }
    }

    public function validateFlowNodeId()
    {
        if(count($this->node->actions) > 1 && $this->node->isNewRecord)
        {
            $this->addError('flow_node_id', '流程节点中最多只能添加两个按钮。');
        }
    }

    public function attributeLabels()
    {
        $hintLabel = '提示信息';
        if($this->model && $this->model->isTypeUpload())
        {
            $hintLabel = '上传文件文字提醒';
        }
        else if($this->model && $this->model->isTypeDate())
        {
            $hintLabel = '时间选框文字提醒';
        }

        return [
            'type' => '类型',
            'action_label' => '按钮上显示的文字',
            'action_hint' => $hintLabel,
            'input_list' => '输入框的文字提醒',
            'input_type_list' => '输入内容为公司名',
            'is_stay' => '停留在当前操作',

            'hint_customer_title' => '前台提示信息',
            'hint_customer_content' => '',
            'hint_operator_title' => '',
            'hint_operator_content' => '后台提示信息',

            'sms_id' => '短信平台模板ID',
            'sms_preview' => '',
            'has_send_var' => '短信模板包含收件参数',
        ];
    }

    public function validateInputList()
    {
        foreach ($this->input_list as $k => $v){
            $sv = new StringValidator();
            $sv->max = 15;
            if(!$sv->validate($v))
            {
                $this->addError('input_list', '输入框的文字提醒最多15字符，第'.($k+1).'条已超出15个字符。');
            }
        }
    }
    public function validateInputType()
    {

    }
    public function attributeHints()
    {
        return [
            'hint_customer_content' => '此处输入点击该按钮后管理后台订单详情页订单状态处的提示内容，可用提示变量:<code>%日期%</code>,<code>%{输入框的文字提醒}%</code>,<code>%邮寄地址%</code>',
            'hint_operator_title' => '此处输入点击该按钮后前台用户中心订单详情页订单状态处的提示内容标题，可用提示变量:<code>%日期%</code>,<code>%{输入框的文字提醒}%</code>,<code>%邮寄地址%</code>',
            'hint_operator_content' => '此处输入点击该按钮后前台用户中心订单详情页订单状态处的提示内容，可用提示变量:<code>%日期%</code>,<code>%{输入框的文字提醒}%</code>,<code>%邮寄地址%</code>',
            'sms_preview' => '此处输入该按钮点击后触发的短信预览模板与云通讯中的一致，此处仅用于预览和订单短信记录，变量替换规则：<br />
<code>1.日期输入框当勾选包含收件参数时，{1}为地址，{2}为收件人，{3}为电话，{4}表示所选日期，
<br />
{5}、{6}...依次为输入框内容；</code>
<code>2.日期输入框当未勾选包含收件参数时，{1}表示所选日期，{2}、{3}...依次为输入框内容；</code>
<code>3.文件上传类型，{1}为地址，{2}为收件人，{3}为电话，{4}、{5}...依次为输入框；</code>
<code>4.普通按钮当勾选包含收件参数时，{1}为地址，{2}为收件人，{3}为电话，{4}、{5}...依次为输入框；</code>
<code>5.普通按钮当未勾选包含收件参数时，{1}、{2}...依次为输入框。</code>',
            'has_send_var' => '表示短信模板中是否支持使用收件参数。'
        ];
    }

    /**
     * @param FlowNode $node
     */
    public function setNodeModel($node)
    {

        $this->node = $node;
        $this->flow_node_id = $node->id;
        $this->flow_id = $node->flow_id;
    }

    /**
     * @param FlowNodeAction $model
     */
    public function setModel($model)
    {
        $this->setNodeModel($model->flowNode);
        $this->model = $model;

        $this->type = $model->type;
        $this->action_label = $model->action_label;
        $this->action_hint = $model->action_hint;

        $inputList = $model->getInputList();
        $this->input_list = $inputList['input_list'];
        $this->is_stay = $model->is_stay;

        $hintCustomer = $model->getHintCustomer();
        $hintOperator = $model->getHintOperator();

        $this->hint_customer_title = $hintCustomer['title'];
        $this->hint_customer_content = $hintCustomer['content'];
        $this->hint_operator_title = $hintOperator['title'];
        $this->hint_operator_content = $hintOperator['content'];

        $this->sms_id = $model->sms_id;
        $this->has_send_var = $model->has_send_var;
        $this->sms_preview = $model->sms_preview;
    }

    public function save()
    {
        $model = null;
        if($this->model)
        {
            $model = $this->model;
        }
        else
        {
            $model = new FlowNodeAction();
            $model->flow_id = $this->flow_id;
            $model->flow_node_id = $this->flow_node_id;
        }
        $model->type = $this->type;
        $model->action_label = $this->action_label;
        $model->action_hint = $this->action_hint;
        $model->is_stay = $this->is_stay;
        $model->setHintCustomer($this->hint_customer_title, $this->hint_customer_content);
        $model->setHintOperator($this->hint_operator_title, $this->hint_operator_content);
        $model->sms_id = $this->sms_id;
        $model->sms_preview = $this->sms_preview;
        $model->has_send_var = $this->has_send_var;
        $model->setInputList($this->input_list, $this->input_type_list);
        if($model->save(false)) return $model;
        return null;
    }
}