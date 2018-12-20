<?php

namespace common\models;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%call_center}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $company
 */
class CallCenter extends \yii\db\ActiveRecord
{
    const STATUS_OFFLINE = 0;//禁用
    const STATUS_ONLINE = 1;//启用

    public $company_id;
    public $debugging;
    public $update_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%call_center}}';
    }

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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'created_at', 'updated_at', 'company_id'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['url'], 'string', 'max' => 255],
            [['name'], 'required', 'on' => ['insert', 'update']],
            [['url'], 'required', 'on' => 'update'],
            [['url'], 'url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '描述名称',
            'url' => '对接地址',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'company_id' => '所属公司',
            'debugging' => '调试参数',
        ];
    }

    public function getCompany()
    {
        return static::hasMany(CallCenterAssignCompany::className(), ['call_center_id' => 'id']);
    }

    public function isOnline()
    {
        return $this->status == self::STATUS_ONLINE;
    }
}
