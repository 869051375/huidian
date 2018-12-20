<?php

namespace backend\controllers;

use backend\models\CustomerConfirmClaimNewForm;
use backend\models\CustomerConfirmDistributionForm;
use common\models\CrmCustomer;
use common\models\CrmCustomerApi;
use common\models\CrmCustomerLog;
use common\models\CrmDepartment;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerLevel;
use common\models\CustomerNewCustomField;
use common\models\CustomerPublic;
use common\models\Administrator;
use common\models\CrmCustomerCombine;
use common\models\Company;
use common\models\CustomerFollowRecord;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use League\Csv\Writer;
use common\utils\BC;
use yii\db\Query;
use yii\httpclient\Client;

class CrmCustomerApiController extends ApiController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
//                    [
//                        'actions' => ['customer-list', 'customer-detail', 'customer-basic-detail', 'crm-customer-update', 'crm-customer-basic-update', 'customer-add', 'extract', 'distribution','customer-public-list','get-login-state','export','get-phone-list','get-editors','call','show-call','get-custom','customer-field-update'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
                    [
                        'actions' => ['customer-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/customer-list'],
                    ],
                    [
                        'actions' => ['customer-detail'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/customer-detail'],
                    ],
                    [
                        'actions' => ['customer-basic-detail'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/customer-basic-detail'],
                    ],
                    [
                        'actions' => ['crm-customer-update'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/crm-customer-update'],
                    ],
                    [
                        'actions' => ['crm-customer-basic-update'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/crm-customer-basic-update'],
                    ],
                    [
                        'actions' => ['customer-add'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/customer-add'],
                    ],
                    [
                        'actions' => ['extract'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/extract'],
                    ],
                    [
                        'actions' => ['distribution'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/distribution'],
                    ],
                    [
                        'actions' => ['customer-public-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/customer-public-list'],
                    ],
                    [
                        'actions' => ['get-login-state'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/get-login-state'],
                    ],
                    [
                        'actions' => ['export'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/export'],
                    ],
                    [
                        'actions' => ['get-phone-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/get-phone-list'],
                    ],
                    [
                        'actions' => ['get-editors'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/get-editors'],
                    ],
                    [
                        'actions' => ['call'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/call'],
                    ],
                    [
                        'actions' => ['show-call'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/show-call'],
                    ],
                    [
                        'actions' => ['get-custom'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/get-custom'],
                    ],
                    [
                        'actions' => ['customer-field-update'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['crm-customer-api/customer-field-update'],
                    ]
                ],
            ],
        ];
        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }

    //我的客户 列表
    public function actionCustomerList()
    {

        $arr = Yii::$app->request->post();

        //customer_public_id  0我的客户  不等于0公海客户
        //subject_type  0企业  1自然人
        // scene_type 筛选我的客户1 全部客户3 下属客户2   判断是否是领导 以及获取下属id

//        $arr['page'] = 1;
//        $arr['page_num'] = '10';
//        $arr['subject_type'] = 0;
//        $arr['scene_type'] = 1;
//        $arr['customer_public_id'] = 45;
//        $arr['customer_address'] = '';
//        $arr['last_record_creator_id'] = '';
//        $arr['last_record'] = '';
//        $arr['industry_id'] = '';
//        $arr['business_address'] = '';
//        $arr['keyword'] = '';
//        $arr['labels'] = '';
//        $arr['levels'] = '';
//        $arr['create_start_time'] = '';
//        $arr['create_end_time'] = '';
//        $arr['weihu_start_time'] = '';
//        $arr['weihu_end_time'] = '';
//        $arr['start_last_record'] = '';
//        $arr['end_last_record'] = '';
//        $arr['source'] = '';
//        $arr['get_way'] = '';
//        $arr['combine_id'] = '';
//        $arr['administrator_id'] = '';
//        $arr['department_id'] = '';
//        $arr['dates'] = '';
//        $arr['keyword_field'] = '';
//        $arr['keyword_value'] = '';
//        $arr['customer_type'] = '45';
//        $arr['company_id'] = '';
//        $arr['department_id'] = '';

        $model = new CrmCustomerApi();
        $model->load($arr, '');

        $model->setScenario('list');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $page_num = $arr['page_num'] ? $arr['page_num'] : 10;
        $count = $model->customerList(0, 1);
        $page = $arr['page'];
        $page_count = ceil($count / $page_num);
        if ($page <= 1 || $page == "") {
            $page = 1;
        } else if ($page >= $page_count) {
            $page = $page_count;
        }
        //计算偏移量
        $limit = ($page - 1) * $arr['page_num'];

        $rs = $model->customerList($limit, 2);

        $data = [
            'count' => $count,
            'page' => $page,
            'list' => $rs
        ];

        return $this->response(self::SUCCESS, '查询成功', $data);

    }

    //关联信息查询
    public function actionCustomerDetail()
    {
        //传客户id
        $post = Yii::$app->request->post();

        //customer_public_id  1我的客户 2 公海客户

//        $post['id'] = '181294';
//        $post['customer_public_id'] = 2;

        $model = new CrmCustomerApi();

        $model->load($post, '');

        $model->setScenario('detail');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->getCustomerDetail($post);

        return $this->response(self::SUCCESS, '查询成功', $rs);

    }

    //基本信息查询个人
    public function actionCustomerBasicDetail()
    {
        $post = Yii::$app->request->post();

        //customer_public_id  1我的客户 2 公海客户

//        $post['id'] = 1493;
//        $post['customer_public_id'] = 2;

        $model = new CrmCustomerApi();

        $model->load($post, '');

        $model->setScenario('detail');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->getCustomerBasicDetail($post);

        return $this->response(self::SUCCESS, '查询成功', $rs);

    }

    //关联信息修改

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCrmCustomerUpdate()
    {
        $arr = Yii::$app->request->post();

//        $arr['id'] = '1';
//        $arr['contact_id'] = 2;
//        $arr['name'] = '鱼儿';
//        $arr['gender'] = 0;
//        $arr['phone'] = '13716880579';
//        $arr['qq'] = '648043478';
//        $arr['wechat'] = 'yusirui211';
//        $arr['tel'] = '010-4444455';
//        $arr['email'] = 'wuliYsr@163.com';
//        $arr['birthday'] = '1900-10-11';
//        $arr['caller'] = '13716880579';
//        $arr['source'] = '1';
//        $arr['channel_id'] = '1';
//        $arr['position'] = '业务员啊';
//        $arr['department'] = '技术部啊';
//        $arr['province_id'] = '11';
//        $arr['province_name'] = '北京啊';
//        $arr['city_id'] = '22';
//        $arr['city_name'] = '北京啊';
//        $arr['district_id'] = '33';
//        $arr['district_name'] = '朝阳区啊';
//        $arr['street'] = '京粮大厦啊';
//        $arr['customer_hobby'] = '没啥爱好啊没啥爱好爱好啊没啥爱好啊没啥爱好啊没啊';
//        $arr['remark'] = '';
//        $arr['native_place'] = '北京啊';

        $model = new CrmCustomerApi();

        $model->load($arr, '');

        $model->setScenario('update');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->updateCustomerDetail();

        if (!$rs) {
            return $this->response(self::FAIL, '修改失败');
        } else {
            return $this->response(self::SUCCESS, '修改成功', $rs);

        }


    }

    //个人基本信息修改

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCrmCustomerBasicUpdate()
    {
        $arr = Yii::$app->request->post();

//        $arr['customer_id'] = '1501';
//        $arr['contacts_id'] = '1483';
//        $arr['business_id'] = '333';
//        $arr['user_id'] = 1488;
//        $arr['user_name'] = '测试啊';
//        $arr['id_number'] = '11111333333311122222';
//        $arr['province_id'] = '';
//        $arr['province_name'] = '';
//        $arr['city_id'] = '';
//        $arr['city_name'] = '';
//        $arr['district_id'] = '';
//        $arr['district_name'] = '';
//        $arr['company_remark'] = '';
//        $arr['street'] = '';

        $model = new CrmCustomerApi();

        $model->load($arr, '');

        $model->setScenario('basic');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->crmCustomerBasicUpdate();

        if (!$rs) {
            return $this->response(self::FAIL, '修改失败', $rs);
        } else {
            return $this->response(self::SUCCESS, '修改成功', $rs);

        }

    }


    //新增客户接口

    /**
     * @return array
     * @throws \Exception
     */
    public function actionCustomerAdd()
    {
        $arr = Yii::$app->request->post();

        //business表信息
        //business_subject  0 企业  1  自然人
//        $arr['subject_type'] = 1;
//        $arr['company_name'] = '武汉掘金网络营销顾问有限公司22';
//        $arr['business_name'] = '';
//        $arr['industry_id'] = '';
//        $arr['industry_name'] = '';
//        $arr['tax_type'] = '';
//        $arr['credit_code'] = '91420111578276708R';
//        $arr['register_status'] = '存续';
//        $arr['company_type_id'] = '';
//        $arr['enterprise_type'] = '有限责任公司(自然人肚独资)';
//        $arr['legal_person_name'] = '姚媛媛';
//        $arr['registered_capital'] = '10';
//        $arr['operating_period_begin'] = '2018-10-11 11:22:33';
//        $arr['operating_period_end'] = '2018-11-11 11:22:33';
//        $arr['register_unit'] = '武汉市洪山区工商行政管理和质量技术监督局';
//        $arr['business_province_id'] = '';
//        $arr['business_province_name'] = '';
//        $arr['business_city_id'] = '';
//        $arr['business_city_name'] = '';
//        $arr['business_district_id'] = '';
//        $arr['business_district_name'] = '';
//        $arr['address'] = '洪山区珞南街珞瑜路446号5楼3号';
//        $arr['scope'] = '有限责任公司(自然人投资或控股)有限责任公司(自然人投资或控股)有限责任公司(自然人投资或控股)有限责任公司(自然人投资或控股)有限责任公司(自然人投资或控股)有限责任公司(自然人投资或控股)';
//        $arr['official_website'] = '';
//        $arr['filing_tel'] = '';
//        $arr['filing_email'] = '';
//        $arr['company_remark'] = '';
//
//        //联系人信息
//        $arr['customer_name'] = '第三方';
//        $arr['gender'] = 0;
//        $arr['phone'] = '13716555555';
//        $arr['wechat'] = '';
//        $arr['qq'] = '';
//        $arr['tel'] = '0913-62655555';
//        $arr['caller'] = '';
//        $arr['email'] = '';
//        $arr['birthday'] = '1995-11-11';
//        $arr['source'] = '';
//        $arr['source_name'] = '';
//        $arr['channel_id'] = '';
//        $arr['position'] = '';
//        $arr['department'] = '';
//        $arr['customer_province_id'] = '';
//        $arr['customer_province_name'] = '';
//        $arr['customer_city_id'] = '';
//        $arr['customer_city_name'] = '';
//        $arr['customer_district_id'] = '';
//        $arr['customer_district_name'] = '';
//        $arr['street'] = '';
//        $arr['customer_hobby'] = '';
//        $arr['remark'] = '';
//        $arr['level'] = '';
//        $arr['native_place'] = '';
//
//        $arr['administrator_id'] = '';
//        $arr['administrator_name'] = '';
//        $arr['department_id'] = '';

        $model = new CrmCustomerApi();

        $model->load($arr, '');

        $model->setScenario('create');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }
        $rs = $model->customerAdd();

        if ($rs['code'] == '200') {
            return $this->response(self::SUCCESS, $rs['message'], $rs['data']);
        } else {
            return $this->response(self::FAIL, $rs['message']);
        }
    }

    /**
     * 公海客户提取
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionExtract()
    {
        $post = Yii::$app->request->post();

//        $post['id'] = '1780';

        $model = new CustomerConfirmClaimNewForm();
        $model -> load($post,'');
        if(!$model -> validate()){
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $customer_model = new CrmCustomerApi();

        $customer_model->load($post, '');

        $customer_model->setScenario('extract');

        if (!$customer_model->validate()) {
            $errors = $customer_model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $customer_model->updateExtract($post);

        if ($rs == true) {
            return $this->response(self::SUCCESS, '提取成功', $rs);
        } else {
            return $this->response(self::FAIL, '提取失败', $rs);
        }

    }

    /**
     * 公海客户分配
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionDistribution()
    {
        $post = Yii::$app->request->post();

//        $post['administrator_id'] = 732;
//        $post['id'] = '1781,1782';
//        $post['company_id'] = '18';

        $model = new CustomerConfirmDistributionForm();
        $model -> load($post,'');
        if(!$model -> validate()){
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $customer_model = new CrmCustomerApi();

        $customer_model->load($post, '');

        $customer_model->setScenario('distribution');

        if (!$customer_model->validate()) {
            $errors = $customer_model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }
        $rs = $customer_model->updateDistribution($post);

        if ($rs == true) {
            return $this->response(self::SUCCESS, '分配成功', $rs);
        } else {
            return $this->response(self::FAIL, '分配失败', $rs);
        }


    }

    //公海场景列表
    public function actionCustomerPublicList(){

        $post = Yii::$app ->request->post();
//        $post['customer_type'] = 1;

        if($post['customer_type'] == ''){
            return $this->response(self::FAIL, 'customer_type不得为空');
        }

        $query = CustomerPublic::find() ->alias('c')
            -> select('id,name')
            ->leftJoin(['d' => CustomerDepartmentPublic::tableName()],'c.id=d.customer_public_id')
            -> where(['in','c.customer_type',[0,$post['customer_type']]]);

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        if($identity->is_belong_company == 1 || $identity->company_id != ''){
            $query -> andWhere(['d.customer_department_id' => $identity->department_id]);
        }
        $list = $query->all();

        return $this->response(self::SUCCESS, '公海列表', $list);
    }

    //获取当前登录人职位
    public function actionGetLoginState(){
        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        if($identity->isDepartmentManager() || $identity->isLeader()){
            $rs['status'] = 1;
        }else{
            $rs['status'] = 0;
        }
        return $this->response(self::SUCCESS, '1:领导；0:非领导', $rs);
    }


    /**
     * 客户导出 导出全部客户
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionExport()
    {
        $arr = Yii::$app->request->queryParams;
//        $arr['scene_type'] = 3;
//        $arr['customer_public_id'] = 0;
//        $arr['subject_type'] = 0;
        //customer_public_id  0我的客户  不等于0公海客户
        //subject_type  0企业  1自然人
        // scene_type 筛选我的客户1 全部客户3 下属客户2   判断是否是领导 以及获取下属id
        $export_code = Yii::$app->cache->get('crm-customer-export-' . Yii::$app->user->id);
        if($export_code)
        {
            $second = date('s',BC::sub($export_code+30,time()));
            return $this->response(self::FAIL,'您的操作过于频繁，请等待'.$second.'秒！');
        }
        $batchNum = 100;
        $model = new CrmCustomerApi();
        $model->load($arr,'');
        $model->setScenario('export');
        if(!$model->validate()) {
            $error = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($error));
        }
        $query = new Query();
        $model->customerList(0,1, $query);

        $query->select('any_value(c.id) as id,any_value(c.created_at) as created_at,any_value(bs.company_name) as name, any_value(s.name) as source_name,any_value(tag.name) as tag_name,any_value(c.get_way) as get_way,any_value(a.name) as administrator_name,any_value(co.name) as company_name,any_value(d.name) as department_name,any_value(c.last_record_creator_name) as last_record_creator_name, any_value(c.last_record) as last_record,any_value(cl.name) as level')
            ->leftJoin(['co' => Company::tableName()],'a.company_id=co.id')
            ->leftJoin(['d' => CrmDepartment::tableName()],'c.department_id=d.id')
            ->leftJoin(['cl' => CustomerLevel::tableName()],'c.level=cl.id');

        $count = $query->count('c.id');
        if(empty($count))
        {
            return $this->response(self::FAIL,'没有获取到任何客户记录');
        }
        $batch = ceil($count / $batchNum);
        $csv = Writer::createFromString('');
        $header = ['客户创建时间','客户ID','客户名称','客户来源','标签','客户级别','客户获取方式','负责人', '负责人所属公司',
            '负责人所属部门', '合作人/合作人所属公司/合作人所属部门','最后跟进人','最后跟进时间'];
        $csv->insertOne($header);
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        for($i = 0; $i < $batch; $i++)
        {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
            $models = $query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $crmCustomer)
            {
                $lastRecord = $this->getLastRecordInfo($crmCustomer['id'],$crmCustomer['last_record_creator_name'],$crmCustomer['last_record']);
                $csv->insertOne([
                    "\t" . date('Y-m-d H:i:s',$crmCustomer['created_at']),
                    "\t" . $crmCustomer['id'],
                    "\t" . $this->trimStr($crmCustomer['name']),
                    "\t" . $this->trimStr($crmCustomer['source_name']),
                    "\t" . $this->trimStr($crmCustomer['tag_name']),
                    "\t" . $this->trimStr($crmCustomer['level']),
                    "\t" . $crmCustomer['get_way'] == 0 ? 'CRM录入' : '自动注册',
                    "\t" . $this->trimStr($crmCustomer['administrator_name']),
//                    "\t" . $this->getAdministratorLevelName($crmCustomer['id'],$crmCustomer['administrator_id']),
                    "\t" . $this->trimStr($crmCustomer['company_name']),
                    "\t" . $this->trimStr($crmCustomer['department_name']),
                    "\t" . $this->trimStr($this->getCrmCustomerCombines($crmCustomer['id'])),
                    "\t" . $this->trimStr($lastRecord['name']),
                    "\t" . $lastRecord['time'],
                ]);
                unset($lastRecord);
                unset($crmCustomer);
            }
            unset($models);
        }
        $filename = date('YmdHis').rand(10000,99999).'_客户记录.csv';
        Yii::$app->cache->set('crm-customer-export-' . Yii::$app->user->id,time(),30);
        Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
        return @iconv(Yii::$app->charset,'gbk', $csv);
    }

    //删除特殊符号
    private function trimStr($str)
    {
        //注意有个特殊空格符号" "
        $needReplace = [" ","　"," ","\t","\n","\r"];
        $result = ["","","","",""];
        return str_replace($needReplace,$result,$str);
    }


    //跟进记录
    public function getLastRecordInfo($id,$last_record_creator_name,$last_record)
    {
        /** @var  CrmCustomerLog $lastRecord */
        $lastRecord = CrmCustomerLog::find()->select(['creator_name', 'created_at'])->where(['customer_id' => $id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])->orderBy(['created_at'=>SORT_DESC])->one();
        $data = [];
        if(!empty($last_record_creator_name))
        {
            $data['name'] = $last_record_creator_name;
        }
        else
        {
            $data['name'] = $lastRecord ? $lastRecord->creator_name : '--';
        }

        if(!empty($last_record))
        {
            $data['time'] = date('Y-m-d H:i:s',$last_record);
        }
        else
        {
            $data['time'] = $lastRecord ? date('Y-m-d H:i:s',$lastRecord->created_at) : '--';
        }
        return $data;
    }


    //查询合伙人
    public function getCrmCustomerCombines($id)
    {
        //优化查询，减少内存使用
        $crmCustomerCombines = CrmCustomerCombine::find()->select(['a.name as administrator_name', 'co.name as company_name', 'd.name as department_name', 'c.level'])->alias('c')
            ->leftJoin(['a'=>Administrator::tableName()],'c.administrator_id = a.id')
            ->leftJoin(['d'=>CrmDepartment::tableName()],'c.department_id = d.id')
            ->leftJoin(['co' => Company::tableName()],'c.company_id = co.id')
            ->where(['c.customer_id' => $id])
            ->asArray()
            ->all();
        if($crmCustomerCombines){
            $str = '';
            $count = count($crmCustomerCombines);
            foreach($crmCustomerCombines as $key => $val){

                $administratorName = $val['administrator_name'] ? $val['administrator_name'] : '--';
                $companyName = $val['company_name'] ? $val['company_name'] : '--';
                $departmentName = $val['department_name'] ? $val['department_name'] : '--';
                if($key != $count -1)
                {
                    $str .= $administratorName.'/'.$companyName.'/'.$departmentName."&";
                }
                else
                {
                    $str .= $administratorName.'/'.$companyName.'/'.$departmentName;
                }
                unset($administrator);
                unset($administratorName);
                unset($company);
                unset($companyName);
                unset($department);
                unset($departmentName);
            }
            return $str;
        }
        return '';
    }


    /**
     * 修改时 查询手机号是否在用户表注册  todo 暂时不用了
     * @return array
     */
    public function actionGetPhoneList(){
        $arr = Yii::$app->request->post();
        $model = new  CrmCustomerApi();
        $model -> load($arr,'');
        $model->setScenario('phone_status');
        if(!$model->validate()) {
            $error = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($error));
        }
        $phone = User::find() -> where(['phone' => $arr['phone']]) -> one();
        if(!empty($phone)){
            $data['status'] = 1;
            $message = '重复';
        }else{
            $data['status'] = 0;
            $message = '无重复';
        }
        return $this -> response(self::SUCCESS,$message,$data);
    }


    /**
     * 判断当前登录人是否有编辑权限
     * @return array
     */
    public function actionGetEditors(){

        $customer_id = \Yii::$app->request->post();
//        $customer_id['customer_id'] = 1495;
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;

        if($administrator->type == 1){
            $administrator_id = [$administrator->id];
        }else{
            $administrator_id = $administrator->getTreeAdministratorId(true,true);
        }
        if($administrator->isLeader()){
            if(!in_array($administrator->id,$administrator_id)){
                $data['status'] = 0;
            }else{
                $data['status'] = 1;
            }
        }else{
            /** @var CrmCustomer $customer */
            $customer = CrmCustomer::find()->where(['id'=>$customer_id['customer_id']])->one();
            if($administrator->id == $customer->administrator_id){
                $data['status'] = 1;
            }else{
                $data['status'] = 0;
            }
        }

        return $this -> response(self::SUCCESS,'0:无编辑权限；1：有编辑权限',$data);
    }


    /**
     * 外呼接口
     * @param $id
     * @return array
     */
    public function actionCall()
    {
        $id = Yii::$app->request->post('id');
        if(empty($id)){
            return $this->response(self::FAIL,'参数不得为空！');
        }
        $customer = CrmCustomer::findOne($id);
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $callCenter = $admin->callCenterAssignCompany ? $admin->callCenterAssignCompany->callCenter ? $admin->callCenterAssignCompany->callCenter : null :null;

        if(null == $callCenter || empty($customer->phone) || empty($admin->call_center))
        {
            return $this->response(self::FAIL,'呼叫失败，api呼叫已禁用！');
        }

        if($callCenter)
        {
            if(!$callCenter->isOnline())
            {
                return $this->response(self::FAIL,'呼叫失败，api呼叫已禁用！');
            }
            $client = new Client();
            $url = $callCenter->url.'&phonenum='.$customer->phone.'&integratedid='.trim($admin->call_center);
            $response = $client->get($url)->send();
            if($response->getIsOk())
            {
                $jsonString = $response->getContent();
                if($jsonString == 200)
                {
                    return $this->response(self::SUCCESS,'呼叫成功');
                }
                else
                {
                    //失败时返回json数据
                    //$jsonDecodeString = json_decode($jsonString);
                    return $this->response(self::FAIL,'呼叫失败');
                }
            }
        }
        return $this->response(self::FAIL,'呼叫失败');
    }

    //判断是否显示外呼按钮
    public function actionShowCall(){
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $call = $admin->call_center;
        if($call){
            $data['status'] = 1;
        }else{
            $data['status'] = 0;
        }
        return $this->response(self::SUCCESS,'1：显示；0：不显示',$data);

    }

    //获取自定义列表
    public function actionGetCustom(){
        $post = Yii::$app->request->post();
//        $post['type'] = 2;
        $model = new CustomerNewCustomField();
        $model ->load($post,'');
        if(!$model->validate()){
            $error = $model->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }
        $rs = $model -> getCustomerField();

        return $this->response(self::SUCCESS,'客户自定义列表获取成功',$rs);

    }

    //自定义列表修改
    public function actionCustomerFieldUpdate(){
        $post = Yii::$app->request->post();
//        $post['type'] = 1;
//        $post['field'] = [
//            ['field'=>'id','field_name'=>'主体id','sort'=>'1','status'=>1,'is_update'=>0],
//            ['field'=>'customer_id','field_name'=>'客户id','sort'=>'2','status'=>1,'is_update'=>0],
//            ['field'=>'contact_id','field_name'=>'联系人id','sort'=>'3','status'=>1,'is_update'=>0],
//            ['field'=>'company_name','field_name'=>'客户名称','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'phone','field_name'=>'联系电话','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'username','field_name'=>'负责人','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'next_record','field_name'=>'下次跟进时间','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'last_operation_creator_name','field_name'=>'最后维护人','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'operation_time','field_name'=>'最后维护时间','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'created_at','field_name'=>'创建时间','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'source_name','field_name'=>'来源','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'channel_name','field_name'=>'来源渠道','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'level','field_name'=>'客户级别','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'tag_name','field_name'=>'标签','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'yichengjiao','field_name'=>'已成交商机数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'genjinzhong','field_name'=>'跟进中商机数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'shenqingzhong','field_name'=>'申请中商机数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'yishibai','field_name'=>'已失败商机数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'daiqueren','field_name'=>'待确认商机数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'daitiqu','field_name'=>'待提取商机数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'order_yifukuan','field_name'=>'已付款订单数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'order_weifukuan','field_name'=>'未付款订单数','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'customer_number','field_name'=>'客户编号','sort'=>'3','status'=>1,'is_update'=>1],
//            ['field'=>'get_way','field_name'=>'获取方1231式','sort'=>'3','status'=>1,'is_update'=>1]
//        ];
        $model = new CustomerNewCustomField();
        $model ->load($post,'');
        if(!$model->validate()){
            $error = $model->getFirstErrors();
            return $this->response(self::FAIL,reset($error));
        }
        $rs = $model -> customerFieldUpdate();
        if($rs){
            return $this->response(self::SUCCESS,'客户自定义列表修改成功',$rs);
        }else{
            return $this->response(self::FAIL,'客户自定义列表修改失败');

        }
    }

}