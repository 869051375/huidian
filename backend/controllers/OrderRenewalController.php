<?php

namespace backend\controllers;

use backend\models\OrderRenewalForm;
use backend\models\OrderRenewalSearch;
use backend\models\SendRemindSmsForm;
use common\models\BusinessSubject;
use common\models\Order;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class OrderRenewalController extends BaseController
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
                    'ajax-list',
                    'validation',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['pending-renewal', 'already-renewal', 'no-renewal'],
                        'allow' => true,
                        'roles' => ['order-renewal/list'],
                    ],
                    [
                        'actions' => ['create', 'validation'],
                        'allow' => true,
                        'roles' => ['order-renewal/create'],
                    ],
                    [
                        'actions' => ['send-remind-sms'],
                        'allow' => true,
                        'roles' => ['order-renewal/send-remind-sms'],
                    ],
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    // 待续费
    public function actionPendingRenewal()
    {
        return $this->searchOrders(Order::RENEWAL_STATUS_PENDING);
    }

    // 已续费
    public function actionAlreadyRenewal()
    {
        return $this->searchOrders(Order::RENEWAL_STATUS_ALREADY);
    }

    // 无意向
    public function actionNoRenewal()
    {
        return $this->searchOrders(Order::RENEWAL_STATUS_NO);
    }

    private function searchOrders($status)
    {
        $searchModel = new OrderRenewalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
//            'status' => $status,
        ]);
    }

    /**
     * 新增关联商品
     */
    public function actionCreate()
    {
        $model = new OrderRenewalForm();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            $model->save();
            Yii::$app->session->setFlash('success', '关联续费订单添加成功!');
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($errors) : '关联失败！');
            return $this->redirect(['order-renewal/pending-renewal']);
        }
        return $this->redirect(['order-renewal/already-renewal']);
    }

    // 发送提醒短信
    public function actionSendRemindSms($is_validate = 0)
    {
        $model = new SendRemindSmsForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded && $is_validate)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        return $this->responseJson($loaded && $model->save(), $model->getFirstErrors());
    }

    /**
     * @param null $keyword
     * @param null $id
     * @return array
     */
    public function actionAjaxList($keyword=null, $id=null)
    {
        $order = Order::findOne($id);
        if(null != $order)
        {
            //客户前台个人中心点击续费后，并且支付成功的，直接获取前台个人中心的续费过的订单
            if($order->renewal_order_id > 0)
            {
                $query = Order::find();
                /** @var ActiveQuery $query */
                $query->select(['id', 'sn'])
                    ->andWhere(['user_id' => $order->user_id, 'id' => $order->renewal_order_id]);
                if(!empty($keyword))
                {
                    $query->andWhere(['like', 'sn', $keyword]);
                }
                if(!empty($id))
                {
                    $query->andWhere(['not in', 'id', $order->id]);//不能关联自己
                }
                $query->orderBy(['id' => SORT_ASC]);
                return ['status' => 200, 'products' => $this->serializeData($query->all())];
            }
            else
            {
                /** @var Query $query */
                $query = $order->getRenewalOrdersQuery();
                //前台客户没有点击续费商品下单
                if(null != $query)
                {
                    $query->orderBy(['id' => SORT_ASC]);

                    $date = [];
                    $dates = [];
                    /** @var Order $order */
                    foreach ($query->all() as $order)
                    {
                        $date['user_name'] = $order->user->name;
                        $date['user_phone'] = $order->user->phone;
                        if(!empty($order->businessSubject))
                        {
                            if($order->businessSubject->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED)
                            {
                                $date['business_name'] = !empty($order->businessSubject->company_name) ? $order->businessSubject->company_name : '';
                            }
                            else
                            {
                                $date['business_name'] = !empty($order->businessSubject->region) ? $order->businessSubject->region : '';
                            }
                        }
                        else
                        {
                            $date['business_name'] = '';
                        }
                        if($order->district_id > 0)
                        {
                            $date['area_name'] = $order->province_name.'-'.$order->city_name.'-'.$order->district_name;
                        }
                        else
                        {
                            $date['area_name'] = $order->service_area;
                        }
                        if($order->virtualOrder)
                        {
                            $date['payment_time']= $order->virtualOrder->payment_time > 0 ? Yii::$app->formatter->asDate($order->virtualOrder->payment_time) : '';
                        }
                        $order->created_at = Yii::$app->formatter->asDate($order->created_at);
                        $order->begin_service_cycle = $order->begin_service_cycle > 0 ? Yii::$app->formatter->asDate($order->begin_service_cycle) : '';
                        $order->end_service_cycle = $order->end_service_cycle > 0 ? Yii::$app->formatter->asDate($order->end_service_cycle) : '';

                        $dates[] = array_merge($this->serializeData($date),$this->serializeData($order));
                    }
                    return ['status' => 200, 'products' => $this->serializeData($dates)];
                }
            }
        }
        return ['status' => 200, 'products' => []];
    }

    private function responseJson($isSuccess, $errors = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($isSuccess)
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => $errors ? reset($errors) : '您的操作有误!'];
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new OrderRenewalForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
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