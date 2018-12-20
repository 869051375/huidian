<?php
namespace imxiangli\ckeditor;

use yii\web\AssetBundle;

class CKEditorAsset extends AssetBundle
{
	public $sourcePath = '@vendor/bower/ckeditor';
	public $css = [
	];
	public $js = [
		'ckeditor.js',
		'adapters/jquery.js'
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];
}