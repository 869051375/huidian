<?php

namespace backend\modules\niche\controllers;


use backend\controllers\ApiBaseController;
use backend\modules\niche\models\AddNicheForm;
use backend\modules\niche\models\CategoryList;
use backend\modules\niche\models\ChannelList;
use backend\modules\niche\models\CompanyList;
use backend\modules\niche\models\DepartmentList;
use backend\modules\niche\models\NicheAdministratorList;
use backend\modules\niche\models\NicheCreateList;
use backend\modules\niche\models\NicheDetail;
use backend\modules\niche\models\NicheDistributionList;
use backend\modules\niche\models\NicheGetList;
use backend\modules\niche\models\NicheLastRecordList;
use backend\modules\niche\models\NicheSalesmanList;
use backend\modules\niche\models\PersonList;
use backend\modules\niche\models\ProductCategoryList;
use backend\modules\niche\models\SourceList;
use common\models\Administrator;
use common\models\CrmOpportunity;
use common\models\Niche;
use common\models\NicheProduct;
use common\models\NichePublicDepartment;
use common\utils\BC;
use League\Csv\Writer;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class NicheController extends ApiBaseController
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
                        'actions' => ['list', 'export'],
                        'allow' => true,
                        'roles' => ['@'], // todo 示例，实际开发的时候修改
                    ],
                    [
                        'actions' => ['add'],
                        'allow' => true,
                        'roles' => ['@'], // todo 示例，实际开发的时候修改
                    ],
                    [
                        'actions' => ['detail','create-list','salesman-list','administrator-list','last-record-list','distribution-list','source-list','channel-list','category-list','product-category-list','company-list','department-list','person-list'],
                        'allow' => true,
                        'roles' => ['@'], // todo 示例，实际开发的时候修改
                    ]
                ],
            ],
        ];
        return ArrayHelper::merge($behaviors_arr, $behaviors);
    }

    /**
     * @SWG\Post(path="/niche/niche/list",
     *     tags={"niche"},
     *     summary="商机列表接口",
     *     description="通用商机接口，可查询：我的商机、下属商机、公海商机列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheList")
     *    )
     * )
     *
     */
    public function actionList()
    {
        $model = new NicheGetList();
        $model->load(\Yii::$app->request->post(),'');
        if(!$model->validate())
        {
            return $this->responseValidateError($model);
        }

        $data = $this->serializeData($model->getList(false));
        foreach ($data['items'] as $k=> $item)
        {
            $niche_product_str = '';
            /** @var NicheProduct $niche_product */
            $niche_product = NicheProduct::find()->where(['niche_id'=>$item['id']])->all();
            foreach ($niche_product as $niche_product_item)
            {
                $niche_product_str .= $niche_product_item->product_name.'*'.$niche_product_item->qty.',';
            }
            $data['items'][$k]['product'] = $niche_product_str;
            $data['items'][$k]['customer_created_at'] = Yii::$app->formatter->asDatetime($item['customer_created_at'] == 0 ? null : $item['customer_created_at']);
        }
        $data['total_amount'] = sprintf("%.2f",(float)array_sum(array_column($data['items'],'total_amount')));
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $data['display_give_up'] = 1;
        if ($administrator->company_id == 0)
        {
            if ($administrator->department_id == 0)
            {
                $data['display_give_up'] = 0;
            }
            else
            {
                $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
                if (empty($niche_public))
                {
                    $data['display_give_up'] = 0;
                }
            }
        }
        else
        {
            $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
            if (empty($niche_public))
            {
                $data['display_give_up'] = 0;
            }
        }
        return $this->responseData($data);
    }

    /**
     * @SWG\Post(path="/niche/niche/export",
     *     tags={"niche"},
     *     summary="商机导出接口",
     *     description="通用商机导出接口，可导出：我的商机、下属商机、公海商机数据",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "查询参数",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/NicheList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "导出成功",
     *        @SWG\Schema(type="file")
     *    )
     * )
     */
    public function actionExport()
    {
        $export_code = Yii::$app->cache->get('niche-export-' . Yii::$app->user->id);
        if($export_code)
        {

            $second = date('s',BC::sub($export_code+30,time()));
            return $this->response(400,'您的操作过于频繁，请等待'.$second.'秒！');
        }
        $batchNum = 100;
        $search = new NicheGetList();
        $search->setScenario('export');
        $search->load(\Yii::$app->request->get(),'');

        if(!$search->validate())
        {
            $error = $search->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($error));
            return $this->response(400,reset($error));
        }
        $dataProvider = $search->getList(false);
        $count = $dataProvider->totalCount;

        if(empty($count))
        {
            return $this->response(400,'没有获取到任何商机记录');
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['商机ID','商机创建时间','商机名称','商机状态','商机状态百分比','跟进人', '创建人','标签','所属客户id',
            '所属客户名称', '所属客户来源','商机最后跟进时间','商机下次跟进时间','作废原因','所属公司','所属部门','最后一次跟进记录'];
        $csv->insertOne($header);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var CrmOpportunity[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();

            /** @var Niche $opportunity */
            foreach ($models as $opportunity)
            {
                $csv->insertOne([
                    "\t" . $opportunity->id,
                    "\t" . Yii::$app->formatter->asDatetime($opportunity->created_at),
                    "\t" . $this->trimStr($opportunity->name),
                    "\t" . $this->trimStr($opportunity->getExportStatusName()),
                    "\t" . $opportunity->progress.'%',
                    "\t" . $this->trimStr($opportunity->administrator_name ? $opportunity->administrator_name : '--'),
                    "\t" . $this->trimStr($opportunity->creator_name),
                    "\t" . $this->trimStr($opportunity->getTag()),
                    "\t" . $opportunity->customer_id,
                    "\t" . $this->trimStr($opportunity->customer_name),
                    "\t" . $opportunity->getExportSourceName(),
                    "\t" . $this->trimStr($opportunity->last_record ? Yii::$app->formatter->asDatetime($opportunity->last_record) : '--'),
                    "\t" . $opportunity->getExportNextFollowTime(),
                    "\t" . $this->trimStr($opportunity->getExportInvalidReason()),
                    "\t" . $this->trimStr($opportunity->getExportCompany()),
                    "\t" . $this->trimStr($opportunity->getExportDepartment()),
                    "\t" . $this->trimStr($opportunity->getLastRecordContent()),
                ]);
            }
        }
        //记录操作日志
//        AdministratorLog::logExport('商机',$count);

        $filename = date('YmdHis').rand(10000,99999).'_商机记录.csv';
        Yii::$app->cache->set('niche-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return @iconv(Yii::$app->charset,'gbk//IGNORE', $csv);
    }

    //删除特殊符号
    private function trimStr($str)
    {
        //注意有个特殊空格符号" "
        $needReplace = [" ","　"," ","\t","\n","\r"];
        $result = ["","","","",""];
        return str_replace($needReplace,$result,$str);
    }

    /**
     * @SWG\Post(path="/niche/niche/add",
     *     tags={"niche"},
     *     summary="新增商机接口",
     *     description="新增商机接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "商机数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/AddNicheForm")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "新增成功",
     *        @SWG\Schema(ref="#/definitions/NicheForm")
     *    )
     * )
     *
     */
    public function actionAdd()
    {
        $form = new AddNicheForm();
        $form->load(\Yii::$app->request->bodyParams);
        if(!$form->validate())
        {
            return $this->responseValidateError($form);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $niche = $form->save($administrator);
        $error = $form->getFirstErrors();
        if ($error){
            if (isset($error['products']))
            {
                return $this->response(400, current($error), isset($niche->id) ? $niche->id :'');
            }
            else
            {
                return $this->response(200, current($error), isset($niche->id) ? $niche->id :'');
            }

        }
        if(null == $niche)
        {
            return $this->response(400, '商机新增保存失败！');
        }
        return $this->response(200, '商机新增保存成功！', $niche->id);
    }

    /**
     * 商机联系人接口
     */
    public function actionConcat()
    {
        // todo 这个接口方法应该不需要，直接从客户中读取放入到商机详情中
    }

    /**
     * @SWG\Post(path="/niche/niche/detail",
     *     tags={"niche"},
     *     summary="商机详情接口",
     *     description="根据id获取商机详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "id",
     *		description = "商机id",
     *		required = true,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/Niche")
     *    )
     * )
     * @param $id integer
     * @return array
     */
    public function actionDetail($id)
    {
        $niche = NicheDetail::findOne($id);
        // todo 需要检查该商机当前登录人是否有权限查看
        $niche->select();
        // todo 读取客户联系人信息放入到相应数据中
        return $this->response(200, "OK", $niche);
    }

    /**
     * 商机商品列表接口
     */
    public function actionProducts()
    {
        // todo 这个接口方法应该不需要，直接从商机详情中获取商品列表，待讨论
    }


    private function queryNiches($query)
    {

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        return $dataProvider;
    }

    /**
     * @SWG\Post(path="/niche/niche/create-list",
     *     tags={"niche"},
     *     summary="创建人列表",
     *     description="创建人列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheCreateList")
     *    )
     * )
     * @return array
     */
    public function actionCreateList()
    {
        $model = new NicheCreateList();
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/salesman-list",
     *     tags={"niche"},
     *     summary="业务员列表",
     *     description="业务员列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheSalesmanList")
     *    )
     * )
     * @return array
     */
    public function actionSalesmanList()
    {
        $model = new NicheSalesmanList();
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/administrator-list",
     *     tags={"niche"},
     *     summary="负责人列表",
     *     description="负责人列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheAdministratorList")
     *    )
     * )
     * @return array
     */
    public function actionAdministratorList()
    {
        $model = new NicheAdministratorList();
        return $this->response(200,'查询成功',$model->select());
    }


    /**
     * @SWG\Post(path="/niche/niche/last-record-list",
     *     tags={"niche"},
     *     summary="最后跟进人列表",
     *     description="最后跟进人列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheLastRecordList")
     *    )
     * )
     * @return array
     */
    public function actionLastRecordList()
    {
        $model = new NicheLastRecordList();
        return $this->response(200,'查询成功',$model->select());
    }


    /**
     * @SWG\Post(path="/niche/niche/distribution-list",
     *     tags={"niche"},
     *     summary="分配人列表",
     *     description="分配人列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/NicheDistributionList")
     *    )
     * )
     * @return array
     */
    public function actionDistributionList()
    {
        $model = new NicheDistributionList();
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/source-list",
     *     tags={"niche"},
     *     summary="商机来源列表",
     *     description="商机来源列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/SourceList")
     *    )
     * )
     * @return array
     */
    public function actionSourceList()
    {
        $model = new SourceList();
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/channel-list",
     *     tags={"niche"},
     *     summary="商机渠道来源列表",
     *     description="商机渠道来源列表",
     *     produces={"application/json"},
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/ChannelList")
     *    )
     * )
     * @return array
     */
    public function actionChannelList()
    {
        $model = new ChannelList();
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/category-list",
     *     tags={"niche"},
     *     summary="类目列表",
     *     description="类目列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "商机数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/CategoryList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/CategoryList")
     *    )
     * )
     * @return array
     */
    public function actionCategoryList()
    {
        $model = new CategoryList();
        $model->load(\Yii::$app->request->post(),'');
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/product-category-list",
     *     tags={"niche"},
     *     summary="类目联动商品列表",
     *     description="类目联动商品列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "body",
     *		name = "body",
     *		description = "商机数据",
     *		required = false,
     *		type = "string",
     *      @SWG\Schema(ref = "#/definitions/ProductCategoryList")
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/ProductCategoryList")
     *    )
     * )
     * @return array
     */
    public function actionProductCategoryList()
    {
        $model = new ProductCategoryList();
        $model->load(\Yii::$app->request->post(),'');
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/company-list",
     *     tags={"niche"},
     *     summary="公司列表",
     *     description="公司列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "type",
     *		description = "公司列表",
     *		required = false,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/CompanyList")
     *    )
     * )
     * @param $type
     * @return array
     */
    public function actionCompanyList($type = 0)
    {
        $model = new CompanyList();
        return $this->response(200,'查询成功',$model->select($type));
    }

    /**
     * @SWG\Post(path="/niche/niche/department-list",
     *     tags={"niche"},
     *     summary="根据公司ID获取部门列表",
     *     description="根据公司ID获取部门列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "id",
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
     * @param $id integer
     * @return array
     */
    public function actionDepartmentList($id)
    {
        $model = new DepartmentList();
        $model->id = $id;
        return $this->response(200,'查询成功',$model->select());
    }

    /**
     * @SWG\Post(path="/niche/niche/person-list",
     *     tags={"niche"},
     *     summary="根据公司ID获取人员列表",
     *     description="根据公司ID获取人员列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *		in = "query",
     *		name = "id",
     *		description = "公司ID",
     *		required = true,
     *		type = "integer"
     *    ),
     *    @SWG\Response(
     *        response = 200,
     *        description = "获取成功",
     *        @SWG\Schema(ref="#/definitions/PersonList")
     *    )
     * )
     * @param $id integer
     * @return array
     */
    public function actionPersonList($id)
    {
        $model = new PersonList();
        return $this->response(200,'查询成功',$model->getList($id));
    }


}