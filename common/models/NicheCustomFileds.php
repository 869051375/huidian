<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_custom_fileds".
 *
 * @property int $id 自增id
 * @property int $administrator_id 人员ID
 * @property string $fileds 字段
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $type 类型
 */
class NicheCustomFileds extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_custom_fileds';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['administrator_id', 'created_at', 'updated_at'], 'integer'],
            [['fileds'], 'string', 'max' => 300],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'administrator_id' => 'Administrator ID',
            'fileds' => 'Fileds',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
