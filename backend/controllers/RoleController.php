<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/20
 * Time: 上午11:45
 */

namespace backend\controllers;

use backend\models\AdministratorRole;
use common\models\Administrator;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RoleController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['ajax-status'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list','ajax-status','member'],
                        'allow' => true,
                        'roles' => ['role/list'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['role/create'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['role/update'],
                    ],
                ],
            ],
        ];
    }

    // 角色管理-列表
    public function actionList($keyword = null)
    {
        $query = AdministratorRole::find();
        if (!empty($keyword)) {
            $query->andWhere([
                'like', 'name', $keyword
            ]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    // 角色管理-新增
    public function actionCreate()
    {
        $role = new AdministratorRole();
        if ($role->load(Yii::$app->request->post()) && $role->validate()) {
            $role->save(false);
            $this->processRolePermission($role);
            Yii::$app->session->setFlash('success', '角色保存成功!');
            return $this->redirect(['list']);
        }

        if ($role->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败, 您的表单填写有误, 请检查!');
        }
        return $this->render('create', [
            'role' => $role,
            'permissionGroup' => $this->permissionGroup(),
        ]);
    }

    // 角色管理-编辑
    public function actionUpdate($id)
    {
        $role = $this->findModel($id);
        if ($role->load(Yii::$app->request->post()) && $role->validate()) {
            $role->save(false);
            $this->processRolePermission($role);
            Yii::$app->session->setFlash('success', '角色保存成功!');
            return $this->redirect(['update', 'id' => $role->id]);
        }

        if ($role->hasErrors()) {
            Yii::$app->session->setFlash('error', '保存失败!');
        }

        return $this->render('update', [
            'role' => $role,
            'permissionGroup' => $this->permissionGroup(),
        ]);
    }

    // 角色成员管理
    public function actionMember($id)
    {
        $role = $this->findModel($id);
        $auth = Yii::$app->authManager;
        $admin_ids = $auth->getUserIdsByRole($role->id);
        $query = Administrator::find()->where(['in','id',$admin_ids]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('member', [
            'provider' => $provider,
            'role' => $role,
        ]);
    }

    /**
     * @param $role AdministratorRole
     */
    private function processRolePermission($role)
    {
        $permissions = Yii::$app->request->post('permission');
        $auth = Yii::$app->authManager;
        $roleItem = $auth->getRole($role->id);
        if (null == $roleItem) {
            $roleItem = $auth->createRole($role->id);
            $roleItem->description = $role->name;
            $auth->add($roleItem);
        } else {
            $roleItem->description = $role->name;
            $auth->update($roleItem->name, $roleItem);
            $auth->removeChildren($roleItem);
        }
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                $permissionItem = $auth->getPermission($permission);
                if (!$auth->hasChild($roleItem, $permissionItem)) {
                    $auth->addChild($roleItem, $permissionItem);
                }
            }
        }
    }

    //推荐位上下线操作
    public function  actionAjaxStatus()
    {
        $status = Yii::$app->getRequest()->post('status');
        $role_id = Yii::$app->getRequest()->post('role_id');
        $model = $this->findModel($role_id);
        $model->status = $status > 0 ? AdministratorRole::STATUS_OFFLINE : AdministratorRole::STATUS_ONLINE;
        if($model->validate(['status']) && $model->save(false))
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => '状态修改失败：'.$model->getFirstError('status')];
    }

    /**
     * @param $id
     * @return AdministratorRole
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        /** @var AdministratorRole $role */
        $role = AdministratorRole::findOne($id);
        if (null == $role) {
            throw new NotFoundHttpException('找不到角色!');
        }
        return $role;
    }

    /**
     * @return array
     */
    private function permissionGroup()
    {
        $group = [
            [
                'group_name' => '组织机构',
                'items' => [
                    'company/all' => '组织机构视图列表',
                    'department/list' => '组织机构管理列表',
                    'company/create' => '新建公司',
                    'company/update' => '编辑公司',
                    'company/delete' => '删除公司',
                    'department/create' =>'新建部门',
                    'department/update' =>'编辑部门',
                    'department/delete' =>'删除部门',
                    'company/department-modify' => '修改人员所属公司与部门',
                    'administrator/change-jobs' => '人员调岗',
                    'administrator/leave' => '人员离职',
                    'administrator/ajax-hire' => '人员返聘',
                    'administrator/dimission' => '离职人员列表',
                ],
            ],
            [
                'group_name' => '管理员账号管理',
                'items' => [
                    'administrator/list-manager' => '管理员列表',
                    'administrator/add-manager' => '新建管理员',
                    'administrator/update-manager' => '编辑管理员',
                    'administrator/status-manager' => '管理员上下线',
                ],
            ],
            [
                'group_name' => '客服管理',
                'items' => [
                    'administrator/list-customer-service' => '客服列表',
                    'administrator/add-customer-service' => '新建客服',
                    'administrator/update-customer-service' => '编辑客服',
                    'administrator/status-customer-service' => '客服上下线',
                ],
            ],
            [
                'group_name' => '嘟嘟妹管理',
                'items' => [
                    'administrator/list-supervisor' => '嘟嘟妹列表',
                    'administrator/add-supervisor' => '新建嘟嘟妹',
                    'administrator/update-supervisor' => '编辑嘟嘟妹',
                    'administrator/status-supervisor' => '嘟嘟妹上下线',
                ],
            ],
            [
                'group_name' => '服务人员管理',
                'items' => [
                    'administrator/list-clerk' => '服务人员列表',
                    'administrator/add-clerk' => '新建服务人员',
                    'administrator/update-clerk' => '编辑服务人员',
                    'administrator/status-clerk' => '服务人员上下线',
                ],
            ],
            [
                'group_name' => '业务员管理',
                'items' => [
                    'administrator/list-salesman' => '业务员列表',
                    'administrator/add-salesman' => '新建业务员',
                    'administrator/update-salesman' => '编辑业务员',
                    'administrator/status-salesman' => '业务员上下线',
                ],
            ],
            [
                'group_name' => '市场运营',
                'items' => [
                    'banner/list' => '焦点图列表',
                    'banner/create' => '新建焦点图',
                    'banner/update' => '编辑焦点图',
                    'banner/delete' => '删除焦点图',
                    'partner/list' => '合作客户列表',
                    'partner/create' => '新建合作客户',
                    'partner/update' => '编辑合作客户',
                    'partner/delete' => '删除合作客户',
                    'customer-said/list' => '客户说列表',
                    'customer-said/create' => '新建客户说',
                    'customer-said/update' => '编辑客户说',
                    'customer-said/delete' => '删除客户说',
                    'link/list' => '友情链接列表',
                    'link/create' => '新建友情链接',
                    'link/update' => '编辑友情链接',
                    'link/delete' => '删除友情链接',
                ],
            ],
            [
                'group_name' => '资讯管理',
                'items' => [
                    'information-category/list' => '百科分类列表',
                    'information-category/create' => '新建百科分类',
                    'information-category/update' => '编辑百科分类',
                    'information-category/delete' => '删除百科分类',
                    'information/list' => '资讯百科列表',
                    'information/create' => '新建资讯百科',
                    'information/update' => '编辑资讯百科',
                    'information/delete' => '删除资讯百科',
                    'information/ajax-status' => '资讯百科上下线',
                    'information/product-list' => '推荐商品',
                ],
            ],
            [
                'group_name' => '产品运营',
                'items' => [
                   'nav/list' => '导航菜单列表',
                   'nav/create' => '新建导航菜单',
                   'nav/update' => '编辑导航菜单',
                   'nav/delete' => '删除导航菜单',
                   'featured/list' => '推荐位列表',
                   'featured/create' => '新建推荐位',
                   'featured/update' => '编辑推荐位',
                   'featured/ajax-status' => '推荐位上下线',
                   'featured/add-product' => '推荐位商品列表',
                   'featured/ajax-create' => '新建&编辑推荐位商品',
                   'featured/ajax-delete' => '删除推荐位商品',
                   'company-name/list' => '公司起名列表',
                   'company-name/create' => '新建&编辑公司起名',
                   'company-name/delete' => '删除公司起名',
                   'sensitive-word/list' => '敏感词列表',
                   'sensitive-word/create' => '新建敏感词',
                   'sensitive-word/update' => '编辑敏感词',
                   'sensitive-word/delete' => '删除敏感词',
                   'trademark-word/list' => '著驰名商标列表',
                   'trademark-word/create' => '新建著驰名商标',
                   'trademark-word/update' => '编辑著驰名商标',
                   'trademark-word/delete' => '删除著驰名商标',
                ],
            ],
            [
                'group_name' => '落地页管理',
                'items' => [
                    'pages/list' => '落地页列表',
                    'pages/create' => '新建落地页',
                    'pages/update' => '编辑落地页',
                ],
            ],
            [
                'group_name' => '马甲管理',
                'items' => [
                    'user/vest-list' => '马甲客户列表',
                    'user/create_vest' => '新建马甲客户',
                    'order-list/vest' => '马甲订单列表',
                ],
            ],
            [
                'group_name' => '评论管理',
                'items' => [
                    'evaluate/list' => '评价列表',
                    'evaluate/review' => '评价审核',
                    'order-evaluate/reply' => '回复订单评价',
                ],
            ],
            [
                'group_name' => '订单列表',
                'items' => [
                    'order-list/all' => '全部子订单列表',
                    'order-list/refund' => '退款中订单列表',
                    'order-list/pending-payment' => '待付款订单列表',
                    'order-list/pending-assign' => '待分配订单列表',
                    'order-list/pending-service' => '待服务订单列表',
                    'order-list/in-service' => '服务中订单列表',
                    'order-list/completed' => '服务完成订单列表',
                    'order-list/break' => '服务终止订单列表',
                    'order-list/timeout' => '报警订单列表',
                    'order-renewal/list' => '续费订单列表',
                    'order-list/vest' => '马甲订单列表',
                ],
            ],
            [
                'group_name' => '订单操作',
                'items' => [
                    'order/info' => '订单详情',
                    'order-action/change-salesman' => '修改业务人员',
                    'order-action/change-customer-service' => '修改客服',
                    'order-action/change-clerk' => '修改服务人员',
                    'order-action/refund' => '申请退款',
                    'order-action/refund-review' => '退款审核',
                    'refund/do' => '财务退款',
                    'receipt/create' => '新建回款',
                    'receipt/review' => '确认回款',
                    'order-action/cancel' => '取消订单',
                    'order-action/adjust-price' => '修改价格',
                    'order-action/review-adjust-price' => '修改价格审核',
                    'follow-record/create' => '订单跟进记录',
                    'order-list/export' => '导出订单记录',
                    'order-action/start-service' => '开始服务订单',
                    'order-action/do-flow-action' => '订单流程操作',
                    'order-action/add-remark' => '订单详情添加备注',
                    'order-follow-record/create' => '续费订单跟进记录',
                    'order-renewal/create' => '续费订单操作',
                    'order-renewal/send-remind-sms' => '续费订单发送短信',
                    'cost/list' => '成本查看',
                    'order-cost-record/*' => '实际成本录入',
                    'expected-cost/insert' => '预计成本录入',
                    'order-cost/*' => '实际成本库维护',
                    'performance/list' => '实际利润和提成管理(子)',
                    'order-action/financial-update' => '修改财务明细编号',
                    'order-action/satisfaction' => '客户满意度修改',
                    'virtual-order/score' => '实际利润查看(虚拟订单)',
                    'virtual-order-action/calculate-profit' => '计算提成(虚拟订单)',
                    'virtual-order-action/batch-calculate-profit' => '批量计算提成',
                    'virtual-order-action/performance-correct' => '提成金额更正(子订单)',
                    'order-action/upload' => '文件上传',
                    'order/update-order-service' => '批量修改客服人员',
                    'order/update-order-clerk' => '批量修改服务人员'
                ],
            ],
            [
                'group_name' => '订单管理',
                'items' => [
                    'order-receive/all' => '待认领订单列表',
                    'order-receive/receive' => '订单认领',
                    'order-receive/access-allocation' => '订单认领回访分配',
                    'order-receive-record/list' => '订单认领记录列表',
                    'order-list/apply' => '待计算提成列表',
                    'order-action/apply-calculate' => '申请计算提成',
                    'performance-statistics/*' => '去计算提成(待计算提成列表)',
                    'virtual-order-list/list' => '全部虚拟订单(列表)',
                    'virtual-order-action/batch-adjust-price' => '批量修改订单价格',
                    'virtual-order-action/receipt' => '新建回款(虚拟订单)',
                    'virtual-order-action/allot-price' => '分配回款(虚拟订单)',
                    'virtual-order/expected-cost-list' => '预计成本和预计利润查看(虚拟订单)',
                    'virtual-order/cost-list' => '实际成本和实际利润查看(虚拟订单)',
                    'virtual-order-action/expected-cost' => '预计成本录入(虚拟订单)',
                    'virtual-order-action/cost' => '实际成本录入(虚拟订单)',
                    'virtual-order-action/payment-mode' => '批量修改付款方式',
                    'virtual-order-action/replace-order-team' => '批量替换订单共享业务员',
                    'virtual-order-action/replace-order-salesman' => '批量替换订单负责业务员',
                    'virtual-order-action/change-settlement-month' => '批量编辑订单业绩提点月',
                    'virtual-order-action/change-financial' => '批量编辑财务明细编号',
                    'order-action/settlement-month' => '订单业绩提点月修改(子订单)',
                    'virtual-order-action/knot-expected-cost' => '批量结转预计利润',
                    'virtual-order-action/expected-profit-correct' => '预计利润更正(虚拟订单)',
                    'order/profit-update' => '预计利润更正(子订单)',
                    'virtual-order-action/calculate-order-expected-profit' => '计算预计利润(子订单)',
                    'virtual-order-action/calculate-expected-profit' => '计算预计利润(虚拟订单)',
                    'virtual-order/turnover' => '订单流水(虚拟订单)',
                    'virtual-order-action/detail' => '子订单详情(虚拟订单)',
                    'bills-book/index' => '个人记账簿列表页',
                    'bills-book/department' => '部门记账簿列表页',
                    'bills-book/detail' => '个人记账簿详情页',
                    'bills-book/department-detail' => '部门记账簿详情页',
                    'profit-rule/profit-rule' => '业绩提成规则修改',
                    'expected-profit-rule/rate' => '预计利润计算控制',
                    'order-action/service-status-update' => '服务状态标记'
                ],
            ],
            [
                'group_name' => '线上用户管理',
                'items' => [
                    'user/list' => '注册用户列表',
                    'user/create' => '新建用户',
                    'customer-detail/*' => '客户详情(只读)',
                ],
            ],
            [
                'group_name' => 'CRM管理',
                'items' => [
                    'customer/*' => '我的客户',
                    'business-subject/create' => '新增业务主体',
                    'business-subject/update' => '编辑业务主体',
                    'customer/all' => '全部客户(只读)',
                    'customer/export' => '导出客户记录',
                    'customer/import' => '客户批量导入',
                    'order/create' => '代客下单',
                    'opportunity/*' => '我的商机',
                    'opportunity/create-all-customer' => '新建全部客户商机',
                    'opportunity/all' => '全部商机(只读)',
                    'opportunity/export' => '导出商机记录',
                    'reward-proportion/list' => '提成比例方案查看',
                    'reward-proportion/update' => '提成比例方案编辑',
                    'reward-proportion/delete' => '提成比例方案删除',
                    'opportunity-public/list' => '商机公海',
                    'opportunity-public/setting' => '商机公海配置',
                    'opportunity-public/confirm-claim' => '公海商机提取',
                    'customer-public/list' => '客户公海',
                    'customer-public/setting' => '客户公海配置',
                    'customer-public/confirm-claim' => '公海客户提取',
                    'tag/update' => '标签维护',
                    'opportunity/forced-deal' => '商机强制成交',
                    'customer-source/list' => '客户来源管理',
                    'opportunity-public/export' => '商机公海信息导出'
                ],
            ],
            [
                'group_name' => '业务主体',
                'items' => [
                    'business-subject/list' => '业务主体列表',
                    'business-subject/detail' => '业务主体详情',
                ],
            ],
            [
                'group_name' => '商品管理',
                'items' => [
                    'product-category/list' => '商品分类列表',
                    'product-category/create' => '新建商品分类',
                    'product-category/update' => '编辑商品分类',
                    'product-category/delete' => '删除商品分类',
                    'product/export' => '导出全部商品',
                    'flow/create' => '新建流程',
                    'flow/update' => '编辑流程',
                    'flow/publish' => '发布流程',
                    'flow/list' => '查看流程',
                    'flow/status' => '编辑流程状态',
                    'flow/delete' => '删除流程',
                    'product/list' => '商品列表',
                    'product/create' => '新建商品',
                    'product/update' => '编辑商品',
                    'product/status' => '商品上下线',
                    'product-price/list' => '商品价格列表',
                    'product-price/update' => '设置商品价格',
                    'product-related/*' => '关联商品',
                    'collocation/list' => '搭配商品列表',
                    'collocation/add' => '新建搭配商品',
                    'collocation/remove' => '删除搭配商品',
                    'product-faq/*' => '商品常见问题',
                    'product-introduce/*' => '商品描述',
                    'product/seo' => '商品SEO设置',
                    'renewal-product-related/list' => '管理续费商品列表',
                    'renewal-product-related/create' => '新建关联续费商品',
                    'renewal-product-related/update' => '编辑关联续费商品',
                    'renewal-product-related/status' => '关联续费商品状态修改',
                    'product-category/seo' => '商品分类SEO设置',
                ],
            ],
            [
                'group_name' => '优惠管理',
                'items' => [
                    'coupon-list/list' => '优惠券列表',
                    'coupon/create' => '新建优惠券',
                    'coupon/update' => '编辑优惠券',
                    'coupon/status' => '作废优惠券',
                    'coupon/confirm' => '优惠券发布',
                    'coupon/info' => '优惠券详情',
                    'coupon-code/export' => '优惠码导出',
                ],
            ],
            [
                'group_name' => '搜索记录管理',
                'items' => [
                    'record/search' => '商品搜索记录',
                    'record/check-name' => '核名预查记录',
                    'record/trademark' => '商标查询记录',
                ],
            ],
            [
                'group_name' => '统计管理',
                'items' => [
                    'statistics/list' => '统计分析',
                    'statistics/this-week' => '交易分析',
                    'statistics/week-summary' => '商品概况分析',
                    'statistics/transaction-ranking' => '详细分析',
                    'profit-statistics/*' => '预计利润表查看',
                    'ranking/*' => '业绩龙虎榜查看',
                    'expected-profit-settlement/*' => '预计利润结算',
                    'settlement_performance/*' => '业绩提成计算',
                    'administrator-log/record' => '操作日志',
                    'administrator-log/warning' => '风险操作告警',
                    'performance/real-time-data' => '预计利润表实时数据',
                    'profit-statistics/update-rate' => '修改预计利润表（个人提成比例）',
                    'statistics/opp_funnel_list'=>'商机统计',
                    'statistics/customer_flow_list'=>'客户流转统计'
                ],
            ],
            [
                'group_name' => '财务管理',
                'items' => [
                    'financial-statements/list' => '交易流水',
                    'financial-statements/export' => '导出交易流水',
                ],
            ],
            [
                'group_name' => '发票管理',
                'items' => [
                    'invoice-list/list' => '发票列表',
                    'invoice-action/confirm' => '确认发票信息',
                    'invoice-action/invoiced' => '确认开具发票',
                    'invoice-action/send' => '确认发票寄送',
                    'invoice-list/export' => '导出发票',
                    'invoice-action/apply-invoice' => '申请发票',
                ],
            ],
            [
                'group_name' => '系统设置',
                'items' => [
                    'industry/list' => '行业列表',
                    'industry/create' => '新建行业',
                    'industry/update' => '编辑行业',
                    'industry/delete' => '删除行业',
                    'region/list' => '地区列表',
                    'region/create' => '新建地区',
                    'region/update' => '编辑地区',
                    'region/delete' => '删除地区',
                    'holidays/create' => '新建年度工作日',
                    'holidays/update' => '编辑年度工作日',
                    'holidays/delete' => '删除年度工作日',
                    'holidays/list' => '查看年度工作日',
                    'setting/index' => '公共参数设置',
                    'setting/seo' => '全局SEO设置',
                    'setting/other-refund-time' => '第三方平台退款限制时间修改',
                    'cache/flush' => '缓存管理',
                    'call-center/*' => '外呼集成设置',
                    'electricity/data-synchronous' => '电商数据同步设置',
                    'administrator/system' => '系统设置管理',
                ],
            ],
            [
                'group_name' => '帮助文档',
                'items' => [
                    'document-category/list' => '帮助文档首页',
                    'document-category/update' => '编辑文档库',
                    'document-category/delete' => '删除文档库',
                    'document/list' => '文档列表',
                    'document/info' => '文档详情',
                    'document/update' => '编辑文档',
                    'document/delete' => '删除文档',
                ],
            ],
            [
                'group_name' => '合同管理',
                'items' => [
                    'contract/create' => '创建合同',
                    'contract/type-list' => '合同类型列表',
                    'contract/type' => '合同类型管理',
                    'contract/list' => '合同列表',
                    'contract/view' => '合同查看(详情)',
                    'contract/change-administrator' => '修改合同负责人',
                    'contract/sign' => '合同签约审核',
                    'contract/invalid' => '合同作废审核',
                    'contract/change-signature' => '合同签章修改',
                ],
            ],
            [
                'group_name' => '线索管理',
                'items' => [
                    'clue/add_clue' => '新增线索（私海）',
                    'clue/add_clue_public' => '新增线索（公海）',
                    'clue/add_clue_tag'=>'添加线索标签',
                    'clue/add_public_set_up'=>'新增线索公海设置',
                    'clue/all_abandon'=>'放弃线索（全部场景）',
                    'clue/all_add_record'=>'添加线索跟进记录（全部场景）',
                    'clue/all_delete'=>'删除线索（全部场景）',
                    'clue/all_edit_clue'=>'编辑线索（全部场景）',
                    'clue/all_follow_up_list'=>'全部跟进中线索列表',
                    'clue/all_invalid'=>'废弃线索（全部场景）',
                    'clue/all_remove_tag' => '清除线索标签（全部场景）',
                    'clue/all_transfer' => '转移线索（全部场景）',
                    'clue/all_transformed_list' => '全部已转换线索列表',
                    'clue/all_turn_to_customer' => '线索转为客户（全部场景）',
                    'clue/all_update_record_status' => '跟进状态修改（全部场景）',
                    'clue/all_use_tag' => '应用线索标签（全部场景）',
                    'clue/change_clue_public' => '更换线索公海池（公海）',
                    'clue/clue_public_list' => '线索公海列表',
                    'clue/clue_public_set_up' => '线索公海设置列表',
                    'clue/clue_public_status_update' => '线索公海启用禁用',
                    'clue/delete_clue_public' => '删除线索（公海）',
                    'clue/delete_public_set_up' => '删除线索公海设置',
                    'clue/discarded_clue_public' => '废弃线索（公海）',
                    'clue/distribution_clue_public' => '分配线索（公海）',
                    'clue/edit_clue_public' => '编辑销售线索（公海）',
                    'clue/edit_public_set_up' => '编辑线索公海设置',
                    'clue/extract_clue_public' => '提取线索（公海）',
                    'clue/my_abandon' => '放弃线索（我的场景）',
                    'clue/my_add_record' => '添加线索跟进记录（我的场景）',
                    'clue/my_delete' => '删除线索（我的场景）',
                    'clue/my_edit_clue' => '编辑线索（我的场景）',
                    'clue/my_follow_up_list' => '我跟进中线索列表',
                    'clue/my_invalid' => '废弃线索（我的场景）',
                    'clue/my_remove_tag' => '清除线索标签（我的场景）',
                    'clue/my_transfer' => '转移线索（我的场景）',
                    'clue/my_transformed_list' => '我已转换线索列表',
                    'clue/my_turn_to_customer' => '线索转为客户（我的场景）',
                    'clue/my_update_record_status' => '跟进状态修改（我的场景）',
                    'clue/my_use_tag' => '应用线索标签（我的场景）',
                    'clue/subordinate_abandon' => '放弃线索（下属场景）',
                    'clue/subordinate_add_record' => '添加线索跟进记录（下属场景）',
                    'clue/subordinate_delete' => '删除线索（下属场景）',
                    'clue/subordinate_edit_clue' => '编辑线索（下属场景）',
                    'clue/subordinate_follow_up_list' => '下属跟进中线索列表',
                    'clue/subordinate_invalid' => '废弃线索（下属场景）',
                    'clue/subordinate_remove_tag' => '清除线索标签（下属场景）',
                    'clue/subordinate_transfer' => '转移线索（下属场景）',
                    'clue/subordinate_transformed_list' => '下属已转换线索列表',
                    'clue/subordinate_turn_to_customer' => '线索转为客户（下属场景）',
                    'clue/subordinate_update_record_status' => '跟进状态修改（下属场景）',
                    'clue/subordinate_use_tag' => '应用线索标签（下属场景）',
                    'clue/use_clue_tag' => '设置线索标签',
                ],
            ],
            [
                'group_name' => '客户管理',
                'items'=>[
                    'customer-public/visit' => '客户公海配置访问',
                    'customer-public/operation' => '客户公海配置操作',
                    'business-public/visit' =>'企业公海访问' ,
                    'business-public/extract' => '企业公海数据提取' ,
                    'business-public/distribution' => '企业公海数据分配',
                    'person-public/visit' =>'个人公海访问',
                    'person-public/extract' =>'个人公海数据提取',
                    'person-public/distribution' =>'个人公海数据分配',
                    'business-customer/visit' =>'企业客户访问',
                    'business-customer/operation' =>'企业客户数据操作',
                    'person-customer/visit' =>'个人客户访问',
                    'person-customer/operation' =>'个人客户数据操作'
                ],
            ],
            [
                'group_name' => '商机管理',
                'items'=>[
                    'niche/export' => '导出',
                    'niche/set_up_tag' => '设置标签',
                    'niche/my_deal_list' => '我已成交商机列表',
                    'niche/my_lose_list' => '我已输单商机列表',
                    'niche/all_deal_list' => '全部已成交商机列表',
                    'niche/all_lose_list' => '全部已输单商机列表',
                    'niche/niche_extract' => '商机提取（公海）',
                    'niche/personal_give_up' => '放弃（个人场景）',
                    'niche/my_follow_up_list' => '我跟进中商机列表',
                    'niche/niche_public_list' => '商机公海列表',
                    'niche/personal_contract' => '创建订单/合同（个人场景）',
                    'niche/all_follow_up_list' => '全部跟进中商机列表',
                    'niche/my_follow_up_share' => '协作（我跟进中商机）',
                    'niche/niche_public_change' => '商机更换公海（小公海）',
                    'niche/subordinate_give_up' => '放弃（下属场景）',
                    'niche/my_follow_up_protect' => '保护（我跟进中商机）',
                    'niche/my_follow_up_transfer' => '转移（我跟进中商机）',
                    'niche/niche_big_public_list' => '商机大公海列表',
                    'niche/subordinate_deal_list' => '下属已成交商机列表',
                    'niche/subordinate_lose_list' => '下属已输单商机列表',
                    'niche/my_follow_up_tag_change' => '添加/取消标签（我跟进中商机）',
                    'niche/niche_big_public_change' => '商机更换公海（大公海）',
                    'niche/niche_public_add_config' => '商机公海配置新增',
                    'niche/niche_public_set_up_list' => '商机公海设置列表',
                    'niche/niche_public_distribution' => '商机分配（小公海）',
                    'niche/subordinate_follow_up_tag' => '添加标签（下属跟进中商机）',
                    'niche/niche_public_delete_config' => '商机公海配置删除',
                    'niche/niche_public_enable_config' => '商机公海配置启用/禁用',
                    'niche/niche_public_update_config' => '商机公海配置编辑',
                    'niche/subordinate_follow_up_list' => '下属跟进中商机列表',
                    'niche/subordinate_follow_up_share' => '协作（下属跟进中商机）',
                    'niche/niche_big_public_distribution' => '商机分配（大公海）',
                    'niche/subordinate_follow_up_protect' => '保护（下属跟进中商机）',
                    'niche/subordinate_follow_up_contract' => '创建订单/合同（下属场景）',
                    'niche/subordinate_follow_up_transfer' => '转移（下属跟进中商机）',
                    'niche/niche_big_public_config' => '商机大公海配置管理',
                ],
            ],

        ];
        return $group;
    }

}