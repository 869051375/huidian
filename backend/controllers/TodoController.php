<?php

namespace backend\controllers;

use backend\models\OrderSearch;
use Yii;
use yii\filters\AccessControl;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class TodoController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['adjust-order-price-list'],
                        'allow' => true,
                        'roles' => ['order-action/review-adjust-price'],
                    ],
                ],
            ],
        ];
    }

    public function actionAdjustOrderPriceList()
    {
        $searchModel = new OrderSearch();
        $searchModel->administrator = Yii::$app->user->identity;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'adjust-price');
        return $this->render('adjust-order-price-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}