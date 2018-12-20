<?php

namespace backend\actions;

use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\base\Action;
use yii\validators\FileValidator;
use yii\web\Response;
use yii\web\UploadedFile;

class CKEditorAction extends Action
{
    public $uploadName = 'upload';
    public $path = '';

    public function init()
    {
        Yii::$app->request->enableCsrfValidation = false;
        parent::init();
    }

    public function run()
    {
        $responseType = "json";//Yii::$app->getRequest()->get('responseType');
        $picFile = UploadedFile::getInstanceByName($this->uploadName);
        if ($responseType == 'json') {
            Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        }
        $validator = new FileValidator();
        $validator->extensions = array('jpg', 'png', 'jpeg');
        $validator->mimeTypes = array('image/png', 'image/jpg', 'image/jpeg');
        $validator->maxSize = 2097152;
        $validator->skipOnEmpty = false;
        $error = null;
        if ($validator->validate($picFile, $error)) {
            $original = $picFile->name;
            /** @var ImageStorageInterface $imageStorage */
            $imageStorage = Yii::$app->get('imageStorage');
            $key = $this->path . '/' . md5($picFile->name.time()) . '.' . strtolower($picFile->extension);
            if ($imageStorage->upload($key, $picFile->tempName)) {
                $url = $imageStorage->getImageUrl($key);
                if ($responseType == 'json') {
                    return [
                        'uploaded' => 1,
                        'fileName' => $original,
                        'url' => $url,
                    ];
                }
                $funcNum = Yii::$app->request->get('CKEditorFuncNum', '0');
                return '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$funcNum.'", "' . $url . '", "");</script>';
            }
        }
        if ($responseType == 'json') {
            return [
                'uploaded' => 0,
                'error' => [
                    'message' => $error,
                ],
            ];
        }
        return $error;
    }

}