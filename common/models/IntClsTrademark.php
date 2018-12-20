<?php
namespace common\models;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/27
 * Time: 17:54
 *
 * 接口模型
 *
 * 国际商标分类
 *
 */

    class IntClsTrademark
    {
        public $no;
        public $name;
        public $count;

        public function load($data)
        {
            $this->no = $data['Value'];
            $this->name = $data['Desc'];
            $this->count = $data['Count'];
        }
    }