<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "salesman".
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $qq
 * @property integer $status
 * @property integer $administrator_id
 * @property integer $company_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $updated_at
 *
  * @property Administrator $administrator
 */
class Salesman extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    /**
     * @return array
     * 添加时间
     * 修改时间
     */
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
        return '{{%salesman}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'administrator_id', 'company_id','creator_id', 'created_at', 'updated_at'], 'integer'],
            [['qq'], 'required'],
            [['name', 'creator_name'], 'string', 'max' => 10],
            ['email', 'string', 'max' => 64],
            ['qq', 'string', 'max' => 20],
            ['qq', 'string', 'min' => 5],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'qq' => 'QQ',
            'status' => 'Status',
            'administrator_id' => 'Administrator ID',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    public static function getStatusList()
    {
        return [
            static::STATUS_ACTIVE => '开通服务',
            static::STATUS_DISABLED => '暂停服务',
        ];
    }

    public function getStatusName()
    {
        $list = static::getStatusList();
        if(null === $this->status)
            $this->status = 0;
        return $list[$this->status];
    }

    public function isActive()
    {
        return $this->status == static::STATUS_ACTIVE;
    }
}
