<?php

namespace common\models;

use common\components\OSS;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%order_file}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $flow_id
 * @property integer $flow_node_id
 * @property integer $flow_action_id
 * @property string $remark
 * @property string $files
 * @property integer $is_customer
 * @property integer $clerk_id
 * @property string $clerk_name
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $is_internal
 * @property integer $created_at
 */
class OrderFile extends \yii\db\ActiveRecord
{
    const INTERNAL_ACTIVE = 1;//仅内部后台查看
    const INTERNAL_DISABLED = 0;//前后台都可查看

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'flow_id', 'flow_node_id', 'flow_action_id', 'is_customer', 'clerk_id', 'creator_id', 'created_at'], 'integer'],
            [['files', 'clerk_name', 'creator_name'], 'string'],
            [['remark'], 'string', 'max' => 200],
            ['is_internal', 'boolean'],
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
            'remark' => '文件备注',
            'files' => '文件，json格式',
            'is_customer' => '是否客户上传',
            'clerk_id' => '服务人员id',
            'clerk_name' => '服务人员名字',
            'creator_id' => '操作人员id',
            'creator_name' => '操作人名字',
            'created_at' => '上传时间',
            'is_internal' => 'Is Internal',
        ];
    }

    public function addFile($key, $name)
    {
        $files = $this->getFiles();
        $has = false;
        foreach($files as $file)
        {
            if($file['key'] == $key)
            {
                $has = true;
            }
        }
        if(!$has)
        {
            $files[] = [
                'key' => $key,
                'name' => $name
            ];
        }
        $this->setFiles($files);
    }

    public function setFiles($files)
    {
        $this->files = Json::encode($files);
    }

    public function getFiles()
    {
        if(empty($this->files)) return [];
        return Json::decode($this->files);
    }

    public function removeFile($key)
    {
        $files = $this->getFiles();
        foreach ($files as $k=>$file)
        {
            if($file['key'] == $key)
            {
                unset($files[$k]);
            }
        }
        $this->setFiles($files);
    }

    /**
     * @return bool 是否为客户上传
     */
    public function isCustomer()
    {
        return $this->is_customer == 1;
    }

    public static function getUrl($key, $timeout = 3600)
    {
        /** @var OSS $oss */
        $oss = Yii::$app->get('oss');
        return $oss->getUrl($key, $timeout);
    }
}
