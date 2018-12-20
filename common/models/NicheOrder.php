<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "niche_order".
 *
 * @property int $niche_id 商机ID
 * @property int $order_id 订单ID
 */
class NicheOrder extends \yii\db\ActiveRecord
{

    public $contract;
    public $status;
    public $progress;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'niche_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['niche_id', 'order_id'], 'required'],
            [['niche_id', 'order_id'], 'integer'],
            [['niche_id', 'order_id'], 'unique', 'targetAttribute' => ['niche_id', 'order_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'niche_id' => 'Niche ID',
            'order_id' => 'Order ID',
        ];
    }

    public function getNiche()
    {
        return static::hasOne(Niche::className(), ['id' => 'niche_id']);
    }
}
