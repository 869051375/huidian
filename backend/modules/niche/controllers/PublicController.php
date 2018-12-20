<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\CreateNichePublicForm;
use backend\modules\niche\models\NichePublic;
use backend\modules\niche\models\NichePublicDetail;
use backend\modules\niche\models\NichePublicItemForm;
use backend\modules\niche\models\NichePublicList;
use backend\modules\niche\models\PublicExportSearch;
use backend\modules\niche\models\PublicListForm;
use backend\modules\niche\models\UpdateBigNichePublicForm;
use backend\modules\niche\models\UpdateNichePublicForm;
use common\models\Administrator;
use common\models\Niche;
use common\models\NichePublicDepartment;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Yii;
use common\utils\BC;
use League\Csv\Writer;
use yii\data\ActiveDataProvider;
use yii\filters\ContentNegotiator;
use yii\web\Response;


class PublicController extends ApiBaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return ArrayHelper::merge($behaviors, [
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
                        'actions' => ['public-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
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
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' =>['@'],
                    ],
                    [
                        'actions' => ['update-big-public'],
                        'allow' => true,
                        'roles' =>['@'],
                    ],
                    [
                        'actions' => ['items'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['status'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['detail'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['organize'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ]);
    }

    /**
     * @SWG\Post(path="/niche/public/list",
     *     tags={"niche"},
     *     summary="商机公海设置列表接口",
     *     description="商机公海设置列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NichePublicListForm")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $nichePublicList = new NichePublicList();
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $post = Yii::$app->request->post();
        $nichePublicList->load($post,'');
        return $this->responseData($nichePublicList->getNichePublic($administrator));
    }

    /**
     * @SWG\Post(path="/niche/public/public-list",
     *     tags={"niche"},
     *     summary="商机公海列表接口",
     *     description="商机公海列表接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicListForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/PublicList")
     *    )
     * )
     *
     */
    public function actionPublicList()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $publicList = new PublicListForm();
        $post = Yii::$app->request->post();
        $publicList->load($post,'');
        $data = $publicList->getList($administrator);
        $result = $this->serializeData($data);
        $total_amount = 0;
        $publics = '';
        /** @var \common\models\NichePublic $publics */
        if(empty($post['all']) && $post['type'] !=1 && empty($post['is_share'])){
            if(!empty($post['niche_public_id'])){
                $publics = \common\models\NichePublic::find()->where(['id'=>$post['niche_public_id']])->one();
            }
            if($administrator->company_id != 0 && $administrator->department_id != 0){
                /** @var NichePublicDepartment $public_department */
                $public_department = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
                if($public_department){
                    $publics = \common\models\NichePublic::find()->where(['id'=>$public_department->niche_public_id])->one();
                }else{
                    /** @var \common\models\Administrator $administrator */
                    $administrator = Yii::$app->user->identity;
                    $department_ids = $administrator->getTreeDepartmentId(true);
                    if(empty($department_ids)){
                        $department_ids = [$administrator->department_id];
                    }
                    /** @var NichePublicDepartment $niche_public */
                    $niche_public = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->orderBy(['niche_public_id'=>SORT_DESC])->one();
                    if(!empty($niche_public)){
                        $publics = \common\models\NichePublic::find()->where(['id'=>$niche_public->niche_public_id])->one();
                    }
                }
            }else{
                $publics = \common\models\NichePublic::find()->where(['id'=>1])->one();
            }
        }
        $public_name = '';
        $public_id = '';
        if(!empty($result['items']) && isset($result['items'])){
            foreach ($result['items'] as $key=>$value){
                $total_amount += floatval($value['total_amount']);
                $public_name = $value['public_name'];
                $public_id = $value['niche_public_id'];
            }
        }
        if(isset($post['all']) && $post['all'] != '' || (empty($result) && empty($post['niche_public_id']))){
            $model = new NichePublicItemForm();
            /** @var \common\models\Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $results = $model->queryNichePublicItem($administrator);
            if(!empty($results) && isset($result[0]['id']) && isset($result[0]['name'])){
                $public_id = $result[0]['id'];
                $public_name = $result[0]['name'];
            }else{
                $public_id = 'all';
                $public_name = '全部商机';
            }
        }
        if(isset($post['is_share']) && $post['is_share'] != ''){
            $public_id = 'is_share';
            $public_name = '我分享的商机';
        }
        if(isset($publics->name) && isset($publics->id)){
            $name = $publics->name;
            $id = $publics->id;
        }elseif($public_name != '' && $public_id != ''){
            $name = $public_name;
            $id = $public_id;
        }else{
            $name = "商机公海";
            $id = 0;
        }
        $result['total_amount'] = sprintf("%.2f",$total_amount);
        $result['public_name'] = $name;
        $result['public_id'] = $id;
        return $this->response(200, '查询成功',$result);
    }


    /**
     * @SWG\Post(path="/niche/public/items",
     *     tags={"niche"},
     *     summary="商机公海列表接口（用于场景）接口",
     *     description="商机公海列表接口（用于场景）接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicItemForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     *
     */
    public function actionItems()
    {
        $nichePublicItemForm = new NichePublicItemForm();
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        return $this->responseData($nichePublicItemForm->queryNichePublicItem($administrator));
    }



    /**
     * @SWG\Post(path="/niche/public/add",
     *     tags={"niche"},
     *     summary="商机公海新增接口",
     *     description="商机公海新增接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/CreateNichePublicForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "新增成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     *
     */
    public function actionAdd()
    {
        $form = new CreateNichePublicForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $nichePublic = $form->save($administrator);
        if(null == $nichePublic)
        {
            return $this->responseError('商机公海创建操作失败！');
        }
        return $this->response(200, '商机公海创建操作成功！',[]);
    }


    /**
     * @SWG\Post(path="/niche/public/status",
     *     tags={"niche"},
     *     summary="商机公海启用禁用接口",
     *     description="商机公海启用禁用接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "修改参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicChangeForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/NichePublicChangeForm")
     *    )
     * )
     *
     */
    public function actionStatus()
    {
        $model = new \backend\modules\niche\models\NichePublicChangeForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        if($model -> change()){
            if($post['status'] == 1){
                return $this->response(200,'商机公海启用成功！',$model->id);
            }else{
                return $this->response(200,'商机公海禁用成功！',$model->id);
            }

        }
        return $this->responseError('修改失败！');
    }


    /**
     * @SWG\Post(path="/niche/public/delete",
     *     tags={"niche"},
     *     summary="商机公海删除接口",
     *     description="商机公海删除接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "删除参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicDeleteForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "删除成功",
     *        @SWG\Schema(ref="#/definitions/NichePublicDeleteForm")
     *    )
     * )
     *
     */

    public function actionDelete()
    {
        $model = new \backend\modules\niche\models\NichePublicDeleteForm();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        if(!$model->validate()){
            return $this->responseValidateError($model);
        }
        if($model -> remove()){
            return $this->response(200,'商机公海删除操作成功！',$model->id);
        }
        return $this->responseError('商机公海删除操作失败！');
    }


    /**
     * @SWG\Post(path="/niche/public/detail",
     *     tags={"niche"},
     *     summary="商机公海详情接口",
     *     description="根据id获取公海详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicDetail")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     * @return array
     */

    public function actionDetail()
    {
        $model = new NichePublicDetail();
        $post = Yii::$app->request->post();
        $model->load($post,'');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if(!$model->validate($administrator)){
            return $this->responseValidateError($model);
        }
        return $this->response(200, "OK",$model->getDetail());
    }

    /**
     * @SWG\Post(path="/niche/public/update",
     *     tags={"niche"},
     *     summary="商机公海编辑接口",
     *     description="商机公海编辑接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/UpdateNichePublicForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     *
     */
    public function actionUpdate()
    {
        $form = new UpdateNichePublicForm();
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
            return $this->responseError('商机公海保存操作失败！');
        }
        return $this->response(200, '商机公海保存操作成功！',$form->id);

    }


    /**
     * @SWG\Post(path="/niche/public/update-big-public",
     *     tags={"niche"},
     *     summary="商机公海编辑接口",
     *     description="商机公海编辑接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/UpdateBigNichePublicForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     *
     */
    public function actionUpdateBigPublic()
    {
        $form = new UpdateBigNichePublicForm();
        $post = Yii::$app->request->post();
        $form->load($post,'');
        if(!$form->validate()){
            return $this->responseValidateError($form);
        }
        $nicheRecord = $form->save();
        if(null == $nicheRecord)
        {
            return $this->responseError('商机大公海保存操作失败！');
        }
        return $this->response(200, '商机大公海保存操作成功！',$form->id);

    }


    /**
     * @SWG\Post(path="/niche/public/organize",
     *     tags={"niche"},
     *     summary="商机公海部门树接口",
     *     description="商机公海部门树接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "公海数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NichePublicItemForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "修改成功",
     *        @SWG\Schema(ref="#/definitions/NichePublicTree")
     *    )
     * )
     *
     */
    public function actionOrganize()
    {
        $nichePublic = new NichePublic();
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        return $this->responseData($nichePublic->organize($administrator));
    }

    /**
     * @SWG\Post(path="/niche/public/export",
     *     tags={"niche"},
     *     summary="商机公海列表导出接口",
     *     description="商机公海列表导出接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/PublicListForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "查询成功",
     *        @SWG\Schema(ref="#/definitions/NichePublic")
     *    )
     * )
     *
     */
    public function actionExport()
    {
        $export_code = Yii::$app->cache->get('crm-niche-public-export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            return $this->responseError('您的操作过于频繁，请等待'.$second.'秒！');
        }
        $batchNum = 100;
        $search = new PublicListForm();
        $get = Yii::$app->request->get();
        $search->load($get,'');
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $dataProvider = $search->getList($administrator);
        $count = $dataProvider->totalCount;
        if(empty($count))
        {
            return $this->responseError('没有获取到任何公海数据！');
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['商机ID','商机名称', '商机状态','客户ID','客户名称','客户创建时间','商机状态百分比','所属公海','负责人',
            '最后跟进人','最后跟进时间','创建人','商机创建时间','标签','来源','来源渠道','回收时间','作废原因','最后一次跟进记录'];
        $csv->insertOne($header);
        $model = new PublicListForm();
        for($i = 0; $i < $batch; $i++)
        {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
            /** @var Niche $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $nichePublic)
            {
                $csv->insertOne([
                    "\t" . !empty($nichePublic->id)?$nichePublic->id:1111,
                    "\t" . $this->trimStr($nichePublic->name),
                    "\t" . $this->status($nichePublic->status),
                    "\t" . $this->trimStr($nichePublic->customer_id),
                    "\t" . $this->trimStr($nichePublic->customer_name),
                    "\t" . date("Y-m-d H:i:s",$model->exportCustomerCreateTime($nichePublic->id)),
                    "\t" . $this->trimStr($nichePublic->progress),
                    "\t" . $this->trimStr($nichePublic->public_name),
                    "\t" . $this->trimStr($nichePublic->administrator_name),
                    "\t" . $this->trimStr($nichePublic->last_record_creator_name),
                    "\t" . $this->trimStr($nichePublic->last_record),
                    "\t" . $this->trimStr($nichePublic->creator_name),
                    "\t" . date("Y-m-d H:i:s",$nichePublic->created_at),
                    "\t" . $this->trimStr($nichePublic->label_name),
                    "\t" . $this->trimStr($nichePublic->source_name),
                    "\t" . $this->trimStr($nichePublic->channel_name),
                    "\t" . date("Y-m-d H:i:s",$nichePublic->recovery_at),
                    "\t" . $this->trimStr($nichePublic->lose_reason),
                    "\t" . $this->trimStr($model->getLastRecord($nichePublic->id)),
                ]);
            }
            unset($models);
        }
        $filename = date('YmdHis').rand(10000,99999).'_公海商机记录.csv';
        Yii::$app->cache->set('crm-niche-public-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }

    public function status($status)
    {
        if($status == 0){
            return "未成交";
        }elseif($status == 2){
            return "已成交";
        }else{
            return "已失败";
        }
    }

    //删除特殊符号
    private function trimStr($str)
    {
        //注意有个特殊空格符号" "
        $needReplace = [" ","　"," ","\t","\n","\r"];
        $result = ["","","","",""];
        return str_replace($needReplace,$result,$str);
    }
}