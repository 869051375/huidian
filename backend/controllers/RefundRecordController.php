<?php

namespace backend\controllers;

use common\models\RefundRecord;
use yii\filters\AccessControl;

class RefundRecordController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['refund-record/list', 'refund/do'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($order_id = null, $virtual_order_id = null)
    {
        if(null == $order_id)
        {
            $models = RefundRecord::findAll(['virtual_order_id' => $virtual_order_id]);
        }
        else
        {
            $models = RefundRecord::findAll(['order_id' => $order_id]);
        }
        return $this->render('list', [
            'models' => $models,
        ]);
    }
}
