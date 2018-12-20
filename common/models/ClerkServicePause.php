<?php

namespace common\models;

/**
 * This is the model class for table "{{%clerk_service_pause}}".
 *
 * @property integer $id
 * @property integer $clerk_id
 * @property string $product_id
 * @property integer $district_id
 * @property integer $created_at
 */
class ClerkServicePause extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%clerk_service_pause}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clerk_id', 'district_id', 'created_at'], 'integer'],
            [['product_id'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clerk_id' => 'Clerk ID',
            'product_id' => 'Product ID',
            'district_id' => 'District ID',
            'created_at' => 'Created At',
        ];
    }
}
