<?php
namespace backend\controllers;


use backend\models\CouponSearch;
use common\models\Coupon;
use Yii;
use yii\filters\AccessControl;

class CouponListController extends BaseController
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
                        'actions' => ['list', 'code-list'],
                        'allow' => true,
                        'roles' => ['coupon-list/list'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        return $this->searchCoupons(Coupon::MODE_COUPON);
    }

    public function actionCodeList()
    {
        return $this->searchCoupons(Coupon::MODE_COUPON_CODE);
    }

    private function searchCoupons($status)
    {
        $searchModel = new CouponSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $status);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}