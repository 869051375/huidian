<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\NicheLabelAddForm;
use backend\modules\niche\models\NicheLabelChangeForm;
use backend\modules\niche\models\NicheLabelClearForm;
use backend\modules\niche\models\NicheLabelList;
use backend\modules\niche\models\NicheLabelUpdateForm;
use common\models\Administrator;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Yii;


class LabelController extends ApiBaseController
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
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['add'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['change'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['clear'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }


    /**
     * @SWG\Post(path="/niche/label/list",
     *     tags={"niche"},
     *     summary="商机标签列表接口",
     *     description="商机标签列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheLabelList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheLabel")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $nicheLabelList = new NicheLabelList();
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($nicheLabelList->getNicheLabel($administrator));
    }


    /**
     * @SWG\Post(path="/niche/label/update",
     *     tags={"niche"},
     *     summary="商机标签编辑接口",
     *     description="商机标签编辑接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "标签数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheLabelUpdateForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/NicheLabelUpdateForm")
     *    )
     * )
     *
     */
    public function actionUpdate()
    {
        $form = new NicheLabelUpdateForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        $nicheLabel = $form->save();
        if(null == $nicheLabel)
        {
            return $this->responseError('标签修改失败！');
        }
        return $this->response(200, '标签修改成功！',$form->id);
    }

    /**
     * @SWG\Post(path="/niche/label/add",
     *     tags={"niche"},
     *     summary="商机标签添加接口",
     *     description="商机标签添加接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheLabelAddForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "新增成功",
     *        @SWG\Schema(ref="#/definitions/NicheLabel")
     *    )
     * )
     *
     */
    public function actionAdd()
    {
        $form = new NicheLabelAddForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $nicheLabel = $form->save($administrator);
        if(null == $nicheLabel)
        {
            return $this->responseError('标签新增失败！');
        }
        return $this->response(200, '标签新增成功！',[]);
    }


    /**
     * @SWG\Post(path="/niche/label/change",
     *     tags={"niche"},
     *     summary="商机标签设置接口",
     *     description="商机标签设置接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheLabelChangeForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "新增成功",
     *        @SWG\Schema(ref="#/definitions/NicheLabel")
     *    )
     * )
     *
     */
    public function actionChange()
    {
        $form = new NicheLabelChangeForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        $nicheLabel = $form->save();
        if(null == $nicheLabel)
        {
            return $this->responseError('设商机标签添加失败！');
        }
        return $this->response(200, '商机标签添加成功！',$form->id);
    }


    /**
     * @SWG\Post(path="/niche/label/clear",
     *     tags={"niche"},
     *     summary="商机标签清除接口",
     *     description="商机标签清除接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheLabelClearForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "新增成功",
     *        @SWG\Schema(ref="#/definitions/NicheLabel")
     *    )
     * )
     *
     */
    public function actionClear()
    {
        $form = new NicheLabelClearForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        $nicheLabel = $form->save();
        if(null == $nicheLabel)
        {
            return $this->responseError('清除标签失败！');
        }
        return $this->response(200, '清除标签成功！',[]);
    }
}