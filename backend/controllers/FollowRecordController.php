<?php

namespace backend\controllers;

use backend\models\FollowRecordForm;
use common\models\FollowRecord;
use common\models\VirtualOrder;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FollowRecordController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'ajax-list'],
                        'allow' => true,
                        'roles' => ['follow-record/create'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($is_validate = 0)
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new FollowRecordForm();
        $loaded = $model->load(Yii::$app->request->post());
        if($loaded)
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
        $error = $model->getFirstErrors();
        return ['status' => 400, 'message' => $model->hasErrors() ? reset($error) : '您的操作有误!'];
    }

    public function actionAjaxList($virtual_order_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $virtualOrder = $this->findVirtualOrder($virtual_order_id);
        $modelData = [];
        foreach($virtualOrder->followRecords as $model)
        {
            $modelData[] = $this->serialize($model);
        }
        return ['status' => 200, 'models' => $modelData];
    }

    /**
     * @param FollowRecord $model
     * @return array
     */
    private function serialize($model)
    {
        return [
            'id' => $model->id,
            'next_follow_time' => Yii::$app->formatter->asDatetime($model->next_follow_time, 'yyyy-MM-dd HH:00'),
            'created_at' => Yii::$app->formatter->asDatetime($model->created_at, 'yyyy-MM-dd HH:mm'),
            'is_follow' => $model->isFollow(),
            'follow_remark' => $model->follow_remark,
            'creator_name' => $model->creator_name,
        ];
    }

    /**
     * @param $id
     * @return VirtualOrder
     * @throws NotFoundHttpException
     */
    private function findVirtualOrder($id)
    {
        /** @var VirtualOrder $order */
        $order = VirtualOrder::findOne($id);
        if(null == $order)
        {
            throw new NotFoundHttpException('找不到订单信息!');
        }
        return $order;
    }
}