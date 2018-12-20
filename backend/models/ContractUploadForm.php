<?php
namespace backend\models;

use common\components\OSS;
use OSS\OssClient;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ContractUploadForm extends Model
{
    public $file;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['file'], 'file', 'maxSize' => 10*1024*1024, 'tooBig' => '文件大小不能超过10MB'],
        ];
    }

    public function beforeValidate()
    {
        if(parent::beforeValidate())
        {
            return true;
        }
        return false;
    }

    public function upload()
    {
        if(!$this->validate()) return null;
        /** @var OSS $oss */
        $oss = Yii::$app->get('oss');
        /** @var UploadedFile $uploadFile */
        $uploadFile = $this->file;
        $key = 'contract/'.md5($uploadFile->name).time().'.'.$uploadFile->extension;
        $oss->upload($key, $uploadFile->tempName, [OssClient::OSS_HEADERS => [OssClient::OSS_CONTENT_DISPOSTION => 'attachment;filename="'.$uploadFile->name.'"']]);
        return [
            'key' => $key,
            'name' => $uploadFile->name,
            'url' => $oss->getUrl($key),
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => '上传文件'
        ];
    }
}