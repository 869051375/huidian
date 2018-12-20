<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\NicheFunnel;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use Yii;

class NicheFunnelController extends ApiBaseController
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
                        [
                            'actions' => ['funnel'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                        [
                            'actions' => ['add'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                        [
                            'actions' => ['person-list'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                        [
                            'actions' => ['department-list'],
                            'allow' => true,
                            'roles' => ['@'],
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * @SWG\Post(path="/niche/niche-funnel/list",
     *     tags={"niche"},
     *     summary="商机漏斗商机列表",
     *     description="商机漏斗商机列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheFunnel")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheList")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $model = new NicheFunnel();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        return $this->responseData($model->getList());
    }

    /**
     * @SWG\Post(path="/niche/niche-funnel/funnel",
     *     tags={"niche"},
     *     summary="商机漏斗数据统计",
     *     description="商机漏斗数据统计",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheFunnel")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NicheFunnelForm")
     *    )
     * )
     *
     */
    public function actionFunnel()
    {
        $model = new NicheFunnel();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        return $this->responseData($model->getFunnel());
    }


    /**
     * @SWG\Post(path="/niche/niche-funnel/person-list",
     *     tags={"niche"},
     *     summary="根据公司ID获取人员列表",
     *     description="根据公司ID获取人员列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "company_id,department_id",
     *		description = "公司ID,部门ID",
     *		required = true,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/PersonList")
     *    )
     * )
     * @param $company_id string
     * @param $department_id string
     * @return array
     */
    public function actionPersonList($company_id,$department_id = '0')
    {
        $model = new NicheFunnel();
        return $this->response(200,'查询成功',$model->getPersonList($company_id,$department_id));
    }

    /**
     * @SWG\Post(path="/niche/niche-funnel/department-list",
     *     tags={"niche"},
     *     summary="根据公司ID获取人员列表",
     *     description="根据公司ID获取人员列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "company_id",
     *		description = "公司ID",
     *		required = true,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/CompanyList")
     *    )
     * )
     * @param $company_id string
     * @return array
     */
    public function actionDepartmentList($company_id)
    {
        $model = new NicheFunnel();
        return $this->response(200,'查询成功',$model->getDepartmentList($company_id));
    }

}