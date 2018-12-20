<?php

namespace backend\controllers;

use backend\models\ClerkAreaForm;
use backend\models\ClerkItemsForm;
use common\models\Clerk;
use common\models\ClerkArea;
use common\models\ClerkItems;
use common\models\District;
use common\models\ProductCategory;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class ClerkController extends BaseController
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
                'only' => [
                    'ajax-add-district',
                    'ajax-del-district',
                    'ajax-product-list',
                    'ajax-del-items',
                    'ajax-update-list',
                    'ajax-remove-product',
                    'ajax-delete',
                    'ajax-list',
                    'update-area-list',
                    'ajax-category-list',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-add-district', 'ajax-del-district', 'ajax-product-list', 'ajax-update-list', 'ajax-del-items', 'ajax-remove-product', 'update-items', 'items-add','update-area-list','update-area','ajax-category-list'],
                        'allow' => true,
                        'roles' => ['administrator/update-clerk'],
                    ],
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                        'roles' => ['order-action/change-clerk'],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxAddDistrict()
    {
        $model = new ClerkAreaForm();
        if($model->load(Yii::$app->request->post()))
        {
            $district_id = Yii::$app->request->post('discrict_id');
            $model->district_id = $district_id;
            if($model->validate())
            {
                $model->save();
                Yii::$app->session->setFlash('success', '保存成功!');
                return ['status' => 200];
            }
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    //添加服务
    public function actionItemsAdd()
    {
        $model = new ClerkItemsForm();
        if($model->load(Yii::$app->request->post()))
        {
            $model->product_ids = Yii::$app->request->post('product_ids');
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->redirect(['administrator/clerk-update', 'id' => $model->clerk_id]);
    }

    //修改服务
    public function actionUpdateItems()
    {
        $ClerkItems = Yii::$app->request->post('ClerkItems');
        $model = $this->findClerkItems($ClerkItems['id']);
        if($model->load(Yii::$app->request->post()))
        {
            $model->setProductIds(Yii::$app->request->post('product_ids'));
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->redirect(['administrator/clerk-update', 'id' => $model->clerk_id]);
    }

    public function actionAjaxDelDistrict()
    {
        $model = new ClerkArea();
        $city_id = Yii::$app->request->post('city_id');
        $clerk_id = Yii::$app->request->post('clerk_id');
        if($model->del($city_id,$clerk_id))
        {
            return ['status' => 200];
        }
        return ['status' => 400];
    }

    public function actionAjaxDelItems($items_id)
    {
        $model = $this->findClerkItems($items_id);
        $model->delete();
        return ['status' => 200];
    }

    public function actionAjaxRemoveProduct()
    {
        $product_id = Yii::$app->getRequest()->post('product_id');
        $items_id = Yii::$app->getRequest()->post('items_id');
        $model = $this->findClerkItems($items_id);
        if($model->removeProduct($product_id))
        {
            return ['status' => 200];
        }
        return ['status' => 500, 'message' => '内部错误。'];
    }

    public function actionAjaxProductList()
    {
        $model = new Clerk();
        $category_id = Yii::$app->request->post('category_id');
        $products = $model->findModel($category_id);
        return ['status'=>200,'products'=>$products];
    }

    public function actionAjaxUpdateList()
    {
        $model = new Clerk();
        $category_id = Yii::$app->request->post('category_id');
        $clerk_items_id = Yii::$app->request->post('clerk_items_id');
        $items = $this->findClerkItems($clerk_items_id);
        $products = $model->findModel($category_id);
        return ['status'=>200,'products'=>$products,'items'=>$items->getProductIds()];
    }

    public function actionUpdateAreaList()
    {
        $clerk_id = Yii::$app->request->post('clerk_id');
        $city_id = Yii::$app->request->post('city_id');
        /** @var ClerkArea[] $clerk_areas */
        $clerk_areas = ClerkArea::find()->select('district_id,district_name')->where(['city_id'=>$city_id,'clerk_id'=>$clerk_id])->all();
        $area=[];
        foreach($clerk_areas as $key=>$value)
        {
            $area[$key] =$value->district_id;
        }
        $districts = District::find()->select('id,name')->where(['city_id'=>$city_id])->all();
        return ['status'=>200,'clerk_areas'=>$area,'districts'=>$districts];
    }

    public function actionUpdateArea()
    {
        $model = new ClerkAreaForm();
        $ClerkArea = new ClerkArea();
        if($model->load(Yii::$app->request->post()))
        {
            $district_id = Yii::$app->request->post('discrict_id');
            $model->district_id = $district_id;
            if($model->validate(['discrict_id','clerk_id']))
            {
                $t = Yii::$app->db->beginTransaction();
                try
                {
                    $ClerkArea->del($model->city_id,$model->clerk_id);
                    $model->save();
                    $t->commit();
                    Yii::$app->session->setFlash('success', '保存成功!');
                    return $this->redirect(['administrator/clerk-update', 'id' => $model->clerk_id]);
                }
                catch (\Exception $e)
                {
                    $t->rollBack();
                    throw $e;
                }
            }
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['administrator/clerk-update', 'id' => $model->clerk_id]);
    }

    public function actionAjaxCategoryList($keyword = null,$id = null)
    {
        /** @var ClerkItems[] $clerk_item */
        $clerk_item = ClerkItems::find()->where(['clerk_id'=>$id])->all();
        $parent_id = Yii::$app->getRequest()->get('parent_id', 0);
        $query = ProductCategory::find()->select(['id', 'name', 'parent_id'])->where('parent_id=:parent_id', [':parent_id' => $parent_id]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $categoryIds = [];
        foreach($clerk_item as $category)
        {
            $categoryIds[] = $category->category_id;
        }

        if(!empty($categoryIds))
        {
            $query->andWhere(['not in', 'id', $categoryIds]);
        }

        $data = $query->all();
        return ['status' => 200, 'categories' =>$data];
    }

    /**
     * 获取订单匹配的服务人员
     * @param $product_id
     * @param $district_id
     * @return array
     */
    public function actionAjaxList($product_id, $district_id)
    {
        $models = Clerk::findByMatchList($product_id, $district_id);
        $modelData = [];
        foreach($models as $model)
        {
            $clerkData = [
                'id' => $model->id,
                'name' => $model->name,
                'phone' => $model->phone,
                'address' => $model->getAddressName(),
            ];
            $modelData[] = $clerkData;
        }
        return ['status' => 200, 'models' => $modelData];
    }

    private function findClerkItems($id)
    {
        $model = ClerkItems::findOne($id);
        if (null == $model){
            throw new NotFoundHttpException('找不到指定的数据！');
        }
        return $model;
    }
}
