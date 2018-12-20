<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace imxiangli\slimscroll;

use yii\web\AssetBundle;

class SlimscrollAsset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/jquery-slimscroll';
	public $js = [
		'jquery.slimscroll.js'
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];
}