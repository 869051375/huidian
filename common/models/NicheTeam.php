<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%niche_team}}".
 *
 * @property int $id 自增id
 * @property int $niche_id 商机表ID
 * @property int $administrator_id 负责人ID
 * @property string $administrator_name 负责人名称
 * @property int $is_update 是否有修改权限
 * @property int $sort 排序
 * @property int $create_at 创建时间
 * @property int $updated_at 最后修改时间
 */
class NicheTeam extends \yii\db\ActiveRecord
{
    public $yang;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%niche_team}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['create_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','niche_id', 'administrator_id', 'is_update', 'sort', 'create_at', 'updated_at'], 'integer'],
            [['administrator_name'], 'string', 'max' => 25],
        ];
    }


    public function getNiche()
    {
        return self::hasOne(Niche::className(), ['id' => 'niche_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增id',
            'niche_id' => '商机表ID',
            'administrator_id' => '负责人ID',
            'administrator_name' => '负责人名称',
            'is_update' => '是否有修改权限',
            'sort' => '排序',
            'create_at' => '创建时间',
            'updated_at' => '最后修改时间',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['create_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->create_at);
        };
        $fields['updated_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->updated_at);
        };
        return $fields;
    }

}
