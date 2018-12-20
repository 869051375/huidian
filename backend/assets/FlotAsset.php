<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

class FlotAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
        'js/flot/jquery.flot.js',
        'js/flot/Chart.min.js',
        'js/flot/jquery.flot.pie.js',
        'js/flot/jquery.flot.categories.js',
        'js/jquery.flot.tooltip.min.js',
        'js/echarts.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
