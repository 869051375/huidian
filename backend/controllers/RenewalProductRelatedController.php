<?php

namespace backend\controllers;

use backend\models\RenewalProductRelatedForm;
use common\models\Product;
use common\models\RenewalProductRelated;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RenewalProductRelatedController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'status',
                    'ajax-list',
                    'ajax-delete-product',
                    ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list','status'],
                        'allow' => true,
                        'roles' => ['renewal-product-related/list'],
                    ],
                    [
                        'actions' => ['status'],
                        'allow' => true,
                        'roles' => ['renewal-product-related/status'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['renewal-product-related/create'],
                    ],
                    [
                        'actions' => ['update', 'ajax-delete-product'],
                        'allow' => true,
                        'roles' => ['renewal-product-related/update'],
                    ],
                    [
                        'actions' => ['ajax-list', 'add-renewal-product'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        $query = RenewalProductRelated::find();
        $provider = new ActiveDataProvider([
            'query' => $query
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    /**
     * 新增关联续费商品
     * @param null $id
     * @return string
     */
    public function actionCreate($id=null)
    {
        if($id)
        {
            $model = $this->findModel($id);
        }
        else
        {
            $model = new RenewalProductRelated();
        }
        return $this->render('create', [
            'model' => $model,
        ]);

    }

    /**
     * 编辑更新
     * @return Response
     */
    public function actionUpdate()
    {
        $renewalModel= Yii::$app->request->post('RenewalProductRelated');
        if($renewalModel['id'])
        {
            $model = $this->findModel($renewalModel['id']);
        }
        else
        {
            $model = new RenewalProductRelated();
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        }
        else {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list']);
    }

    /**
     * 上下线状态
     * @return array
     */
    public function actionStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->status = $status;
        $model->setScenario('status');
        if($model->validate(['status']))
        {
            $model->save(false);
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    /**
     * 删除包含商品
     * @return array
     */
    public function actionAjaxDeleteProduct()
    {
        $product_id = Yii::$app->getRequest()->post('product_id');
        $renewal_id = Yii::$app->getRequest()->post('renewal_id');
        $model = $this->findModel($renewal_id);
        if($model->removeProduct($product_id))
        {
            return ['status' => 200];
        }
        return ['status' => 500, 'message' => '内部错误。'];
    }

    /***
     * 添加续费商品
     * @return Response
     */
    public function actionAddRenewalProduct()
    {
        $model = new RenewalProductRelatedForm();
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '保存成功！');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
//            Yii::$app->session->setFlash('error', '请先保存后再添加续费包含商品！');
//            return $this->redirect(['create']);
        }
        return $this->redirect(['create', 'id' => $model->id]);
    }

    /**
     * @param null $keyword
     * @param null $id
     * @return array
     */
    public function actionAjaxList($keyword=null, $id=null)
    {
        /** @var ActiveQuery $query */
        $query = Product::find()
                    ->select(['id', 'name'])
                    ->where(['is_renewal' => Product::RENEWAL_ACTIVE, 'is_package' => Product::PACKAGE_DISABLED]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        if(!empty($id))
        {
            $renewalProduct = $this->findModel($id);
            if(null != $renewalProduct)
            {
                $query->andWhere(['not in', 'id', $renewalProduct->getProductIds()]);
            }
        }
        $query->orderBy(['id' => SORT_ASC]);
        return ['status' => 200, 'products' => $this->serializeData($query->all())];
    }

    private function findModel($id)
    {
        $model = RenewalProductRelated::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的商品！');
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
}