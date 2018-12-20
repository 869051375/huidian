<?php

namespace backend\controllers;

use backend\models\OrderFollowRecordForm;
use common\models\Order;
use common\models\OrderFollowRecord;
use common\models\Product;
use common\models\RenewalProductRelated;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OrderFollowRecordController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'ajax-renewal-product-list'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'ajax-list', 'ajax-renewal-product-list'],
                        'allow' => true,
                        'roles' => ['order-follow-record/create'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($is_validate = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new OrderFollowRecordForm();
        $data = Yii::$app->request->post();
        if($model->load($data))
        {
            if($is_validate)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $followRecord = $model->save();
            if(null != $followRecord)
            {
                return ['status' => 200, 'model' => $this->serialize($followRecord)];
            }
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => $model->hasErrors() ? reset($errors) : '您的操作有误!'];
    }

    public function actionAjaxList($order_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $this->findOrder($order_id);
        $modelData = [];
        foreach($order->orderFollowRecords as $model)
        {
            $modelData[] = $this->serialize($model);
        }
        return ['status' => 200, 'models' => $modelData];
    }

    /**
     * @param null $product_id
     * @param null $keyword
     * @return array
     */
    public function actionAjaxRenewalProductList($product_id, $keyword = null)
    {
        /** @var RenewalProductRelated $renewalProduct */
        $renewalProduct = RenewalProductRelated::find()->andWhere(['like', 'product_ids', ','.$product_id.','])->orderBy(['id' => SORT_ASC])->one();
        $ids = [$product_id];
        if(null != $renewalProduct)
        {
            $ids = $renewalProduct->getProductIds();
        }
        /** @var ActiveQuery $query */
        $query = Product::find()->select(['id', 'name'])->andWhere(['is_renewal' => Product::RENEWAL_ACTIVE]);
        if(!empty($product_id))
        {
            $query->andWhere(['in', 'id', $ids]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['or', ['like', 'name', $keyword], ['like', 'alias', $keyword]]);
        }
        return ['status' => 200, 'products' => $this->serializeData($query->all())];
    }

    /**
     * @param OrderFollowRecord $model
     * @return array
     */
    private function serialize($model)
    {
        $nextFollowTime = '';
        if($model->next_follow_time > 0 )
        {
            $nextFollowTime = Yii::$app->formatter->asDatetime($model->next_follow_time, 'yyyy-MM-dd HH:00');
        }
        return [
            'id' => $model->id,
            'next_follow_time' => $nextFollowTime,
            'created_at' => Yii::$app->formatter->asDatetime($model->created_at, 'yyyy-MM-dd HH:mm'),
            'is_follow' => $model->isFollow(),
            'follow_remark' => $model->follow_remark,
            'creator_name' => $model->creator_name,
        ];
    }

    /**
     * @param $id
     * @return Order
     * @throws NotFoundHttpException
     */
    private function findOrder($id)
    {
        /** @var Order $order */
        $order = Order::findOne($id);
        if(null == $order)
        {
            throw new NotFoundHttpException('找不到订单信息!');
        }
        return $order;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}