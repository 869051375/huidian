<?php

namespace common\models;

use common\biztraits\FlowHint;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%flow_node}}".
 *
 * @property integer $id
 * @property integer $flow_id
 * @property string $name
 * @property boolean $is_limit_time
 * @property integer $limit_work_days
 * @property string $hint_customer
 * @property string $hint_operator
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property string $sequence
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Flow $flow
 * @property FlowNodeAction[] $actions
 * @property FlowNode $nextNode
 */
class FlowNode extends \yii\db\ActiveRecord
{
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
        return '{{%flow_node}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flow_id', 'limit_work_days', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 8],
            [['hint_customer', 'hint_operator'], 'string', 'max' => 255],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
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
            'name' => 'Name',
            'limit_work_days' => 'Limit Work Days',
            'hint_customer' => 'Hint Customer',
            'hint_operator' => 'Hint Operator',
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
                $maxSort = static::find()->where('flow_id=:flow_id', [':flow_id' => $this->flow_id])
                    ->orderBy(['sequence' => SORT_DESC])->select('sequence')->limit(1)->scalar();
                $this->sequence = $maxSort + 10; // 加10 表示往后排（因为越大越靠后）
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
        foreach ($this->actions as $action)
        {
            $action->delete();
        }
        return parent::delete();
    }

    public function getFlow()
    {
        return static::hasOne(Flow::className(), ['id' => 'flow_id']);
    }

    public function getNextNode()
    {
        return static::hasOne(FlowNode::className(), ['flow_id' => 'flow_id'])
            ->orderBy(['sequence' => SORT_ASC])->where('sequence > :sequence', [':sequence' => $this->sequence]);
    }

    public function getActions()
    {
        return static::hasMany(FlowNodeAction::className(), ['flow_node_id' => 'id']);
    }

    public function isLimitTime()
    {
        return $this->is_limit_time == 1;
    }

}
