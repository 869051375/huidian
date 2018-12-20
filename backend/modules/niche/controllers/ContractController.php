<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\ReleContract;
use backend\modules\niche\models\WaitReleContract;
use common\models\Administrator;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use Yii;

class ContractController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
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
        ]);
    }

    /**
     * @SWG\Post(path="/niche/contract/wait-rele-list",
     *     tags={"niche"},
     *     summary="待关联合同列表接口",
     *     description="待关联合同列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/WaitReleContract")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/Contract")
     *    )
     * )
     *
     */
    public function actionWaitReleList()
    {
        $waitrele = new WaitReleContract();
        $post = Yii::$app->request->post();
        $waitrele->load($post,'');
        if(!$waitrele->validate()){
            return $this->responseValidateError($waitrele);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($waitrele->getWaitReleList($administrator));
    }

    /**
     * @SWG\Post(path="/niche/contract/rele-list",
     *     tags={"niche"},
     *     summary="已关联合同列表接口",
     *     description="已关联合同列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ReleContract")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/Contract")
     *    )
     * )
     *
     */
    public function actionReleList()
    {
        $rele = new ReleContract();
        $post = Yii::$app->request->post();
        $rele->load($post,'');
        if(!$rele->validate()){
            return $this->responseValidateError($rele);
        }
        return $this->responseData($rele->getList());
    }



}