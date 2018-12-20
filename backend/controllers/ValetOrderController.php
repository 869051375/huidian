<?php

namespace backend\controllers;

use backend\models\OrderUserSearch;
use backend\models\ValetOrderForm;
use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\DeleteOrderFileForm;
use common\models\Niche;
use common\models\NicheProduct;
use common\models\Order;
use common\models\OrderFile;
use common\models\OrderRecord;
use common\models\Product;
use common\models\User;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ValetOrderController implements the CRUD actions for Order model.
 */
class ValetOrderController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['get-user','subject-info','ajax-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create','get-user','subject-info','ajax-list'],
                        'allow' => true,
                        'roles' => ['order/create'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $get = Yii::$app->request->get();
        $niche = null;
        $niche_product = null;
        if (isset($get['niche_id']))
        {
            $query = new Query();
            $niche = $query->from(['ni'=>Niche::tableName()])->select('cc.name as customer_name,cc.user_id as customer_id,ni.administrator_id as administrator_id,ni.administrator_name as administrator_name,ni.id as id,bs.id as business_subject_id,bs.company_name as business_subject_name')
                ->leftJoin(['cc'=>CrmCustomer::tableName()],'ni.customer_id = cc.id')
                ->leftJoin(['bs'=>BusinessSubject::tableName()],'ni.business_subject_id = bs.id')
                ->where(['ni.id'=>$get['niche_id']])
                ->one();
            if (!empty($niche))
            {
                $query = new Query();
                $niche_product = $query->from(['np'=>NicheProduct::tableName()])->select('np.*,p.is_installment as is_installment')
                    ->leftJoin(['p'=>Product::tableName()],'np.product_id = p.id')
                    ->where(['np.niche_id'=>$niche['id']])
                    ->all();
//                $niche_product = NicheProduct::find()->where(['niche_id'=>$niche['id']])->all();
            }

//            $niche = Niche::find()->where(['id'=>$get['niche_id']])->one();
        }
        $model = new ValetOrderForm();
        if ($model->load(Yii::$app->request->post()))
        {
            $data = Yii::$app->request->post();
            if(count($data['ValetOrderForm']) < 6)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 400,'err' => '请选择至少选择一项商品'];
            }
            $model->products = $data['ValetOrderForm']['products'];
            $virtualOrder = $model->save();
            if($virtualOrder)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 200];
            }
            if($model->hasErrors())
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 400,'err' => reset($model->getFirstErrors())];
            }
        }
        $searchModel = new OrderUserSearch();
        return $this->render('create', ['searchModel' => $searchModel, 'model' => $model,'niche'=>$niche,'niche_product'=>$niche_product]);
    }

    //已有用户数据
    public function actionGetUser()
    {
        $model = new OrderUserSearch();
        $model->load(Yii::$app->request->post(),'');
        $data = $model->search();
        if($data == 2)
        {
            return ['status' => 400,'error' => reset($model->getFirstErrors()) ];
        }
        else if(empty($data))
        {
            return ['status'=> 400,'error'=> '对不起，您输入的有误！请精确输入客户名称或联系方式！' ];
        }
        return ['status' => 200,'data'=> $data];
    }

    //订单主体信息
    public function actionSubjectInfo($user_id)
    {
        $user = $this->findUser($user_id);
        /** @var BusinessSubject[] $models */
        $models = BusinessSubject::find()->where(['user_id' => $user->customer->user_id])->andWhere(['>','customer_id',0])->all();
        $data = [];
        foreach($models as $model)
        {
            if($model->subject_type)
            {
                $data[] = ['id' => $model->id, 'name' => $model->region,];
            }
            else
            {
                $data[] = ['id' => $model->id, 'name' => $model->company_name,];
            }
        }
        return ['status' => 200, 'data' => $data];
    }

    private function findUser($user_id)
    {
        $model = User::findOne($user_id);
        if(empty($model))
        {
            throw new NotFoundHttpException('找不到指定的用户');
        }
        return $model;
    }

//    /**
//     * @param null $keyword
//     * @return array
//     */
//    public function actionAjaxList($keyword=null)
//    {
//        /** @var ActiveQuery $query */
//        $query = Product::find()->alias('p')->joinWith(['productPrices pp'])->select(['p.id', 'p.name', 'p.alias', 'p.is_area_price', 'p.price', 'p.tax','p.is_installment','pp.province_name','pp.city_name','pp.district_name']);
//        if(!empty($keyword))
//        {
//            $query->where(['or', ['like', 'p.name', $keyword], ['like', 'p.alias', $keyword]]);
//        }
//
//        $query->andWhere('p.status=:status', [':status' => 1]);
//        return ['status' => 200, 'products' => $this->serializeData($query->all())];
//    }
//
//    protected function serializeData($data)
//    {
//        return Yii::createObject($this->serializer)->serialize($data);
//    }

    /**
     * @param $id
     * @return Order
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            if(!$model->isBelongs($user))
            {
                throw new NotFoundHttpException('找不到指定的订单!');
            }
            return $model;
        } else {
            throw new NotFoundHttpException('找不到指定的订单!');
        }
    }

    //删除文件
    public function actionDelete()
    {
        $model = new DeleteOrderFileForm();
        if ($model->load(Yii::$app->request->post(),''))
        {
            if($model->delete())
            {
                $file = OrderFile::findOne(Yii::$app->request->post('file_id'));
                if(null == $file)
                {
                    return ['status' => 200, 'has_file' => false];
                }
                else
                {
                    return ['status' => 200, 'has_file' => true];
                }
            }
        }
        if($model->hasErrors())
        {
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 400, 'message' => '您的请求有误。'];
    }


}