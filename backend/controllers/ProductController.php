<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/3
 * Time: 上午9:14
 */

namespace backend\controllers;

use backend\models\DistrictPriceForm;
use backend\models\DistrictPriceUpdateForm;
use backend\models\PackagePriceForm;
use backend\models\PackageProductsForm;
use backend\models\PriceDetailForm;
use backend\models\PriceForm;
use backend\models\ProductForm;
use backend\models\ProductSearch;
use common\models\AdministratorLog;
use common\models\District;
use common\models\Industry;
use common\models\OpportunityAssignDepartment;
use common\models\PackageProduct;
use common\models\Product;
use common\models\ProductPrice;
use common\models\ProductSeo;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ProductController extends BaseController
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
                'only' => [
                    'ajax-list',
                    'status',
                    'validation-price-detail',
                    'validation-district-price',
                    'save-price-detail',
                    'delete-price-detail',
                    'save-district-price',
                    'district-price-status',
                    'delete-district-price',
                    'ajax-district-price-detail',
                    'ajax-districts',
                    'update-district-price',
                    'ajax-un-set-districts',
                    'save-price',
                    'save-package-price',
                    'bath-district-price-status',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-list', 'ajax-districts', 'ajax-un-set-districts'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['status'],
                        'allow' => true,
                        'roles' => ['product/status'],
                    ],
                    [
                        'actions' => ['list', 'package-list'],
                        'allow' => true,
                        'roles' => ['product/list'],
                    ],
                    [
                        'actions' => ['create', 'package-create'],
                        'allow' => true,
                        'roles' => ['product/create'],
                    ],
                    [
                        'actions' => ['update', 'package-update'],
                        'allow' => true,
                        'roles' => ['product/update'],
                    ],
                    [
                        'actions' => ['seo'],
                        'allow' => true,
                        'roles' => ['product/seo'],
                    ],
                    [
                        'actions' => ['price', 'ajax-district-price-detail'],
                        'allow' => true,
                        'roles' => ['product-price/list'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['product/export'],
                    ],
                    [
                        'actions' => [
                            'save-price-detail',
                            'validation-price-detail',
                            'validation-district-price',
                            'delete-price-detail',
                            'district-price-status',
                            'delete-district-price',
                            'ajax-district-price-detail',
                            'save-price',
                            'save-package-price',
                            'save-district-price',
                            'update-district-price',
                            'bath-district-price-status',
                        ],
                        'allow' => true,
                        'roles' => ['product-price/update'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        return $this->searchProducts(Product::PACKAGE_DISABLED);
    }

    public function actionPackageList()
    {
        return $this->searchProducts(Product::PACKAGE_ACTIVE);
    }

    private function searchProducts($status)
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        /** @var Query $query */
//        $query = $dataProvider->query;
//        $query->select(['id', 'name', 'flow_id', 'price', 'original_price', 'is_hot', 'is_home', 'is_home_nav',
//            'status', 'home_nav_sort', 'home_sort', 'is_area_price', 'is_bargain', 'is_package', 'is_confirm']);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * 新增商品
     */
    public function actionCreate()
    {
        $model = new ProductForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $product = $model->save();
            if($product){
                Yii::$app->session->setFlash('success', '商品保存成功!');
                if(Yii::$app->request->post('next') == 'save-next'){
                    if(Yii::$app->user->can('product/price')){
                        return $this->redirect(['price', 'product_id' => $product->id]);
                    }
                    return $this->redirect(['product-related/list', 'id' => $product->id]);
                }
                return $this->redirect(['update', 'id' => $product->id]);
            }
            Yii::$app->session->setFlash('error', '保存失败!');
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败, 您的表单填写有误, 请检查!');
        }
        $industries = Industry::find()->select(['id', 'name'])->asArray()->all();
        $addressList = Product::find()->select(['id', 'name'])->where(['type'=> Product::TYPE_ADDRESS])->asArray()->all();
        return $this->render('create', [
            'model' => $model,
            'industries' => $industries,
            'addressList' => $addressList,
        ]);
    }

    /**
     * 新增套餐
     */
    public function actionPackageCreate()
    {
        $model = new PackageProductsForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $product = $model->save();
            if($product){
                Yii::$app->session->setFlash('success', '套餐保存成功!');
                if(Yii::$app->request->post('next') == 'save-next'){

                    return $this->redirect(['package-product/list', 'id' => $product->id]);
                }
                return $this->redirect(['package-update', 'id' => $product->id]);
            }
            Yii::$app->session->setFlash('error', '保存失败!');
        }
        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败, 您的表单填写有误, 请检查!');
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    // 商品编辑
    public function actionUpdate($id)
    {
        $product = $this->findModel($id);
        $model = new ProductForm();
        $model->setAttributes($product->attributes);
//        $model->industries = $product->getIndustryIds();
//        $model->address_list = $product->getAddressIds();
        if($model->load(Yii::$app->request->post())){
            if($model->update($product)){
                Yii::$app->session->setFlash('success', '更新成功!');
                if(Yii::$app->request->post('next') == 'save-next'){
                    return $this->redirect(['price', 'product_id' => $product->id]);
                }
                return $this->redirect(['update', 'id' => $product->id]);
            }

            Yii::$app->session->setFlash('error', '更新失败!');
        }

        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '更新失败, 您的表单填写有误, 请检查!');
        }
        $industries = Industry::find()->select(['id', 'name'])->asArray()->all();
        $addressList = Product::find()->select(['id', 'name'])->where(['type'=> Product::TYPE_ADDRESS])->asArray()->all();
        return $this->render('update', [
            'model' => $model,
            'product' => $product,
            'industries' => $industries,
            'addressList' => $addressList,
        ]);
    }

    // 套餐编辑
    public function actionPackageUpdate($id)
    {
        $product = $this->findModel($id);
        $model = new PackageProductsForm();
        $model->setAttributes($product->attributes);
        if($model->load(Yii::$app->request->post())){
            if($model->update($product)){
                Yii::$app->session->setFlash('success', '更新成功!');
                if(Yii::$app->request->post('next') == 'save-next'){
                    return $this->redirect(['package-product/list', 'id' => $product->id]);
                }
                return $this->redirect(['package-update', 'id' => $product->id]);
            }

            Yii::$app->session->setFlash('error', '更新失败!');
        }

        if ($model->hasErrors()) {
            Yii::$app->session->setFlash('error', '更新失败, 您的表单填写有误, 请检查!');
        }
        return $this->render('update', [
            'model' => $model,
            'product' => $product,
        ]);
    }

    public function actionSavePrice($product_id, $save_name)
    {

        $product = $this->findModel($product_id);
        $model = new PriceForm();
        if($model->load(Yii::$app->request->post()))
        {

            if($model->save($product))
            {
                $url = '';
                if($save_name == 'save-next')
                {
                   $url = '/opportunity-assign-department/list?product_id='.$product->id;
                }
                return ['status' => 200, 'url' => $url];
            }
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
            return ['status' => 500, 'message' => '内部错误!'];
    }

    //套餐商品价格
    public function actionSavePackagePrice($product_id, $save_name)
    {
        $product = $this->findModel($product_id);
        $model = new PackagePriceForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->save($product))
            {
                $url = '';
                if($save_name == 'save-next')
                {
                    $url = '/product-related/list?id='.$product->id;
                }
                return ['status' => 200, 'url' => $url];
            }
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return ['status' => 500, 'message' => '内部错误!'];
    }

    public function actionPrice($product_id)
    {
        $product = $this->findModel($product_id);
        if($product->isPackage())
        {
            $model = new PackagePriceForm();
        }
        else
        {
            $model = new PriceForm();
        }
        $model->setScenario('init-form');
        $model->load($product->attributes, '');
        return $this->render('price', [
            'product' => $product,
            'model' => $model,
            'province_id' => $province_id=[],
            'city_id' => $city_id=[],
            'district_id' => $district_id=[],
        ]);
    }
    public function actionSavePriceDetail($product_id = null, $product_price_id = null)
    {
        $model = null;
        $remit_amount = 0.00;
        if($product_id)
        {
            /** @var Product $model */
            $model = $this->findModel($product_id);
        }
        else
        {
            /** @var ProductPrice $model */
            $model = $this->findProductPrice($product_price_id);
            $packageModel = $this->findModel($model->product_id);
            if($packageModel->isPackage() && $packageModel->isAreaPrice())
            {
                $remit_amount = BC::sub($model->original_price,$model->price) > 0 ? BC::sub($model->original_price,$model->price) : 0.00;
            }
        }
        $formModel = new PriceDetailForm();
        if($formModel->load(Yii::$app->request->post()) && $formModel->validate()){
            $item = $formModel->save($model);
            if($item)
            {
                //新增后台操作日志
                AdministratorLog::logSavePriceDetail($product_id, $product_price_id);
                //套餐商品的优惠金额展示
                if($product_price_id)
                {
                    $packageModel = $this->findModel($model->product_id);
                    if($packageModel->isPackage() && $packageModel->isAreaPrice())
                    {
                        $remit_amount = BC::sub($model->original_price,$model->price) > 0 ? BC::sub($model->original_price,$model->price) : 0.00;
                    }
                }
                else
                {
                    //获取非区分区域优惠金额
                    $remit_amount = BC::sub(PackageProduct::getPackageOriginalPrice($model),$model->price) > 0 ? BC::sub(PackageProduct::getPackageOriginalPrice($model), $model->price) : 0.00;
                }
                return ['status' => 200, 'priceDetail' => $item,
                    'total_tax' => $model->tax, 'total_price' => $model->price, 'remit_amount' => $remit_amount];
            }
            return ['status' => 500, 'message' => '内部错误!'];
        }
        $errors = $formModel->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionValidationPriceDetail()
    {
        $model = new PriceDetailForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }
    // 删除价格明细
    public function actionDeletePriceDetail()
    {
        $id = Yii::$app->getRequest()->post('id');
        $product_price_id = Yii::$app->getRequest()->post('product_price_id');
        $key = Yii::$app->getRequest()->post('key');
        $model = null;
        if($id)
        {
            $model = $this->findModel($id);
        }
        else
        {
            $model = $this->findProductPrice($product_price_id);
        }
        $model->removePriceDetail($key);
        //新增后台操作日志
        AdministratorLog::logDeletePriceDetail($id, $product_price_id);
        $model->save(false);
        $remit_amount = 0;
        /** @var Product $model */
        if($id)
        {
            if($model->isPackage() && !$model->isAreaPrice() && !$model->isBargain())
            {
                //获取非区分区域优惠金额
                $remit_amount = BC::sub(PackageProduct::getPackageOriginalPrice($model), $model->price) > 0 ? BC::sub(PackageProduct::getPackageOriginalPrice($model), $model->price) : 0.00;
            }
        }
        return ['status' => 200, 'total_tax' => $model->tax, 'total_price' => $model->price, 'remit_amount' => $remit_amount];
    }

    public function actionSaveDistrictPrice()
    {
        $model = new DistrictPriceForm();
        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $price = $model->save();
            if($price)
            {
                $packageProduct = $this->findModel($model->product_id);
                $packageOriginalPrice = 0.00;
                foreach ($packageProduct->packageProducts as $product)
                {
                    //如果套餐区分区域，则对商品进行区域校验
                    if($product->isAreaPrice())
                    {
                        $productPrice = $product->getProductPriceByDistrict($model->district_id);
                        if(null == $productPrice) return ['status' => 400];
//                        $packageOriginalPrice = BC::add($productPrice->original_price, $packageOriginalPrice);
                        $packageOriginalPrice = BC::add($productPrice->price, $packageOriginalPrice);
                    }
                    else if($product->isBargain())
                    {
                        //return ['status' => 200, 'packageOriginalPrice' => '0.00'];
                    }
                    else
                    {
//                        $packageOriginalPrice = BC::add($product->original_price, $packageOriginalPrice);
                        $packageOriginalPrice = BC::add($product->price, $packageOriginalPrice);
                    }
                }
                $price->refresh();
                return ['status' => 200, 'productPrice' => $price, 'packageOriginalPrice' => $packageOriginalPrice];
            }
            return ['status' => 500, 'message' => '内部错误!'];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionUpdateDistrictPrice()
    {
        $model = new DistrictPriceUpdateForm();
        if($model->load(Yii::$app->request->post()) && $model->validate()){
            $price = $model->save();
            if($price)
            {
                //新增后台操作日志
                AdministratorLog::logUpdateDistrictPrice($price);
                $price->refresh();
                return ['status' => 200, 'productPrice' => $price];
            }
            return ['status' => 500, 'message' => '内部错误!'];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionValidationDistrictPrice()
    {
        $model = new DistrictPriceForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }

    /**
     * @param null $id
     * @param null $keyword
     * @param null $category_id
     * @param null $status
     * @param null $is_package
     * @param null $is_show_list
     * @param null $company_id
     * @return array
     */
    public function actionAjaxList($id=null, $keyword=null, $category_id=null, $status =null, $is_package = null, $is_show_list = null, $company_id = null)
    {
        /** @var ActiveQuery $query */
        $query = Product::find()->alias('p')->select(['p.id', 'p.name', 'p.alias', 'p.is_area_price','p.is_bargain', 'p.price', 'p.tax', 'p.is_installment', 'p.original_price']);
        if(!empty($id))
        {
            $query->andWhere(['<>', 'p.id', $id]);
        }
        if(!empty($keyword))
        {
            $query->where(['or', ['like', 'p.name', $keyword], ['like', 'p.alias', $keyword]]);
        }

        if(!empty($category_id))
        {
            $query->andWhere('p.category_id=:category_id', [':category_id' => $category_id]);
        }

        if(!empty($status))
        {
            $query->andWhere('p.status=:status', [':status' => $status]);
        }

        if($is_package !== null)
        {
            $query->andWhere('p.is_package=:is_package', [':is_package' => $is_package ? 1 : 0]);
        }

        if($is_show_list !== null)
        {
            $query->andWhere('p.is_show_list=:is_show_list', [':is_show_list' => $is_show_list ? 1 : 0]);
        }
        if(!empty($company_id))
        {
            $query->innerJoinWith('opportunityAssignDepartments d');
            $query->andWhere('d.company_id=:company_id', [':company_id' => $company_id ? $company_id : 0]);
        }

        return ['status' => 200, 'products' => $this->serializeData($query->all())];
    }

    public function actionStatus()
    {
        $status = Yii::$app->request->post('status');
        $product_id = Yii::$app->request->post('product_id');
        $model = $this->findModel($product_id);
        $model->status = $status;
        if($model->validate(['status']) && $model->save(false))
        {
            //新增后台操作日志
            AdministratorLog::logProductStatus($model);
            return ['status' => 200, 'is_online' => $model->isOnline()];
        }
        return ['status' => 400, 'message' => '状态修改失败：'.$model->getFirstError('status')];
    }

    // 启用禁用
    public function actionDistrictPriceStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findProductPrice($id);
        $model->status = $status;
        if($model->validate(['status']) && $model->save(false))
        {
            //新增后台操作日志
            AdministratorLog::logDistrictPriceStatus($model);
            return ['status' => 200, 'is_enabled' => $model->isEnabled()];
        }
        return ['status' => 400, 'message' => '状态修改失败：'.$model->getFirstError('status')];
    }

    // 批量启用禁用
    public function actionBathDistrictPriceStatus()
    {
        $ids = Yii::$app->request->post('ids');
        $status = Yii::$app->request->get('status');
        $t = Yii::$app->db->beginTransaction();
        try
        {
            if(!empty($ids))
            {
                foreach ($ids as $id)
                {
                    $model = $this->findProductPrice($id);
                    if($model->status != $status)
                    {
                        $model->status = $status;
                        if($model->validate(['status']) && $model->save(false))
                        {
                            //新增后台操作日志
                            AdministratorLog::logDistrictPriceStatus($model);
                        }
                    }
                }
                $t->commit();
                return ['status' => 200, 'is_enabled' => $status ? true : false];
            }
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            return ['status' => 400, 'message' => '批量状态修改失败'];
        }
        return ['status' => 403, 'message' => '您的操作有误'];
    }

    public function actionAjaxDistrictPriceDetail($product_price_id)
    {
        $model = $this->findProductPrice($product_price_id);
        return ['status' => 200, 'priceDetail' => $model->getPriceDetail()];
    }

    private function findProductPrice($id)
    {
        $model = ProductPrice::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的地区价格!');
        }
        return $model;
    }

    //删除地区价格
    public function actionDeleteDistrictPrice()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findProductPrice($id);
        AdministratorLog::logDeleteDistrictPrice($model);
        $model->delete();
        return ['status' => 200];
    }

    public function actionSeo($product_id)
    {
        $product = $this->findModel($product_id);
        $model= ProductSeo::findOne($product_id);
        if(null == $model)
        {
            $model = new ProductSeo();
            $model->product_id = $product->id;
            $model->save(false);
        }

        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['seo', 'product_id' => $product->id]);
            }
            if($model->hasErrors())
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }

        return $this->render('seo', [
            'product' => $product,
            'model' => $model,
        ]);
    }

    public function actionAjaxDistricts($keyword = null)
    {
        $product_id = Yii::$app->request->get('product_id');
        $product = $this->findModel($product_id);
        $data = [];
        if(null != $product && $product->isAreaPrice()){

            /** @var ProductPrice[] $districts */
            $query = ProductPrice::find()->where('product_id=:product_id',
                [':product_id' => $product_id]);
//                ->andWhere(['status' => ProductPrice::STATUS_ENABLED]);
            if(!empty($keyword))
            {
                $query->andWhere(['or', ['like', 'province_name', $keyword], ['like', 'city_name', $keyword], ['like', 'district_name', $keyword]]);
            }
            $districts = $query->all();

            if (null == $districts) {
//                throw new NotFoundHttpException('找不到指定的商品!');
                return ['status' => 200, 'districts' => []];
            }

            foreach ($districts as $district)
            {
                $data[] = [
                    'id' => $district->id,
                    'name' => $district->getRegionFullName().'('.Yii::$app->formatter->asCurrency($district->price).')',
                    'price' => $district->price,
                    'original_price' => $district->original_price,
                ];
            }
        }

        return ['status' => 200, 'districts' => $data];
    }

    public function actionAjaxUnSetDistricts($city_id, $product_id, $keyword = null)
    {
        $query = District::find()->select(['id', 'name'])->where(['city_id' => $city_id]);
        /** @var ProductPrice[] $productPrices */
        $productPrices = ProductPrice::find()->where('product_id=:product_id',
            [':product_id' => $product_id])->all();
        $districtIds = [];
        foreach($productPrices as $productPrice)
        {
            $districtIds[] = $productPrice->district_id;
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        if(!empty($districtIds))
        {
            $query->andWhere(['not in', 'id', $districtIds]);
        }
        $query->orderBy(['sort' => SORT_ASC]);
        return ['status' => 200, 'districts' => $this->serializeData($query->all())];
    }

    public function actionExport()
    {
        $url = Yii::$app->request->getReferrer();
        $export_code = Yii::$app->cache->get('export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            Yii::$app->session->setFlash('error', '您的操作过于频繁，请等待'.$second.'秒！');
            return $this->redirect($url);
        }
        /** @var Product[] $models */
        $models = Product::find()->all();
        $csv = Writer::createFromString('');
        $header = ['一级分类','二级分类','商品id','商品名称','商品别名','关联商品名称','是否面议','是否区域价格','省份','城市','区县','区县id','商品是否下线','是否上下线','区域价格','商品价格','所属公司','所属部门','是否分期付款'];
        $csv->insertOne($header);
        foreach ($models as  $product)
        {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
            /** @var OpportunityAssignDepartment[] $departments */
            $departments = $product->opportunityAssignDepartments;
                if ($product->isAreaPrice())
                {
                    /** @var ProductPrice[] $prices */
                    $prices = $product->prices;
                    foreach ($prices as $price)
                    {
                        if($departments)
                        {
                            foreach ($departments as $department)
                            {
                                $csv->insertOne([
                                    "\t" . $product->topCategory->name,
                                    "\t" . $product->category->name,
                                    "\t" . $product->id,
                                    "\t" . $product->name,
                                    "\t" . $product->alias,
                                    "\t" . $product->spec_name,//关联商品名称
                                    "\t" . (!empty($product->is_bargain) ? '是' : '否'),
                                    "\t" . (!empty($product->is_area_price) ? '是' : '否'),
                                    "\t" . $price->province_name,
                                    "\t" . $price->city_name,
                                    "\t" . $price->district_name,
                                    "\t" . $price->district_id,
                                    "\t" . (!empty($product->status) ? '是' : '否'),
                                    "\t" . (!empty($price->status) ? '是' : '否'),
                                    "\t" . $price->price,
                                    "\t" . $product->price,
                                    "\t" . (isset($department->company) ? $department->company->name : ''),
                                    "\t" . (isset($department->department) ? $department->department->name : ''),
                                    "\t" . (!empty($product->is_installment) ? '是' : '否'),
                                ]);
                            }
                        }
                        else
                        {
                            $csv->insertOne([
                                "\t" . (isset($product->topCategory) ? $product->topCategory->name : ''),
                                "\t" . (isset($product->category) ? $product->category->name : ''),
                                "\t" . $product->id,
                                "\t" . $product->name,
                                "\t" . $product->alias,
                                "\t" . $product->spec_name,//关联商品名称
                                "\t" . (!empty($product->is_bargain) ? '是' : '否'),
                                "\t" . (!empty($product->is_area_price) ? '是' : '否'),
                                "\t" . $price->province_name,
                                "\t" . $price->city_name,
                                "\t" . $price->district_name,
                                "\t" . $price->district_id,
                                "\t" . (!empty($product->status) ? '是' : '否'),
                                "\t" . (!empty($price->status) ? '是' : '否'),
                                "\t" . $price->price,
                                "\t" . $product->price,
                                "\t" . '',
                                "\t" . '',
                                "\t" . (!empty($product->is_installment) ? '是' : '否'),
                            ]);
                        }
                    }
                }
                else
                {
                    if($departments)
                    {
                        foreach ($departments as $department)
                        {
                            $csv->insertOne([
                                "\t" . (isset($product->topCategory) ? $product->topCategory->name : ''),
                                "\t" . (isset($product->category) ? $product->category->name : ''),
                                "\t" . $product->id,
                                "\t" . $product->name,
                                "\t" . $product->alias,
                                "\t" . $product->spec_name,//关联商品名称
                                "\t" . (!empty($product->is_bargain) ? '是' : '否'),
                                "\t" . (!empty($product->is_area_price) ? '是' : '否'),
                                "\t" . '',
                                "\t" . '',
                                "\t" . '',
                                "\t" . '',
                                "\t" . (!empty($product->status) ? '是' : '否'),
                                "\t" . (!empty($price->status) ? '是' : '否'),
                                "\t" . '',
                                "\t" . $product->price,
                                "\t" . (isset($department->company) ? $department->company->name : ''),
                                "\t" . (isset($department->department) ? $department->department->name : ''),
                                "\t" . (!empty($product->is_installment) ? '是' : '否'),
                            ]);
                        }
                    }
                    else
                    {
                        $csv->insertOne([
                            "\t" . (isset($product->topCategory) ? $product->topCategory->name : ''),
                            "\t" . (isset($product->category) ? $product->category->name : ''),
                            "\t" . $product->id,
                            "\t" . $product->name,
                            "\t" . $product->alias,
                            "\t" . $product->spec_name,//关联商品名称
                            "\t" . (!empty($product->is_bargain) ? '是' : '否'),
                            "\t" . (!empty($product->is_area_price) ? '是' : '否'),
                            "\t" . '',
                            "\t" . '',
                            "\t" . '',
                            "\t" . '',
                            "\t" . (!empty($product->status) ? '是' : '否'),
                            "\t" . (!empty($price->status) ? '是' : '否'),
                            "\t" . '',
                            "\t" . $product->price,
                            "\t" . '',
                            "\t" . '',
                            "\t" . (!empty($product->is_installment) ? '是' : '否'),
                        ]);
                    }
                }
        }

        $filename = date('YmdHis').'商品记录.csv';
        Yii::$app->cache->set('export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
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
            throw new NotFoundHttpException('找不到指定的产品!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}