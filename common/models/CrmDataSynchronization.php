<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_data_synchronization".
 *
 * @property int $id id
 * @property int $clue_public_id 线索公海ID
 * @property int $customer_public_id 客户公海ID
 */
class CrmDataSynchronization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'crm_data_synchronization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clue_public_id', 'customer_public_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clue_public_id' => 'Clue Public ID',
            'customer_public_id' => 'Customer Public ID',
        ];
    }

    public function add()
    {
        /** @var CrmDataSynchronization $data */
        $data = CrmDataSynchronization::find()->one();
        if ($data){
            $data->clue_public_id = $this->clue_public_id;
            $data->customer_public_id = $this->customer_public_id;
            $data->save();
        }else{
            $this->save();
        }
        return true;
    }
}
