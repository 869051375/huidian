<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

class CompanyAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/jstree/animate.css',
        'css/jstree/style.min.css',
    ];
    public $js = [
        'js/flot/jstree.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
