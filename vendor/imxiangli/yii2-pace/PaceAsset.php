<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace imxiangli\pace;

use yii\web\AssetBundle;

class PaceAsset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/pace';
	public $js = [
		'pace.js'
	];
}