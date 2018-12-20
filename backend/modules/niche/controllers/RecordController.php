<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\CreateNicheRecordForm;
use common\models\Administrator;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Yii;
use backend\modules\niche\models\NicheRecordList;

class RecordController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['add'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/record/add",
     *     tags={"niche"},
     *     summary="新增商机跟进记录接口",
     *     description="新增商机跟进记录接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "商机操作数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/CreateNicheRecordForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "新增成功",
     *        @SWG\Schema(ref="#/definitions/NicheRecord")
     *    )
     * )
     *
     */
    public function actionAdd()
    {
        $form = new CreateNicheRecordForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $nicheRecord = $form->save($administrator);
        if(null == $nicheRecord)
        {
            return $this->responseError('商机跟进记录新增保存失败！');
        }
        return $this->response(200, '商机跟进记录新增保存成功！',[]);
    }

    /**
     * @SWG\Post(path="/niche/record/list",
     *     tags={"niche"},
     *     summary="商机跟进记录列表接口",
     *     description="商机跟进记录列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheRecordList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheRecord")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $nicheRecordList = new NicheRecordList();
        $post = Yii::$app->request->post();
        $nicheRecordList->load($post,'');
        return $this->responseData($nicheRecordList->getNicheRecord());
    }

}