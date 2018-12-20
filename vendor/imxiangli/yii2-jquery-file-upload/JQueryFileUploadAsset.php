<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace imxiangli\upload;

use yii\web\AssetBundle;

class JQueryFileUploadAsset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/blueimp-file-upload';
	public $css = [
	];
	public $js = [
		'js/vendor/jquery.ui.widget.js',
		'js/jquery.iframe-transport.js',
		'js/jquery.fileupload.js',
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];
}
