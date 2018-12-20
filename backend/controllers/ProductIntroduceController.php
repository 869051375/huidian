<?php

namespace backend\controllers;

use backend\models\ImageForm;
use common\models\Product;
use common\models\ProductImage;
use common\models\ProductIntroduce;
use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ProductIntroduceController extends BaseController
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
//            'contentNegotiator' => [
//                'class' => ContentNegotiator::className(),
//                'only' => [],
//                'formats' => [
//                    'application/json' => Response::FORMAT_JSON,
//                ],
//            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'upload','update-pc','update-mobile','pc-desc','mobile-desc','guarantee','validation', 'detail'],
                        'allow' => true,
                        'roles' => ['product-introduce/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionUpload()
    {
        $model = new ImageForm();
        $model->file = UploadedFile::getInstanceByName('file');
        $model->type = Yii::$app->request->post('type');
        $model->product_id = Yii::$app->request->post('product_id');
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
        $fileKey = 'images/product/'.$model->product_id.'/'.rand(10000000,99999999).md5($model->file->baseName).'.'.strtolower($model->file->extension);
        if($imageStorage->upload($fileKey, $model->file->tempName))
        {
            $model->image = $fileKey;
            $imageModel = $model->save();
            $files[] = [
                'id' => $imageModel->id,
                'key' => $fileKey,
                'name' => $model->file->baseName,
                'url' => $imageStorage->getImageUrl($fileKey),
                'thumbnailUrl' => $imageStorage->getImageUrl($fileKey, ['width' => 300, 'height' => 300, 'mode' => 1]),
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

    public function actionList($product_id)
    {
        /** @var Product $product */
        $product = $this->findProduct($product_id);
        $images = ProductImage::findAll(['product_id' => $product->id]);
        $introduce = $this->findIntroduce($product);
        return $this->render('list', [
            'introduce' => $introduce,
            'images' => $images,
            'product' => $product,
        ]);
    }

    public function actionPcDesc($product_id)
    {
        $product = $this->findProduct($product_id);
        $introduce = $this->findIntroduce($product);
        return $this->render('pc_desc', [
            'introduce' => $introduce,
            'product' => $product,
        ]);
    }

    public function actionMobileDesc($product_id)
    {
        $product = $this->findProduct($product_id);
        $introduce = $this->findIntroduce($product);
        return $this->render('mobile_desc', [
            'introduce' => $introduce,
            'product' => $product,
        ]);
    }

    public  function actionGuarantee($product_id)
    {
        if(Yii::$app->request->isPost)
        {
            /** @var Product $product */
            $product = $this->findProduct($product_id);
            $introduce = $this->findIntroduce($product);

            if ($introduce->load(Yii::$app->request->post()) && $introduce->validate())
            {
                if($introduce->save(false))
                {
                    Yii::$app->session->setFlash('success', '保存成功!');
                }
            }
            if($introduce->hasErrors())
            {
                Yii::$app->session->setFlash('error', reset($introduce->getFirstErrors()));
            }
            return $this->redirect(['guarantee','product_id' => $product->id]);
        }
        else
        {
            $product = $this->findProduct($product_id);
            $introduce = $this->findIntroduce($product);
            return $this->render('guarantee', [
                'introduce' => $introduce,
                'product' => $product,
            ]);
        }

    }

    // 新增
    public function actionUpdatePc($product_id)
    {
        /** @var Product $product */
        $product = $this->findProduct($product_id);
        $introduce = $this->findIntroduce($product);
        if ($introduce->load(Yii::$app->request->post()) && $introduce->validate())
        {
            if($introduce->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
        }
        if($introduce->hasErrors())
        {
            Yii::$app->session->setFlash('error', reset($introduce->getFirstErrors()));
        }
        return $this->redirect(['pc-desc','product_id' => $product->id]);
    }

    // 新增
    public function actionUpdateMobile($product_id)
    {
        /** @var Product $product */
        $product = $this->findProduct($product_id);
        $introduce = $this->findIntroduce($product);

        if ($introduce->load(Yii::$app->request->post()) && $introduce->validate())
        {
            if($introduce->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
        }
        if($introduce->hasErrors())
        {
            Yii::$app->session->setFlash('error', reset($introduce->getFirstErrors()));
        }
        return $this->redirect(['mobile-desc','product_id' => $product->id]);
    }

    /**
     * @param $product
     * @return ProductIntroduce
     */
    private function findIntroduce($product)
    {
        $introduce = ProductIntroduce::findOne(['product_id' => $product->id]);
        if(null == $introduce)
        {
            $introduce = new ProductIntroduce();
            $introduce->product_id = $product->id;
            $introduce->save(false);
        }
        return $introduce;
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new ImageForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $bannerData = $this->serializeData($model);
        $bannerData['imageUrl'] = $model->getImageUrl();
        return ['status' => 200, 'model' => $bannerData];
    }

    // 加载时当找不到时抛出异常
    /**
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = ProductIntroduce::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的数据!');
        }
        return $model;
    }

    /**
     * @param $product_id
     * @return string
     * @throws NotFoundHttpException
     */
    private function findProduct($product_id)
    {
        $model = Product::findOne($product_id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的数据!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

}
