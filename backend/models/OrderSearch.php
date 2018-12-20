<?php

namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\City;
use common\models\District;
use common\models\Order;
use common\models\OrderRemark;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Province;
use common\models\User;
use common\models\VirtualOrder;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * Class OrderSearch
 * @property ProductCategory $topCategory
 * @property OrderRemark $orderRemark
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 * @property Product $product
 */

class OrderSearch extends Model
{
    public $top_category_id;
    public $category_id;
    public $product_id;
    public $keyword;
    public $type = 6;
    public $province_id;
    public $city_id;
    public $district_id;
    public $status;
    public $starting_time;
    public $end_time;
    public $begin_service_start_time;
    public $begin_service_end_time;
    public $end_service_start_time;
    public $end_service_end_time;
    public $content_add_start;
    public $content_add_end;

    public $first_pay_start_time;
    public $first_pay_end_time;
    public $settlement_month;
    public $total_cost;

    public $break_reason;
    public $source_app;
    public $is_proxy;
    public $is_installment;
    public $is_satisfaction;
    public $finance_num;
    public $expected_profit_calculate;
    public $actual_profit_calculate;
    public $service_status;

    public $order_dispatch_time_start;
    public $order_dispatch_time_end;

    public $remark_id;

    /**
     * @var Administrator
     */
    public $administrator;

    const TYPE_COMPANY_NAME = 1;//公司名称
    const TYPE_USER_NAME = 2;//客户姓名/昵称
    const TYPE_USER_PHONE = 3;//客户联系方式
    const TYPE_CUSTOMER_SERVICE_NAME = 4;//客服姓名
    const TYPE_CLERK_NAME = 5;//服务人员姓名
    const TYPE_SN = 6;//订单号
    const TYPE_SALESMAN_NAME = 7;//业务人员姓名
    const TYPE_TRADEMARK_APPLY_NO = 8;//商标申请号
    const TYPE_FINANCIAL_CODE = 9;//财务明细编号

    const BREAK_REASON_NOT_FOLLOW = 1;//停止跟进
    const BREAK_REASON_REFUND_AND_CANCEL = 2;//退款并且取消
    const BREAK_REASON_USER_CANCEL = 3;//客户主动取消
    const BREAK_REASON_OVERTIME_CLOSE = 4;//订单关闭(超时)

    const SOURCE_APP_PC  = 1; //PC端下单
    const SOURCE_APP_WAP = 2; //手机端下单
    const SOURCE_APP_WX  = 3; //微信下单

    const EXPECTED_TOTAL_COST = 0;
    const EXPECTED_TOTAL_COST_NOT = 1;//未录入
    const EXPECTED_TOTAL_COST_FINISH = 2;//已录入

