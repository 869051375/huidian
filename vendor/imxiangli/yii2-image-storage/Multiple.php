<?php

namespace imxiangli\image\storage;
use yii\base\Component;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/8/30
 * Time: 11:13
 */
class Multiple extends Component implements ImageStorageInterface
{
    /**
     * @var array 多个适配器配置数组
     * 'oss' => [
	 *     'class' => \imxiangli\image\storage\OSS::className(),
	 *     'endPoint' => 'oss-cn-beijing.aliyuncs.com', // oss-cn-beijing-internal.aliyuncs.com
	 *     'accessKeyId' => '',
	 *     'accessKeySecret' => '',
	 *     'isCName' => false,
	 *     'securityToken' => null,
	 *     'defaultBucket' => '',
	 * 	   'imageDomain' => 'oss-cn-beijing.aliyuncs.com'
	 * ],
     * 'iss' => [
     *     'class' => \common\image\storage\ISS::className(),
     *     'accessKeyId' => '',
     *     'accessKeySecret' => '',
     *     'imageDomain' => '',
     *     'apiServer' => 'http://api.iss.hufenbao.com',
     * ]
     */
    public $adapters = [];

    /**
     * @var string|null 指定读取所使用的存储,值为$this->adapters数组的key
     */
    public $mainAdapter = null;

    /**
     * @var ImageStorageInterface[]
     */
    private $_adapters = [];

    public function init()
    {
        parent::init();
        foreach($this->adapters as $key => $config)
        {
            $this->_adapters[$key] = \Yii::createObject($config);
        }
    }

    public function upload($key, $file)
    {
        $success = true;
        foreach($this->_adapters as $adapter)
        {
            $success = $success && $adapter->upload($key, $file);
        }
        return $success;
    }

    public function delete($key)
    {
        $success = true;
        foreach($this->_adapters as $adapter)
        {
            $success = $success && $adapter->delete($key);
        }
        return $success;
    }

    public function getImageUrl($key, $params = [])
    {
        $adapter = null;
        if(empty($this->mainAdapter) && !empty($this->_adapters))
            $adapter = end($this->_adapters);
        else if(isset($this->_adapters[$this->mainAdapter]))
            $adapter = $this->_adapters[$this->mainAdapter];
        if($adapter)
        {
            return $adapter->getImageUrl($key, $params);
        }
    }
}