<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clue_record".
 *
 * @property integer $id
 * @property integer $clue_id
 * @property string $content
 * @property integer $next_follow_time
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $follow_mode_id
 * @property string $follow_mode_name
 */
class ClueRecord extends \yii\db\ActiveRecord
{

    const RECORD_MODE = [
        [
            'id'=>1,
            'name'=>'打电话'
        ],
        [
            'id'=>2,
            'name'=>'见面拜访'
        ],
        [
            'id'=>3,
            'name'=>'发邮件'
        ],
        [
            'id'=>4,
            'name'=>'发短信'
        ],
        [
            'id'=>5,
            'name'=>'其他'
        ],
    ];



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clue_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clue_id', 'next_follow_time', 'start_at','end_at','creator_id', 'created_at', 'follow_mode_id'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string', 'max' => 500,'tooLong'=>'跟进内容长度不允许超过500个文字。'],
            [['creator_name', 'follow_mode_name'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clue_id' => 'Clue ID',
            'content' => 'Content',
            'next_follow_time' => 'Next Follow Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'follow_mode_id' => 'Follow Mode ID',
            'follow_mode_name' => 'Follow Mode Name',
        ];
    }
}
