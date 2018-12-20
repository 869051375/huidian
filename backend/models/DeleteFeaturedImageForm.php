<?php

namespace backend\models;

use common\models\AdministratorLog;
use common\models\FeaturedImage;
use common\models\Product;
use common\models\ProductImage;
use imxiangli\image\storage\ImageStorageInterface;
use yii\base\Model;
use yii\web\NotFoundHttpException;

class DeleteFeaturedImageForm extends Model
{
    public $image_id;

    /**
     * @var FeaturedImage
     */
    public $image;

    public function rules()
    {
        return [
            [['image_id'], 'required'],
            [['image_id'], 'validateImageId'],
        ];
    }

    public function validateImageId()
    {
        $this->image = FeaturedImage::findOne($this->image_id);
        if(null === $this->image)
        {
            $this->addError('image_id', '找不到指定的推荐位图片');
        }
    }

    public function delete()
    {
        if(!$this->validate()) return false;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        $imageStorage->delete($this->image->image);
        $this->image->delete();
        return true;
    }
}