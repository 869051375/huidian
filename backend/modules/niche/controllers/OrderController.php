<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\fixtures\Administrator;
use backend\modules\niche\models\ReleOrder;
use backend\modules\niche\models\WaitReleOrder;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use Yii;

class OrderController extends ApiBaseController
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
                            'actions' => ['wait-rele-list'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                        [
                            'actions' => ['rele-list'],
                            'allow' => true,
                            'roles' => ['@'],
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * @SWG\Post(path="/niche/order/wait-rele-list",
     *     tags={"niche"},
     *     summary="待关联订单列表接口",
     *     description="待关联订单列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/WaitReleOrder")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/OrderList")
     *    )
     * )
     *
     */
    public function actionWaitReleList()
    {
        $waitrele = new WaitReleOrder();
        $post = Yii::$app->request->post();
        $waitrele->load($post,'');
        if(!$waitrele->validate()){
            return $this->responseValidateError($waitrele);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($waitrele->getList($administrator));
    }

    /**
     * @SWG\Post(path="/niche/order/rele-list",
     *     tags={"niche"},
     *     summary="已关联订单列表接口",
     *     description="已关联订单列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ReleOrder")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/Order")
     *    )
     * )
     *
     */
    public function actionReleList()
    {
        $waitrele = new ReleOrder();
        $post = Yii::$app->request->post();
        $waitrele->load($post,'');;
        return $this->responseData($waitrele->getList());
    }
}