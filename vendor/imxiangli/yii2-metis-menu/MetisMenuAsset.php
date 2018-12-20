<?php

namespace imxiangli\metismenu;

use yii\web\AssetBundle;

class MetisMenuAsset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/metismenu/dist';
	public $js = [
		'metisMenu.js'
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];
}
