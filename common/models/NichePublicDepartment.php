<?php

namespace common\models;


/**
 * This is the model class for table "niche_public_department".
 *
 * @property int $niche_public_id 商机公海ID
 * @property int $department_id 部门ID
 * @property NichePublic $nichePublic 获取公海
 */
class NichePublicDepartment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_public_department';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_public_id', 'department_id'], 'required'],
            [['niche_public_id', 'department_id'], 'integer'],
            [['niche_public_id', 'department_id'], 'unique', 'targetAttribute' => ['niche_public_id', 'department_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'niche_public_id' => 'Niche Public ID',
            'department_id' => 'Department ID',
        ];
    }


    public function getNichePublic()
    {
        return static::hasOne(NichePublic::className(), ['id' => 'niche_public_id']);
    }


}
