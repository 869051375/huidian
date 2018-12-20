<?php

namespace backend\assets;

use yii\web\AssetBundle;

class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/style.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
        'imxiangli\fontawesome\FontAwesomeAsset',
        'imxiangli\animate\AnimateAsset',
        'imxiangli\pace\PaceAsset',
    ];
}
