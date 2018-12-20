<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_contract".
 *
 * @property int $niche_id 商机ID
 * @property int $contract_id 合同ID
 * @property Niche $niche
 */
class NicheContract extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_contract';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'contract_id'], 'required'],
            [['niche_id', 'contract_id'], 'integer'],
            [['niche_id', 'contract_id'], 'unique', 'targetAttribute' => ['niche_id', 'contract_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'niche_id' => 'Niche ID',
            'contract_id' => 'Contract ID',
        ];
    }

    public function getNiche()
    {
        return $this->hasOne(Niche::className(),['id' => 'niche_id']);
    }


}