    const INSTALLMENT_ONE = 0;
    const INSTALLMENT_TRUE = 1;//分期付款
    const INSTALLMENT_FALSE = 2;//一次付款

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['top_category_id', 'category_id', 'is_installment','type','product_id', 'province_id', 'city_id', 'district_id', 'break_reason','source_app','total_cost','is_satisfaction','finance_num','service_status','remark_id'], 'integer'],
            ['keyword', 'safe'],
            [['starting_time', 'end_time', 'begin_service_start_time', 'begin_service_end_time', 'end_service_start_time', 'end_service_end_time','first_pay_start_time','first_pay_end_time','content_add_start','content_add_end','order_dispatch_time_start','order_dispatch_time_end'], 'date', 'format' => 'yyyy-MM-dd'],
            [['settlement_month'],'date', 'format' => 'yyyy-MM'],
            [['starting_time'], 'validateTimes'],
            [['begin_service_start_time'], 'validateBeginServiceTime'],
            [['end_service_start_time'], 'validateEndServiceTime'],
            [['first_pay_start_time'], 'validatePayTime'],
            [['is_proxy','expected_profit_calculate','actual_profit_calculate'], 'integer'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '【下单】起始时间不能大于结束时间！');
        }
    }

    public function validateBeginServiceTime()
    {
        if($this->begin_service_start_time > $this->begin_service_end_time && $this->begin_service_end_time)
        {
            $this->addError('begin_service_start_time', '【服务开始】起始时间不能大于结束时间！');
        }
    }

    public function validateEndServiceTime()
    {
        if($this->end_service_start_time > $this->end_service_end_time && $this->end_service_end_time)
        {
            $this->addError('end_service_start_time', '【服务结束】开始时间不能大于结束时间！');
        }
    }

    public function validatePayTime()
    {
        if($this->first_pay_start_time > $this->first_pay_end_time && $this->first_pay_end_time)
        {
            $this->addError('end_service_start_time', '【首次付款时间结束】开始时间不能大于结束时间！');
        }
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'top_category_id' => '商品类目',
            'category_id' => '',
            'city_id' => '',
            'district_id' => '',
            'product_id' => '',
            'keyword' => '关键词',
            'type' => '类型',
            'province_id' => '地区',
            'starting_time' => '下单时间（起）',
            'end_time' => '下单时间（止）',
            'begin_service_start_time' => '服务开始（起）',
            'begin_service_end_time' => '服务开始（止）',
            'end_service_start_time' => '服务结束（起）',
            'end_service_end_time' => '服务结束（止）',
            'first_pay_start_time' => '首次付款时间（起）',
            'first_pay_end_time' => '首次付款时间（止）',
            'content_add_start' => '备注添加时间(起)',
            'content_add_end' => '备注添加时间(止)',
            'break_reason' => '终止原因',
            'source_app' => '订单来源',
            'settlement_month' => '订单业绩提点月',
            'total_cost' => '预计成本录入',
            'is_proxy' => '',
            'is_installment' => '付款方式',
            'is_satisfaction' => '客户满意度',
            'finance_num' => '财务明细编号',
            'remark_id' => '备注人'
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param int $status
     * @param Query $query1
     * @return ActiveDataProvider
     * @throws
     */
    public function search($params, $status, $query1 = null)
    {
        if(null == $query1) {
            $query = Order::find()->alias('o');//起别名
        } else {
            $query = $query1;
        }
        //ActiveDataProvider通过使用$ query执行数据库查询来提供数据。
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        unset($params['status']);
        $this->load($params);//查询条件载入模型
        if (!$this->validate())
        {
            return $dataProvider;
        }
        $query->andFilterWhere(['o.is_contract_show' => 1]);

        if ($this->type == self::TYPE_SN)
        {
            if($status === 'adjust-price')
            {
                //订单号(子订单或者虚拟订单号)
                if(null == $query1) {
                    $query->innerJoinWith(['virtualOrder vo']);
                } else {
                    $query->innerJoin(['vo' => VirtualOrder::tableName()], ['o.virtual_order_id' => 'vo.id']);
                }
                $query->andFilterWhere(['or', ['o.sn' => $this->keyword], ['vo.sn' => $this->keyword]]);
            }
            else
            {
                //订单号(子订单号)
                $query->andFilterWhere(['o.sn' => $this->keyword]);
            }
        }
        if($status === 'vest')
        {
            $query->andWhere(['o.is_vest' => '1']);
        }
        else
        {
            $query->andWhere(['o.is_vest' => 0]);
        }

        if($this->expected_profit_calculate == 1)
        {
            $query->andWhere(['o.expected_profit_calculate' => 1]);
        }
        elseif ($this->expected_profit_calculate == 2)
        {
            $query->andWhere(['o.expected_profit_calculate' => 0]);
        }

        if($this->actual_profit_calculate == 1)
        {
            $query->andWhere(['o.actual_profit_calculate' => 1]);
        }
        elseif ($this->actual_profit_calculate == 2)
        {
            $query->andWhere(['o.actual_profit_calculate' => 0]);
        }

        if($this->finance_num)
        {
            if($this->finance_num == 1)
            {
                $sql = '`financial_code` != ""';
                $query->andWhere($sql);
            }
            elseif($this->finance_num == 2)
            {
                $sql = '`financial_code` = ""';
                $query->andWhere($sql);
            }
        }
        $query->andFilterWhere([
            'o.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'o.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'o.product_id' => $this->product_id <= 0 ? null : $this->product_id,
        ]);

        if($this->type == self::TYPE_COMPANY_NAME){
            //公司名称
            $query->andFilterWhere(['like', 'o.company_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_CUSTOMER_SERVICE_NAME){
            //客服姓名
            $query->andFilterWhere(['like', 'o.customer_service_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_CLERK_NAME){
            //服务人员姓名
            $query->andFilterWhere(['like', 'o.clerk_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_SALESMAN_NAME){
            //业务人员姓名
            $query->andFilterWhere(['like', 'o.salesman_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_TRADEMARK_APPLY_NO){
            //商标申请号
            $query->andFilterWhere(['like', 'o.trademark_apply_no', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_NAME){
            //客户姓名/昵称
            if(null == $query1) {
                $query->innerJoinWith(['user u']);
            } else {
                $query->innerJoin(['u' => User::tableName()], ['o.user_id' => 'u.id']);
            }
            $query->andFilterWhere(['like', 'u.name', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_PHONE){
            //客户联系方式
            if(null == $query1) {
                $query->innerJoinWith(['user u']);
            } else {
                $query->innerJoin(['u' => User::tableName()], ['o.user_id' => 'u.id']);
            }
            $query->andFilterWhere(['like', 'u.phone', $this->keyword]);
        }elseif($this->type == self::TYPE_FINANCIAL_CODE)
        {
            //财务明细编号
            $query->andFilterWhere(['=', 'o.financial_code', $this->keyword]);
        }

        //订单服务终止原因
        if($this->break_reason == self::BREAK_REASON_NOT_FOLLOW)
        {
            //停止跟进
            $query->andWhere(['o.break_reason' => Order::BREAK_REASON_NOT_FOLLOW]);
        }
        elseif ($this->break_reason == self::BREAK_REASON_REFUND_AND_CANCEL)
        {
            //退款并且取消(已退款)
            $query->andWhere(['o.break_reason' => Order::BREAK_REASON_REFUND_AND_CANCEL, 'o.refund_status' => Order::REFUND_STATUS_REFUNDED]);
        }
        elseif ($this->break_reason == self::BREAK_REASON_USER_CANCEL)
        {
            //客户主动取消
            $query->andWhere(['o.break_reason' => Order::BREAK_REASON_USER_CANCEL]);
        }
        elseif ($this->break_reason == self::BREAK_REASON_OVERTIME_CLOSE)
        {
            //订单超时关闭
            $query->andWhere(['o.break_reason' => Order::BREAK_REASON_OVERTIME_CLOSE]);
        }

        //不为空，且不是马甲的订单
        if( null !== $status && $status !== '' && $status !== 'vest')
        {
            if('refund' === $status)
            {
                //退款中包含申请中和已审核2种状态
                $query->andWhere(['in', 'o.refund_status', [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]]);
            }
            elseif('need-refund' === $status) // 需要去退款的财务操作的订单
            {
                if(null == $query1) {
                    $query->innerJoinWith(['virtualOrder vo']);
                } else {
                    $query->innerJoin(['vo' => VirtualOrder::tableName()], ['o.virtual_order_id' => 'vo.id']);
                }
                $query->andWhere(['or', ['o.refund_status' => Order::REFUND_STATUS_AUDITED], ['vo.refund_status' => VirtualOrder::REFUND_STATUS_PENDING_REFUND]]);
            }
            elseif('need-refund-review' === $status) // 待退款审核的订单
            {
                $query->andWhere(['o.refund_status' => Order::REFUND_STATUS_APPLY]);
            }
            elseif('refunded' === $status) // 已退款的订单
            {
                if(null == $query1) {
                    $query->innerJoinWith(['virtualOrder vo']);
                } else {
                    $query->innerJoin(['vo' => VirtualOrder::tableName()], ['o.virtual_order_id' => 'vo.id']);
                }
                $query->andWhere(['or', ['o.refund_status' => Order::REFUND_STATUS_REFUNDED], ['vo.refund_status' => VirtualOrder::REFUND_STATUS_REFUNDED]]);
            }
            elseif('warning' === $status)
            {
                $query->andWhere(['or',
                    'o.next_node_warn_time > 0 and o.next_node_warn_time < :current_time and o.status!=:status_complete_service',
                    //'o.next_follow_time > 0 and o.next_follow_time < :current_time' // 去除待付款下次跟进状态的订单
                ],
                    [':current_time' => time(), ':status_complete_service' => Order::STATUS_COMPLETE_SERVICE]);
                $query->andWhere(['not in', 'o.status', [
                    Order::STATUS_BREAK_SERVICE,
                    Order::STATUS_COMPLETE_SERVICE,
                    Order::STATUS_PENDING_SERVICE,
                    Order::STATUS_PENDING_ALLOT,
                    Order::STATUS_PENDING_PAY, // 去除待付款下次跟进状态的订单
                ]]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
                $query->orderBy(['o.next_node_warn_time' => SORT_DESC]);
            }
            elseif($status === 'apply')
            {
                //待计算业绩订单
                $query->andWhere(['is_apply' => 1]);
            }
            elseif($status === 'adjust-price')
            {
                if(null == $query1) {
                    $query->innerJoinWith(['virtualOrder vo']);
                } else {
                    $query->innerJoin(['vo' => VirtualOrder::tableName()], ['o.virtual_order_id' => 'vo.id']);
                }
                $query->andWhere(['o.adjust_status' => AdjustOrderPrice::STATUS_PENDING, 'vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]);
            }
            elseif($status == Order::STATUS_PENDING_ALLOT)
            {
                //待分配
                $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
                    ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY]]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status == Order::STATUS_PENDING_SERVICE)
            {
                //待服务
                $query->andWhere(['o.status' => Order::STATUS_PENDING_SERVICE]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status == Order::STATUS_IN_SERVICE)
            {
                $query->andWhere(['o.status' => Order::STATUS_IN_SERVICE]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status == Order::STATUS_COMPLETE_SERVICE)
            {
                $query->andWhere(['o.status' => Order::STATUS_COMPLETE_SERVICE]);
            }
            elseif($status == Order::STATUS_BREAK_SERVICE)
            {
                $query->andWhere(['o.status' => Order::STATUS_BREAK_SERVICE]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status == Order::STATUS_UNPAID)
            {
                //未付清
                if(null == $query1) {
                    $query->innerJoinWith(['virtualOrder vo']);
                } else {
                    $query->innerJoin(['vo' => VirtualOrder::tableName()], ['o.virtual_order_id' => 'vo.id']);
                }
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_UNPAID]);
            }
            elseif($status == Order::STATUS_PENDING_PAY)
            {
                //待付款
                if(null == $query1) {
                    $query->innerJoinWith(['virtualOrder vo']);
                } else {
                    $query->innerJoin(['vo' => VirtualOrder::tableName()], ['o.virtual_order_id' => 'vo.id']);
                }
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]);
            }
        }
        //订单来源
        if($this->source_app == self::SOURCE_APP_PC)
        {
            //PC端下单
            $query->andWhere(['o.source_app' => Order::SOURCE_APP_PC]);
        }
        elseif ($this->source_app == self::SOURCE_APP_WAP)
        {
            //手机端下单
            $query->andWhere(['o.source_app' => Order::SOURCE_APP_WAP]);
        }
        elseif ($this->source_app == self::SOURCE_APP_WX)
        {
            //微信下单
            $query->andWhere(['o.source_app' => Order::SOURCE_APP_WX]);
        }

        //成本录入
        if($this->total_cost == self::EXPECTED_TOTAL_COST_NOT)
        {
            $query->andWhere(['o.total_cost' => self::EXPECTED_TOTAL_COST]);
        }
        elseif ($this->total_cost == self::EXPECTED_TOTAL_COST_FINISH)
        {
            $query->andWhere(['>','o.total_cost',self::EXPECTED_TOTAL_COST]);
        }

        if($this->settlement_month)
        {
            $settlement_month = str_replace('-', '', $this->settlement_month);
            $query->andWhere(['o.settlement_month' => $settlement_month]);
        }

        if($this->is_installment == self::INSTALLMENT_TRUE)
        {
            $query->andWhere(['o.is_installment' => self::INSTALLMENT_TRUE]);
        }
        elseif ($this->is_installment == self::INSTALLMENT_FALSE)
        {
            $query->andWhere(['o.is_installment' => self::INSTALLMENT_ONE]);
        }

        $query->andFilterWhere([
            'o.province_id' => $this->province_id <= 0 ? null : $this->province_id,
            'o.city_id' => $this->city_id <= 0 ? null : $this->city_id,
            'o.district_id' => $this->district_id <= 0 ? null : $this->district_id,
        ]);

        if(!empty($this->starting_time))
        {
            $query->andWhere('o.created_at >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('o.created_at <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        if(!empty($this->begin_service_start_time))
        {
            $query->andWhere('o.begin_service_time >= :start_time', [':start_time' => strtotime($this->begin_service_start_time)]);
        }
        if(!empty($this->begin_service_end_time))
        {
            $query->andWhere('o.begin_service_time <= :end_time', [':end_time' => strtotime($this->begin_service_end_time)+86400]);
        }

        if(!empty($this->end_service_start_time))
        {
            $query->andWhere('o.complete_service_time >= :start_time', [':start_time' => strtotime($this->end_service_start_time)]);
        }
        if(!empty($this->end_service_end_time))
        {
            $query->andWhere('o.complete_service_time <= :end_time', [':end_time' => strtotime($this->end_service_end_time)+86400]);
        }

        if(!empty($this->first_pay_start_time))
        {
            $query->andWhere('o.first_payment_time >= :start_time', [':start_time' => strtotime($this->first_pay_start_time)]);
        }
        if(!empty($this->first_pay_end_time))
        {
            $query->andWhere('o.first_payment_time <= :end_time', [':end_time' => strtotime($this->first_pay_end_time)+86400]);
        }
        if(!empty($this->content_add_end) || !empty($this->content_add_start) || !empty($this->remark_id)){
            $query->leftJoin(['remark'=>OrderRemark::tableName()],'remark.order_id = o.id');
        }
        if(!empty($this->content_add_start)){
            $query->andWhere(['>','remark.created_at', strtotime($this->content_add_start)]);
        }
        if(!empty($this->content_add_end)){
            if(!empty($this->content_add_start) && $this->content_add_start == $this->content_add_end){
                $query->andWhere(['<','remark.created_at', strtotime($this->content_add_end)+86400]);
            }else{
                $query->andWhere(['<','remark.created_at', strtotime($this->content_add_end)]);
            }

        }

        //派单时间
        if(!empty($this->order_dispatch_time_start))
        {
            $query->andWhere('o.order_dispatch_time >= :start_time', [':start_time' => strtotime($this->order_dispatch_time_start)]);
        }
        if(!empty($this->order_dispatch_time_end))
        {
            $query->andWhere('o.order_dispatch_time <= :end_time', [':end_time' => strtotime($this->order_dispatch_time_end)+86400]);
        }

        /** @var \common\models\Administrator $user */
        $user = \Yii::$app->user->identity;
        Order::filterRole($query, $user);
        if('warning' !== $status)
        {
            $query->orderBy(['o.id' => SORT_DESC]);
        }
        if($this->is_proxy === '2')
        {
            $query->andWhere(['o.is_proxy' => 1]);
        }
        else if($this->is_proxy === '1')
        {
            $query->andWhere(['o.is_proxy' => 0]);
        }
        if($this->is_satisfaction)
        {
            $query->andWhere(['o.is_satisfaction' => $this->is_satisfaction]);
        }

        if($this->service_status){
            $query->andWhere(['o.service_status' => $this -> service_status]);
        }

        if($this->remark_id){
            $query->andWhere(['remark.creator_id' => $this->remark_id]);
        }
        return $dataProvider;
    }

    public function getCategory()
    {
        return ProductCategory::find()->where(['id' => $this->category_id])
            ->andWhere('parent_id!=:parent_id', [':parent_id' => '0'])->one();
    }

    public function getTopCategory()
    {
        return ProductCategory::find()->where(['id' => $this->top_category_id, 'parent_id' => '0'])->one();
    }

    public function getProvince()
    {
        return Province::find()->where('id=:id', [':id' => $this->province_id])->one();
    }

    public function getOrderRemark()
    {
        $rs = OrderRemark::find()->where('creator_id = :creator_id',[':creator_id' => $this->remark_id])->one();
        return $rs ;
    }


    public function getCity()
    {
        return City::find()->where(['id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])->one();
    }

    public function getProduct()
    {
        return Product::find()->where(['id' => $this->product_id])->one();
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id = :city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])
            ->one();
    }

    public static function getTypes()
    {
        return [
            self::TYPE_COMPANY_NAME => '公司名称',
            self::TYPE_FINANCIAL_CODE => '财务明细编号',
            self::TYPE_USER_NAME => '客户姓名/昵称',
            self::TYPE_USER_PHONE => '客户联系方式',
            self::TYPE_CUSTOMER_SERVICE_NAME => '客服姓名',
            self::TYPE_CLERK_NAME => '服务人员姓名',
            self::TYPE_SALESMAN_NAME => '业务人员',
            self::TYPE_SN => '订单号',
            self::TYPE_TRADEMARK_APPLY_NO => '商标申请号',

        ];
    }

    public static function getAdjustTypes()
    {
        return [
            self::TYPE_SN => '订单号',
            self::TYPE_SALESMAN_NAME => '业务人员',

        ];
    }

    public static function getBreakReasons()
    {
        return [
            self::BREAK_REASON_NOT_FOLLOW => '停止跟进',
            self::BREAK_REASON_REFUND_AND_CANCEL => '退款并且取消',
            self::BREAK_REASON_USER_CANCEL => '客户主动取消',
            self::BREAK_REASON_OVERTIME_CLOSE => '订单关闭',
        ];
    }

    public static function getSourceApps()
    {
        return [
            self::SOURCE_APP_PC => 'PC端下单',
            self::SOURCE_APP_WAP => '手机端下单',
            self::SOURCE_APP_WX => '微信下单',
        ];
    }

    public static function getCost()
    {
        return [
            self::EXPECTED_TOTAL_COST_NOT => '未录入',
            self::EXPECTED_TOTAL_COST_FINISH => '已录入',
        ];
    }

    public static function getInstallment()
    {
        return [
            self::INSTALLMENT_FALSE => '一次付款',
            self::INSTALLMENT_TRUE => '分期付款',
        ];
    }
}
