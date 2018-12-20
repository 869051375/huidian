<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/21
 * Time: 下午5:20
 */

namespace common\validators;

use yii\base\InvalidConfigException;
use yii\validators\RegularExpressionValidator;

class TelPhoneValidator extends RegularExpressionValidator
{
    public $message = '电话号码无效。';
    public $pattern = '';
    public $phoneOnly = false;
    public $telOnly = false;

    public function init()
    {
        parent::init();
        if($this->phoneOnly && $this->telOnly)
        {
            throw new InvalidConfigException('phoneOnly 和 telOnly 同时只能有一个为 true');
        }
        if($this->phoneOnly && empty($this->pattern))
        {
            //$this->pattern = '/^(13[0-9]|15[012356789]|17[3678]|18[0-9]|14[57])[0-9]{8}$/';
            $this->pattern = '/^1[0-9]{10}$/';
        }
        else if($this->telOnly && empty($this->pattern))
        {
            $this->pattern = '/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$/';
        }
        else if(empty($this->pattern))
        {
            // '/(^(13[0-9]|15[012356789]|17[3678]|18[0-9]|14[57])[0-9]{8}$)|(^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$)/';
            $this->pattern = '/(^1[0-9]{10}$)|(^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$)/';
        }
    }
}