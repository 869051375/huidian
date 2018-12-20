<?php

namespace backend\controllers;

use backend\models\CrmCustomerCombineShareForm;
use backend\models\CustomerConfirmReceiveNewForm;
use backend\models\CustomerReleaseNewForm;
use backend\models\CustomerTagNewForm;
use backend\models\TagForm;
use backend\modules\niche\models\Contract;
use backend\modules\niche\models\CustomerExchangeList;
use common\models\CrmOpportunity;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerFollowRecord;
use backend\models\OperateCustomerRecord;
use backend\models\Pagenation;
use backend\models\CustomerReleaseForm;
use common\models\BusinessSubject;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CustomerCustomField;
use common\models\Administrator;
use common\models\CustomerPublic;
use common\models\MessageRemind;
use common\models\Niche;
use common\models\Order;
use common\models\VirtualOrder;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use common\models\Tag;
use common\models\CustomerTag;
use backend\models\CrmCustomerForm;
use backend\models\CustomerOperateRecord;
use yii\helpers\ArrayHelper;

class CompanySearchController extends ApiController
{
    public function behaviors()
    {
        $behaviors = [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['tax-level'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/tax-level'],
                    ],
                    [
                        'actions' => ['industry-level'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/industry-level'],
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
                        'actions' => ['industry-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/industry-list'],
                    ],

                    [
                        'actions' => ['customer-leading'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/customer-leading'],
                    ],
                    [
                        'actions' => ['customer-remove'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/customer-remove'],
                    ],
                    [
                        'actions' => ['customer-share'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/customer-share'],
                    ],
                    [
                        'actions' => ['tag-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/tag-list'],
                    ],
                    [
                        'actions' => ['add-customertag'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/add-customertag'],
                    ],
                    [
                        'actions' => ['delete-customertag'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/delete-customertag'],
                    ],
                    [
                        'actions' => ['add-follow-record'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/add-follow-record'],
                    ],
                    [
                        'actions' => ['follow-record-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/follow-record-list'],
                    ],
                    [
                        'actions' => ['opportunity-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/opportunity-list'],
                    ],
                    [
                        'actions' => ['order-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/order-list'],
                    ],
                    [
                        'actions' => ['operate-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/operate-list'],
                    ],

                    [
                        'actions' => ['persion-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/persion-list'],
                    ],
                    [
                        'actions' => ['persion-save'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/persion-save'],
                    ],
                    [
                        'actions' => ['business-save'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/business-save'],
                    ],
                    [
                        'actions' => ['business-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/business-save'],
                    ],
                    [
                        'actions' => ['abandon-customer'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/abandon-customer'],
                    ],
                    [
                        'actions' => ['delete-customer-tag'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/delete-customer-tag'],
                    ],
                    [
                        'actions' => ['add-customer-tag'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/add-customer-tag'],
                    ],
                    [
                        'actions' => ['opportunity-abandon'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/opportunity-abandon'],
                    ],
                    [
                        'actions' => ['follow-list'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/follow-list'],
                    ],
                    [
                        'actions' => ['add-tag'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/add-tag'],
                    ],
                    [
                        'actions' => ['update-tag'],
                        'allow' => true,
                        'roles' => ['@'],
//                        'roles' => ['company-search/update-tag'],
                    ],
                ],
            ],
        ];
        return ArrayHelper::merge(parent::behaviors(), $behaviors);
    }

    //纳税性质接口
    public function actionTaxLevel()
    {
        $list = [
            ['tax_id' => 1, 'name' => '一般人',],
            ['tax_id' => 2, 'name' => '小规模纳税人',],

        ];
        return $this->response(200, '纳税人列表', $list);
    }

    //行业类型列表
    public function actionIndustryLevel()
    {
        $rows = (new \yii\db\Query())
            ->select(['id as industry_id', 'name'])
            ->from('industry')
            ->orderBy([
                'sort' => SORT_DESC,
            ])
            ->all();

        return $this->response(200, '客户来源列表展示成功', $rows);


    }

    //工商局信息接口
    public function actionIndustryList()
    {
        $rows = (new \yii\db\Query())
            ->select(['id as industry_info_id', 'name'])
            ->from('industry_info')
            ->all();

        return $this->response(200, '工商局信息展示成功', $rows);

    }


    //负责人接口
    public function actionCustomerLeading()
    {

        $list = Administrator::find()->select(['id', 'name'])->where(['type' => 5,'status'=>1,'is_dimission'=>0])->orderBy(['name' => SORT_ASC])->all();

        return $this->response(self::SUCCESS, '返回成功', $list);

    }

    /**
     * 客户转移
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCustomerRemove()
    {
        $post = Yii::$app->request->post();

//        $post['crm_customer_id'] = '1515,1516';
//        $post['administrator_id'] = 252;

        $model = new CustomerConfirmReceiveNewForm();

        $model->load($post, '');

        if(!$model->validate()){
            $errors=$model->getFirstErrors();
            return $this->response(self::FAIL,reset($errors));
        }

        $administrator_list = (new \yii\db\Query())
            ->from('administrator')
            ->where(['id' => $post['administrator_id']])
            ->one();
        $company_id = $administrator_list['company_id'];
        $department_id = $administrator_list['department_id'];

        if(!$administrator_list){
            return $this->response(self::FAIL, '员工不存在');
        }
        if ($administrator_list['status'] == 0) {
            return $this->response(self::FAIL, '员工被禁用');
        }
        if ($administrator_list['is_dimission'] == 1) {
            return $this->response(self::FAIL, '员工已经离职');
        }
        if ($administrator_list['type'] != 5) {
            return $this->response(self::FAIL, '员工不是业务员');
        }
        $update_time = time();

        $customer_id = explode(',', $post['crm_customer_id']);

        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try{
            foreach ($customer_id as $key => $val) {
                /** @var CrmCustomer $customer */
                $customer = CrmCustomer::find() -> where(['id' => $val]) -> one();

                $admin_front = $customer->administrator_id;

                //转移客户时删除当前负责人信息
                CrmCustomerCombine::find()->createCommand()->delete(CrmCustomerCombine::tableName(), [
                    'customer_id' => $val,
                    'administrator_id' => $customer->administrator_id
                ])->execute();

                $crm_customer_combine = CrmCustomerCombine::find()->where(['customer_id'=>$val])->count();
                if(!empty($customer)){
                   $customer -> administrator_id = $post['administrator_id'];
                   $customer -> administrator_name = $administrator_list['name'];
                   $customer -> company_id = $company_id;
                   $customer -> department_id = $department_id;
                   $customer -> updated_at = $update_time;
                   $customer -> updater_id = $administrator->getId();
                   $customer -> updater_name = $administrator->name;
                   $customer -> is_receive = 1;
                   $customer -> transfer_time = time();
                   if($crm_customer_combine == 1){
                       $customer -> is_share = 0;
                   }
                   $customer -> save(false);
                }
                /** @var BusinessSubject $business */
                $business = BusinessSubject::find()->where(['customer_id' => $val])->one();

                /** @var CrmCustomerCombine $c */
                $c = CrmCustomerCombine::find()->where(['customer_id' => $val,
                    'administrator_id' => $post['administrator_id']])->one();
                if (null == $c) {
                    $c = new CrmCustomerCombine();
                    $c->level = CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE;
                    $c->business_subject_id = isset($business->id) ? $business->id : 0;
                    $c->administrator_id = $post['administrator_id'] ;
                    $c->customer_id = $val;
                    $c->company_id = $company_id;
                    $c->department_id = $department_id;
                    $c->user_id = $customer->user_id;
                    $c->status = CrmCustomerCombine::STATUS_RECEIVED;
                    $c->created_at = time();
                    $c->save(false);
                }
                $business_id = isset($business->id)? $business->id : 0;
                CrmCustomerLog::add('转移客户给'.$administrator_list['name'] , $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

                $customer_model = new CustomerExchangeList();
                /** @var CrmContacts $contact */
                $contact = CrmContacts::find()->where(['customer_id'=>$val])->one();
                $province_id =isset($business->province_id) ? $business->province_id : (isset($contact->province_id) ? $contact->province_id : 0);
                $city_id = isset($business->city_id) ? $business-> city_id : (isset($contact->city_id) ? $contact->city_id : 0);
                $district_id = isset($business->district_id) ? $business->district_id : (isset($contact->district_id) ? $contact->district_id : 0);

                $data = [
                    'id' => $val,
                    'from' => $admin_front,
                    'administrator_id' => $post['administrator_id'],
                    'province_id' => $province_id,
                    'city_id' => $city_id,
                    'district_id' => $district_id,
                    'source_id' => $contact->source ,
                    'channel_id' => $contact->channel_id
                ];
                $customer_model -> customer($data,'change');
                //添加消息提醒
                /** @var Administrator $administrator */
                $administrator = Yii::$app->user->identity;
                $type = MessageRemind::TYPE_COMMON;
                $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
                $receive_id = $post['administrator_id'];
                $customer_id = $customer->id;
                $sign = 'd-'.$receive_id.'-'.$customer->id.'-'.$type.'-'.$type_url;
                $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                if($customer->id && null == $messageRemind)
                {
                    $business_name = isset($business->company_name) ? $business->company_name : $customer->name;
                    $administrator_name = isset($administrator->name) ? $administrator->name : '';
                    $message = '你有一个新转移过来的客户“'. $business_name .'”，请及时查看跟进！';
                    $popup_message = '"'.$administrator_name.'"给您转移一个新客户，请及时查看哦！';
                    MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id,  0,  0, $administrator);
                }
            }
            $transaction -> commit();
            $res = true;
        }catch (\Exception $e){
            $transaction -> rollBack();
            throw $e;
            $res = false;
        }

        if ($res) {
            return $this->response(self::SUCCESS, '更新成功');
        } else {
            return $this->response(self::FAIL, '更新失败');

        }
    }

    /**
     * 客户分享
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionCustomerShare()
    {
        $post = Yii::$app->request->post();

//        $post['customer_id'] = '1847';
//        $post['administrator_id'] = 733;
//        $post['company_id'] = 18;

        $model = new CrmCustomerCombineShareForm();

        $model->load($post, '');

        $model->setScenario('share');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $rows = (new \yii\db\Query())
            ->from('administrator')
            ->where(['id' => $post['administrator_id']])
            ->one();
        if ($rows['status'] == 0) {
            $this->response(self::FAIL, '员工被禁用');
        }
        if ($rows['is_dimission'] == 1) {
            $this->response(self::FAIL, '员工已经离职');
        }
        if ($rows['type'] != 5) {
            $this->response(self::FAIL, '员工不是业务员');
        }

        $customer_id = explode(',', $post['customer_id']);

        $transaction = Yii::$app->db->beginTransaction();

        try{
            foreach ($customer_id as $key => $val) {
                $share = (new \yii\db\Query())
                    ->from('crm_customer_combine')
                    ->where(['administrator_id' => $post['administrator_id'], 'customer_id' => $val])
                    ->one();
                if (empty($share)) {
                    $depart = (new \yii\db\Query())->select(['department_id'])->from('administrator')->where(['id' => $post['administrator_id']])->orderBy(['id' => SORT_DESC,])->one()['department_id'];
                   /** @var BusinessSubject $business_list */
                    $business_list = BusinessSubject::find()->select('id,company_name')->where(['customer_id' => $val])->asArray()->one();
                    /** @var CrmCustomer $customer_list */
                    $customer_list = CrmCustomer::find()->select('id,name,user_id,level,is_receive,is_share')->where(['id' => $val])->one();

                    CrmCustomer::find()->createCommand()->update('crm_customer',[
                        'is_share' => 1
                    ],'id='.$val) -> execute();

                    /** @var CrmCustomerCombine $c */
                    $c = CrmCustomerCombine::find()->where(['customer_id' => $val,
                        'administrator_id' => $post['administrator_id']])->one();

                    if($c == null){
                         CrmCustomerCombine::find()->createCommand()->insert('crm_customer_combine', [
                            'administrator_id' => $post['administrator_id'],
                            'company_id' => $post['company_id'],
                            'customer_id' => $val,
                            'business_subject_id' => isset($business_list['id']) ? $business_list['id'] : 0,
                            'user_id' => $customer_list->user_id ? $customer_list->user_id : 0,
                            'level' => $customer_list->level ? $customer_list->level : 0,
                            'status' => $customer_list->is_receive ? $customer_list->is_receive : 0,
                            'department_id' => $depart ? $depart :0,
                            'created_at' => time(),
                        ])->execute();
                    }
                    $business_id = isset($business_list->id)? $business_list->id : 0;
                    CrmCustomerLog::add('分享给'.$rows['name'], $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

                    $customer_model = new CustomerExchangeList();
                    /** @var CrmContacts $contact */
                    $contact = CrmContacts::find()->where(['customer_id'=>$val])->one();
                    $province_id =isset($business_list->province_id) ? $business_list->province_id : (isset($contact->province_id) ? $contact->province_id : 0);
                    $city_id = isset($business_list->city_id) ? $business_list-> city_id : (isset($contact->city_id) ? $contact->city_id : 0);
                    $district_id = isset($business_list->district_id) ? $business_list->district_id : (isset($contact->district_id) ? $contact->district_id : 0);
                    $data = [
                        'id' => $val,
                        'administrator_id' => $post['administrator_id'],
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'source_id' => $contact->source ,
                        'channel_id' => $contact->channel_id
                    ];
                    $customer_model -> customer($data);

                    //添加消息提醒
                    /** @var Administrator $administrator */
                    $administrator = Yii::$app->user->identity;
                    $type = MessageRemind::TYPE_COMMON;
                    $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
                    $receive_id = $post['administrator_id'];
                    $customer_id = $customer_list->id;
                    $sign = 'd-'.$receive_id.'-'.$val.'-'.$type.'-'.$type_url;
                    $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                    if($customer_list->id && null == $messageRemind)
                    {
                        $business_name = isset($business_list->company_name) ? $business_list->company_name : $customer_list->name;
                        $administrator_name = isset($administrator->name) ? $administrator->name:'';
                        $message = '你有一个新分享客户“'. $business_name .'”，请及时查看跟进！';
                        $popup_message = '"'.$administrator_name.'"给您分享了一个新客户，请及时查看哦！';
                        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id,  0,  0, $administrator);
                    }
                }
            }
            $transaction -> commit();
            $res = true;
        }catch (\Exception $e){
            $transaction -> rollBack();
            $res = false;

        }

        if ($res) {
            return $this->response(self::SUCCESS, '分享成功');
        } else {
            return $this->response(self::FAIL, '分享失败');
        }
    }

    /**
     * 设置新增标签
     * @return array
     */
    public function actionAddTag(){
        $post = Yii::$app->request->post();
//        $post['name'] = '新增标签2';
//        $post['color'] = '9268ff';
        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;
        $post['company_id'] = $identity->company_id;
        $model = new Tag();
        $model->load($post,'');
        $model->setScenario('add');
        if(!$model->validate()){
            $errors = $model->getFirstErrors();
            return  $this->response(self::FAIL,reset($errors));
        }
        if( $model->inserts()){
            return $this->response(self::SUCCESS,'新增成功');
        }else{
            return $this->response(self::FAIL,'新增失败');
        }

    }

    /**
     * 设置修改标签
     * @return array
     */
    public function actionUpdateTag(){
        $post = Yii::$app->request->post();
//        $post['param'] = [['id'=>'24','full_name'=>'修改标签1'],['id'=>'25','full_name'=>'修改标签2'],['id'=>'26','full_name'=>'修改标签3'],['id'=>'37','full_name'=>'修改标签4'],['id'=>'38','full_name'=>'修改标签5']];
        if(!is_array($post['param'])){
            return $this->response(self::FAIL,'参数错误');
        }
        /** @var Administrator $identity */
        $identity = Yii::$app->user->identity;
        $post['company_id'] = $identity->company_id;
        $model = new TagForm();
        $model->full_names = $post['param'];
        $model->load($post,'');
        $errors = $model->validateCompany($post);

        if($errors != null){
            return  $this->response(self::FAIL,$errors);
        }

        if( $model->update()){
            return $this->response(self::SUCCESS,'修改成功');
        }else{
            return $this->response(self::FAIL,'修改失败');
        }

    }

    /**
     * 客户标签列表
     * @param int $type
     * @param null $keyword
     * @return array
     */
    public function actionTagList($type = 0, $keyword = null)
    {
        $query = Tag::find()->select(['id', 'name', 'color'])->where(['type' => $type]);
        if (!empty($keyword)) {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();

        return $this->response(self::SUCCESS, '标签列表', $data);

    }

    /**
     * 添加客户标签
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionAddCustomerTag()
    {
        $post = Yii::$app->request->post();

//        $post['tag_id'] = 1;
//        $post['customer_id'] = '1,2,3';

        $model = new CustomerTagNewForm();

        $model->load($post, '');

        $model->setScenario('add');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $customer_id = explode(',', $post['customer_id']);

        $t = Yii::$app->db->beginTransaction();

        try{
            foreach ($customer_id as $key => $val) {
                /** @var CustomerTag $customer */
                $customer = CustomerTag::find() -> where(['customer_id' => $val]) -> one();
                /** @var BusinessSubject $business */
                $business = BusinessSubject::find()->where(['customer_id' => $val])->one();

                if(empty($customer)){
                    $customer = new CustomerTag();
                }

                $customer -> tag_id = $post['tag_id'];
                $customer -> customer_id = $val;
                $customer -> save(false);

                $business_id = isset($business->id)? $business->id : 0;
                CrmCustomerLog::add('设置客户标签', $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

            }
            $t->commit();
            $rs = true;
        }catch (\Exception $e){
            $t->rollBack();
            $rs = false;
        }

        if ($rs) {
            return $this->response(self::SUCCESS, '添加成功');
        } else {
            return $this->response(self::FAIL, '添加失败');
        }

    }


    /**
     * 清除客户标签
     * @return array
     * @throws \Exception
     */
    public function actionDeleteCustomerTag()
    {
        $post = Yii::$app->request->post();
//        $post['customer_id'] = '1,2,3';

        $model = New CustomerTagNewForm();

        $model -> load($post,'');

        $model -> setScenario('cancel');

        if(!$model->validate()){
            $errors = $model -> getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }
        $customer_id = explode(',', $post['customer_id']);

        $customer_list = CustomerTag::find()->where(['in','customer_id',$customer_id])->all();
        if(empty($customer_list)){
            return $this->response(self::SUCCESS, '删除成功');
        }

        $t = Yii::$app->db->beginTransaction();
        try{
            $res = CustomerTag::deleteAll(['customer_id' => $customer_id]);
            foreach ($customer_id as $key => $val){
                /** @var BusinessSubject $business */
                $business = BusinessSubject::find()->where(['customer_id'=>$val])->one();
                $business_id = isset($business->id)? $business->id : 0;
                CrmCustomerLog::add('清除客户标签', $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);
            }
            $t->commit();
        }catch (\Exception $e){
            throw $e;
            $t->rollBack();
        }



        if ($res) {
            return $this->response(self::SUCCESS, '删除成功');
        } else {
            return $this->response(self::FAIL, '删除失败');
        }
    }

    /**
     * 添加跟进记录
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionAddFollowRecord()
    {
        $post = Yii::$app->request->post();

//        $post['follow_mode_id'] = 1;
//        $post['follow_mode'] = '打电话';
//        $post['remark'] = '22222';
//        $post['customer_id'] = 7;
//        $post['business_subject_id'] = 1;
//        $post['next_follow_time'] = '2018-11-14 12:22:22';
//        $post['follow_end_time'] = '2018-11-12 12:22:22';
//        $post['follow_start_time'] = '2018-11-12 12:22:22';

        $model = new CustomerFollowRecord();

        $model->load($post, '');

        $model->setScenario('add');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        /** @var BusinessSubject $business_list */
        $business_list = BusinessSubject::find()->where(['customer_id' => $post['customer_id']])->one();

        /** @var CrmCustomer $customer_list */
        $customer_list = CrmCustomer::find()->where(['id' => $post['customer_id']])-> one();

        if($customer_list == null){
            return $this->response(self::FAIL, '当前客户不存在');
        }

        $transaction = Yii::$app->db->beginTransaction();

        $time = time();
        try{
            /** @var Administrator $info */
            $info = Yii::$app->user->identity;
            $model->subject_id = isset($business_list -> id) ? $business_list -> id : 0;
            $model->customer_id = $post['customer_id'];
            $model->remark = $post['remark'];
            $model->creator_id = $info->id;
            $model->creator_name = $info->name;
            $model->created_at = $time;
            $model->follow_end_time = isset($post['follow_end_time']) ? strtotime($post['follow_end_time']) : 0;
            $model->follow_start_time = isset($post['follow_start_time']) ? strtotime($post['follow_start_time']) : 0;
            $model->next_follow_time = isset($post['next_follow_time']) ? strtotime($post['next_follow_time']) :0;
            $model->follow_mode_id = $post['follow_mode_id'];
            $model->follow_mode = $post['follow_mode'];
            $model->save(false);

            $customer_list ->operation_time = $time;
            $customer_list ->last_operation_creator_id = $info->id;
            $customer_list ->last_operation_creator_name = $info->name;
            $customer_list ->last_record = $time;
            $customer_list ->last_record_creator_id = $info->id;
            $customer_list ->last_record_creator_name = $info->name;
            $customer_list ->next_record = isset($post['next_follow_time']) ? strtotime($post['next_follow_time']) :0;
            $customer_list -> save(false);

            CrmCustomerLog::add('发布跟进记录',  $post['customer_id'], 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $post['business_subject_id'] );

            $transaction -> commit();

            $add = true;
        }catch (\Exception $e){
            $transaction->rollBack();
            throw $e;
            $add = false;
        }

        if ($add) {
            return $this->response(self::SUCCESS, '添加成功');
        } else {
            return $this->response(self::FAIL, '添加失败');
        }

    }

    //跟进记录列表
    public function actionFollowRecordList()
    {
        $post = Yii::$app->request->post();

        $model = new CustomerFollowRecord();

        $model->load($post, '');

        $model->setScenario('list');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }
        $rows = CustomerFollowRecord::find()->where(['customer_id' => $post['customer_id']])->orderBy(['id'=>SORT_DESC])->all();

        return $this->response(self::SUCCESS, '跟进记录列表', $rows);

    }

    //跟进方式列表
    public function actionFollowList()
    {
        $data = [
            ['id' => 1, 'name' => '打电话'],
            ['id' => 2, 'name' => '见面拜访'],
            ['id' => 3, 'name' => '发邮件'],
            ['id' => 4, 'name' => '发短信'],
            ['id' => 5, 'name' => '其他'],
        ];
        return $this->response(self::SUCCESS, '跟进方式列表', $data);
    }

    //客户商机列表
    public function actionOpportunityList()
    {
        $page = Yii::$app->request->post('page' );
        $customer_id = Yii::$app->request->post('customer_id');
        $status = Yii::$app->request->post('status');

        $query = Niche::find()
            ->select(['id', 'name', 'creator_name', 'administrator_name as last_record_creator_name', 'created_at', 'total_amount', 'predict_deal_time', 'progress', 'remark'])
            ->where(['customer_id' => $customer_id]);
        if ($status == 1) {
            $query->andWhere(['in','status',[0,1,3]]);
        }
        if ($status == 2) {
            $query->andWhere(['=', 'status', 2]);
        }
        $count = $query ->count();
        $page_count = ceil($count / 6);
        if ($page <= 1 || $page == "") {
            $page = 1;
        } else if ($page >= $page_count) {
            $page = $page_count;
        }
        //计算偏移量
        $limit = ($page - 1) * 6;

        $rs = $query -> offset($limit)->limit(6)->all();

        $data = [
            'data' => $rs,
            'page' => $page,
            'total' => $count
        ];
        return $this->response(self::SUCCESS, '商机列表', $data);

    }

    //主体订单列表
    public function actionOrderList()
    {
        $page = Yii::$app->request->post('page');
        $page_size = Yii::$app->request->post('page_num');
        $user_id = Yii::$app->request->post('user_id');
        $status = Yii::$app->request->post('status',8);//代付款0  已付款8 服务终止4

        if($user_id == ''){
            return $this->response(self::FAIL,'参数不得为空');
        }

        $where = [];
        if($status == 0){
            $where = ['o.status'=>0];
        }else if($status == 8){
            $where = ['in','vo.status',[1,2]];
        }else if($status == 4){
            $where = ['o.status'=>4];
        }
        /** @var CrmCustomer $customer_id */
        $customer_id =  CrmCustomer::find()->where(['id'=>$user_id])->one();  //客服customer_service_name  //服务clerk_name
        $query = Order::find()->alias('o')
            ->select(['o.id','o.sn', 'o.product_name', 'o.salesman_name', 'o.customer_service_name as supervisor_name', 'o.clerk_name', 'o.status', 'o.is_invoice', 'o.price'])
            ->leftJoin(['vo' => VirtualOrder::tableName()],'o.virtual_order_id=vo.id')
            ->where(['o.user_id' => $customer_id->user_id])
            ->andWhere($where);

        $count = $query ->count();
        $page_count = ceil($count / 6);
        if ($page <= 1 || $page == "") {
            $page = 1;
        } else if ($page >= $page_count) {
            $page = $page_count;
        }
        //计算偏移量
        $limit = ($page - 1) * 6;

        $rs = $query -> offset($limit)->limit(6)->all();

        $data = [
            'data' => $rs,
            'page' => $page,
            'total' => $count
        ];
        return $this->response(self::SUCCESS, '订单列表', $data);

    }

    //操作记录表
    public function actionOperateList()
    {
        $customer_id = Yii::$app->request->post('customer_id');
        if (empty($customer_id)) {
            return $this->response(self::FAIL, '客户id不能为空');
        }
        $rows = (new \yii\db\Query())
            ->from(['crm_customer_log'])
            ->where(['customer_id' => $customer_id, 'type' => 3])
            ->orderBy(['id'=>SORT_DESC])
            ->all();

        return $this->response(self::SUCCESS, '操作记录表', $rows);

    }

    //个人自定义列表展示字段
    public function actionPersionList()
    {
        $administrator_id = Yii::$app->request->post('administrator_id', 94);
        $rows = (new \yii\db\Query())
            ->select(['fields'])
            ->from(['customer_custom_field'])
            ->where(['administrator_id' => $administrator_id, 'from' => 1])
            ->one()['fields'];
        $rows = json_decode($rows);

        if ($rows == null) {
            $rows = [
                ['name' => 'customer_name', 'show' => 1],
                ['name' => 'tel', 'show' => 1],
                ['name' => 'administrator_name', 'show' => 0],
                ['name' => 'last_record_creator_name', 'show' => 0],
                ['name' => 'last_record', 'show' => 0],
                ['name' => 'created_at', 'show' => 0],
                ['name' => 'source', 'show' => 0],
                ['name' => 'channel_name', 'show' => 0],
                ['name' => 'done_opportunity', 'show' => 0],
                ['name' => 'follow_up_opportunity', 'show' => 0],
                ['name' => 'ask_for_opportunity', 'show' => 0],
                ['name' => 'fail_opportunity', 'show' => 0],
                ['name' => 'conform_opportunity', 'show' => 0],
                ['name' => 'extracted_opportunity', 'show' => 0],
                ['name' => 'already_paid', 'show' => 0],
                ['name' => 'non_payment', 'show' => 0],
                ['name' => 'customer_number', 'show' => 0],
                ['name' => 'get_way', 'show' => 0],
            ];
        }

        if ($rows) {
            return $this->response(self::SUCCESS, '个人自定义列表', $rows);
        } else {
            return $this->response(self::FAIL, '查询失败');
        }
    }

    //个人自定义列表展示字段
    public function actionBusinessList()
    {
        $administrator_id = Yii::$app->request->post('administrator_id', 94);
        $rows = (new \yii\db\Query())
            ->select(['fields'])
            ->from(['customer_custom_field'])
            ->where(['administrator_id' => $administrator_id, 'from' => 2])
            ->one()['fields'];
        $rows = json_decode($rows);

        if ($rows == null) {
            $rows = [
                ['name' => 'customer_name', 'show' => 1],
                ['name' => 'tel', 'show' => 1],
                ['name' => 'administrator_name', 'show' => 0],
                ['name' => 'last_record_creator_name', 'show' => 0],
                ['name' => 'last_record', 'show' => 0],
                ['name' => 'created_at', 'show' => 0],
                ['name' => 'source', 'show' => 0],
                ['name' => 'channel_name', 'show' => 0],
                ['name' => 'done_opportunity', 'show' => 0],
                ['name' => 'follow_up_opportunity', 'show' => 0],
                ['name' => 'ask_for_opportunity', 'show' => 0],
                ['name' => 'fail_opportunity', 'show' => 0],
                ['name' => 'conform_opportunity', 'show' => 0],
                ['name' => 'extracted_opportunity', 'show' => 0],
                ['name' => 'already_paid', 'show' => 0],
                ['name' => 'non_payment', 'show' => 0],
                ['name' => 'customer_number', 'show' => 0],
                ['name' => 'get_way', 'show' => 0],
            ];
        }
        if ($rows) {
            return $this->response(self::SUCCESS, '企业自定义列表', $rows);
        } else {
            return $this->response(self::FAIL, '查询失败');
        }
    }

    //个人自定义列表编辑
    public function actionPersionSave()
    {
        $administrator_id = Yii::$app->request->post('administrator_id', 94);
        $fields = Yii::$app->request->post('fields', 'dwdd');
        $rows = (new \yii\db\Query())
            ->from(['customer_custom_field'])
            ->where(['administrator_id' => $administrator_id, 'from' => 1])
            ->count();
        if ($rows == 0) {
            $person = CustomerCustomField::find()->createCommand()->insert(CustomerCustomField::tableName(), [
                'administrator_id' => $administrator_id,
                'fields' => $fields,
                'from' => 1,
            ])->execute();
        } else {
            $person = CustomerCustomField::find()->createCommand()->update(CustomerCustomField::tableName(), [
                'administrator_id' => $administrator_id,
                'fields' => $fields,
                'from' => 1,
            ], ['from' => 1, 'administrator_id' => $administrator_id])->execute();
        }

        if ($person) {
            return $this->response(self::SUCCESS, '编辑成功');
        } else {
            return $this->response(self::FAIL, '编辑失败');
        }

    }

    //企业自定义列表编辑
    public function actionBusinessSave()
    {
        $administrator_id = Yii::$app->request->post('administrator_id', 94);
        $fields = Yii::$app->request->post('fields', 'dwdd');
        $rows = (new \yii\db\Query())
            ->from(['customer_custom_field'])
            ->where(['administrator_id' => $administrator_id, 'from' => 2])
            ->count();
        if ($rows == 0) {
            $business_person = CustomerCustomField::find()->createCommand()->insert(CustomerCustomField::tableName(), [
                'administrator_id' => $administrator_id,
                'fields' => $fields,
                'from' => 2,
            ])->execute();
        } else {
            $business_person = CustomerCustomField::find()->createCommand()->update(CustomerCustomField::tableName(), [
                'administrator_id' => $administrator_id,
                'fields' => $fields,
                'from' => 2,
            ], ['from' => 2, 'administrator_id' => $administrator_id])->execute();
        }

        if ($business_person) {
            return $this->response(self::SUCCESS, '编辑成功');
        } else {
            return $this->response(self::FAIL, '编辑失败');
        }
    }

    //放弃客户
    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionAbandonCustomer()
    {
        $post = Yii::$app->request->post();

//        $post['customer_id'] = '1871';
//        $post['abandon_reason'] = '就不想要了lalalalalal';

        $model = new CustomerReleaseNewForm();

        $model -> load($post,'');

        if(!$model->validate()){
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL,reset($errors));
        }

        $customer_id = explode(',',$post['customer_id']);
        /** @var Administrator $info */
        $info = Yii::$app->user->identity;

        $time = time();

        $error = 0;
        $success = 0;

        $connection = Yii::$app->db;

        $transaction = $connection->beginTransaction();

        try {

            foreach ($customer_id as $key => $val) {

                /** @var CrmCustomer $customer_list */
                $customer_list = CrmCustomer::find()->where(['id' => $val])->one();

                /** @var BusinessSubject $business_list */
                $business_list = BusinessSubject::find()->where(['customer_id' => $val])->one();

                $department_id = $customer_list['department_id'] ? $customer_list['department_id'] : $info->department_id;
                //根据客户表的department_id 取出 公海关联部门表中的id;
                $customer_public_id = CustomerDepartmentPublic::find()->select('customer_public_id')->where(['customer_department_id' => $department_id])->one();

                CrmCustomerCombine::find()->createCommand()->delete(CrmCustomerCombine::tableName(), ['and',['customer_id' => $val,]])->execute();

                if ($customer_list['customer_public_id'] != 0) {
                    $error++;
                } else if ($customer_public_id ==null) {
                    $error++;
                } else {
                    $customer_list -> last_record = $time;
                    $customer_list ->last_operation_creator_id = $info->id;
                    $customer_list ->last_operation_creator_name = $info->name;
                    $customer_list -> customer_public_id = $customer_public_id['customer_public_id'];
                    $customer_list -> move_public_time = $time;
                    $customer_list -> abandon_reason = $post['abandon_reason'];
                    $customer_list ->is_protect = 0;
                    $customer_list -> is_share = 0;
                    $customer_list -> is_receive = 0;
                    $customer_list -> administrator_id = 0;
                    $customer_list ->save(false);
                    $success++;
                }

                $business_id = isset($business_list->id)? $business_list->id : 0;
                CrmCustomerLog::add('放弃客户到公海' , $val, 0, '', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD, $business_id);

                CustomerFollowRecord:: add($business_id, $val, 0, "放弃客户到公海",  '其他', 5, 0,  0,   0, '');

                $customer_model = new CustomerExchangeList();
                /** @var CrmContacts $contact */
                $contact = CrmContacts::find()->where(['customer_id'=>$val])->one();
                $province_id =isset($business_list->province_id) ? $business_list->province_id : (isset($contact->province_id) ? $contact->province_id : 0);
                $city_id = isset($business_list->city_id) ? $business_list-> city_id : (isset($contact->city_id) ? $contact->city_id : 0);
                $district_id = isset($business_list->district_id) ? $business_list->district_id : (isset($contact->district_id) ? $contact->district_id : 0);
                $data = [
                    'id' => $val,
                    'administrator_id' => $customer_list->administrator_id,
                    'province_id' => $province_id,
                    'city_id' => $city_id,
                    'district_id' => $district_id,
                    'source_id' => $contact->source ,
                    'channel_id' => $contact->channel_id
                ];
                $customer_model -> customer($data,'giveup');

            }
            $transaction -> commit();
        }catch (\Exception $e){
            throw $e;
            $transaction -> rollBack();
        }

        return $this->response(self::SUCCESS, '请求成功','成功' . $success . ' ; 失败' . $error);
    }


    //商机废弃接口

    public function actionOpportunityAbandon()
    {

        $post = Yii::$app->request->post();

//        $post['crm_customer_id'] = '1501,1502,1503,1504';
//        $post['invalid_reason'] = '废弃废弃';

        $model = new CrmOpportunity();

        $model->load($post, '');

        $model->setScenario('abandon');

        if (!$model->validate()) {
            $errors = $model->getFirstErrors();
            return $this->response(self::FAIL, reset($errors));
        }

        $info = Yii::$app->user->identity;

        $customer_id = explode(',', $post['crm_customer_id']);

        $error = 0;

        $success = 0;

        foreach ($customer_id as $key => $val) {

            /** @var CrmOpportunity $opportunity_list */
            $opportunity_list = CrmOpportunity::find()->where(['customer_id' => $val])->one();

            if (null == $opportunity_list) {
                $error++;
            } else if ($opportunity_list['administrator_id'] != $info['id']) {
                $error++;
            } else {

                $opportunity_list->invalid_reason = $post['invalid_reason'];
                $opportunity_list->invalid_time = time();
                $opportunity_list->status = CrmOpportunity::STATUS_FAIL;
                $opportunity_list->progress = 0;
                $opportunity_list->next_follow_time = null;
                $opportunity_list->is_protect = CrmOpportunity::PROTECT_DISABLED;

                $opportunity_list->save(false);

                CrmCustomerLog::add('商机废弃', $val, $opportunity_list['id'], '', CrmCustomerLog::TYPE_CUSTOMER_OPPORTUNITY, 0);
                $success++;

            }
        }

        return $this->response(self::SUCCESS, '成功：' . $success . '；失败：' . $error);
    }

}