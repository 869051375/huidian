<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;

/**
 * This is the model class for table "product_image".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $image
 * @property integer $type
 * @property  Product $product
 */
class ProductImage extends \yii\db\ActiveRecord
{
    // 1：商品详情页图片，2：全部服务页图片，3：购物车图标，4：热门商品图片，5服务体验官图片，6推荐位图片(pc)，7推荐位图片（移动）
    const TYPE_DETAIL = 1;
    const TYPE_LIST = 2;
    const TYPE_CAR = 3;
    const TYPE_HOT = 4;
    const TYPE_EXPERIENCE = 5;
    const TYPE_FEATURED = 6;
    const TYPE_FEATURED_WAP = 7;
    const TYPE_HOT_WAP = 8;
    const TYPE_MOBILE_DETAIL = 9;//移动端商品详情页图片

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'type'], 'integer'],
            [['image'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'image' => 'Image',
            'type' => 'Type',
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

    public function getProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'product_id']);
    }
}
