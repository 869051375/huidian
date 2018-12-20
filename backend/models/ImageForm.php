<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\ProductImage;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Class ProductForm
 * @package backend\models
 *
 */
class ImageForm extends Model
{
    public $file;
    public $image;
    public $product_id;
    public $type;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['file'], 'file'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => '图片',
            'image' => '',
        ];
    }

    /**
     * @return ProductImage|null
     * @throws NotFoundHttpException
     */
    public function save()
    {
        $model = ProductImage::find()->where(['product_id' => $this->product_id, 'type' => $this->type])->one();
        /** @var ProductImage $model */
        $model = $model ? $model : new ProductImage();
        $model->product_id = $this->product_id;
        $model->image = $this->image;
        $model->type = $this->type;
        if($model->save())
        {
            //新增后台操作日志
            AdministratorLog::logUploadProductImage($model);
            return $model;
        }
        return null;
    }
}
