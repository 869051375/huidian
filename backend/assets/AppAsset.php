<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/style.css',
    ];
    public $js = [
        'js/inspinia.js',
        'js/loading.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
        'imxiangli\fontawesome\FontAwesomeAsset',
        'imxiangli\animate\AnimateAsset',
        'imxiangli\pace\PaceAsset',
        'imxiangli\slimscroll\SlimscrollAsset',
    ];
}
