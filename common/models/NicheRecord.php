<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_record".
 *
 * @property int $id 自增id
 * @property int $niche_id 商机表ID
 * @property string $content 跟进记录内容
 * @property int $next_follow_time 下次跟进时间
 * @property int $creator_id 跟进人ID
 * @property string $creator_name 跟进人名字
 * @property int $created_at 跟进时间
 * @property int $follow_mode_id 跟进方式ID
 * @property string $follow_mode_name 跟进方式名称
 * @property int $start_at 开始时间
 * @property int $end_at 结束时间
 */
class NicheRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'next_follow_time', 'creator_id', 'created_at', 'follow_mode_id', 'start_at', 'end_at'], 'integer'],
            [['content'], 'string','tooLong'=>500],
            [['creator_name', 'follow_mode_name'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'niche_id' => 'Niche ID',
            'content' => 'Content',
            'next_follow_time' => 'Next Follow Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'follow_mode_id' => 'Follow Mode ID',
            'follow_mode_name' => 'Follow Mode Name',
            'start_at' => 'Start At',
            'end_at' => 'End At',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['next_follow_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->next_follow_time == 0 ? null : $this->next_follow_time);
        };
        $fields['created_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->created_at == 0 ? null : $this->created_at);
        };
        $fields['start_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->start_at == 0 ? null : $this->start_at);
        };
        $fields['end_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->end_at == 0 ? null : $this->end_at);
        };
        return $fields;

    }

}
