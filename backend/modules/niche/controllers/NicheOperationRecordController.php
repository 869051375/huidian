<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\NicheOperationRecordList;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Yii;

class NicheOperationRecordController extends ApiBaseController
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
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * @SWG\Post(path="/niche/niche-operation-record/list",
     *     tags={"niche"},
     *     summary="商机操作记录列表接口",
     *     description="商机操作记录列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheOperationRecordList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheOperationRecord")
     *    )
     * )
     *
     */

    public function actionList()
    {
        $nicheOperationRecord = new NicheOperationRecordList();
        $post = Yii::$app->request->post();
        $nicheOperationRecord->load($post,'');
        return $this->responseData($nicheOperationRecord->getNicheOperationRecord());
    }

}