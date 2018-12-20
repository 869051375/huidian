<?php

namespace backend\controllers;

use backend\models\CollocationForm;
use common\models\Collocation;
use common\models\Product;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class CollocationController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['remove','validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['collocation/list'],
                    ],
                    [
                        'actions' => ['list', 'add', 'validation'],
                        'allow' => true,
                        'roles' => ['collocation/add'],
                    ],
                    [
                        'actions' => ['list', 'remove'],
                        'allow' => true,
                        'roles' => ['collocation/remove'],
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
            ->select(['c.collocation_product_id', 'c.product_id', 'p.spec_name', 'p.name', 'c.desc'])
            ->from(['p' => Product::tableName()])
            ->innerJoin(['c' => Collocation::tableName()], 'p.id = c.collocation_product_id')
            ->where(['c.product_id' => $model->id]);
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
    public function actionAdd()
    {
        $model = new CollocationForm();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            $model->save();
            Yii::$app->session->setFlash('success', '关联搭配商品添加成功!');
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($errors) : '关联失败！');
        }
        return $this->redirect(['list', 'id'=> $model->product_id]);
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new CollocationForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionRemove()
    {
        $p_id = Yii::$app->getRequest()->post('p_id');
        $c_id = Yii::$app->getRequest()->post('c_id');
        if(Collocation::deleteAll(['product_id' => $p_id,'collocation_product_id' => $c_id]))
        {
            return ['status' => 200];
        }
        return ['status' => 400];
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