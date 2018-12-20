<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

class ReasonController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['wins'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['fails'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/reason/wins",
     *     tags={"niche"},
     *     summary="商机赢单原因列表接口",
     *     description="商机赢单原因列表接口",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/NicheReason"))
     *    )
     * )
     *
     */
    public function actionWins()
    {
        return $this->responseData([
            ['id' => 1, 'name' => '价格原因'],
            ['id' => 2, 'name' => '竞品原因'],
            ['id' => 3, 'name' => '服务原因'],
            ['id' => 4, 'name' => '其他原因'],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/reason/fails",
     *     tags={"niche"},
     *     summary="商机输单原因列表接口",
     *     description="商机输单原因列表接口",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/NicheReason"))
     *    )
     * )
     *
     */
    public function actionFails()
    {
        return $this->responseData([
            ['id' => 1, 'name' => '价格原因'],
            ['id' => 2, 'name' => '竞品原因'],
            ['id' => 3, 'name' => '服务原因'],
            ['id' => 4, 'name' => '其他原因'],
        ]);
    }
}
