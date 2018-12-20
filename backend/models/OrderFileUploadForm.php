<?php
namespace backend\models;

use common\components\OSS;
use common\models\Administrator;
use common\models\Clerk;
use common\models\FlowNode;
use common\models\FlowNodeAction;
use common\models\Order;
use common\models\OrderFile;
use OSS\OssClient;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class OrderFileUploadForm extends Model
{
    public $order_id;
    public $flow_id = 0;
    public $node_id = 0;
    public $action_id = 0;
    public $remark;
    public $file;

    public $file_id; // 可为空，如果不为空，则在原有文件基础上添加文件

    /**
     * @var FlowNodeAction
     */
    public $action;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var FlowNode
     */
    public $node;

    /**
     * @var Clerk
     */
    public $clerk;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            //[['remark'], 'trim'],
            [['order_id'], 'required'],
            [['flow_id', 'node_id', 'action_id'], 'required', 'on' => 'flow'],
            [['order_id'], 'validateOrderId', 'on' => 'flow'],
            [['flow_id'], 'validateFlowId', 'on' => 'flow'],
            [['node_id'], 'validateNodeId', 'on' => 'flow'],
            [['action_id'], 'validateActionId', 'on' => 'flow'],
            ['file_id', 'integer'],
            [['file'], 'file', 'maxSize' => 10*1024*1024, 'tooBig' => '文件大小不能超过10MB'],
        ];
    }

    public function beforeValidate()
    {
        if(parent::beforeValidate())
        {
            return true;
        }
        return false;
    }

    public function validateOrderId()
    {
        if($this->order) return ;
        //2018.6.21修改去除条件：去除文件上传时校验当前上传者是否是该订单的服务人员。
        $this->order = Order::find()->where(['id' => $this->order_id, 'clerk_id' => $this->clerk->id])->one();
        if(null == $this->order)
        {
            $this->addError('order_id', '该订单不存在或不是您服务的订单。');
        }
    }

    public function validateFlowId()
    {
        if($this->order->flow_id != $this->flow_id)
        {
            $this->addError('flow_id', '订单流程不正确。');
        }
    }

    public function validateNodeId()
    {
        if($this->node) return ;
        $this->node = FlowNode::find()->where(['id' => $this->node_id, 'flow_id' => $this->flow_id])->one();
        if(null == $this->node)
        {
            $this->addError('node_id', '当前流程节点错误。');
        }
    }

    public function validateActionId()
    {
        if($this->action) return ;
        $this->action = FlowNodeAction::find()->where(['id' => $this->action_id, 'flow_node_id' => $this->node_id, 'flow_id' => $this->flow_id])->one();
        if(null == $this->action)
        {
            $this->addError('action_id', '流程操作错误。');
        }
    }

    public function upload()
    {
        if(!$this->validate()) return null;
        /** @var OSS $oss */
        $oss = Yii::$app->get('oss');
        /** @var UploadedFile $uploadFile */
        $uploadFile = $this->file;
        $key = $this->order_id.'/'.md5($uploadFile->name).time().'.'.$uploadFile->extension;
        $oss->upload($key, $uploadFile->tempName, [OssClient::OSS_HEADERS => [OssClient::OSS_CONTENT_DISPOSTION => 'attachment;filename="'.$uploadFile->name.'"']]);
        $file = null;
        if($this->file_id)
        {
            /** @var OrderFile $file */
            $file = OrderFile::find()->where(['id' => $this->file_id, 'order_id' => $this->order_id])->one();
        }
        if(null == $file)
        {
            $file = new OrderFile();
            $file->order_id = $this->order_id;
            $file->flow_id = $this->flow_id;
            $file->flow_node_id = $this->node_id;
            $file->flow_action_id = $this->action_id;
            //$file->remark = $this->remark;
            $file->is_customer = 0;
            //$file->clerk_id = $this->clerk->id;
            //$file->clerk_name = $this->clerk->name;
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            $file->creator_id = $admin->id;
            $file->creator_name = $admin->name;
        }
        $file->created_at = time();
        $file->addFile($key, $uploadFile->name);
        $file->save(false);
        return [
            'id' => $file->id,
            'key' => $key,
            'name' => $uploadFile->name,
            'url' => $oss->getUrl($key),
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => '上传文件'
        ];
    }
}