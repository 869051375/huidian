<?php

namespace backend\controllers;
//use backend\fixtures\Administrator;
use backend\models\CustomerProtectForm;
use common\models\BusinessSubject;
use common\models\CompanyType;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CustomerPublic;
use common\models\Niche;
use common\models\Source;
use common\models\Trademark;
use Imagine\Gmagick\Image;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use common\models\Company;
use common\models\CrmDepartment;
use common\models\Administrator;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\Order;
use common\models\VirtualOrder;
use common\models\CrmCustomerApi;
use common\models\CustomerDepartmentPublic;
use common\models\Tag;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use common\components\QCC;
use yii\behaviors\TimestampBehavior;

class CustomerSearchController extends ApiController
{
    public function behaviors()
    {
        $behaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['customer-level'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-level'],
                    ],
                    [
                        'actions' => ['customer-level-update'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-level-update'],
                    ],
                    [
                        'actions' => ['company-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/company-list'],
                    ],
                    [
                        'actions' => ['companytype-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/companytype-list'],
                    ],
                    [
                        'actions' => ['company-detail'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/company-detail'],
                    ],
                    [
                        'actions' => ['customer-from'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-from'],
                    ],
                    [
                        'actions' => ['customer-access'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-access'],
                    ],

                    [
                        'actions' => ['province-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/province-list'],
                    ],

                    [
                        'actions' => ['city-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/city-list'],
                    ],

                    [
                        'actions' => ['district-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/district-list'],
                    ],
                    [
                        'actions' => ['customer-leading'],
                        'allow' => true,
                        'roles' => ['@'],
                        //'roles' => ['customer-search/customer-leading'],
                    ],
                    [
                        'actions' => ['cooper-delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        //'roles' => ['customer-search/cooper-delete'],
                    ],
                    [
                        'actions' => ['customer-public-list'],
                        'allow' => true,
                        'roles' => ['@'],
                        //'roles' => ['customer-search/customer-public-list'],
                    ],
                    [
                        'actions' => ['customer-public-detail'],
                        'allow' => true,
                        'roles' => ['@'],
                        //'roles' => ['customer-search/customer-public-detail'],
                    ],
                    [
                        'actions' => ['customer-public-save', 'customer-public-status', 'customer-public-delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        //'roles' => ['customer-search/customer-public-save'],
                    ],

                    [
                        'actions' => ['organize'],
                        'allow' => true,
                        'roles' => ['@'],
                        //'roles' => ['customer-search/organize'],
                    ],
                    [
                        'actions' => ['permanent-address'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/permanent-address'],
                    ],
                    [
                        'actions' => ['public-follow'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/public-follow'],
                    ],
                    [
                        'actions' => ['customer-trademark'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-trademark'],
                    ],
                    [
                        'actions' => ['company-data'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/company-data'],
                    ],
                    [
                        'actions' => ['get-partner'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/get-partner'],
                    ],
                    [
                        'actions' => ['get-register-status'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/get-register-status'],
                    ],
                    [
                        'actions' => ['get-last-record-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/get-last-record-list'],
                    ],
                    [
                        'actions' => ['customer-protect'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-protect'],
                    ],
                    [
                        'actions' => ['customer-cancel-protect'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-cancel-protect'],
                    ],
                    [
                        'actions' => ['customer-repeat'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/customer-repeat'],
                    ],
                    [
                        'actions' => ['organization-tree'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/organization-tree'],
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/organization-tree'],
                    ],
                    [
                        'actions' => ['source-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['customer-search/organization-tree'],
                    ],
                ],
            ],
        ];
        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }

    //跟进人接口
    public function actionGetLastRecordList()
    {
        $list = CrmCustomer::find()
            ->select('last_record_creator_id,last_record_creator_name')
            ->distinct(true)
            ->where(['or', "last_record_creator_id != ''", "last_record_creator_name != ''"])
            ->all();

        return $this->response(self::SUCCESS, '跟进人列表', $list);
    }

    //客户级别接口
    public function actionCustomerLevel()
    {
        $rows = (new \yii\db\Query())
            ->from('customer_level')
            ->all();
        $list = $this->genTree($rows, 0);
        return $this->response(self::SUCCESS, '客户级别列表', $list);
    }

    /**
     * 客户级别修改
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCustomerLevelUpdate()
    {
        $customer_id = Yii::$app->request->post('customer_id');
        $level_id = Yii::$app->request->post('level_id');
        if (empty($customer_id)) {
            return $this->response(self::FAIL, 'customer_id不得为空');
        }
        if (empty($level_id)) {
            return $this->response(self::FAIL, 'level_id不得为空');
        }
        $level = CrmCustomer::find()->where(['id'=>$customer_id])->asArray()->one();

        /** @var BusinessSubject $business */
        $business = BusinessSubject::find()->where(['customer_id'=>$customer_id])->one();
        if($level['level'] == $level_id){
            return $this->response(self::SUCCESS, '修改成功');
        }

        $t = Yii::$app->db->beginTransaction();

        try{
            $level_update = CrmCustomer::find()->createCommand()->update(CrmCustomer::tableName(), [
                'level' => $level_id,
            ], ['id' => $customer_id])->execute();

            $business_id = isset($business->id)? $business->id : 0;
            CrmCustomerLog::add('编辑客户信息中的客户级别模块', $customer_id, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
        }

        if ($level_update) {
            return $this->response(self::SUCCESS, '修改成功');
        } else {
            return $this->response(self::FAIL, '修改失败');
        }
    }

    //客户来源列表
    public function actionCustomerFrom()
    {
        $rows = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('source')
            ->where(['status' => 1])
            ->orderBy([
                'sort' => SORT_DESC,
            ])
            ->all();
        if ($rows) {
            return $this->response(self::SUCCESS, '客户来源列表展示成功', $rows);
        }
    }

    //获取方式
    public function actionCustomerAccess()
    {
        $list = [
            ['id' => 1, 'name' => 'CRM录入'],
            ['id' => 2, 'name' => '客户自主注册'],
        ];
        return $this->response(self::SUCCESS, '获取客户展示成功', $list);
    }

    //省接口
    public function actionProvinceList()
    {
        $list = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('province')
            ->orderBy([
                'id' => SORT_ASC,
            ])
            ->all();
        return $this->response(self::SUCCESS, '省列表展示成功', $list);
    }

    //市接口
    public function actionCityList()
    {
        $province_id = Yii::$app->request->post('province_id');
        if (empty($province_id)) {
            return $this->response(self::FAIL, '没有传递省id');

        }
        $list = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('city')
            ->where(['province_id' => $province_id])
            ->orderBy([
                'id' => SORT_ASC,
            ])
            ->all();
        return $this->response(self::SUCCESS, '市列表展示成功', $list);
    }

    //区接口
    public function actionDistrictList()
    {
        $city_id = Yii::$app->request->post('city_id');
        if (empty($city_id)) {
            return $this->response(self::FAIL, '没有传递市id');

        }
        $list = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('district')
            ->where(['city_id' => $city_id])
            ->orderBy([
                'id' => SORT_ASC,
            ])
            ->all();
        return $this->response(self::SUCCESS, '区列表展示成功', $list);
    }

    //合作人接口接口
    public function actionCustomerLeading()
    {
        $customer_id = Yii::$app->request->post('customer_id');
        if (empty($customer_id)) {
            $this->response(self::FAIL, 'customer_id不能为空');
        }

        $model = CrmCustomerCombine::find()->alias('ccc')
            ->leftJoin(['c' => CrmCustomer::tableName()], 'ccc.customer_id = c.id')
            ->leftJoin(['a' => Administrator::tableName()], 'ccc.administrator_id = a.id')
            ->leftJoin(['d' => CrmDepartment::tableName()], 'ccc.department_id = d.id')
            ->leftJoin(['cn' => Company::tableName()], 'ccc.company_id = cn.id')
            ->select('ccc.customer_id,c.name as customer_name,c.user_id,a.name,ccc.administrator_id,a.name as administrator_name,d.name as department_name,cn.name as company_name')
            ->where(['ccc.customer_id' => $customer_id])
            ->orderBy(['ccc.created_at'=>SORT_ASC])
            ->asArray()
            ->all();
        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['id'=>$customer_id])->one();
        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $arr = [];
        $i = 0;
        foreach ($model as $key => $val) {
            $arr[] = [
                'order_paid' => $this->getOrderAlreadyAmount($val ['user_id'], $val['administrator_id']),
                'order_unpaid' => $this->getOrderUnpaidAmount($val['user_id'], $val['administrator_id']),
                'opportunity_deal' => $this->getOpportunityAmount(Niche::STATUS_DEAL, $val['customer_id'], $val['administrator_id']),  //已成交
                'opportunity_apply' => $this->getOpportunityAmount(Niche::STATUS_NOT_DEAL, $val['customer_id'], $val['administrator_id']), //申请中 未成交
                'opportunity_fail' => $this->getOpportunityAmount(Niche::STATUS_FAIL, $val['customer_id'], $val['administrator_id']), //已失败
                'administrator_name' => $val['administrator_name'],
                'customer_name' => $val['customer_name'],
                'department_name' => $val['department_name'],
                'customer_id' => $val['customer_id'],
                'company_name' => $val['company_name'],
                'administrator_id' => $val['administrator_id'],
                'can_delete' => 1
            ];
            //can_delete 0 不显示 1显示
//            if($customer->administrator_id == $identity->id){
                if($val['administrator_id'] == $customer->administrator_id){
                    $arr[$i]['can_delete'] = 0;
                }else{
                    $arr[$i]['can_delete'] = 1;
                }
//            }else{
//                $arr[$i]['can_delete'] = 0;
//            }
            $i++;
        }

        array_multisort(array_column($arr,'can_delete'),SORT_ASC,$arr);

        return $this->response(self::SUCCESS, '返回成功', $arr);
    }


    //商机数统计
    public function getOpportunityAmount($status, $customer_id, $administrator_id)
    {
        $query = Niche::find()
            ->where(['customer_id' => $customer_id, 'administrator_id' => $administrator_id]);
        if ($status == 2 || $status == 3) {
            $query->andWhere(['status' => $status]);
        } else {
            $query->andWhere(['not in', 'status', [Niche::STATUS_DEAL, Niche::STATUS_FAIL]]);
        }
        return $query->count();
    }

    /**
     * @return int|null|string
     * 已付订单数量
     */
    public function getOrderAlreadyAmount($user_id, $administrator_id)
    {

        $count = Order::find()->alias('o')
            ->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.user_id' => $user_id, 'o.salesman_aid' => $administrator_id])
            ->andWhere(['or', ['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT], ['vo.status' => VirtualOrder::STATUS_UNPAID]])
            ->count();
        return $count;
    }

    /**
     * @return int|null|string
     * 未付订单数量
     */
    public function getOrderUnpaidAmount($user_id, $administrator_id)
    {

        $count = Order::find()
            ->andWhere(['user_id' => $user_id, 'salesman_aid' => $administrator_id])
            ->andWhere(['status' => Order::STATUS_PENDING_PAY])->count();
        return $count;

    }

    /**
     * 合伙人删除
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCooperDelete()
    {
        $administrator_id = Yii::$app->request->post('administrator_id');
        if (empty($administrator_id)) {
            $this->response(self::FAIL, '合伙人不能为空');
        }
        $customer_id = Yii::$app->request->post('customer_id');
        if (empty($customer_id)) {
            $this->response(self::FAIL, '客户不能为空');
        }

        $res = CrmCustomerCombine::find()->createCommand()->delete(CrmCustomerCombine::tableName(), [
            'administrator_id' => $administrator_id,
            'customer_id' => $customer_id,
        ])->execute();

        /** @var CrmCustomerCombine $crm_customer_combine */
        $crm_customer_combine = CrmCustomerCombine::find()->where(['customer_id' => $customer_id])->count();

        if($crm_customer_combine == 1){
            /** @var CrmCustomer $customer */
            $customer = CrmCustomer::find()->where(['id'=>$customer_id])->one();
            /** @var CrmCustomerCombine $combine */
            $combine = CrmCustomerCombine::find()->where(['customer_id' => $customer_id])->one();
            if($customer->administrator_id == $combine -> administrator_id){
                $customer->is_share = 0;
                $customer->save(false);
            }
        }

        if ($res) {
            return $this->response(self::SUCCESS, '删除成功');
        } else {
            return $this->response(self::FAIL, '删除失败');
        }
    }

    //企业名称查询接口
    public function actionCompanyList()
    {
        /**@var $qcc QCC* */
        $company = Yii::$app->request->post('name');

        if ($company == '') {
            return $this->response(self::FAIL, '参数不得为空');
        }
        $qcc = Yii::$app->get('qcc');
        set_time_limit(0);
        $resultData = $qcc->apiECISimpleSearch($company);
        if ($resultData->status == self::SUCCESS) {
            $data =  $resultData->result;
            return $this->response(self::SUCCESS, '公司名称列表',$data);

        }
//        return $this->response(self::FAIL, '查询失败');
    }

    //公司类型查询列表
    public function actionCompanytypeList()
    {
        $list = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('company_type')
            ->orderBy([
                'id' => SORT_DESC,
            ])
            ->all();
        return $this->response(self::SUCCESS, '公司类型列表', $list);
    }

    //查询添加公司的类型接口
    public function actionCompanyDetail()
    {
        /**@var $qcc QCC* */
        $company = Yii::$app->request->post('keyNo');
        $qcc = Yii::$app->get('qcc');
        $resultData = $qcc->apiECISimpleGetDetailsByName($company);
        if ($resultData) {
            $list = json_decode($resultData);

            //是否添加公司类型
            $count = (new \yii\db\Query())
                ->from('company_type')
                ->where(['name' => $list->Result->EconKind])->count();
            if ($count == 0) {
                CompanyType::find()->createCommand()->insert(CompanyType::tableName(), [
                    'name' => $list->Result->EconKind,
                ])->execute();
            }
            return $this->response(self::SUCCESS, '查询成功', $list->Result);
        }
    }

    //客户公海列表接口
    public function actionCustomerPublicList()
    {
        $page = Yii::$app->request->post('page');
        $page_num = Yii::$app->request->post('page_num');

        $page_num =$page_num ? $page_num : 10;
        $count =  CustomerPublic::find()->count('id');
        $page = $page;
        $page_count = ceil($count / $page_num);
        if ($page <= 1 || $page == "") {
            $page = 1;
        } else if ($page >= $page_count) {
            $page = $page_count;
        }
        //计算偏移量
        $limit = ($page - 1) * $page_num;

        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;

        $query = (new \yii\db\Query())
            ->select(['a.*', 'b.name as company_name', 'c.name as department_name'])
            ->from(['a' => CustomerPublic::tableName()])
            ->leftJoin(['b' => 'company'], 'b.id=a.company_id')
            ->leftJoin(['c' => 'crm_department'], 'c.id=a.department_id')
            ->leftJoin(['d' => CustomerDepartmentPublic::tableName()],'a.id=d.customer_public_id')
            ->offset($limit)
            ->limit($page_num)
            ->orderBy([
                'a.id' => SORT_DESC,
            ]);

        if($identity->is_belong_company == 1 || $identity->company_id != ''){
            $query -> andWhere(['d.customer_department_id' => $identity->department_id]);
        }
        $list = $query->all();

        $data = ['total' => $count,'page' =>$page,'data' => $list];
        
        return $this->response(self::SUCCESS, '客户公海列表', $data);
    }

    //客户公海详情接口
    public function actionCustomerPublicDetail()
    {
        $id = Yii::$app->request->post('id');
        if (empty($id)) {
            $this->response(self::FAIL, 'id不能为空');
        }

        /** @var CustomerPublic $list */
        $list = CustomerPublic::find() ->alias('c') -> select('c.*,a.type') -> leftJoin(['a'=>Administrator::tableName()],'c.administrator_id = a.id')->where(['c.id' => $id]) ->asArray() ->one();

        $department_id = CustomerDepartmentPublic::find() -> select('customer_department_id')-> where(['customer_public_id'=>$id]) -> all();
        foreach($department_id as $key => $val){
            $department[] = $val['customer_department_id'];
        }

        if(!empty($department)){
            $list['department_id'] =  implode(',',$department);
        }
        $type_list = Administrator::getTypes();

        $list['type'] = $type_list[$list['type']];

        return $this->response(self::SUCCESS, '客户公海详情', $list);
    }

    // 修改客户公海状态，先临时实现，该控制器中的代码需要完全重构，目前结构非常不合理
    public function actionCustomerPublicStatus()
    {
        $data['id'] = Yii::$app->request->post('id');
        $data['status'] = Yii::$app->request->post('status', 1);
        /** @var CustomerPublic $customerPublic */
        $customerPublic = CustomerPublic::findOne($data['id']);
        if (null == $customerPublic) {
            return $this->response(self::FAIL, '公海不存在');
        }
        $data['status'] = (bool)$data['status'];
        $customerPublic->status = (int)$data['status'];
        $customerPublic->save(false);
        return $this->response(self::SUCCESS, $data['status'] ? '启用成功' : '禁用成功');
    }


    /**
     * 删除客户公海，先临时实现，该控制器中的代码需要完全重构，目前结构非常不合理
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCustomerPublicDelete()
    {
        $data['id'] = Yii::$app->request->post('id');
        $customerPublic = CustomerPublic::findOne($data['id']);
        if (null == $customerPublic) {
            return $this->response(self::FAIL, '客户公海删除失败！');
        }
        if (0 < CrmCustomer::find()->where(['customer_public_id' => $data['id']])->limit(1)->count()) {
            return $this->response(self::FAIL, '公海内还存在客户数据，无法删除！');
        }
        $connection = Yii::$app->db;
        $t = $connection->beginTransaction();
        try {
            CustomerPublic::deleteAll(['id' => $customerPublic->id]);
            CustomerDepartmentPublic::deleteAll(['customer_public_id'=>$customerPublic->id]);
            $rs = true;
            $t->commit();
        }catch (\Exception $e){
           $t->rollBack();
           $rs=  false;
        }
        if (!$rs) {
            return $this->response(self::FAIL, '客户公海删除失败！');
        }
        return $this->response(self::SUCCESS, '客户公海删除成功！');
    }


    /**
     * 客户公海编辑
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCustomerPublicSave()
    {
        $data = Yii::$app->request->post();

//        $data['id'] ='45';
//        $data['name'] = '王三一企业';
//        $data['customer_type'] = 2;
//        $data['move_time'] = 10;
//        $data['release_time'] = 1;
//        $data['opportunity_time'] = 1;
//        $data['protect_number_limit'] = 13;
//        $data['extract_number_limit'] = 13;
//        $data['big_customer'] = 13;
//        $data['big_customer_status'] = 0;
//        $data['department_id'] = '150';

        $model = new CustomerPublic();

        $model->load($data, '');

        $model->attributes;

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $department_id = explode(',', $data['department_id']);

        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;

        /** @var CrmDepartment $department_list */
        $department_list = CrmDepartment::find()->where(['id' => $administrator->department_id])->one();

        /** @var CrmDepartment $company_id */
        $company_id = CrmDepartment::find()->where(['id' => $data['department_id']])->one();

        if ($data['id'] == '') {

            //判断名称是否存在
            $public_name = CustomerPublic::find()->where(['name'=>$data['name']])->one();

            if ($public_name != null) {
                return $this->response(self::FAIL, '客户公海名称已存在');
            }

            $customer_public = $model;
        } else {
            //判断名称是否存在
            $public_name = CustomerPublic::find()->where(['name'=>$data['name']])->andWhere(['<>','id',$data['id']])->one();

            if ($public_name != null) {
                return $this->response(self::FAIL, '客户公海名称已存在');
            }

            /** @var CustomerPublic $customer_public */
            $customer_public = CustomerPublic::find()->where(['id' => $data['id']])->one();

            if(empty($customer_public)){
                $customer_public = new CustomerPublic();
            }
        }

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        $customer_public->updated_at = time();
        $customer_public->name = isset($data['name']) ? $data['name'] : ($customer_public->name ? $customer_public->name : '');
        $customer_public->customer_type = $data['customer_type'] = 0 ? 0:  $data['customer_type'];
        $customer_public->move_time = isset($data['move_time']) ? $data['move_time'] : ($customer_public->move_time ? $customer_public->move_time : 1);
        $customer_public->release_time = isset($data['release_time']) ? $data['release_time'] : ($customer_public->release_time ? $customer_public->release_time : 0);
        $customer_public->opportunity_time = isset($data['opportunity_time']) ? $data['opportunity_time'] : ($customer_public->opportunity_time ? $customer_public->opportunity_time : 0);
        $customer_public->protect_number_limit = isset($data['protect_number_limit']) ? $data['protect_number_limit'] : ($customer_public->protect_number_limit ? $customer_public->protect_number_limit : 0);
        $customer_public->extract_number_limit = isset($data['extract_number_limit']) ? $data['extract_number_limit'] : ($customer_public->extract_number_limit ? $customer_public->extract_number_limit : 0);
        $customer_public->big_customer = isset($data['big_customer']) ? $data['big_customer'] : ($customer_public->big_customer ? $customer_public->big_customer : 0);
        $customer_public->big_customer_status = isset($data['big_customer_status']) ? $data['big_customer_status'] : ($customer_public->big_customer_status ? $customer_public->big_customer_status : 0);
        $customer_public->company_id = $company_id->company_id ? $company_id->company_id : ($customer_public->company_id ? $customer_public->company_id : 0);
        $customer_public->department_id = 0;
        if($data['id'] == ''){
            $customer_public->administrator_id = $administrator->id ? $administrator->id :0;
            $customer_public->administrator_name = $administrator->name ? $administrator->name : '';
            $customer_public->administrator_department = isset($department_list->name) ? $department_list->name :'';
            $customer_public->administrator_title = $administrator->title ? $administrator->title : '';
        }

        try {

            $customer_public->save(false);

            if ($data['id'] == '') {
                $customer_public_id = $connection->getlastInsertID();
            } else {
                $customer_public_id = $data['id'];
                CustomerDepartmentPublic::deleteAll(['customer_public_id' => $customer_public_id]);
            }

            foreach ($department_id as $key => $val) {
                CustomerDepartmentPublic::find()->createCommand()->insert(CustomerDepartmentPublic::tableName(), [
                    'customer_public_id' => $customer_public_id,
                    'customer_department_id' => $val
                ])->execute();

            }
            $transaction->commit();
            $rs = true;
        } catch (\Exception $e) {
            throw $e;
            $transaction->rollBack();
            $rs = false;
        }

        if ($rs == true) {
            return $this->response(self::SUCCESS, '更新成功');
        } else {
            return $this->response(self::FAIL, '更新失败');
        }

    }

    public function primaryKey()
    {
        return 'customer_public_id';//自定义主键
    }

    private function findModel($id)
    {
        $model = CustomerPublic::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到客户公海信息');
        }
        return $model;
    }

    /**
     * 根据公司算出部门 tree
     * @return array
     * 适用范围（组织结构）
     */

    public function actionOrganize()
    {

        $data = [];
        $company_id = Yii::$app->request->post('company_id');
        if (isset($company_id)) {
            $data = CrmDepartment::find()->select(['id', 'name'])->where(['company_id' => $company_id])->asArray()->all();
        } else {
            /** @var Company $company */
            $company = Company::find()->all();
            foreach ($company as $k => $v) {
                $data[] = CrmDepartment::find()->select(['id', 'name'])->where(['company_id' => $v->id])->asArray()->all();
            }
        }
        return $this->resPonse(self::SUCCESS, '查询成功', $data);

    }

    function genTree($a, $pid)
    {
        $tree = array();
        foreach ($a as $v) {
            if ($v['parent_id'] == $pid) {
                $v['subGrade'] = $this->genTree($a, $v['id']);
                if ($v['subGrade'] == null) {
                    unset($v['subGrade']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 户籍地址 tree
     * @return array
     * 适用范围（组织结构）
     */
    public function actionPermanentAddress()
    {
        $name = Yii::$app->request->post('name');
        if (!empty($name)) {
            $province = (new \yii\db\Query())
                ->select(['id', 'name'])
                ->from('district')
                ->where(['like', 'name', $name])
                ->orderBy(['id'=>SORT_ASC])
                ->one();
        } else {
            $province = (new \yii\db\Query())
                ->select(['id', 'name'])
                ->from('province')
                ->orderBy(['id'=>SORT_ASC])
                ->all();
            foreach ($province as $k => $v) {
                $province[$k]['children'] = (new \yii\db\Query())
                    ->select(['id', 'name'])
                    ->from('city')
                    ->where(['province_id' => $v['id']])
                    ->orderBy(['id'=>SORT_ASC])
                    ->all();
                foreach ($province[$k]['children'] as $kk => $vv) {
                    $province[$k]['children'][$kk]['children'] = (new \yii\db\Query())->select(['id', 'name'])
                        ->from('district')
                        ->where(['city_id' => $vv['id']])
                        ->orderBy(['id'=>SORT_ASC])
                        ->all();
                }
            }
        }
        if (empty($province)) {
            return $this->response(self::FAIL, '暂无数据');
        }
        return $this->response(self::SUCCESS, '户籍列表', $province);
    }

    /***
     * 公海的客户跟进列表
     */
    public function actionPublicFollow()
    {
        $list = (new \yii\db\Query())
            ->select(['last_record_creator_id', 'last_record_creator_name'])
            ->from('crm_customer')
            ->distinct(['last_record_creator_name'])
            ->where(['customer_public_id' => 1])
            ->all();
        return $this->response(self::SUCCESS, '公海的客户跟进列表', $list);
    }

    /**
     * 客户下的商标接口
     * */
    public function actionCustomerTrademark()
    {
        $user_id = Yii::$app->request->post('user_id');
        /** @var CrmCustomer $id */
        $id = CrmCustomer::find()->where(['id' => $user_id])->one();
        $list = Trademark::find()
            ->where(['user_id' => $id->user_id])
            ->all();
        /** @var TimestampBehavior $is */
        $is = Yii::$app->get('imageStorage');
        $url = $is->getImageUrl('');
        $data =[
            'image_url' => $url,
            'item' => $list
        ];
        return $this->response(self::SUCCESS, '客户下的商标接口', $data);
    }

    //获取组织机构，所属公司
    public function actionCompanyData()
    {
        $post = Yii::$app->request->post();
        $type = isset($post['type']) ? 'part' : '';

        if($type == 'part') {
            /** @var Administrator $info */
            $info = Yii::$app->user->identity;
            if($info->is_belong_company==0){
                $list = Company::find()->select('id,name')->all();
            }else{
                $list = Company::find()->select('id,name')->where(['id'=>$info->company_id])->all();
            }
        }else{

            $list = Company::find()->select('id,name')->all();
        }
        return $this->response(self::SUCCESS, '所属公司列表', $list);

    }

    //根据所属公司id 获取合伙人
    public function actionGetPartner()
    {
        $company_id = Yii::$app->request->post('company_id');
        if (empty($company_id)) {
            return $this->response(self::FAIL, '参数不得为空', '');
        }
//        $company_id = 1;
        $list = Company::find()->alias('c')
            ->distinct(true)
            ->select('c.id as company_id,a.id AS administrator_id,a.name')
            ->leftJoin(['a' => Administrator::tableName()], 'c.id = a.company_id')
            ->where(['c.id' => $company_id])
            ->andWhere(['a.type' => 5,'a.status'=>1,'a.is_dimission'=>0])
            ->orderBy(['a.name'=>SORT_ASC])
            ->asArray()
            ->all();

        return $this->response(self::SUCCESS, '合伙人列表', $list);

    }

    //登记状态列表
    public function actionGetRegisterStatus()
    {
        $list = [
            ['id' => 1, 'name' => '在业'],
            ['id' => 2, 'name' => '存续'],
            ['id' => 3, 'name' => '注销'],
            ['id' => 4, 'name' => '迁入'],
            ['id' => 5, 'name' => '吊销'],
            ['id' => 6, 'name' => '迁出'],
            ['id' => 7, 'name' => '停业'],
            ['id' => 8, 'name' => '清算']
        ];
        return $this->response(self::SUCCESS, '登记状态列表', $list);

    }


    //客户保护  or 取消保护
    public function actionCustomerProtect()
    {

        $post = Yii::$app->request->post();

//        $post['customer_id'] = '2011';

        $error_model = new CustomerProtectForm();
        $error_model->load($post,'');
        if(!$error_model -> validate()){
            $errors = $error_model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $model = new CrmCustomer();

        $model->load($post, '');

        $model->setScenario('protect');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $customer_id = explode(',', $post['customer_id']);

        foreach ($customer_id as $key => $val) {

            /** @var CrmCustomer $customer_list */
            $customer_list = CrmCustomer::find()->where(['id' => $val])->one();

            /** @var BusinessSubject $business_list */
            $business_list = BusinessSubject::find()->where(['customer_id' => $val])->one();

            //1：验证客户是受收到保护

            if($customer_list->is_protect == CrmCustomer::PROTECT_ACTIVE){
                return $this->response(self::FAIL, '您所选择客户已经保护，不能重复保护！');
            }
            if ($customer_list['is_protect'] == CrmCustomer::PROTECT_DISABLED) {
                $customer_list->is_protect = 1;
                $customer_list->save(false);
                $business_id = isset($business_list->id)? $business_list->id : 0;
                CrmCustomerLog::add('对客户进行保护' , $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            }
        }
        return $this->response(self::SUCCESS, '保护成功');

    }

    //取消保护
    public function actionCustomerCancelProtect()
    {
        $post = Yii::$app->request->post();

//        $post['customer_id'] = '1,2,3';

        $model = new CrmCustomer();

        $model->load($post, '');

        $model->setScenario('protect');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;

        if($administrator->type == 1){
            $administrator_id = [$administrator->id];
        }else{
            $administrator_id = $administrator->getTreeAdministratorId(true,true);
        }
        $customer_id = explode(',', $post['customer_id']);

        foreach ($customer_id as $key => $val) {

            /** @var CrmCustomer $customer_list */
            $customer_list = CrmCustomer::find()->where(['id' => $val])->one();

            /** @var BusinessSubject $business_list */
            $business_list = BusinessSubject::find()-> where(['customer_id' => $val]) ->one();

            if(!in_array($customer_list->administrator_id,$administrator_id)){
                return $this->response(self::FAIL, '您没有取消保护客户的权限！');
            }

            //1：验证客户是否收到保护  2：验证业务员是否有公海信息 3：验证当前业务员的客户最大保护数量
            if($customer_list -> is_protect == CrmCustomer::PROTECT_DISABLED){
                return $this->response(self::FAIL, '所选择客户未进行保护，不能执行取消保护！');
            }
            if ($customer_list['is_protect'] == CrmCustomer::PROTECT_ACTIVE) {

                $customer_list->is_protect = 0;

                $customer_list->save(false);
                $business_id = isset($business_list->id)? $business_list->id : 0;
                CrmCustomerLog::add('对客户取消保护', $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            }
        }
        return $this->response(self::SUCCESS, '取消保护成功');
    }


    //客户验重
    public function actionCustomerRepeat()
    {
        $post = Yii::$app->request->post();

//        $post['company_name'] = '测试深圳市沃特测试技术服务有限公司';
//        $post['business_name'] = '123456184010120033';
//        $post['qq'] = '648043478';
//        $post['wechat'] = 'yusirui211';
//        $post['email'] = 'wuliYsr@163.com';
//        $post['phone'] = '13866225546';
//        $post['tel'] = '010-4444455';
//        $post['customer_id'] = '1520';

        $model = new CrmCustomerApi();

        $model->load($post, '');

        $model->setScenario('repeat');
        
        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rs = $model->getRepeat();

        if (empty($rs)) {
            return $this->response(self::SUCCESS, '无重复');
        }
        return $this->response(self::SUCCESS, '请求成功',$rs);

    }

    //组织结构树状
    public function actionOrganizationTree()
    {
        $array = [];
        $post['customer_type'] = Yii::$app->request->post('customer_type', 0);
        if (!in_array($post['customer_type'], [0, 1, 2])) {
            return $this->response(self::FAIL, '参数有误');
        }

        if($post['customer_type'] == 0){
            $id_in = [0,1,2];
        }else if($post['customer_type'] == 1){
            $id_in = [0,1];
        }else{
            $id_in = [0,2];
        }
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;

        $type_data = CustomerPublic::find()->select('customer_type')->where(['administrator_id' => $user->id])->asArray()->all();
        $type_data = array_column($type_data, 'customer_type');
        $company = $user->company;
        if (isset($company)) {
            $item = [
                'id' => $company->id,
                'label' => $company->name,
            ];
            $query = new Query();
            $data = $data = $query->distinct(true)
                ->select('d.id,d.name as label,d.parent_id')
                ->from(['d' => CrmDepartment::tableName()])
                ->where(['d.company_id' => $company->id])
                ->all();

            $d_data = CustomerPublic::find()->alias('c')
                ->select('c.customer_type,p.customer_department_id as clue_public_id')
                -> leftJoin(['p' => CustomerDepartmentPublic::tableName()],'c.id=p.customer_public_id')
                -> where(['c.company_id'=>$company->id])
                -> andWhere(['in','c.customer_type',$id_in])
                -> asArray()
                -> all();
            $item['children'] = $this->genTree2($data, 0, $type_data, $post['customer_type'],$d_data);

            $array[] = $item;
        } else {
            /** @var Company $company */
            $company = Company::find()->select('id,name')->all();
            foreach ($company as $k => $v) {
                $item = [
                    'id' => $v->id,
                    'label' => $v->name,
                ];

                $query = new Query();
                $data = $query->distinct(true)->select('d.id,d.name as label,d.parent_id')
                    ->from(['d' => CrmDepartment::tableName()])
                    ->where(['d.company_id' => $v->id])
                    ->all();
                $d_data = CustomerPublic::find()->alias('c')
                    ->select('c.customer_type,p.customer_department_id as clue_public_id')
                    -> leftJoin(['p' => CustomerDepartmentPublic::tableName()],'c.id=p.customer_public_id')
                    -> andWhere(['in','c.customer_type',$id_in])
                    -> asArray()
                    -> all();
                $item['children'] = $this->genTree2($data, 0, $type_data, $post['customer_type'],$d_data);
                $array[] = $item;
            }
        }
        return $this->resPonse(self::SUCCESS, '查询成功', $array);
    }

    function genTree2($a, $pid, $type_data, $customer_type,$d_data)
    {
        $rs = [];
        foreach($d_data as $key => $val){
            $rs[$val['clue_public_id']] = $val['customer_type'];
        }
        $tree = array();
        foreach ($a as $k => $v) {
            //true不能选 false可选
            //传全部 如果该部门被个人或者企业使用了，只置灰选中的部门 其他的还是可以选的 根据部门判断是否有个人或者企业的公海  如果有不可以新增 没有可以新增
            $id = array_keys($rs);
            if(in_array($v['id'],$id)){
                $v['disabled'] = true;
                $v['customer_type'] = $rs[$v['id']];
            }else{
                $v['disabled'] = false;
                $v['customer_type'] = isset($rs[$v['id']]) ? $rs[$v['id']] : 0;
            }

            if ($v['parent_id'] == $pid) {
                $v['children'] = $this->genTree2($a, $v['id'], $type_data, $customer_type,$d_data);
                if ($v['children'] == null) {
                    unset($v['children']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }


    //标签列表
    public function actionList()
    {
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $post_arr['company_id'] = $user->company_id;
        $obj =  new Tag();
        $obj->load($post_arr,'');
        $request = $obj->getList ();

        return $this->resPonse(self::SUCCESS,'查询成功',$request);
    }


    //客户来源列表
    public function actionSourceList()
    {
        /** @var Source $data */
        $data = Source::find()->where(['status' => 1])->orderBy(['sort'=>SORT_ASC])->asArray()->all();
        return $this->resPonse(self::SUCCESS,'查询成功',$data);
    }

}