<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_remark}}".
 *
 * @property integer $id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $order_id
 * @property string $remark
 * @property integer $created_at
 */
class OrderRemark extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_remark}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creator_id', 'order_id', 'created_at'], 'integer'],
            [['creator_name'], 'string', 'max' => 10],
            [['remark'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'order_id' => 'Order ID',
            'remark' => 'Remark',
            'created_at' => 'Created At',
        ];
    }
}
