<?php

namespace common\models;

/**
 * This is the model class for table "{{%trademark_search_record}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $trademark_word
 * @property integer $created_at
 *
 * @property User $user
 */
class TrademarkSearchRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trademark_search_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at'], 'integer'],
            [['trademark_word'], 'string', 'max' => 40],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'trademark_word' => '查询词',
            'created_at' => '创建时间戳',
        ];
    }

    public function getUser()
    {
        return static::hasOne(User::className(), ['id' => 'user_id']);
    }
}
