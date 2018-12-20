<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;

/**
 * This is the model class for table "featured_image".
 *
 * @property integer $id
 * @property string $image
 * @property integer $featured_item_id
 */
class FeaturedImage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%featured_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['featured_item_id'], 'integer'],
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
        ];
    }

    public function getImageUrl($w, $h)
    {
        if(empty($this->image))return null;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($this->image, [
            'width' => $w,
            'height' => $h,
        ]);
    }
}
