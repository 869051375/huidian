<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\ChangePublicList;
use backend\modules\niche\models\CustomerExchangeList;
use backend\modules\niche\models\NichePublicDetailForm;
use backend\modules\niche\models\PublicNicheAssign;
use backend\modules\niche\models\PublicNicheChangePool;
use backend\modules\niche\models\PublicNicheExtract;
use backend\modules\niche\models\PublicNicheOrderForm;
use common\models\Administrator;
use common\models\CrmContacts;
use common\models\Niche;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Yii;

class PublicNicheController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['detail'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['orders'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['extract'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['extract-big-public'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['assign'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['change-pool'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['public-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/public-niche/detail",
     *     tags={"niche"},
     *     summary="公海商机详情关联信息（加密）接口",
     *     description="公海商机详情关联信息（加密）接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicDetailForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NichePublicDetail")
     *    )
     * )
     *
     */
    public function actionDetail()
    {
        $model = new NichePublicDetailForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        return $this->responseData($model->getDetail());
    }

    /**
     * @SWG\Post(path="/niche/public-niche/orders",
     *     tags={"niche"},
     *     summary="公海商机订单列表（加密）接口",
     *     description="公海商机订单列表（加密）接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicNicheOrderForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/PublicNicheOrderList")
     *    )
     * )
     *
     */
    public function actionOrders()
    {
        $model = new PublicNicheOrderForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        return $this->responseData($model->getOrder());
    }

    /**
     * @SWG\Post(path="/niche/public-niche/extract",
     *     tags={"niche"},
     *     summary="公海商机提取接口",
     *     description="公海商机提取接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicNicheExtract")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "执行成功",
     *        @SWG\Schema(ref="#/definitions/PublicList")
     *    )
     * )
     *
     */
    public function actionExtract()
    {
        $model = new PublicNicheExtract();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        $re = $model->extract($administrator);
        if($re['status'] == 0)
        {
            return $this->responseError($re['msg']);
        }
        //统计
        $models = new \backend\modules\niche\models\NicheFunnel();
        $data = new CustomerExchangeList();
        $niche_id = explode(',',$post['niche_id']);
        for ($i=0;$i<count($niche_id);$i++){
            $models->add($niche_id[$i],10);
            /** @var Niche $niche_one */
            $niche_one = Niche::find()->where(['id' => $niche_id])->one();
            /** @var CrmContacts $contract */
            $contract = CrmContacts::find()->where(['customer_id' => $niche_one->customer_id])->one();
            $data->niche(['id' => $niche_one->id, 'from' => '', 'administrator_id' => $niche_one->administrator_id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche_one->source_id) ? $niche_one->source_id : 0, 'channel_id' => isset($niche_one->channel_id) ? $niche_one->channel_id : 0, 'amount' => $niche_one->total_amount]);
        }
        return $this->responseMessage($re['msg']);
    }

    /**
     * @SWG\Post(path="/niche/public-niche/extract-big-public",
     *     tags={"niche"},
     *     summary="公海商机提取接口",
     *     description="公海商机提取接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicNicheExtract")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "执行成功",
     *        @SWG\Schema(ref="#/definitions/PublicList")
     *    )
     * )
     *
     */
    public function actionExtractBigPublic()
    {
        $model = new PublicNicheExtract();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        $re = $model->extract_big_public($administrator);
        if($re['status'] == 0)
        {
            return $this->responseError($re['msg']);
        }
        //统计
        $models = new \backend\modules\niche\models\NicheFunnel();
        $niche_id = explode(',',$post['niche_id']);
        $data = new CustomerExchangeList();
        for ($i=0;$i<count($niche_id);$i++){
            $models->add($niche_id[$i],10);
            /** @var Niche $niche_one */
            $niche_one = Niche::find()->where(['id' => $niche_id])->one();
            /** @var CrmContacts $contract */
            $contract = CrmContacts::find()->where(['customer_id' => $niche_one->customer_id])->one();
            $data->niche(['id' => $niche_one->id, 'from' => '', 'administrator_id' => $niche_one->administrator_id, 'province_id' => isset($contract->province_id) ? $contract->province_id : 0, 'city_id' => isset($contract->city_id) ? $contract->city_id : 0, 'district_id' => isset($contract->district_id) ? $contract->district_id : 0, 'source_id' => isset($niche_one->source_id) ? $niche_one->source_id : 0, 'channel_id' => isset($niche_one->channel_id) ? $niche_one->channel_id : 0, 'amount' => $niche_one->total_amount]);
        }
        return $this->responseMessage($re['msg']);
    }

    /**
     * @SWG\Post(path="/niche/public-niche/assign",
     *     tags={"niche"},
     *     summary="商机分配接口",
     *     description="商机分配接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicNicheAssign")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "执行成功",
     *        @SWG\Schema(ref="#/definitions/PublicList")
     *    )
     * )
     *
     */
    public function actionAssign()
    {
        $model = new PublicNicheAssign();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        $re = $model->assign($administrator);
        if($re['status'] == 0)
        {
            return $this->responseError($re['msg']);
        }
        //统计
        $models = new \backend\modules\niche\models\NicheFunnel();
        $niche_id = explode(',',$post['niche_id']);
        for ($i=0;$i<count($niche_id);$i++){
            $models->add($niche_id[$i],10);
        }
        return $this->responseMessage($re['msg']);
    }

    /**
     * @SWG\Post(path="/niche/public-niche/change-pool",
     *     tags={"niche"},
     *     summary="更换商机公海池",
     *     description="更换商机公海池",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicNicheChangePool")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "执行成功",
     *        @SWG\Schema(ref="#/definitions/PublicList")
     *    )
     * )
     *
     */
    public function actionChangePool()
    {
        $model = new PublicNicheChangePool();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        $re = $model->changePool($administrator);
        if(null == $re)
        {
            return $this->responseError('商机更换公海池操作失败！');
        }
        return $this->response(200, '商机更换公海池操作成功！',[]);

    }

    /**
     * @SWG\Post(path="/niche/public-niche/public-list",
     *     tags={"niche"},
     *     summary="商机公海列表接口（用于更换商机池）",
     *     description="商机公海列表接口（用于更换商机池）",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ChangePublicList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     *
     */
    public function actionPublicList()
    {
        $model = new ChangePublicList();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($model->publicNicheList($administrator));
    }
}