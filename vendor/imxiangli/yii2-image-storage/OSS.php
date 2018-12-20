<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/16
 * Time: 上午10:14
 */

namespace imxiangli\image\storage;
use OSS\OssClient;
use yii\base\Component;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/8/29
 * Time: 16:23
 */

class OSS extends Component implements ImageStorageInterface
{
	public $accessKeyId;
	public $accessKeySecret;
	public $endPoint;
	public $isCName = false;
	public $securityToken = null;

	public $defaultBucket;
	public $imageDomain;

	private $client;

	/**
	 * @return OssClient
	 */
	private function getClient()
	{
		if(null != $this->client)
			return $this->client;
		$this->client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endPoint, $this->isCName);
		return $this->client;
	}

	public function upload($imageKey, $file)
	{
		$client = $this->getClient();
		try{
			$client->uploadFile($this->defaultBucket, $imageKey, $file);
			return true;
		}
		catch (\Exception $e)
		{
			throw $e;
		}
	}

	public function delete($imageKey)
	{
		$client = $this->getClient();
		$client->deleteObject($this->defaultBucket, $imageKey);
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
		$mode = isset($params['mode']) ? $params['mode'] : 0;
		$m = 'm_fill';
		if($mode == 1)
		{
			$m = 'm_lfit';
		}
		return 'http://'.$this->defaultBucket.'.'.$this->imageDomain.'/'.$key.
            (($height > 0 || $width) > 0 ? '?x-oss-process=image/resize,'.$m.($height > 0 ? ',h_'.$height : '').($width > 0 ? ',w_'.$width : '').',limit_0/auto-orient,0' : '');
	}
}
