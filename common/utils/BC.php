<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/24
 * Time: 14:30
 */

namespace common\utils;


class BC
{
    //2个任意精度数字的加法计算(默认保留小数点后2位)
    public static function add($left_operand, $right_operand, $scale = 2)
    {
        return bcadd($left_operand, $right_operand, $scale);
    }

    //比较两个任意精度的数字(默认保留小数点后2位)
    public static function comp($left_operand, $right_operand, $scale = 2)
    {
        return bccomp($left_operand, $right_operand, $scale);
    }

    //2个任意精度的数字除法计算(默认保留小数点后2位)
    public static function div($left_operand, $right_operand, $scale = 2)
    {
        if($right_operand == 0)
        {
            return 0;
        }
        return bcdiv($left_operand, $right_operand, $scale);
    }

    //对一个任意精度数字取模
    public static function mod($left_operand, $right_operand)
    {
        return bcmod($left_operand, $right_operand);
    }

    //2个任意精度数字乘法计算(默认保留小数点后2位)
    public static function mul($left_operand, $right_operand, $scale = 2)
    {
        return bcmul($left_operand, $right_operand, $scale);
    }

    //2个任意精度数字的减法(默认保留小数点后2位)
    public static function sub($left_operand, $right_operand, $scale = 2)
    {
        return bcsub($left_operand, $right_operand, $scale);
    }

    //任意精度数字的乘方(默认保留小数点后2位)
    public static function pow($left_operand, $right_operand, $scale = 2)
    {
        return bcpow($left_operand, $right_operand, $scale);
    }

    //任意精度数字的二次方根
    public static function sqrt($left_operand, $right_operand)
    {
        return bcsqrt($left_operand, $right_operand);
    }

}