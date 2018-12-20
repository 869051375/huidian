<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/12/29
 * Time: 上午10:02
 */

namespace common\utils;


class Url extends \yii\helpers\Url
{
    public static function to($url = '', $scheme = true)
    {
        return parent::to($url, $scheme);
    }

    public static function toRoute($route, $scheme = true)
    {
        return parent::toRoute($route, $scheme);
    }
}