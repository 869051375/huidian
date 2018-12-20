<?php

namespace backend\controllers;

use backend\models\RelatedForm;
use common\models\Product;
use common\models\ProductRelated;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductRelatedController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete','validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'validation', 'delete'],
                        'allow' => true,
                        'roles' => ['product-related/*'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $id
     * @return string
     */
    public function actionList($id)
    {
        $model = $this->findModel($id);
        $query = (new Query())
            ->select(['r.related_product_id', 'r.product_id', 'p.spec_name', 'p.name'])->from(['p' => Product::tableName()])
            ->innerJoin(['r' => ProductRelated::tableName()], 'p.id = r.related_product_id')
            ->where(['r.product_id' => $model->id]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', ['provider' => $provider, 'product' => $model]);
    }

    /**
     * 新增关联商品
     */
    public function actionCreate()
    {
        $model = new RelatedForm();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            $model->save();
            Yii::$app->session->setFlash('success', '关联商品添加成功!');
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($errors) : '关联失败！');
        }
        return $this->redirect(['list','id'=> $post['RelatedForm']['product_id']]);
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new RelatedForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDelete()
    {
        $p_id = Yii::$app->getRequest()->post('p_id');
        $r_id = Yii::$app->getRequest()->post('r_id');
        if(ProductRelated::deleteAll(['product_id'=>$p_id,'related_product_id'=>$r_id])){
            ProductRelated::deleteAll(['product_id'=>$r_id,'related_product_id'=>$p_id]);
            return ['status'=>200];
        }
        return ['status'=>400];
    }

    /**
     * @param $id
     * @return Product
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = Product::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的商品!');
        }
        return $model;
    }
}