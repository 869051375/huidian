<?php

namespace common\models;

/**
 * This is the model class for table "{{%check_name_record}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $company_name
 * @property string $possibility
 * @property integer $created_at
 *
 * @property User $user
 */
class CheckNameRecord extends \yii\db\ActiveRecord
{
    const POSSIBILITY_LOW = 0;
    const POSSIBILITY_MEDIUM = 1;
    const POSSIBILITY_HIGH = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%check_name_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at'], 'integer'],
            [['company_name'], 'string', 'max' => 40],
            [['possibility'], 'in', 'range' => [self::POSSIBILITY_LOW, self::POSSIBILITY_MEDIUM, self::POSSIBILITY_HIGH]],
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
            'company_name' => '预查名称',
            'possibility' => '通过可能性',
            'created_at' => '创建时间戳',
        ];
    }

    public function getUser()
    {
        return static::hasOne(User::className(), ['id' => 'user_id']);
    }
}
