<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/31
 * Time: 下午1:23
 */

namespace common\utils;


class Decimal
{
    public static function formatCurrentYuan($value, $decimals = 2, $options = [], $textOptions = [], $sign = false)
    {
        $minus = $value < 0 ? '-' : ($sign ? '+' : '');
        return $minus.\Yii::$app->formatter->asDecimal(abs($value), $decimals, $options, $textOptions).'元';
    }

    public static function formatYenCurrentFrom($value, $decimals = 0, $options = [], $textOptions = [], $sign = false)
    {
        $minus = $value < 0 ? '-' : ($sign ? '+' : '');
        return '<span class="price-money">'.$minus.'&yen;'.\Yii::$app->formatter->asDecimal(abs($value), $decimals, $options, $textOptions).'</span><span class="price-from">起</span>';
    }

    public static function formatYenCurrent($value, $decimals = 0, $options = [], $textOptions = [], $sign = false)
    {
        $minus = $value < 0 ? '-' : ($sign ? '+' : '');
        return '<span class="price-money">'.$minus.'&yen;'.\Yii::$app->formatter->asDecimal(abs($value), $decimals, $options, $textOptions).'</span>';
    }

    public static function formatYenCurrentNoWrap($value, $decimals = 2, $options = [], $textOptions = [], $sign = false)
    {
        $minus = $value < 0 ? '-' : ($sign ? '+' : '');
        return $minus.'&yen;'.\Yii::$app->formatter->asDecimal(abs($value), $decimals, $options, $textOptions);
    }
}