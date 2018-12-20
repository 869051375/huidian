<?php

namespace imxiangli\image\storage;
use yii\base\Component;
use yii\httpclient\Client;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/8/29
 * Time: 16:23
 */

class ISS extends Component implements ImageStorageInterface
{
    public $accessKeyId;
    public $accessKeySecret;
    public $imageDomain;
    public $apiServer;

    public function upload($imageKey, $file)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl([$this->apiServer.'/image/upload', 'access-key' => $this->accessKeyId, 'access-secret' => $this->accessKeySecret])
            ->setData(['key' => $imageKey])
            ->addFile('image', $file)
            ->send();
        return $response->isOk;
    }

    public function delete($imageKey)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl([$this->apiServer.'/image/delete', 'access-key' => $this->accessKeyId, 'access-secret' => $this->accessKeySecret])
            ->setData(['key' => $imageKey])
            ->send();
        return $response->isOk;
    }

    /**
     * @param string $key 图片key
     * @param [] $params width 需要的图片宽度,0为实际宽度 height 需要的图片高度,0为实际高度 quality 图片质量,0-99的整数,数字越大质量越高 mode  0:长边裁剪,1:短边填充
     * @return string
     */
    public function getImageUrl($key, $params = [])
    {
        $width = isset($params['width']) ? $params['width'] : 0;
        $height = isset($params['height']) ? $params['height'] : 0;
        $quality = isset($params['quality']) ? $params['quality'] : 90;
        $mode = isset($params['mode']) ? $params['mode'] : 0;
        return $this->imageDomain . '/' . $key . '/' . ((int)$width) . 'x' . ((int)$height) . '-' . ((int)$quality) . '-' . ((int)$mode) . '.jpg';
    }
}