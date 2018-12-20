<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/18
 * Time: 下午4:11
 */

namespace common\models;

use yii\base\Model;

class UploadImageForm extends Model
{
	public $file;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['file', 'required'],
			['file', 'file',
				//'extensions' => ['jpg', 'jpeg', 'png'],
				'mimeTypes' => ['image/jpeg', 'image/png'],
				'maxSize' => 1024*1024*3,
			],
		];
	}
}