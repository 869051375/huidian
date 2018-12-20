<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;

/**
 * This is the model class for table "order_voucher".
 *
 * @property integer $order_id
 * @property string $image
 */
class OrderVoucher extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_voucher}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id'], 'integer'],
            [['image'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'image' => 'Image',
        ];
    }

    public function getImageUrl($w, $h, $m)
    {
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($this->image, [
            'width' => $w,
            'height' => $h,
            'mode' => $m,
        ]);
    }

    public static function createVoucher($order_id,$image)
    {
        $orderVoucher = new OrderVoucher();
        $orderVoucher->order_id = $order_id;
        $orderVoucher->image = $image;
        $orderVoucher->save(false);
    }
}
