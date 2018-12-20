<?php

namespace backend\controllers;

use common\models\Product;
use common\models\ProductFaq;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductFaqController extends BaseController
{

    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete','validation','detail'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'upload', 'validation', 'delete', 'update', 'detail'],
                        'allow' => true,
                        'roles' => ['product-faq/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($product_id)
    {
        $product = $this->findProduct($product_id);
        $provider = ProductFaq::findAll(['product_id' => $product->id]);
        return $this->render('list', [
            'provider' => $provider,
            'product'  =>$product,
        ]);
    }

    // 新增
    public function actionCreate()
    {
        $model = new ProductFaq();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            Yii::$app->session->setFlash('success', '保存成功!');
            $model->save(false);
        }
        else
        {
            Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        }
        return $this->redirect(['list','product_id'=>$post['ProductFaq']['product_id']]);
    }

    public function actionValidation()
    {
        $model = new ProductFaq();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $this->serializeData($model)];
    }

    // 更新
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        }
        else {
            Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        }
        return $this->redirect(['list','product_id'=>$post['ProductFaq']['product_id']]);
    }

    // 删除
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    // 加载一个问题，当找不到时抛出异常
    private function findModel($id)
    {
        $model = ProductFaq::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的问题!');
        }
        return $model;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }

    private function findProduct($product_id)
    {
        $model = Product::findOne($product_id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的数据!');
        }
        return $model;
    }

}
