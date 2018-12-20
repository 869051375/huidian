<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\NicheCustomFiledsForm;
use backend\modules\niche\models\NicheCustomFiledslist;
use common\models\Administrator;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use Yii;

class NicheCustomFiledsController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors_arr = parent::behaviors();
        unset($behaviors_arr['contentNegotiator']);
        $behaviors = [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'except' => ['export'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
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
                    ],
                ],
            ],
        ];
        return ArrayHelper::merge($behaviors_arr, $behaviors);
    }


    /**
     * @SWG\Post(path="/niche/niche-custom-fileds/list",
     *     tags={"niche"},
     *     summary="商机自定义列表接口",
     *     description="商机自定义列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheCustomFiledslist")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheCustomFileds")
     *    )
     * )
     *
     */
    public function actionList()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheCustomFiledslist();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        return $this->responseData($model->getList($administrator));
    }

    /**
     * @SWG\Post(path="/niche/niche-custom-fileds/add",
     *     tags={"niche"},
     *     summary="新增商机自定义列表接口",
     *     description="新增商机自定义列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheCustomFiledsForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheCustomFileds")
     *    )
     * )
     *
     */
    public function actionAdd()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $model = new NicheCustomFiledsForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        if(!$model->validate()){
            $this->responseValidateError($model);
        }
        $re = $model->add($administrator);
        if(!$re){
            return $this->response(400,'设置失败',[]);
        }else{
            return $this->response(200,'设置成功',[]);
        }
    }
}