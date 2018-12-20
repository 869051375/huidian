<?php

namespace backend\controllers;

use backend\models\PackageRelatedForm;
use common\models\PackageRelated;
use common\models\Product;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PackageRelatedController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'validation', 'ajax-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['list', 'create', 'validation', 'delete'],
                        'allow' => true,
                        'roles' => ['product/update'],
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
            ->select(['pr.package_related_id', 'pr.package_id', 'p.spec_name', 'p.name'])->from(['p' => Product::tableName()])
            ->innerJoin(['pr' => PackageRelated::tableName()], 'p.id = pr.package_related_id')
            ->where(['pr.package_id' => $model->id, 'p.is_package' => Product::PACKAGE_ACTIVE]);
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
        $model = new PackageRelatedForm();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            $model->save();
            Yii::$app->session->setFlash('success', '关联套餐添加成功!');
        }
        else
        {
            Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($model->getFirstErrors()) : '关联失败！');
        }
        return $this->redirect(['list','id'=> $post['PackageRelatedForm']['package_id']]);
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new PackageRelatedForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDelete()
    {
        $p_id = Yii::$app->getRequest()->post('p_id');
        $r_id = Yii::$app->getRequest()->post('r_id');
        if(PackageRelated::deleteAll(['package_id'=>$p_id,'package_related_id'=>$r_id])){
            PackageRelated::deleteAll(['package_id'=>$r_id,'package_related_id'=>$p_id]);
            return ['status'=>200];
        }
        return ['status'=>400];
    }

    /**
     * @param integer $id
     * @param null $keyword
     * @return array
     */
    public function actionAjaxList($id, $keyword=null)
    {
        /** @var ActiveQuery $query */
        $query = Product::find()->select(['id', 'name', 'alias'])->where(['is_package' => Product::PACKAGE_ACTIVE])->andWhere(['<>','id', $id]);
        if(!empty($keyword))
        {
            $query->andWhere(['or', ['like', 'name', $keyword], ['like', 'alias', $keyword]]);
        }
        return ['status' => 200, 'products' => $this->serializeData($query->all())];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
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