<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/18
 * Time: 上午11:28
 */

namespace common\actions;

use imxiangli\image\storage\ImageStorageInterface;
use yii\base\Action;
use yii\helpers\Json;
use yii\imagine\Image;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadImageAction extends Action
{
	public $fieldName = 'file';
	public $modelClass = null;
	public $keyTemplate = 'images/{date:Ymd}/{fileName:md5}_{time}.{ext}';
	public $thumbnailWidth = 240;
	public $thumbnailHeight = 240;
	public $mode = 1;

	public function run()
	{
		//\Yii::$app->response->format = Response::FORMAT_JSON;

		if($this->modelClass)
		{
			/** @var \common\models\UploadImageForm $model */
			$model = new $this->modelClass();
			$model->file = UploadedFile::getInstanceByName($this->fieldName);
			if(!$model->validate())
			{
				return Json::encode([
					'files' => [
						['error' => $model->getFirstError('file')]
					],
				]);
			}
		}

		$instance = UploadedFile::getInstanceByName($this->fieldName);
		/** @var ImageStorageInterface $imageStorage */
		$imageStorage = \Yii::$app->get('imageStorage');
		$files = [];
        //Image::crop($instance->tempName, $width, $height, ['x' => 0, 'y' => 0])->save();
		$fileKey = $this->generateFileKey($instance);
		if($imageStorage->upload($fileKey, $instance->tempName))
		{
			$files[] = [
				'key' => $fileKey,
				'name' => $instance->baseName,
				'url' => $imageStorage->getImageUrl($fileKey),
				'thumbnailUrl' => $imageStorage->getImageUrl($fileKey, ['width' => $this->thumbnailWidth, 'height' => $this->thumbnailHeight, 'mode' => $this->mode]),
			];
			return Json::encode([
				'files' => $files,
			]);
		}
		else
		{
			return Json::encode([
				'files' => [
					['error' => '上传失败']
				],
			]);
		}
	}

	/**
	 * @param $instance UploadedFile
	 * @return string
	 */
	private function generateFileKey($instance)
	{
		return str_replace(['{date:Ymd}', '{fileName:md5}', '{time}', '{ext}'],
			[date('Ymd'), md5($instance->baseName), time(), strtolower($instance->extension)],
			$this->keyTemplate);
	}
}