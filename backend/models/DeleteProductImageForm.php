<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/19
 * Time: 上午11:36
 */

namespace backend\models;

use common\models\AdministratorLog;
use common\models\ProductImage;
use imxiangli\image\storage\ImageStorageInterface;
use yii\base\Model;

class DeleteProductImageForm extends Model
{
    public $image_id;

    /**
     * @var ProductImage
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
        $this->image = ProductImage::findOne($this->image_id);
        if(null === $this->image)
        {
            $this->addError('image_id', '找不到商品图片');
        }
    }

    public function delete()
    {
        if(!$this->validate()) return false;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        $imageStorage->delete($this->image->image);
        AdministratorLog::logDeleteProductImage($this->image);
        $this->image->delete();
        return true;
    }
}