<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "{{%flow}}".
 *
 * @property integer $id
 * @property integer $is_publish
 * @property string $name
 * @property integer $status
 * @property integer $is_delete
 * @property integer $can_disable_sms
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property FlowNode[] $nodes
 * @property FlowNode $firstNode
 * @property FlowNode $lastNode
 */
class Flow extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    const DELETE_ACTIVE = 1;//删除
    const DELETE_NOT = 0;//未删除

    const CAN_ACTIVE_SMS = 1;//可以控制是否发送短信
    const CAN_DISABLE_SMS = 0;//不能控制是否发送短信

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%flow}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_publish', 'status', 'is_delete', 'can_disable_sms', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 15],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLED]],
            ['status', 'validateStatus'],
            [['is_delete'], 'in', 'range' => [self::DELETE_ACTIVE, self::DELETE_NOT]],
            ['is_delete', 'validateIsDelete'],
            [['can_disable_sms'], 'in', 'range' => [self::CAN_ACTIVE_SMS, self::CAN_DISABLE_SMS]],
        ];
    }

    public function validateStatus()
    {
        if(!$this->isPublished() && $this->status == self::STATUS_ACTIVE)
        {
            $this->addError('status', '当前流程尚未发布，不能启用。');
        }
    }

    public function validateIsDelete()
    {
        if($this->isUseFlow())
        {
            $this->addError('is_delete', '当前流程使用中，不能删除。');
        }

        if($this->isDeleted())
        {
            $this->addError('is_delete', '该流程已删除。');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_publish' => 'Is Publish',
            'name' => 'Name',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'can_disable_sms' => 'Can Disable Sms',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],

            ],
        ];
    }
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            if($insert){
                $this->creator_id = $user->id;
                $this->creator_name = $user->name;
                return true;
            }else{
                $this->updater_id = $user->id;
                $this->updater_name = $user->name;
                return true;
            }
        }
        return false;
    }

    public function getNode($node_id)
    {
        return FlowNode::find()->where(['id' => $node_id, 'flow_id' => $this->id])->one();
    }

    public function isPublished()
    {
        return $this->is_publish == 1;
    }

    public function isActive()
    {
        return $this->status == static::STATUS_ACTIVE;
    }

    public function isDeleted()
    {
        return $this->is_delete == static::DELETE_ACTIVE;
    }

    public function getStatusName()
    {
        $list = static::getStatusList();
        if(null === $this->status)
            $this->status = 0;
        return $list[$this->status];
    }

    public static function getStatusList()
    {
        return [
            static::STATUS_ACTIVE => '已启用',
            static::STATUS_DISABLED => '已禁用',
        ];
    }

    public function getNodes()
    {
        return static::hasMany(FlowNode::className(), ['flow_id' => 'id'])->orderBy(['sequence' => SORT_ASC]);
    }

    public function getFirstNode()
    {
        return static::hasOne(FlowNode::className(), ['flow_id' => 'id'])->orderBy(['sequence' => SORT_ASC]);
    }

    public function getLastNode()
    {
        return static::hasOne(FlowNode::className(), ['flow_id' => 'id'])->orderBy(['sequence' => SORT_DESC]);
    }

    public function publish()
    {
        $this->is_publish = 1;
        return $this->save(false);
    }

    /**
     * @return Query
     */
    public static function activeQuery()
    {
        return static::find()->where(['is_publish'=> 1, 'status' => self::STATUS_ACTIVE]);
    }

    public function isUseFlow()
    {
        $model = Product::find()->where(['flow_id' => $this->id])->orderBy(['created_at' => SORT_DESC])->one();
        if($model)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
