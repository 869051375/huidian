<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\NicheTeamList;
use common\models\Administrator;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Yii;
use yii\data\ActiveDataProvider;

class TeamController extends ApiBaseController
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
                    'actions' => ['change'],
                    'allow' => true,
                    'roles' => ['@'],
                    ],
                    [
                        'actions' => ['sort'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['remove'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/team/list",
     *     tags={"niche"},
     *     summary="商机团队成员列表接口",
     *     description="商机团队成员列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheTeamList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheTeam")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $nicheTeamList = new NicheTeamList();
        $post = Yii::$app->request->post();
        $nicheTeamList->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($nicheTeamList->queryNicheTeam($administrator));
    }


    /**
     * @SWG\Post(path="/niche/team/change",
     *     tags={"niche"},
     *     summary="商机团队成员权限修改接口",
     *     description="商机团队成员权限修改接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "修改参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ChangeNicheTeamForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/ChangeNicheTeamForm")
     *    )
     * )
     *
     */
    public function actionChange()
    {
        $model = new \backend\modules\niche\models\ChangeNicheTeamForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }

        if($model -> change()){
            if($post['is_update'] == 1){
                return $this->response(200,'商机修改权限分配成功！',$model->id);
            }else{
                return $this->response(200,'商机修改权限收回成功！',$model->id);
            }
        }
        return $this->responseError('商机修改权限成功！');


    }


    /**
     * @SWG\Post(path="/niche/team/sort",
     *     tags={"niche"},
     *     summary="商机团队成员排序接口",
     *     description="商机团队成员排序接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "修改参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheTeamSortForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/NicheTeamSortForm")
     *    )
     * )
     *
     */
    public function actionSort()
    {
        $model = new \backend\modules\niche\models\NicheTeamSortForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        $model -> change();
        $models = new NicheTeamList();
        $models->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($models->queryNicheTeam($administrator));
    }


    /**
     * @SWG\Post(path="/niche/team/remove",
     *     tags={"niche"},
     *     summary="商机团队成员移除接口",
     *     description="商机团队成员移除接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "删除参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheTeamRemoveForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "删除成功",
     *        @SWG\Schema(ref="#/definitions/NicheTeamRemoveForm")
     *    )
     * )
     *
     */
    public function actionRemove()
    {
        $model = new \backend\modules\niche\models\NicheTeamRemoveForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        if($model -> remove()){
            return $this->response(200,'商机移除成员操作成功！',$model->niche_id);
        }
        return $this->responseError('商机移除成员操作失败！');
    }

}