<?php

namespace common\models;

use common\biztraits\FlowHint;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%flow_node_action}}".
 *
 * @property integer $id
 * @property integer $flow_id
 * @property integer $flow_node_id
 * @property integer $type
 * @property string $action_label
 * @property string $action_hint
 * @property string $input_list
 * @property integer $is_stay
 * @property string $hint_customer
 * @property string $hint_operator
 * @property string $sms_id
 * @property string $sms_preview
 * @property boolean $has_send_var
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Flow $flow
 * @property FlowNode $flowNode
 */
class FlowNodeAction extends \yii\db\ActiveRecord
{
    const TYPE_BUTTON = 1; // 按钮
    const TYPE_UPLOAD = 2; // 上传
    const TYPE_DATE = 3;   // 日期

    use FlowHint;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%flow_node_action}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flow_id', 'flow_node_id', 'type', 'is_stay', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['action_label'], 'string', 'max' => 8],
            [['action_hint'], 'string', 'max' => 15],
            [['input_list', 'hint_customer', 'hint_operator', 'sms_preview'], 'string', 'max' => 255],
            [['sms_id'], 'string', 'max' => 11],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
            [['has_send_var'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'flow_id' => 'Flow ID',
            'flow_node_id' => 'Flow Node ID',
            'type' => 'Type',
            'action_label' => 'Action Label',
            'action_hint' => 'Action Hint',
            'input_list' => 'Input List',
            'is_stay' => 'Is Stay',
            'hint_customer' => 'Hint Customer',
            'hint_operator' => 'Hint Operator',
            'sms_id' => 'Sms ID',
            'sms_preview' => 'Sms Preview',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var User $user */
            $user = Yii::$app->user->identity;
            if($insert)
            {
                $this->creator_id = $user->id;
                $this->creator_name = $user->name;
            }
            else
            {
                $this->updater_id = $user->id;
                $this->updater_name = $user->name;
            }
            return true;
        }
        return false;
    }

    public function delete()
    {
        if($this->flow->isPublished())
        {
            throw new Exception('流程已经发布无法删除');
        }
        return parent::delete();
    }

    public function isTypeButton()
    {
        return $this->type == self::TYPE_BUTTON;
    }

    public function isTypeUpload()
    {
        return $this->type == self::TYPE_UPLOAD;
    }

    public function isTypeDate()
    {
        return $this->type == self::TYPE_DATE;
    }

    /**
     * 短信模板中是否包含收件人信息变量（名称起的有点奇怪）
     * @return bool
     */
    public function isHasSendVar()
    {
        return $this->has_send_var == 1;
    }

    public static function getTypeList()
    {
        return [
            static::TYPE_BUTTON => '普通按钮',
            static::TYPE_UPLOAD => '资料上传',
            static::TYPE_DATE   => '日期选框',
        ];
    }

    public function getTypeName()
    {
        $list = static::getTypeList();
        return isset($list[$this->type]) ? $list[$this->type] : null;
    }

    public function getFlow()
    {
        return static::hasOne(Flow::className(), ['id' => 'flow_id']);
    }

    public function getFlowNode()
    {
        return static::hasOne(FlowNode::className(), ['id' => 'flow_node_id']);
    }

    public function isStay()
    {
        return $this->is_stay == 1;
    }


}
