<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Clerk;
use common\models\Order;
use common\models\OrderFile;
use common\models\OrderRecord;
use Yii;
use yii\base\Model;

class OrderFileSaveForm extends Model
{
    public $order_id;
    public $file_id;
    public $remark;
    public $is_see = 0;

    /**
     * @var Clerk
     */
    public $clerk;

    /**
     * @var OrderFile
     */
    public $file;

    /**
     * @var Order
     */
    public $order;

    public function rules()
    {
        return [
            [['remark'], 'trim'],
            [['order_id'], 'required', 'message' => '订单号错误'],
            [['file_id'], 'required', 'message' => '请上传文件'],
            //[['remark'], 'required'],
            [['remark'], 'safe'],
            [['order_id'], 'validateOrderId'],
            [['file_id'], 'validateFileId'],
            ['is_see', 'boolean'],
        ];
    }

    public function validateOrderId()
    {
        if($this->order) return ;
        //2018.6.21修改去除条件：去除文件上传时校验当前上传者是否是该订单的服务人员。
//        $this->order = Order::find()->where(['id' => $this->order_id, 'clerk_id' => $this->clerk->id])->one();
//        if(null == $this->order)
//        {
//            $this->addError('order_id', '该订单不存在或不是您服务的订单。');
//        }
    }

    public function validateFileId()
    {
        $this->file = OrderFile::find()->where(['id' => $this->file_id, 'order_id' => $this->order_id])->one();
        if(null == $this->file)
        {
            $this->addError('file_id', '请上传文件');
        }
    }

    public function save()
    {
        if(!$this->validate()) return null;
        if(!empty($this->remark))
        {
            $this->file->remark = $this->remark;
            if(!$this->file->save(false)) return false;
        }
        if($this->is_see == 1)
        {
            $this->file->is_internal = OrderFile::INTERNAL_DISABLED;
        }
        else
        {
            $this->file->is_internal = OrderFile::INTERNAL_ACTIVE;
        }
        $order_is_internal = $this->file->is_internal;
        if(!$this->file->save(false)) return false;
        //新增订单记录
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        OrderRecord::create($this->file->order_id, '上传文件', '', $admin, 0, $order_is_internal, $this->file->id);
        return $this->file;
    }

    public function attributeLabels()
    {
        return [
            'remark' => '备注',
            'is_see' => '勾选则前台客户可见和可下载，不勾选则前台客户不可见和不可下载。',
        ];
    }
}