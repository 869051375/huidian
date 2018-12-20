<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\CustomerExchangeList;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use Yii;

class CustomerExchangeController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge(
            $behaviors,
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'actions' => ['list'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }


    /**
     * @SWG\Post(path="/niche/customer-exchange/list",
     *     tags={"niche"},
     *     summary="客户流转数据统计接口",
     *     description="客户流转数据统计接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/CustomerExchangeList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/CustomerExchange")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $model = new CustomerExchangeList();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        return $this->responseData($model->getList());
    }


}
