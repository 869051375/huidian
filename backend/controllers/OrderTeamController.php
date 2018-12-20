<?php

namespace backend\controllers;

use common\models\Administrator;
use common\models\OrderRecord;
use common\models\OrderTeam;
use common\utils\BC;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OrderTeamController extends BaseController
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
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['ajax-list','delete'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-list','delete'],
                        'allow' => true,
                        'roles' => ['order-action/change-salesman'],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxList($order_id)
    {
        $models = OrderTeam::find()->select(['id','administrator_id','administrator_name','department_id','department_name','divide_rate'])->where(['order_id' => $order_id])->all();
        return ['status' => 200, 'models' => $this->serializeData($models)];
    }

    // 删除业务员
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        /** @var OrderTeam $model */
        $model = $this->findModel($id);
        $total_rate = BC::add($model->order->getDivideRate(),$model->divide_rate);
        $order_id = $model->order_id;
        $department_name = $model->administrator->department ? $model->administrator->department->name : null;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        OrderRecord::create($model->order_id, '删除共享人', '删除订单共享人'.$department_name.$model->administrator->name,$admin, 0, OrderRecord::INTERNAL_ACTIVE,0);
        $model->delete();
        return ['status' => 200,'rate' => $total_rate ,'order_id' => $order_id];
    }

    // 加载一个成本类型时，当找不到时抛出异常
    private function findModel($id)
    {
        $model = OrderTeam::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的业务人员!');
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