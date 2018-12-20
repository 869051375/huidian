<?php

namespace backend\controllers;

use backend\models\DeleteTrademarkImageForm;
use backend\models\OrderChangeBusinessSubjectForm;
use backend\models\OrderForm;
use backend\models\OrderInfoForm;
use backend\models\SettlementMonthForm;
use common\actions\UploadImageAction;
use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\Clerk;
use common\models\CostItem;
use common\models\CustomerService;
use common\models\DeleteOrderFileForm;
use common\models\ExperienceApply;
use common\models\Order;
use common\models\OrderBalanceRecord;
use common\models\OrderFile;
use common\models\OrderRecord;
use common\models\Trademark;
use common\models\TrademarkCategory;
use common\models\UploadImageForm;
use common\models\User;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete','validation','validation-trademark',
                    'ajax-trademark-category','trademark-delete', 'order-info-validation','change-business-subject'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['order/create'],
                    ],
                    [
                        'actions' => ['info','delete','upload','business-create','validation',
                            'validation-trademark', 'trademark-create','ajax-trademark-category',
                            'trademark-delete', 'order-info-create', 'order-info-validation','change-business-subject','update-order-service','get-service','get-clerk','update-order-clerk'],
                        'allow' => true,
                        'roles' => ['order/info'],
                    ],
                    [
                        'actions' => ['update-order-service'],
                        'allow' => true,
                        'roles' => ['order/update-order-service'],
                    ],
                    [
                        'actions' => ['update-order-clerk'],
                        'allow' => true,
                        'roles' => ['order/update-order-clerk'],
                    ],
                    [
                        'actions' => ['order/profit-update'],//预计子订单利润更正
                        'allow' => true,
                        'roles' => ['order/profit-update'],
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
                'keyTemplate' => 'business_subject/{date:Ymd}-{time}.{ext}',
                'thumbnailWidth' => 300,
                'thumbnailHeight' => 202,
            ],
        ];
    }

    public function actionCreate($user_id, $is_validate = 0, $experience_id = null)
    {
        $user = $this->findUserModel($user_id);
        //获取意向体验用户的商品
        $experienceApply = $this->findExperienceApplyModel($experience_id);
        $model = new OrderForm();
        $model->user = $user;
        $model->user_id = $user_id;
        if ($model->load(Yii::$app->request->post())) {
            if($is_validate)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
//            if($model->is_need_invoice)
//            {
//                $model->setScenario('need_invoice');
//            }

            $t = Yii::$app->db->beginTransaction();
            try
            {
                $virtualOrder = $model->save();
                $t->commit();
                //修改意向体验用户表的状态
                if($virtualOrder && $experienceApply)
                {
                    $experienceApply->status = ExperienceApply::ALREADY_DEAL;
                    $experienceApply->save(false);
                }
                if ($virtualOrder) {
                    //新增订单记录
                    /** @var Administrator $admin */
                    $admin = Yii::$app->user->identity;
                    foreach($virtualOrder->orders as $o)
                    {
                        OrderRecord::create($o->id, '订单提交成功', '', $admin);
                    }
                    Yii::$app->session->setFlash('success', '订单保存成功!');
                    if($user->is_vest)
                    {
                        return $this->redirect(['order-list/vest']);
                    }
                    else
                    {
                        return $this->redirect(['order-list/all']);
                    }
                }
            }
            catch (\Exception $e)
            {
                $t->rollBack();
                throw $e;
            }

            if ($model->hasErrors())
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
            else
            {
                Yii::$app->session->setFlash('error', '订单保存失败!');
            }
        }

        return $this->render('create', [
            'model' => $model,
            'user' => $user,
            'experienceApply' => $experienceApply,
        ]);
    }

    //查看详情
    public function actionInfo($id, $node_id = null)
    {
        $model = $this->findModel($id);
        $trademarkModel = $this->findTrademark($id);
        $businessSubject = $this->findBusinessSubject($model->business_subject_id);
        $query = CostItem::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);
        $orderQuery = OrderBalanceRecord::find()->where(['order_id' => $model->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $orderQuery,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        // 如果是ajax请求，则是请求评价列表，
        // 放到这个控制器下主要是为了兼容老旧浏览器不支持pjax
//        if(Yii::$app->request->isAjax)
//        {
//            return $this->renderAjax('cost', [
//                'provider' => $provider,
//            ]);
//        }
        return $this->render('info', [
            'id' => $id,
            'model' => $model,
            'trademarkModel' => $trademarkModel,
            'businessSubject' => $businessSubject,
            'node_id' => $node_id,
            'provider' => $provider,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionChangeBusinessSubject()
    {
        $model = new OrderChangeBusinessSubjectForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->change())
            {
                return ['status' => 200];
            }
            else
            {
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    /**
     * 订单详情新增/修改
     * @param int $id
     * @return Response
     */
    public function actionOrderInfoCreate($id)
    {
        $order = $this->findModel($id);
        $model = new OrderInfoForm();
        if($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                $model->save($order);
                Yii::$app->session->setFlash('success', '保存成功!');
            }else{
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->redirect(['info', 'id' => $order->id]);
    }

    /**
     * @return array
     */
    public function actionOrderInfoValidation()
    {
        $model = new OrderInfoForm();
        if($model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }

    //订单主体信息表
    private function findBusinessSubject($id)
    {
        $model = BusinessSubject::findOne($id);
        if(empty($model))
        {
            return null;
        }
        return $this->modifySubjectInfo($model);
    }

    private function modifySubjectInfo($model)
    {
        /**@var BusinessSubject $model**/
        $model->address = $model->province_name.$model->city_name.$model->district_name.$model->address;
        $model->tax_type = $model->getTxtName();
        $model->operating_period_begin = empty($model->operating_period_begin)? '' :\Yii::$app->formatter->asDate($model->operating_period_begin);
        $model->operating_period_end = empty($model->operating_period_end)? '' :\Yii::$app->formatter->asDate($model->operating_period_end);
        return $model;
    }

    //商标信息新增/修改
    public function actionTrademarkCreate()
    {
        $data = Yii::$app->request->post('Trademark');
        $model = $this->findTrademarkModel($data['id']);
        if($model->load(Yii::$app->request->post()))
        {
            $model->getUserId();
            if($model->validate())
            {
                $model->save(false);
                Yii::$app->session->setFlash('success', '保存成功!');
            }else{
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        return $this->redirect(['info', 'id' => $model->order_id]);
    }

    private function findTrademarkModel($id)
    {
        $model = Trademark::findOne($id);
        if(empty($model))
        {
            $model = new Trademark();
        }
        return $model;
    }

    public function actionAjaxTrademarkCategory($keyword = null)
    {
        $query = TrademarkCategory::find()->select(['id', 'name']);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $query->orderBy(['id' => SORT_ASC]);
        return ['status' => 200, 'provinces' => $query->all()];
    }

    public function actionTrademarkDelete()
    {
        $model = new DeleteTrademarkImageForm();
        if ($model->load(Yii::$app->request->post(),''))
        {
            if($model->delete())
            {
                return ['status' => 200];
            }
        }
        if($model->hasErrors())
        {
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return ['status' => 400, 'message' => '您的请求有误。'];
    }

    //商标信息表
    private function findTrademark($id)
    {
        $model = Trademark::find()->where(['order_id'=>$id])->one();
        if(empty($model)){
            $model = new Trademark();
            $model->loadDefaultValues();
        }
        return $model;
    }

    public function actionValidationTrademark()
    {
        $model = new Trademark();
        $model->load(Yii::$app->request->post());
        return ActiveForm::validate($model);
    }

    /**
     * @param $id
     * @return Order
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
//            /** @var Administrator $user */
//            $user = Yii::$app->user->identity;
//            if(!$model->isBelongs($user))
//            {
//                throw new NotFoundHttpException('找不到指定的订单!');
//            }
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
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return ['status' => 400, 'message' => '您的请求有误。'];
    }

    private function findUserModel($id)
    {
        $user = User::findOne($id);
        if (null == $user) {
            throw new NotFoundHttpException('找不到客户!');
        }
        return $user;
    }

    private function findExperienceApplyModel($id)
    {
        $experienceApply = ExperienceApply::findOne($id);
        if (null == $experienceApply || $experienceApply->status == ExperienceApply::ALREADY_DEAL)
        {
            return null;
        }
        return $experienceApply;
    }
    //获取客服列表
    public function actionGetService(){
        $model = new CustomerService();
        $rs = $model -> getService();
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $rs;
    }

    //批量更换客服人员
    public function actionUpdateOrderService(){
        $model = new Order();
        $arr = Yii::$app->request->post();
//        $arr['order_id'] = ['1','2','3'];
//        $arr['service_id'] = 8;
//        $arr['service_name'] = '李笑笑';
//        $arr['administrator_id'] = 78;

        if(count($arr['order_id']) == 0){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['code' => 400,'message' => '请选择要修改的订单'];
        }

        if($arr['service_id'] == '' || $arr['service_name'] == '' || $arr['administrator_id'] == ''){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['code' => 400,'message' => '请选择要更换的客服人员'];
        }

        $model -> load($arr,'');
        $rs = $model -> updateCustomerService();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if($rs == true){
            return ['code' => 200,'message' => '修改成功'];
        }else{
            return ['code' => 400,'message' => '修改失败'];
        }
    }


    //批量获取服务列表
    public function actionGetClerk(){
        $model = new Clerk();
        $rs = $model -> getClerk();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $rs;
    }
    //批量更换服务人员
    public function actionUpdateOrderClerk(){
        $model = new Order();
        $arr = Yii::$app->request->post();
//        $arr['order_id'] = ['1','2','3'];
//        $arr['clerk_id'] = 19;
//        $arr['clerk_name'] = '李相';
//        $arr['administrator_id'] = 112;

        if(count($arr['order_id']) == 0){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['code' => 400,'message' => '请选择要修改的订单'];
        }

        if($arr['clerk_id'] == '' || $arr['clerk_name'] == '' || $arr['administrator_id']== ''){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['code' => 400,'message' => '请选择要更换的服务人员'];
        }

        $model -> load($arr,'');
        $rs = $model -> updateOrderClerk();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if($rs == true){
            return ['code' => 200,'message' => '修改成功'];
        }else{
            return ['code' => 400,'message' => '修改失败'];
        }
    }

}