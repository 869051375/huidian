<?php

namespace common\models;

/**
 * This is the model class for table "order_data".
 *
 * @property integer $order_id
 * @property string $price_detail
 */
class OrderData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'integer'],
            [['price_detail'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'price_detail' => 'Price Detail',
        ];
    }

    /**
     * 订单表不常用字段存储到此表
     * @param $order_id
     * @param $price_detail
     * @throws \Exception
     */
    public static function createData($order_id,$price_detail)
    {
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            $orderData = new OrderData();
            $orderData->order_id = $order_id;
            $orderData->price_detail = $price_detail;
            $t->commit();
            $orderData->save(false);
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}
