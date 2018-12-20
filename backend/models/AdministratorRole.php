<?php

namespace backend\models;

use common\models\Administrator;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%administrator_role}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $type 同Administrator中的type
 * @property string $status
 * @property integer $created_at
 */
class AdministratorRole extends \yii\db\ActiveRecord
{
    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 0;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => false,
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%administrator_role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 10],
            [['name'], 'unique'],
            [['type'], 'integer'],
            [['status'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '角色名称',
            'type' => '角色类型',
            'status' => '状态',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param  $type
     * @return array
     */
    public function getAll($type = null)
    {
        $query = AdministratorRole::find();
        if($type)
        {
            $query->where(['type' => $type,'status' => self::STATUS_ONLINE]);
        }
        return $query->all();
    }

    public function getRoleType()
    {
        $typeList = Administrator::getTypes();
        if($this->type)
        {
            return $typeList[$this->type];
        }
        return null;
    }

}
