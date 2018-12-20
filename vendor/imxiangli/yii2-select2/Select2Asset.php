<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace imxiangli\select2;

use yii\web\AssetBundle;

class Select2Asset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/select2/dist';
	public $css = [
		'css/select2.min.css'
	];
	public $js = [
		'js/select2.full.min.js',
		'js/i18n/zh-CN.js',
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];
}