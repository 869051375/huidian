<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/16
 * Time: 上午10:14
 */

namespace common\components;
use OSS\OssClient;
use yii\base\Component;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/8/29
 * Time: 16:23
 */

class OSS extends Component
{
    public $accessKeyId;
    public $accessKeySecret;
    public $endPoint;
    public $internalEndPoint;
    public $isCName = false;
    public $securityToken = null;

    public $defaultBucket;

    private $client;
    private $clientInternal;

    /**
     * @param boolean $internal
     * @return OssClient
     */
    private function getClient($internal = true)
    {
        if($internal && null != $this->clientInternal)
        {
            return $this->clientInternal;
        }
        if(null != $this->client)
            return $this->client;
        if($internal && !empty($this->internalEndPoint))
        {
            $this->clientInternal = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->internalEndPoint, $this->isCName);
            return $this->clientInternal;
        }
        $this->client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endPoint, $this->isCName);
        return $this->client;
    }

    public function upload($key, $file, $options = null)
    {
        $client = $this->getClient(true);
        try
        {
            $client->uploadFile($this->defaultBucket, $key, $file, $options);
            return true;
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function delete($key)
    {
        $client = $this->getClient(true);
        $client->deleteObject($this->defaultBucket, $key);
    }

    /**
     * @param string $key 文件key
     * @param integer $timeout 链接有效时间（秒）
     * @return string
     */
    public function getUrl($key, $timeout = 3600)
    {
        return $this->getClient(false)->signUrl($this->defaultBucket, $key, $timeout);
    }

    public function getPublicUrl($key)
    {
        return  'http://'.$this->defaultBucket.'.'.$this->endPoint.'/'.$key;
    }
}
