<?php

namespace backend\controllers;

use backend\models\OrderEvaluateForm;
use common\models\OrderEvaluate;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class OrderEvaluateController extends BaseController
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
                'only' => [
                    'ajax-virtual-order-info',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['reply','ajax-virtual-order-info'],
                        'allow' => true,
                        'roles' => ['order-evaluate/reply'],
                    ],
                ],
            ],
        ];
    }

    //评价回复
    public function actionReply($id, $order_id)
    {
        $orderEvaluate = $this->findModel($id);
        $model = new OrderEvaluateForm();

        if($model->load(Yii::$app->request->post())){
            if($model->save($orderEvaluate)){

                Yii::$app->session->setFlash('success', '回复评价成功!');
                return $this->redirect(['order/info', 'id' => $order_id]);
            }
        }

        Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        return $this->redirect(['order/info', 'id' => $order_id]);

    }

    //获取订单详情
    public function actionAjaxVirtualOrderInfo($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $this->serializeData($model)];
    }

    protected function findModel($id)
    {
        if (($model = OrderEvaluate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('找不到指定的评论!');
        }
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