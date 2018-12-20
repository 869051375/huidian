<?php
namespace backend\controllers;


use backend\models\CouponForm;
use common\models\Coupon;
use common\models\CouponCode;
use common\models\Product;
use Yii;
use yii\base\Exception;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CouponController extends BaseController
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax-confirm' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'status',
                    'validation',
                    'ajax-list',
                    'ajax-remove-product',
                    'add-coupon-product'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-confirm'],
                        'allow' => true,
                        'roles' => ['coupon/confirm'],
                    ],
                    [
                        'actions' => ['status'],
                        'allow' => true,
                        'roles' => ['coupon/status'],
                    ],
                    [
                        'actions' => ['info'],
                        'allow' => true,
                        'roles' => ['coupon/info'],
                    ],
                    [
                        'actions' => ['create', 'validation'],
                        'allow' => true,
                        'roles' => ['coupon/create'],
                    ],
                    [
                        'actions' => ['ajax-list', 'update-info', 'add-coupon-product', 'ajax-remove-product'],
                        'allow' => true,
                        'roles' => ['coupon/update'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new CouponForm();
        $model->setScenario('insert');
        $url = Yii::$app->request->getReferrer();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $coupon = $model->save();
            if($coupon)
            {
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['update-info', 'id' => $coupon->id]);
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect($url);
    }

    public function actionUpdateInfo($id)
    {
        $coupon = $this->findModel($id);
        $model = new CouponForm();
        $model->setScenario('update');
        $model->setAttributes($coupon->attributes);
        $model->coupon_id = $coupon->id;
        $model->id = $coupon->id;
        if($model->load(Yii::$app->request->post()))
        {

            if($model->validate())
            {
                $model->update($coupon);
                Yii::$app->session->setFlash('success', '保存成功!');
                return $this->redirect(['/coupon-list/list', 'id' => $coupon->id]);
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->render('update', [
            'coupon' => $coupon,
            'model' => $model,
        ]);
    }

    public function actionValidation()
    {
        $data = Yii::$app->request->post('CouponForm');
        $model = new CouponForm();
        if(!empty($data['id']))
        {
            $model = $this->findModel($data['id']);
            $model->setScenario('update');
        }
        else
        {
            $model->setScenario('insert');
        }
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        return $this->render('info', [
            'model' => $model,
        ]);
    }

    //作废操作
    public function actionStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        if ($model->isObsoleted())
        return ['status' => 400, 'message' => '已作废不可修改'.$model->getFirstError('status')];
        $model->status = $status;
        if($model->isModeCouponCode() && $model->isCodeRandom())
        {
            $t = \Yii::$app->db->beginTransaction();
            try
            {
                $model->save(false);
                Yii::$app->db->createCommand()->update('coupon_code', ['status' => CouponCode::STATUS_OBSOLETED], [
                    'coupon_id' => $model->id])->execute();
                $t->commit();
                return ['status' => 200, 'is_obsoleted' => $model->isObsoleted()];
            }
            catch (Exception $e)
            {
                $t->rollBack();
                throw $e;
            }
        }
        else
        {
            if($model->validate(['status']) && $model->save(false))
            {
                return ['status' => 200, 'is_obsoleted' => $model->isObsoleted()];
            }
        }
        return ['status' => 400, 'message' => '修改失败：'.$model->getFirstError('status')];
    }

    //确认优惠卷
    public function actionAjaxConfirm($id)
    {
        $coupon = $this->findModel($id);
        $model = new CouponForm();
        $model->setScenario('confirm');
        $model->coupon = $coupon;
        if($model->validate())
        {
            if($model->confirm($coupon))
            {
                if($coupon->isModeCoupon()){
                    return $this->redirect(['coupon-list/list', 'id' => $coupon->id]);
                }else{
                    return $this->redirect(['coupon-list/code-list', 'id' => $coupon->id]);
                }
            }
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error',  reset($errors));

        if($coupon->isModeCoupon()) {
            return $this->redirect(['coupon-list/list', 'id' => $coupon->id]);
        }else{
            return $this->redirect(['coupon-list/code-list', 'id' => $coupon->id]);
        }
    }

    public function actionAjaxRemoveProduct()
    {
        $product_id = Yii::$app->getRequest()->post('product_id');
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->removeProduct($product_id))
        {
            return ['status' => 200];
        }
        return ['status' => 500, 'message' => '内部错误。'];
    }

    /**
     * 添加商品
     * @return array
     */
    public function actionAddCouponProduct()
    {
        $model = new CouponForm();
        $model->setScenario('addProduct');
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->saveCouponProduct())
            {
                $productId = Yii::$app->request->post('CouponForm')['product_id'];
                $product = Product::find()->andwhere(['id' => $productId])->asArray()->one();
                return ['status' => 200, 'product' => $this->serializeData($product)];
            }
            else
            {
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
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
            ->select(['id', 'name']);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        if(!empty($id))
        {
            $couponProduct = $this->findModel($id);
            if(null != $couponProduct)
            {
                $query->andWhere(['not in', 'id', $couponProduct->getProductIds()]);
            }
        }
        $query->orderBy(['id' => SORT_ASC]);
        return ['status' => 200, 'products' => $this->serializeData($query->all())];
    }

    /**
     * @param int $id
     * @return Coupon
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = Coupon::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的优惠券!');
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