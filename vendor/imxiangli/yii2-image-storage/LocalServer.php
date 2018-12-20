<?php

namespace imxiangli\image\storage;
use yii\base\Component;

class LocalServer extends Component implements ImageStorageInterface
{
    public $basePath;

    public function upload($key, $file)
    {
    }

    public function delete($key)
    {
    }

    public function getImageUrl($key, $params = [])
    {
    }
}