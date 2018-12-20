<?php
namespace imxiangli\image\storage;
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/8/29
 * Time: 16:23
 */
interface ImageStorageInterface
{
    /**
     * @param string $key
     * @param string $file
     * @return boolean
     */
    public function upload($key, $file);

    /**
     * @param string $key
     * @return boolean
     */
    public function delete($key);

    /**
     * @param string $key 图片key
     * @param [] $params width 需要的图片宽度,0为实际宽度 height 需要的图片高度,0为实际高度 quality 图片质量,0-99的整数,数字越大质量越高 mode  0:长边裁剪,1:短边填充
     * @return string
     */
    public function getImageUrl($key, $params = []);
}