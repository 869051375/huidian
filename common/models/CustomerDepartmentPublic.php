<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer_department_public".
 *
 * @property string $customer_public_id
 * @property string $customer_department_id
 */
class CustomerDepartmentPublic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_department_public}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_public_id', 'customer_department_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_public_id' => 'Customer Public ID',
            'customer_department_id' => 'Customer Department ID',
        ];
    }
}
