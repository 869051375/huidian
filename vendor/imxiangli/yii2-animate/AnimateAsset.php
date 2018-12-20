<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace imxiangli\animate;

use yii\web\AssetBundle;

class AnimateAsset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/animate.css';
	public $css = [
		'animate.css',
	];
}