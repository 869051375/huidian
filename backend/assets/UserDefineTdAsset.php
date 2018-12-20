<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace backend\assets;

use yii\web\AssetBundle;

class UserDefineTdAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'js/jquery-ui-1.12.1.custom/jquery-ui.css',
        'jss/jquery-ui-1.12.1.custom/jquery-ui.theme.css',
    ];
    public $js = [
        'js/jquery-ui-1.12.1.custom/jquery-ui.js',
        'js/addTag/addTagAction.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}