<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/23
 * Time: 下午1:50
 */

namespace backend\controllers;

use common\actions\UploadImageAction;
use common\models\AdministratorLog;
use common\models\ProductCategory;
use common\models\UploadImageForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use common\models\Product;
use backend\models\ClassificationProductsForm;

class ProductCategoryController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'sort', 'update','detail','ajax-list', 'validation','seo'],
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
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['product-category/list'],
                    ],
                    [
                        'actions' => ['create','upload','validation'],
                        'allow' => true,
                        'roles' => ['product-category/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['product-category/delete'],
                    ],
                    [
                        'actions' => ['update', 'sort', 'detail','upload','validation'],
                        'allow' => true,
                        'roles' => ['product-category/update'],
                    ],
                    [
                        'actions' => ['seo'],
                        'allow' => true,
                        'roles' => ['product-category/seo'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadImageAction::className(),
                'modelClass' => UploadImageForm::className(),
                'keyTemplate' => 'product-category/{date:Ymd}-{time}.{ext}',
                'thumbnailWidth' => 100,
                'thumbnailHeight' => 100,
            ],
        ];
    }

    // 获得所有分类列表，如果传入id，则输出下级分类
    public function actionList($id = 0)
    {
        /** @var ProductCategory[] $categories */
        $categories = ProductCategory::find()->where('parent_id=0')->orderBy(['sort' => SORT_ASC])->all();
        return $this->render('list', ['categories' => $categories, 'id' => $id]);
    }

    // 创建一个分类
    public function actionCreate()
    {
        $model = new ProductCategory();
//        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
//            Yii::$app->response->format = Response::FORMAT_JSON;
//            return ActiveForm::validate($model);
//        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $maxSort = ProductCategory::find()->where('parent_id=:parent_id', [':parent_id' => $model->parent_id])
                ->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
            $model->sort = $maxSort + 10;
            $model->save(false);
            //新增后台操作日志
            AdministratorLog::logProductCategoryCreate($model);
            Yii::$app->session->setFlash('success', '保存成功!');
            return $this->redirect(['list', 'id' => !empty($model->parent_id) ? $model->parent_id : $model->id]);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['list', 'id' => $model->parent_id]);
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new ProductCategory();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $data = $this->serializeData($model);
        $data['imageUrl'] = $model->getImageUrl();
        $data['iconImageUrl'] = $model->getIconImageUrl();
        $data['bannerImageUrl'] = $model->getBannerImageUrl(200, 100);
        return ['status' => 200, 'model' => $data];
    }

    // 更新分类
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldName = $model->name;
        if ($model->load(Yii::$app->request->post())) {
            if($model->save())
            {
                //新增后台操作日志
                AdministratorLog::logProductCategoryUpdate($model, $oldName);
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->redirect(['list', 'id' => !empty($model->parent_id) ? $model->parent_id : $model->id]);
    }

    // 分类排序
    public function actionSort()
    {
        // post: source_id, target_id
        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');

        $source = $this->findModel($source_id);
        $target = $this->findModel($target_id);

        // 交换两个分类的排序序号
        $sort = $target->sort;
        $target->sort = $source->sort;
        $source->sort = $sort;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    // 删除分类
    public function actionDelete()
    {
        // post: id
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);

        //新增后台操作日志
        AdministratorLog::logProductCategoryDelete($model);

        $model->delete();
        return ['status' => 200];
    }

    // 加载一个分类，当找不到时抛出异常
    private function findModel($id)
    {
        /** @var ProductCategory $model */
        $model = ProductCategory::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的分类!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
    //商品分类ajax
    public function actionAjaxList($keyword = null)
    {
        $parent_id = Yii::$app->getRequest()->get('parent_id', 0);
        $query = ProductCategory::find()->select(['id', 'name', 'parent_id'])->where('parent_id=:parent_id', [':parent_id' => $parent_id]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'categories' => $this->serializeData($data)];
    }


    public function actionSeo(){

        if(Yii::$app->request->isGet){
           return ClassificationProductsForm::findOne(Yii::$app->request->get('id'));
        }
         $model= ClassificationProductsForm::findOne(Yii::$app->request->get('id'));//实例
         if (null == $model) {
            Yii::$app->session->setFlash('数据来源有误');
            return $this->redirect('list');
          }
          if(Yii::$app->request->isPost){
             if($model->load(Yii::$app->request->post()) && $model->validate()){
               if($product=$model->save()){
                 AdministratorLog::logProductCategoryCreate($model);
                Yii::$app->session->setFlash('success', '保存成功!');
                    }else{
                   $errors = $model->getFirstErrors();
                   Yii::$app->session->setFlash('error', reset($errors));
                    }
            }
        }
         return $this->redirect('list');
    }

}