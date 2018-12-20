<?php

namespace backend\controllers;

use backend\models\AvatarImageForm;
use backend\models\DeleteSettingImageForm;
use backend\models\SettingForm;
use backend\models\SettingProductCommonImageForm;
use backend\models\SettingSeoForm;
use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\UploadedFile;

class SettingController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'upload-image', 'delete-image'],
                        'allow' => true,
                        'roles' => ['setting/index'],
                    ],
                    [
                        'actions' => ['upload-image', 'delete-image', 'product-common-image'],
                        'allow' => true,
                        'roles' => ['product/update'],
                    ],
                    [
                        'actions' => ['seo'],
                        'allow' => true,
                        'roles' => ['setting/seo'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new SettingForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                $model->save();
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($model->getFirstErrors()) : '保存失败');
            }
            return $this->redirect(['index']);
        }

        return $this->render('index', ['model' => $model]);
    }

    public function actionSeo()
    {
        $model = new SettingSeoForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                $model->save();
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($model->getFirstErrors()) : '保存失败');
            }
            return $this->redirect(['seo']);
        }

        return $this->render('seo', ['model' => $model]);
    }

    public function actionProductCommonImage()
    {
        $model = new SettingProductCommonImageForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                $model->save();
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($model->getFirstErrors()) : '保存失败');
            }
            return $this->redirect(['product-common-image']);
        }
        return $this->render('product-common-image', ['model' => $model]);
    }

    public function actionUploadImage()
    {
//        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AvatarImageForm();
        $model->image = UploadedFile::getInstanceByName('image');
        if(!$model->validate())
        {
            return Json::encode([
                'files' => [
                    ['error' => reset($model->getFirstErrors())]
                ],
            ]);
        }
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        $files = [];
        $fileKey = 'images/setting/'.date('Ymd').md5($model->image->baseName).'.'.strtolower($model->image->extension);
        if($imageStorage->upload($fileKey, $model->image->tempName))
        {
            $files[] = [
                'key' => $fileKey,
                'name' => $model->image->baseName,
                'url' => $imageStorage->getImageUrl($fileKey),
                'thumbnailUrl' => $imageStorage->getImageUrl($fileKey, ['width' => 100, 'height' => 100, 'mode' => 1]),
            ];
            return Json::encode([
                'files' => $files,
            ]);
        }
        else
        {
            return Json::encode([
                'files' => [
                    ['error' => '上传失败']
                ],
            ]);
        }
    }

    public function actionDeleteImage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DeleteSettingImageForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->delete())
            {
                return ['status' => 200];
            }
        }
        if($model->hasErrors())
        {
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 400, 'message' => '您的请求有误。'];
    }
}
