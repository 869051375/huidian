<?php
namespace backend\controllers;

use backend\models\PackageProductConfirmForm;
use backend\models\PackageProductForm;
use common\models\PackageProduct;
use common\models\Product;
use common\models\ProductCategory;
use common\utils\BC;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PackageProductController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax-confirm' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete','validation','ajax-list', 'ajax-sort', 'package-product-price'],
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
                        'actions' => [
                            'list',
                            'create',
                            'validation',
                            'delete',
                            'ajax-confirm',
                            'ajax-sort',
                            'package-product-price'
                        ],
                        'allow' => true,
                        'roles' => ['product/update'],
                    ],
                ],
            ],
        ];
    }
    //套餐商品列表
    public function actionList($id)
    {
        $model = $this->findModel($id);
        $query = (new Query())
            ->select(['pp.package_id', 'pp.product_id', 'pp.sort', 'p.top_category_id', 'p.category_id', 'p.name', 'top_category_name' => 'pc.name', 'category_name' => 'ppc.name',])
            ->from(['p' => Product::tableName()])
            ->innerJoin(['pp' => PackageProduct::tableName()], 'p.id = pp.product_id')
            ->innerJoin(['pc' => ProductCategory::tableName()], 'p.top_category_id = pc.id')
            ->innerJoin(['ppc' => ProductCategory::tableName()], 'p.category_id = ppc.id')
            ->where(['pp.package_id' => $model->id])
            ->orderBy(['pp.sort' => SORT_ASC]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', ['provider' => $provider, 'product' => $model]);
    }

    //添加套餐商品
    public function actionCreate()
    {
        $model = new PackageProductForm();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            $model->save();
            Yii::$app->session->setFlash('success', '套餐商品添加成功!');
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($errors) : '添加商品失败!');
        }
        return $this->redirect(['list','id'=> $post['PackageProductForm']['package_id']]);
    }

    //删除套餐商品
    public function actionDelete()
    {
        $package_id = Yii::$app->getRequest()->post('package_id');
        $product_id = Yii::$app->getRequest()->post('product_id');
        if(PackageProduct::deleteAll('package_id=:package_id and product_id=:product_id', [':package_id' => $package_id, ':product_id' => $product_id])){

            return ['status'=>200];
        }
        return ['status'=>400];
    }

    //套餐商品排序
    public function actionAjaxSort($package_id)
    {
        $product = $this->findModel($package_id);

        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');

        $source = $this->findPackageProductModel($product, $source_id);
        $target = $this->findPackageProductModel($product, $target_id);

        // 交换两个商品的排序序号
        $sort = $target->sort;
        $target->sort = $source->sort;
        $source->sort = $sort;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    private function findPackageProductModel($product, $id)
    {
        $model = PackageProduct::find()->where(['package_id' => $product->id, 'product_id' => $id])->one();
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到套餐商品!');
        }
        return $model;
    }

    //确认套餐内商品
    public function actionAjaxConfirm($id)
    {
        $product = $this->findModel($id);
        $model = new PackageProductConfirmForm();
        $model->product = $product;
        if($model->validate())
        {
            if($model->confirm($product))
            {
                return $this->redirect(['package-product/list', 'id' => $product->id]);
            }
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error',  reset($errors));
        return $this->redirect(['package-product/list', 'id' => $product->id]);
    }

    public function actionAjaxList($keyword=null)
    {
        /** @var ActiveQuery $query */
        $query = Product::find()->select(['id', 'name', 'alias'])->where(['is_package' => Product::PACKAGE_DISABLED, 'is_bargain' => 0]);
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

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new PackageProductForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    //获取套餐商品下的商品区域原售价
    public function actionPackageProductPrice($product_id = null, $district_id = null)
    {
        $packageProduct = $this->findModel($product_id);

        $packageOriginalPrice = 0.00;

        foreach ($packageProduct->packageProducts as $product)
        {
            //如果套餐区分区域，则对商品进行区域校验
            if($product->isAreaPrice())
            {
                $productPrice = $product->getProductPriceByDistrict($district_id);
                if(null == $productPrice) return ['status' => 400];
                $packageOriginalPrice = BC::add($productPrice->price, $packageOriginalPrice);

            }
            else if($product->isBargain())
            {
                //return ['status' => 200, 'packageOriginalPrice' => '0.00'];
            }
            else
            {
                $packageOriginalPrice = BC::add($product->price, $packageOriginalPrice);
            }
        }
        return ['status' => 200, 'packageOriginalPrice' => $packageOriginalPrice];
    }
}