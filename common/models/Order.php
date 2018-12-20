<?php

namespace common\models;

use backend\models\OrderExpectedCost;
use common\utils\BC;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $virtual_order_id
 * @property string $sn
 * @property integer $product_id
 * @property string $product_name
 * @property string $company_name
 * @property string $trademark_apply_no
 * @property integer $flow_id
 * @property string $price
 * @property string $payment_amount
 * @property string $original_price
 * @property string $wx_remit_amount
 * @property string $coupon_remit_amount
 * @property string $package_remit_amount
 * @property string $adjust_amount
 * @property integer $adjust_status
 * @property integer $is_bargain
 * @property integer $is_evaluate
 * @property integer $is_contract_show
 * @property string $price_detail
 * @property integer $address_product_id
 * @property string $total_cost
 * @property string $real_cost
 * @property integer $first_payment_time
 * @property integer $settlement_month
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property string $service_area
 * @property integer $is_proxy
 * @property integer $is_pay_after_service
 * @property integer $customer_service_id
 * @property string $customer_service_name
 * @property integer $customer_service_department_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $salesman_aid
 * @property string $salesman_name
 * @property integer $salesman_department_id
 * @property integer $supervisor_id
 * @property string $supervisor_name
 * @property integer $supervisor_department_id
 * @property integer $clerk_id
 * @property string $clerk_name
 * @property integer $clerk_department_id
 * @property integer $industry_id
 * @property string $industry_name
 * @property integer $top_category_id
 * @property string $top_category_name
 * @property integer $category_id
 * @property string $category_name
 * @property integer $status
 * @property integer $break_reason
 * @property integer $is_refund
 * @property string $refund_reason
 * @property string $refund_explain
 * @property string $refund_remark
 * @property string $require_refund_amount
 * @property string $require_refund_time
 * @property string $refund_amount
 * @property string $tax
 * @property string $deposit
 * @property integer $refund_status
 * @property integer $is_cancel
 * @property integer $dispatch_time
 * @property integer $begin_service_time
 * @property integer $complete_service_time
 * @property integer $cancel_time
 * @property integer $break_service_time
 * @property integer $next_follow_time
 * @property integer $is_vest
 * @property integer $is_invoice
 * @property integer $created_at
 * @property integer $is_trademark
 *
 * @property integer $flow_is_finish
 * @property integer $last_node_id
 * @property integer $last_action_id
 * @property integer $next_node_id
 * @property integer $business_subject_id
 * @property integer $next_node_limited_time
 * @property integer $next_node_warn_time
 * @property integer $source_app
 *
 * @property integer $renewal_order_id
 * @property integer $renewal_status
 * @property integer $service_cycle
 * @property integer $begin_service_cycle
 * @property integer $end_service_cycle
 * @property integer $renewal_warn_time
 * @property integer $estimate_service_time
 * @property integer $is_renewal
 * @property integer $is_satisfaction
 * @property integer $original_order_id
 * @property integer $is_installment
 * @property integer $sign
 * @property integer $company_id
 * @property integer $is_apply
 * @property integer $financial_code
 * @property integer $expected_profit_calculate
 * @property integer $actual_profit_calculate
 * @property integer $service_id
 * @property integer $service_status
 * @property integer $administrator_id
 * @property integer $order_dispatch_time
 * @property string $order_id
 * @property string $service_name
 *
 * @property User $user
 * @property OrderVoucher $orderVoucher
 * @property OrderBalanceRecord $orderBalanceRecord
 * @property VirtualOrder $virtualOrder
 * @property Product $product
 * @property OrderEvaluate $orderEvaluate
 * @property Clerk $clerk
 * @property Supervisor $supervisor
 * @property CustomerService $customerService
 * @property Flow $flow
 * @property FlowNode $nextNode
 * @property ProductPrice $productPrice
 * @property Invoice $invoice
 * @property Trademark $trademark
 * @property OrderFlowRecord $lastFlowRecord
 * @property OrderFlowRecord $finalOrderRecord
 * @property OrderFlowRecord[] $flowRecords
 * @property OrderSms[] $orderSms
 * @property OrderFile[] $orderFiles
 * @property OrderRecord[] $orderRecords
 * @property OrderRecord $lastOrderRecord
 * @property OrderRemark[] $orderRemarks
 * @property OrderFollowRecord[] $orderFollowRecords
 * @property OrderFollowRecord $lastOrderFollowRecord
 * @property Order $getRelatedRenewalOrder
 *
 * @property BusinessSubject $businessSubject
 * @property AdjustOrderPrice $adjustOrderPrice
 * @property OrderCostRecord[] $orderCostRecord
 * @property OrderExpectedCost[] $orderExpectedCost
 *
 * @property PerformanceRecord[] $performanceRecord
 * @property PerformanceStatistics[] $performanceStatistics
 *
 * @property Administrator $salesman
 * @property CrmDepartment $clerkDepartment
 * @property CrmDepartment $supervisorDepartment
 * @property CrmDepartment $salesmanDepartment
 * @property CrmDepartment $customerServiceDepartment
 * @property OrderTeam[] $orderTeams
 * @property ExpectedProfitSettlementDetail[] $expectedProfits
 * @property Company $salesmanCompany
 * @property NicheOrder $nicheOrder
 */
class Order extends ActiveRecord
{
    //订单状态
    const STATUS_PENDING_PAY = 0; //待付款
    const STATUS_PENDING_ALLOT = 1;//待分配
    const STATUS_PENDING_SERVICE = 2;//待服务
    const STATUS_IN_SERVICE = 3;//服务中
    const STATUS_BREAK_SERVICE = 4;//服务终止
    const STATUS_UNPAID = 5;//未付清-只是个静态变量
    const STATUS_COMPLETE_SERVICE = 8;//服务完成

    //是否有退款
    const REFUND_ACTIVE = 1;//有退款
    const REFUND_DISABLED = 0;//无退款

    //退款状态
    const REFUND_STATUS_NO = 0;//未退款
    const REFUND_STATUS_APPLY = 1;//申请中
    const REFUND_STATUS_AUDITED = 2;//已审核
    const REFUND_STATUS_REFUNDED = 3;//已退款

    //退款时是否取消订单
    const CANCEL_ACTIVE = 1;//是
    const CANCEL_DISABLED = 0;//否

    //是否代客下单
    const PROXY_ACTIVE = 1;//是
    const PROXY_DISABLED = 0;//否

    //对否议价
    const BARGAIN_ACTIVE = 1;//是
    const BARGAIN_DISABLED = 0;//否

    //是否先服务后付费
    const PAY_AFTER_SERVICE_ACTIVE = 1;//是
    const PAY_AFTER_SERVICE_DISABLED = 0;//否

    //订单是否评价
    const EVALUATE_ACTIVE = 1;//是
    const EVALUATE_DISABLED = 0;//否

    const BREAK_REASON_NOT_FOLLOW = 1;//停止跟进
    const BREAK_REASON_REFUND_AND_CANCEL = 2;//退款并且取消
    const BREAK_REASON_USER_CANCEL = 3;//客户主动取消
    const BREAK_REASON_OVERTIME_CLOSE = 4;//订单关闭(超时)

    const SOURCE_APP_PC = 0; //0:电脑网页
    const SOURCE_APP_WAP = 1; //1:手机网页
    const SOURCE_APP_WX = 2; //2:微信公众号

    //订单是否已开具发票
    const INVOICE_ACTIVE = 1;//是
    const INVOICE_DISABLED = 0;//否

    //订单续费
    const RENEWAL_STATUS_PENDING = 0; //待续费
    const RENEWAL_STATUS_ALREADY = 1; //已续费
    const RENEWAL_STATUS_NO = 2; //无意向（订单不在跟进，跟进终止时）

    //是否续费订单
    const RENEWAL_ACTIVE = 1;//是
    const RENEWAL_DISABLED = 0;//否

    const SATISFACTION_UNSELECTED = 0;
    const SATISFACTION_FINE = 1;
    const SATISFACTION_GENERAL = 2;
    const SATISFACTION_NOT_GOOD = 3;

    const APPLY_DISABLE = 0;
    const APPLY_ACTIVE = 1;

    //服务状态标记
    const SERVICE_NO_BEGIN = 1;
    const SERVICE_HAVE_IN_HAND = 2;
    const SERVICE_COMPLETED =3;
    const SERVICE_SUSPEND = 4;
    const SERVICE_ABNORMAL = 5;

    public $order_id;
    public $service_id;
    public $service_name;
    public $administrator_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'virtual_order_id', 'product_id', 'flow_id', 'is_bargain',
                'address_product_id', 'province_id', 'city_id', 'district_id', 'is_proxy',
                'is_pay_after_service', 'customer_service_id', 'creator_id', 'supervisor_id',
                'clerk_id', 'industry_id', 'top_category_id', 'category_id', 'status', 'break_reason',
                'is_refund', 'refund_status', 'is_cancel', 'is_trademark', 'dispatch_time', 'created_at',
                'begin_service_time', 'complete_service_time', 'cancel_time', 'break_service_time', 'renewal_order_id',
                'renewal_status', 'service_cycle', 'begin_service_cycle', 'end_service_cycle', 'renewal_warn_time',
                'estimate_service_time', 'is_renewal', 'original_order_id', 'is_installment', 'business_subject_id', 'settlement_month', 'first_payment_time', 'company_id'
                , 'is_satisfaction', 'is_apply', 'is_contract_show', 'administrator_id', 'service_id','service_status','actual_profit_calculate','order_dispatch_time'], 'integer'],
//            [['service_status'], 'required'],
            [['price', 'require_refund_amount', 'refund_amount', 'tax', 'total_cost', 'total_cost', 'payment_amount'], 'number'],
            [['price_detail', 'order_id', 'service_name'], 'string'],
            [['sn'], 'string', 'max' => 16],
            [['product_name'], 'string', 'max' => 100],
            [['company_name'], 'string', 'max' => 255],
            [['province_name', 'city_name', 'district_name', 'industry_name'], 'string', 'max' => 15],
            [['customer_service_name', 'creator_name', 'supervisor_name', 'clerk_name', 'top_category_name', 'category_name'], 'string', 'max' => 10],
            [['refund_reason', 'refund_explain', 'refund_remark'], 'string', 'max' => 80],
            ['status', 'in', 'range' => array_keys(self::getStatusList()), 'message' => '订单状态不正确'],
            ['refund_status', 'in', 'range' => array_keys(self::getRefundStatusList()), 'message' => '退款状态不正确'],
            [['is_vest', 'is_invoice', 'sign', 'expected_profit_calculate'], 'boolean'],
            [['financial_code'], 'string', 'max' => 6],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],

            ],
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->sn = static::generateSn();
            } else {
                if (in_array($this->status, [self::STATUS_BREAK_SERVICE, self::STATUS_COMPLETE_SERVICE])
                    && !$this->is_vest && $this->customerService && $this->customerService->servicing_number > 0) {
                    $this->customerService->servicing_number -= 1;
                    $this->customerService->save(false);
                }
                if (in_array($this->status, [self::STATUS_BREAK_SERVICE, self::STATUS_COMPLETE_SERVICE])
                    && !$this->is_vest && $this->supervisor && $this->supervisor->servicing_number > 0) {
                    $this->supervisor->servicing_number -= 1;
                    $this->supervisor->save(false);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'virtual_order_id' => 'Virtual Order ID',
            'sn' => 'Sn',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'company_name' => 'Company Name',
            'flow_id' => 'Flow ID',
            'price' => 'Price',
            'payment_amount' => 'Payment Amount',
            'is_bargain' => 'Is Bargain',
            'price_detail' => 'Price Detail',
            'address_product_id' => 'Address Product ID',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'is_proxy' => 'Is Proxy',
            'is_pay_after_service' => 'Is Pay After Service',
            'customer_service_id' => 'Customer Service ID',
            'customer_service_name' => 'Customer Service Name',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'supervisor_id' => 'Supervisor ID',
            'supervisor_name' => 'Supervisor Name',
            'clerk_id' => 'Clerk ID',
            'clerk_name' => 'Clerk Name',
            'industry_id' => 'Industry ID',
            'industry_name' => 'Industry Name',
            'top_category_id' => 'Top Category ID',
            'top_category_name' => 'Top Category Name',
            'category_id' => 'Category ID',
            'category_name' => 'Category Name',
            'status' => 'Status',
            'break_reason' => 'Break Reason',
            'is_refund' => 'Is Refund',
            'is_contract_show' => 'Is Contract Show',
            'refund_reason' => 'Refund Reason',
            'refund_explain' => 'Refund Explain',
            'refund_remark' => 'Refund Remark',
            'require_refund_amount' => 'Require Refund Amount',
            'refund_amount' => 'Refund Amount',
            'tax' => 'Tax',
            'refund_status' => 'Refund Status',
            'is_cancel' => 'Is Cancel',
            'dispatch_time' => 'Dispatch Time',
            'created_at' => 'Created At',
            'begin_service_time' => 'Begin Service Time',
            'complete_service_time' => 'Complete Service Time',
            'cancel_time' => 'Cancel Time',
            'break_service_time' => 'Break Service Time',
            'is_invoice' => 'Is Invoice',
            'is_renewal' => 'Is Renewal',
            'renewal_order_id' => 'Renewal Order Id',
            'renewal_status' => 'Renewal Status',
            'service_cycle' => 'Service Cycle',
            'begin_service_cycle' => 'Begin Service Cycle',
            'end_service_cycle' => 'End Service Cycle',
            'renewal_warn_time' => 'Renewal Warn Time',
            'estimate_service_time' => 'Estimate Service Time',
            'original_order_id' => 'Original Order Id',
            'sign' => 'Sign',
            'company_id' => 'Company Id',
            'financial_code' => 'Financial Code',
            'expected_profit_calculate' => 'expected profit calculate',
            'service_status' => '服务状态标记',
        ];
    }

    /*
     * 自动分配客服的规则
     * 针对马甲订单
     */
    public function apportionCustomerService()
    {
        /** @var CustomerService $cs */
        $cs = CustomerService::find()->orderBy(['allot_number' => SORT_ASC])->where(['status' => CustomerService::STATUS_ACTIVE])->limit('1')->one();
        if (null != $cs) {
            $cs->allot_number += 1;
            $cs->save(false);
            $this->customer_service_id = $cs->id;
            $this->customer_service_department_id = $cs->administrator->department_id;
            $this->customer_service_name = $cs->name;
            $this->save(false);
        }
    }

    /*
     * 自动分配客服/
     */
    public function autoAssignCustomerService($company_id = 0)
    {
        $cs = null;
        if ($this->user->customerService && $this->user->customerService->company_id == $company_id) {
            /** @var CustomerService $cs */
            $cs = CustomerService::findOne($this->user->customer_service_id);
        } else {
            if (null == $cs) {
                $cs = CustomerService::find()
                    ->orderBy(['servicing_number' => SORT_ASC])
                    ->where(['status' => CustomerService::STATUS_ACTIVE, 'is_default_allot' => 1, 'company_id' => $company_id])
                    ->limit('1')->one();
                if (null == $cs) {
                    $cs = CustomerService::find()
                        ->orderBy(['servicing_number' => SORT_ASC])
                        ->where(['status' => CustomerService::STATUS_ACTIVE, 'company_id' => $company_id])
                        ->limit('1')->one();
                }
            }
        }
        if (null != $cs) {
            $this->assignCustomerService($cs);
        }
    }

    public function getInvoiceAmount()
    {
        $price = BC::sub($this->price, $this->refund_amount);
        return BC::sub($price, $this->deposit);
    }

    /**
     * 分配客服
     * @param CustomerService $customerService
     */
    public function assignCustomerService($customerService)
    {
        $updateOld = false;
        if ($this->customerService && $this->customerService->servicing_number > 0) {
            if (!in_array($this->status, [self::STATUS_BREAK_SERVICE, self::STATUS_COMPLETE_SERVICE]) && !$this->is_vest) {
                $this->customerService->servicing_number -= 1;
                $updateOld = true;
            }
        }
        if (!$this->is_vest) {
            $customerService->servicing_number += 1;
        }
        if ($this->user->customer_service_id != $customerService->id && !$this->is_vest) {
            if ($this->customerService && $this->customerService->assign_count > 0) {
                $this->customerService->assign_count -= 1;
                $updateOld = true;
            }
            $this->user->customer_service_id = $customerService->id;
            $customerService->assign_count += 1;
            $customerService->service_number += 1;
            $this->user->save(false);
        }
        $customerService->save(false);
        if ($updateOld) {
            $this->customerService->save();
        }
        $this->customer_service_id = $customerService->id;
        $this->customer_service_department_id = $customerService->administrator->department_id;
        $this->customer_service_name = $customerService->name;
        $this->save(false);
    }

    /**
     * 自动分配督导
     */
    public function autoAssignSupervisor()
    {
        if ($this->user->supervisor_id) {
            /** @var Supervisor $supervisor */
            $supervisor = Supervisor::findOne($this->user->supervisor_id);
        } else {
            /** @var Supervisor $supervisor */
            $supervisor = Supervisor::find()->orderBy(['servicing_number' => SORT_ASC])->where(['status' => CustomerService::STATUS_ACTIVE])->one();
        }
        if ($supervisor) {
            $this->assignSupervisor($supervisor);
        }
    }

    /**
     * 分配督导
     * @param Supervisor $supervisor
     */
    public function assignSupervisor($supervisor)
    {
        $updateOld = false;
        if ($this->supervisor && $this->supervisor->servicing_number > 0) {
            if (!in_array($this->status, [self::STATUS_BREAK_SERVICE, self::STATUS_COMPLETE_SERVICE]) && !$this->is_vest) {
                $this->supervisor->servicing_number -= 1;
                $updateOld = true;
            }
        }
        if (!$this->is_vest) {
            $supervisor->servicing_number += 1;
        }
        if ($this->user->supervisor_id != $supervisor->id && !$this->is_vest) {
            if ($this->supervisor && $this->supervisor->assign_count > 0) {
                $this->supervisor->assign_count -= 1;
            }
            $this->user->supervisor_id = $supervisor->id;
            $this->user->save(false);
            $supervisor->assign_count += 1;
        }
        $supervisor->save(false);
        if ($updateOld) {
            $this->supervisor->save();
        }
        $this->supervisor_id = $supervisor->id;
        $this->supervisor_department_id = $supervisor->administrator->department_id;
        $this->supervisor_name = $supervisor->name;
        $this->save(false);
    }

    public function refund($refund_amount, $is_cancel, $refund_reason, $refund_explain, $refund_remark, $virtual_order_id, $break_reason)
    {
        $records = $this->virtualOrder->payRecords;
        if ($this->virtualOrder->canRefundAmount() < $refund_amount) {
            throw new Exception('退款金额不能超出可退款金额。');
        }
        if (empty($records)) {
            throw new Exception('找不到该订单的支付记录。');
        }
        if ($this->virtualOrder->status != VirtualOrder::STATUS_ALREADY_PAYMENT && !$this->is_installment) {
            throw new Exception('该订单尚未付清，只能全部退款并取消。');
        }
        $needRefund = $refund_amount;
        foreach ($records as $payRecord) {
            if ($needRefund <= 0) break; // 需要退款金额小于等于0的时候不在继续
            $canRefundAmount = $payRecord->canRefundAmount();
            if ($canRefundAmount <= 0) continue;
            if ($canRefundAmount > $refund_amount || $canRefundAmount == $refund_amount) {
                // 该退款记录 退一部分 或 退完
                $payRecord->preRefund($refund_amount, $this);
                break;
            } else {
                // 不够，需要继续退其它支付记录
                $needRefund = BC::sub($needRefund, $canRefundAmount);
                $payRecord->preRefund($canRefundAmount, $this);
            }
        }
        //2，退款已审核
        $this->refund_status = $refund_amount > 0 ? Order::REFUND_STATUS_AUDITED : Order::REFUND_STATUS_REFUNDED;
        //修改退款时订单状态
        if ($is_cancel) {
            $this->cancel($break_reason);
            // 虚拟订单是否需要取消(如果该虚拟订单下的所有订单都取消了，就需要把虚拟订单也取消掉)
            $notCancelCount = Order::find()
                ->where(['virtual_order_id' => $virtual_order_id, 'is_cancel' => '0'])
                ->andWhere('id != :id', [':id' => $this->id])->count();
            if ($notCancelCount <= 0) {
                $this->virtualOrder->status = VirtualOrder::STATUS_BREAK_PAYMENT;
            }
        } else {
            // 退款不取消订单
            $this->is_cancel = 0;
            $this->cancel_time = 0;
        }
        // 虚拟订单中的退款数据
        $this->virtualOrder->refund_amount = BC::add($this->virtualOrder->refund_amount, $refund_amount);

        $this->is_refund = 1;
        $this->refund_reason = $refund_reason;
        $this->refund_explain = $refund_explain;
        $this->refund_remark = $refund_remark;
        $this->refund_amount = BC::add($this->refund_amount, $refund_amount);
        PerformanceRecord::createRecord($this->virtual_order_id, $this->id, 0, 0, 0, $this->refund_amount);
        $this->require_refund_amount = 0;
        $this->save(false);
        $this->virtualOrder->save(false);

        //新增后台操作日志
        AdministratorLog::logAuditRefund($this, $is_cancel);
    }

    public function canRefundAmount()
    {
        return min(BC::sub($this->price, $this->refund_amount), $this->virtualOrder->canRefundAmount());
    }

    public function isRefund()
    {
        return $this->is_refund == self::REFUND_ACTIVE;
    }

    public function isProxy()
    {
        return $this->is_proxy == self::PROXY_ACTIVE;
    }

    public function isPayAfterService()
    {
        return $this->is_pay_after_service == self::PAY_AFTER_SERVICE_ACTIVE;
    }

    public function isRenewal()
    {
        return $this->is_renewal == self::RENEWAL_ACTIVE;

    }

    public function canRenewal()
    {
        if ($this->status == self::STATUS_PENDING_ALLOT
            || $this->status == self::STATUS_PENDING_SERVICE
            || $this->status == self::STATUS_IN_SERVICE
            || $this->status == self::STATUS_COMPLETE_SERVICE) {
            return true;
        }
        return false;
    }

    /**
     * 退款是否取消订单，该取消并不意味着订单终止服务，
     * 有可能是用户申请了退款并要求取消订单，而后台未做审核。
     * @return bool
     */
    public function isCancel()
    {
        return $this->is_cancel == 1;
    }

    public function isBargain()
    {
        return $this->is_bargain == self::BARGAIN_ACTIVE;
    }

    //后台显示状态
    public function getStatusName()
    {
        $statusList = static::getStatusList();
        if ($this->isPayAfterService() && $this->status == self::STATUS_PENDING_PAY) {
            // 如果是待付款状态，并且是先服务后付费，则显示状态为待分配，其他状态不会出现这个问题。
            return $statusList[self::STATUS_PENDING_ALLOT];
        }
        return $statusList[$this->status];
    }

    //后台显示状态
    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING_PAY => '待付款',
            self::STATUS_PENDING_ALLOT => '待分配',
            self::STATUS_PENDING_SERVICE => '待服务',
            self::STATUS_IN_SERVICE => '服务中',
            self::STATUS_BREAK_SERVICE => '服务终止',
            self::STATUS_COMPLETE_SERVICE => '服务完成'
        ];
    }

    //后台显示订单终止原因
    public function getBreakReason()
    {
        $breakReasonList = static::getBreakReasonList();
        return $breakReasonList[$this->break_reason];
    }

    //后台订单终止原因列表
    public static function getBreakReasonList()
    {
        return [
            self::BREAK_REASON_NOT_FOLLOW => '停止跟进',
            self::BREAK_REASON_REFUND_AND_CANCEL => '退款并且取消',
            self::BREAK_REASON_USER_CANCEL => '客户主动取消',
            self::BREAK_REASON_OVERTIME_CLOSE => '订单关闭',
        ];
    }

    //仅用于客户显示状态
    public function getStatusNameForCustomer()
    {
        $statusList = static::getStatusListForCustomer();
        return $statusList[$this->status];
    }

    //仅用于客户显示状态
    public static function getStatusListForCustomer()
    {
        return [
            self::STATUS_PENDING_PAY => '待付款',
            self::STATUS_PENDING_ALLOT => '已付款',
            self::STATUS_PENDING_SERVICE => '已付款',
            self::STATUS_IN_SERVICE => '已付款',
            self::STATUS_BREAK_SERVICE => '已取消',
            self::STATUS_COMPLETE_SERVICE => '已付款'
        ];
    }

    public function getRefundStatusName()
    {
        $statusList = static::getRefundStatusList();
        return $statusList[$this->refund_status];
    }

    public static function getRefundStatusList()
    {
        return [

            self::REFUND_STATUS_NO => '未退款',
            self::REFUND_STATUS_APPLY => '用户申请退款',
            self::REFUND_STATUS_AUDITED => '退款已审核',
            self::REFUND_STATUS_REFUNDED => '已退款',
        ];
    }

    //后台显示订单来源
    public function getSourceAppName()
    {
        $statusList = static::getSourceAppList();
        return isset($statusList[$this->source_app]) ? $statusList[$this->source_app] : '';
    }

    //后台显示订单来源
    public static function getSourceAppList()
    {
        return [
            self::SOURCE_APP_PC => 'PC端下单',
            self::SOURCE_APP_WAP => '手机端下单',
            self::SOURCE_APP_WX => '微信端下单',
        ];
    }

    public static function generateSn()
    {
        static $index = 0;
        if ($index == 0) {
            $index = intval(rand(1, 7) . rand(0, 9) . rand(0, 9) . rand(0, 9));
        }
        list($year, $month, $day, $h, $i, $s) = explode('-', date('y-m-d-H-i-s'));
        // 2分+2日+2月+2时+2年+2秒+4数
        return $i . $day . $month . $h . $year . $s . ($index++);
    }

    //后台展示的退款原因
    public static function getRefundReasonList()
    {
        // 该数组不能从开头或中间添加内容，如果要调整顺序，只能为每个
        return [
            0 => '其他原因',
            1 => '客户多买/买错/不想要了',
            2 => '该服务无法满足客户的要求',
            3 => '客户对我方服务不满意'
        ];
    }

    //前台个人中心展示的退款原因
    public static function getCustomerRefundReasonList()
    {
        // 该数组不能从开头或中间添加内容，如果要调整顺序，只能为每个
        return [
            0 => '其他原因',
            1 => '多买/买错/不想要了',
            2 => '该服务无法满足要求',
            3 => '服务不满意'
        ];
    }

    //生成订单记录时获取客户退款原因,避免无原因时出现报错页面，默认0（代表其他原因）
    public static function getRefundReason($refund_reason)
    {
        $refund_reason = $refund_reason ? $refund_reason : 0;
        $list = self::getCustomerRefundReasonList();
        return $list[$refund_reason];
    }

    public function getRefundReasonText()
    {
        if ($this->refund_reason != '') {
            $list = static::getRefundReasonList();
            return $list[$this->refund_reason];
        }
        return null;
    }

    public function getUser()
    {
        return static::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrderBalanceRecord()
    {
        return static::hasOne(OrderBalanceRecord::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getOrderVoucher()
    {
        return static::hasOne(OrderVoucher::className(), ['order_id' => 'id']);
    }

    public function getVirtualOrder()
    {
        return static::hasOne(VirtualOrder::className(), ['id' => 'virtual_order_id']);
    }

    public function getNicheOrder()
    {
        return static::hasMany(NicheOrder::className(), ['order_id' => 'id']);
    }

    public function getAdjustOrderPrice()
    {
        return static::hasOne(AdjustOrderPrice::className(), ['order_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getProductPrice()
    {
        return static::hasOne(ProductPrice::className(), ['product_id' => 'product_id']);
    }

    public function getOrderEvaluate()
    {
        return static::hasOne(OrderEvaluate::className(), ['order_id' => 'id']);
    }

    public function getClerk()
    {
        return static::hasOne(Clerk::className(), ['id' => 'clerk_id']);
    }

    public function getSupervisor()
    {
        return static::hasOne(Supervisor::className(), ['id' => 'supervisor_id']);
    }

    public function getCustomerService()
    {
        return static::hasOne(CustomerService::className(), ['id' => 'customer_service_id']);
    }

    public function getFlow()
    {
        return static::hasOne(Flow::className(), ['id' => 'flow_id']);
    }

    public function getNextNode()
    {
        return static::hasOne(FlowNode::className(), ['flow_id' => 'flow_id', 'id' => 'next_node_id']);
    }

    public function getLastFlowRecord()
    {
        return static::hasOne(OrderFlowRecord::className(), ['order_id' => 'id', 'flow_node_id' => 'last_node_id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getFlowRecords()
    {
        return static::hasMany(OrderFlowRecord::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getOrderSms()
    {
        return static::hasMany(OrderSms::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getOrderTeams()
    {
        return static::hasMany(OrderTeam::className(), ['order_id' => 'id']);
    }

    public function getTrademark()
    {
        return static::hasOne(Trademark::className(), ['order_id' => 'id']);
    }

    public function getOrderFiles()
    {
        return static::hasMany(OrderFile::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getPerformanceRecord()
    {
        return static::hasMany(PerformanceRecord::className(), ['order_id' => 'id'])->orderBy(['year' => SORT_ASC, 'month' => SORT_ASC]);
    }

    public function getPerformanceStatistics()
    {
        return static::hasMany(PerformanceStatistics::className(), ['order_id' => 'id']);
    }

    public function getOrderRecords()
    {
        return static::hasMany(OrderRecord::className(), ['order_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getOrderCostRecord()
    {
        return static::hasMany(OrderCostRecord::className(), ['order_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function getLastOrderRecord()
    {
        return static::hasOne(OrderRecord::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getFinalOrderRecord()
    {
        return static::hasOne(OrderRecord::className(), ['order_id' => 'id'])->where(['is_internal' => OrderRecord::INTERNAL_DISABLED])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getInvoice()
    {
        return static::hasOne(Invoice::className(), ['order_id' => 'id']);
    }

    public function getClerkDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'clerk_department_id']);
    }

    public function getSalesmanDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'salesman_department_id']);
    }

    public function getSupervisorDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'supervisor_department_id']);
    }

    public function getCustomerServiceDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'customer_service_department_id']);
    }

    public function getSalesman()
    {
        return static::hasOne(Administrator::className(), ['id' => 'salesman_aid']);
    }

    public function getOrderRemarks()
    {
        return static::hasMany(OrderRemark::className(), ['order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getLastOrderFollowRecord()
    {
        return static::hasOne(OrderFollowRecord::className(), ['order_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    public function getOrderFollowRecords()
    {
        return static::hasMany(OrderFollowRecord::className(), ['order_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    public function getBusinessSubject()
    {
        return static::hasOne(BusinessSubject::className(), ['id' => 'business_subject_id']);
    }

    public function getSalesmanCompany()
    {
        return static::hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getExpectedProfits()
    {
        return static::hasMany(ExpectedProfitSettlementDetail::className(), ['order_id' => 'id']);
    }

    public function getOrderExpectedCost()
    {
        return static::hasMany(OrderExpectedCost::className(), ['order_id' => 'id']);
    }

    /**
     * @param int $renewal_order_id
     * @return null|static
     */
    public function getRelatedRenewalOrder($renewal_order_id)
    {
        $order = Order::findOne($renewal_order_id);
        return $order ? $order : null;
    }

    public function hasOrderFollowRecords()
    {
        return static::hasMany(OrderFollowRecord::className(), ['order_id' => 'id'])->limit(1)->count() > 0;
    }

    public function cancel($break_reason = 0)
    {
        $this->break_reason = $break_reason;
        $this->status = Order::STATUS_BREAK_SERVICE;
        $this->is_cancel = 1;
        $this->cancel_time = time();
        $this->break_service_time = $this->cancel_time;
    }

    public function startService()
    {
        if (!$this->isPendingService()) {
            throw new Exception('该订单不能开始服务。');
        }
        $this->begin_service_time = time();
        $this->status = Order::STATUS_IN_SERVICE;
        if ($this->flow) {
            $this->next_node_id = $this->flow->firstNode->id;
            if ($this->flow->firstNode->is_limit_time && $this->flow->firstNode->limit_work_days > 0) {
                $this->next_node_limited_time = Holidays::getEndTimeByDays($this->flow->firstNode->limit_work_days);
                $this->next_node_warn_time = Holidays::getEndTimeByDays($this->flow->firstNode->limit_work_days - 1);
            }
        }
        return $this->save(false);
    }

    /*
     * 订单状态为待付款
     */
    public function isPendingPay()
    {
        return $this->status == self::STATUS_PENDING_PAY;
    }

    /*
     * 订单状态为待分配
     */
    public function isPendingAllot()
    {
        // 未付款并且是后付费也为待分配
        return $this->status == self::STATUS_PENDING_ALLOT || ($this->isPendingPay() && $this->isPayAfterService());
    }

    /*
     * 订单状态为待服务
     */
    public function isPendingService()
    {
        return $this->status == self::STATUS_PENDING_SERVICE;
    }

    /*
     * 订单状态为服务中
     */
    public function isInService()
    {
        return $this->status == self::STATUS_IN_SERVICE;
    }

    /*
     * 订单状态为服务终止
     */
    public function isBreakService()
    {
        return $this->status == self::STATUS_BREAK_SERVICE;
    }

    /*
     * 订单状态为服务完成
     */
    public function isCompleteService()
    {
        return $this->status == self::STATUS_COMPLETE_SERVICE;
    }


    /*
     * 订单退款状态为未退款
     */
    public function isNoRefund()
    {
        return $this->refund_status == self::REFUND_STATUS_NO;
    }

    /*
     * 订单退款状态为申请中
     */
    public function isRefundApply()
    {
        return $this->refund_status == self::REFUND_STATUS_APPLY;
    }

    /*
     * 订单退款状态为已审核
     */
    public function isRefundAudit()
    {
        return $this->refund_status == self::REFUND_STATUS_AUDITED;
    }

    /*
     * 订单退款状态为已退款
     */
    public function isRefunded()
    {
        return $this->refund_status == self::REFUND_STATUS_REFUNDED;
    }

    //待续费
    public function isPendingRenewal()
    {
        return $this->renewal_status == self::RENEWAL_STATUS_PENDING;
    }

    //已续费
    public function isAlreadyRenewal()
    {
        return $this->renewal_status == self::RENEWAL_STATUS_ALREADY;
    }

    //无意向（订单不在跟进，跟进终止时）
    public function isNoRenewal()
    {
        return $this->renewal_status == self::RENEWAL_STATUS_NO;
    }

    /**
     * 流程是否操作完成
     * @return bool
     */
    public function flowIsFinish()
    {
        return $this->flow_is_finish == 1;
    }

    /**
     * 是否为报警订单
     */
    public function isWarning()
    {
        if (in_array($this->status, [Order::STATUS_BREAK_SERVICE, Order::STATUS_COMPLETE_SERVICE, Order::STATUS_PENDING_SERVICE, Order::STATUS_PENDING_ALLOT])) {
            return false;
        }
        $time = time();
        if ($this->next_node_warn_time > 0 && $this->next_node_warn_time < $time && $this->status != Order::STATUS_COMPLETE_SERVICE) {
            return true;
        }
        if ($this->next_follow_time > 0 && $this->next_follow_time) {
            return true;
        }
        return false;
    }

    /**
     * 是否为续费报警订单
     */
    public function isRenewalWarning()
    {
        $time = time();
        if ($this->isRenewal() && $this->renewal_warn_time > 0 && $this->renewal_warn_time < $time && $this->renewal_order_id <= 0) {
            return true;
        }
        return false;
    }

    /**
     * 已续费订单
     */
    public function isAlreadyRenewalOrder()
    {
        if ($this->isRenewal() && $this->renewal_order_id > 0) {
            return true;
        }
        return false;
    }

    /**
     * 是否已经开具发票
     * @return bool
     */
    public function isInvoiced()
    {
        return $this->is_invoice == self::INVOICE_ACTIVE;
    }

    /**
     * @param FlowNode $currentNode
     * @return array|null
     */
    public function getHintOperator($currentNode)
    {
        if ($this->lastFlowRecord) // 只有最后流程记录存在，才能做上面的逻辑判断
        {
            $hint = null;
            if ($this->lastFlowRecord->flow_node_id == $currentNode->id && $this->lastFlowRecord->action->isStay()) // 是停留的
            {
                // 需要内容替换，返回最后一次操作（action）的提示信息
                $hint = $this->lastFlowRecord->action->getHintOperator();
            } else if ($currentNode->id == $this->next_node_id) // 非停留的，并且是按顺序的下次操作
            {
                // 需要内容替换，返回当前节点提示信息，但需要使用上次操作的变量替换
                $hint = $currentNode->getHintOperator();
            }
            if (null != $hint) {
                $hint = $this->replaceHintParams($hint, $this->lastFlowRecord);
                return $hint;
            }
        }
        // 直接返回节点提示即可
        $hint = $this->replaceHintParams($currentNode->getHintOperator());
        return $hint;
    }

    /**
     * @param FlowNode $currentNode
     * @return array|null
     */
    public function getHintCustomer($currentNode)
    {
        if ($this->lastFlowRecord) // 只有最后流程记录存在，才能做上面的逻辑判断
        {
            $hint = null;
            if ($this->lastFlowRecord->flow_node_id == $currentNode->id && $this->lastFlowRecord->action->isStay()) // 是停留的
            {
                // 需要内容替换，返回最后一次操作（action）的提示信息
                $hint = $this->lastFlowRecord->action->getHintCustomer();
            } else if ($currentNode->id == $this->next_node_id) // 非停留的，并且是按顺序的下次操作
            {
                // 需要内容替换，返回当前节点提示信息，但需要使用上次操作的变量替换
                $hint = $currentNode->getHintCustomer();
            }
            if (null != $hint) {
                $hint = $this->replaceHintParams($hint, $this->lastFlowRecord);
                return $hint;
            }
        }
        // 直接返回节点提示即可
        $hint = $this->replaceHintParams($currentNode->getHintCustomer());
        return $hint;
    }

    /**
     * @param array $hint
     * @param OrderFlowRecord $flowRecord
     * @return mixed
     */
    private function replaceHintParams($hint, $flowRecord = null)
    {
        if (null != $this->clerk) {
            $address = '地址：' . $this->clerk->address . ', 收件人：' . $this->clerk->name . ', 电话：' . $this->clerk->phone;
        } else {
            $address = '';
        }
        if ($flowRecord) {
            // 日期内容替换
            $hint['title'] = str_replace('%日期%', $flowRecord->input_date, $hint['title']);
            $hint['content'] = str_replace('%日期%', $flowRecord->input_date, $hint['content']);
            foreach ($flowRecord->getInputText() as $item) {
                // 文本框内容替换
                $hint['title'] = str_replace('%' . $item['label'] . '%', $item['text'], $hint['title']);
                $hint['content'] = str_replace('%' . $item['label'] . '%', $item['text'], $hint['content']);
            }
        } else {
            if (false !== strpos($hint['title'], '%日期%') || false !== strpos($hint['content'], '%日期%')) {
                $hint['title'] = '';
                $hint['content'] = '';
                return $hint;
            }
        }
        // 邮寄地址替换
        $hint['title'] = str_replace('%邮寄地址%', $address, $hint['title']);
        $hint['content'] = str_replace('%邮寄地址%', $address, $hint['content']);
        return $hint;
    }

    /**
     * 退款中数量(已审核和待退款)
     * @param Administrator $administrator
     * @return int
     */
    public static function getOrderAuditedCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->innerJoinWith(['user u']);
        $query->andWhere(['in', 'o.refund_status', [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]]);
        $query->andWhere(['o.is_vest' => '0']);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 退款中数量（待审核）
     * @param Administrator $administrator
     * @return int
     */
    public static function getOrderRefundReviewCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->innerJoinWith(['user u']);
        $query->andWhere(['o.refund_status' => Order::REFUND_STATUS_APPLY]);
        $query->andWhere(['o.is_vest' => '0']);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 退款中数量(已审核，待退款)
     * @param Administrator $administrator
     * @return int
     */
    public static function getOrderNeedRefundCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->innerJoinWith(['user u']);
        $query->andWhere(['or', ['o.refund_status' => Order::REFUND_STATUS_AUDITED], ['vo.refund_status' => VirtualOrder::REFUND_STATUS_PENDING_REFUND]]);
        $query->andWhere(['o.is_vest' => '0']);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 待付款数量（根据管理员获取）
     * @param Administrator $administrator
     * @return int
     */
    public static function getPendingPayCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->innerJoinWith(['virtualOrder vo']);
            $query->innerJoinWith(['user u']);
            $query->andWhere(['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]);
            $query->andWhere(['vo.is_vest' => '0']);
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;

    }

    /**
     * 未付清数量（根据管理员获取）
     * @param Administrator $administrator
     * @return int
     */
    public static function getUnpaidCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->innerJoinWith(['virtualOrder vo']);
            $query->innerJoinWith(['user u']);
            $query->andWhere(['vo.status' => VirtualOrder::STATUS_UNPAID]);
            $query->andWhere(['vo.is_vest' => '0']);
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;

    }

    /**
     * 待分配数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getPendingAssignCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->innerJoinWith(['virtualOrder vo']);
            $query->innerJoinWith(['user u']);
            $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
                ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY]])
                ->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            $query->andWhere(['o.is_vest' => '0']);
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;

    }

    /**
     * 待服务数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getPendingServiceCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->innerJoinWith(['virtualOrder vo']);
            $query->innerJoinWith(['user u']);
            $query->andWhere(['o.status' => Order::STATUS_PENDING_SERVICE])
                ->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            $query->andWhere(['o.is_vest' => '0']);
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;
    }

    /**
     * 报警数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getTimeoutCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->innerJoinWith(['virtualOrder vo']);
            $query->innerJoinWith(['user u']);
            $query->andWhere(['or',
                'o.next_node_warn_time > 0 and o.next_node_warn_time < :current_time and o.status!=:status_complete_service',
                //'o.next_follow_time > 0 and o.next_follow_time < :current_time' // 去掉未付款下次跟进提醒订单统计
            ],
                [':current_time' => time(), ':status_complete_service' => Order::STATUS_COMPLETE_SERVICE])
                ->andWhere(['not in', 'o.status', [
                    Order::STATUS_BREAK_SERVICE,
                    Order::STATUS_COMPLETE_SERVICE,
                    Order::STATUS_PENDING_SERVICE,
                    Order::STATUS_PENDING_ALLOT,
                    Order::STATUS_PENDING_PAY,// 去掉未付款下次跟进提醒订单统计
                ]])
                ->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            $query->andWhere(['o.is_vest' => '0']);
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;
    }

    /**
     * 全部订单数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getAllCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->andWhere(['o.is_vest' => '0']);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 服务中订单数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getInServiceCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.status' => Order::STATUS_IN_SERVICE]);
        $query->andWhere(['o.is_vest' => '0'])
            ->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 服务完成订单数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getCompletedCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.status' => Order::STATUS_COMPLETE_SERVICE]);
        $query->andWhere(['o.is_vest' => '0']);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 待计算业绩的订单
     * @return int
     */
    public static function getApplyCount()
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.is_apply' => Order::APPLY_ACTIVE]);
        $query->andWhere(['o.is_vest' => '0']);
//        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 服务完成订单数量
     * @param Administrator $administrator
     * @return int
     */
    public static function getBreakCount($administrator)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.status' => Order::STATUS_BREAK_SERVICE]);
        $query->andWhere(['o.is_vest' => '0'])
            ->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
        Order::filterRole($query, $administrator);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 待评价数量(根据用户获取)
     * @param int $uer_id
     * @return int
     */
    public static function getPendingEvaluateCount($uer_id)
    {
        $count = Order::find()
            ->where(['user_id' => $uer_id, 'is_evaluate' => Order::EVALUATE_DISABLED])
            ->andWhere(['in', 'status', [Order::STATUS_COMPLETE_SERVICE, Order::STATUS_IN_SERVICE]])
            ->andWhere(['is_vest' => '0'])
            ->count();
        return $count ? $count : 0;
    }

    /**
     * 可申请发票订单数量
     * @param User $user
     * @return int
     */
    public static function getInvoiceCounts($user)
    {
        $count = Order::find()
            ->where(['user_id' => $user->id, 'is_invoice' => Order::INVOICE_DISABLED])
            ->andWhere(['in', 'status', [
                Order::STATUS_PENDING_ALLOT,
                Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE,
                Order::STATUS_COMPLETE_SERVICE,
            ]])
            ->andWhere(['in', 'refund_status', [
                Order::REFUND_STATUS_NO,
                Order::REFUND_STATUS_REFUNDED,
            ]])
            ->andWhere(['is_cancel' => Order::CANCEL_DISABLED])
            ->andWhere(['or', ['complete_service_time' => 0], ['>', 'complete_service_time', time() - 90 * 86400]])
            ->count();
        return $count ? $count : 0;
    }

    /**
     * @param User $user
     * @return int
     */
    public static function getInvoiceAmounts($user)
    {
        $orders = Order::find()
            ->where(['user_id' => $user->id, 'is_invoice' => Order::INVOICE_DISABLED])
            ->andWhere(['in', 'status', [
                Order::STATUS_PENDING_ALLOT,
                Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE,
                Order::STATUS_COMPLETE_SERVICE,
            ]])
            ->andWhere(['in', 'refund_status', [
                Order::REFUND_STATUS_NO,
                Order::REFUND_STATUS_REFUNDED,
            ]])
            ->andWhere(['is_cancel' => Order::CANCEL_DISABLED])
            ->andWhere(['or', ['complete_service_time' => 0], ['>', 'complete_service_time', time() - 90 * 86400]])
            ->all();
        $totalAmounts = 0;
        /** @var Order $order */
        foreach ($orders as $order) {
            $totalAmounts += $order->getInvoiceAmount();
        }
        return $totalAmounts;
    }

    /**
     * 全部待认领订单数量
     * @return int
     */
    public static function getAllReceiveCount()
    {
        $query = Order::find()->alias('o')->innerJoinWith(['virtualOrder vo'])->andWhere(['o.salesman_aid' => 0]);
        $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
            ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY],
            ['o.refund_status' => Order::REFUND_STATUS_APPLY],
            ['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT],
            ['vo.status' => VirtualOrder::STATUS_UNPAID]]);
        $count = $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]])->count();
        return $count ? $count : 0;
    }

    /**
     * 认领订单记录数量
     * @return int
     */
    public static function getAllReceiveRecordCount()
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->innerJoinWith(['orderVoucher ov']);

        $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
            ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY],
            ['o.status' => Order::STATUS_PENDING_SERVICE],
            ['o.status' => Order::STATUS_IN_SERVICE],
            ['o.status' => Order::STATUS_COMPLETE_SERVICE],
            ['o.status' => Order::STATUS_BREAK_SERVICE],
            ['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT],
            ['vo.status' => VirtualOrder::STATUS_UNPAID]]);

        $count = $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]])->count();

        return $count ? $count : 0;
    }

    public static function getReceiveRecordCount()
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->innerJoinWith(['orderVoucher ov']);
        $count = $query->andWhere(['in', 'o.refund_status', [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]])->count();
        return $count ? $count : 0;
    }

    public function getCurrentNode($node_id = null)
    {
        $currentNode = null;
        if ($this->flow) {
            if ($this->flowIsFinish()) {
                $currentNode = $this->flow->lastNode;
            } else {
                foreach ($this->flow->nodes as $key => $node) {
                    if (null == $currentNode && $node->id == $this->next_node_id) {
                        $currentNode = $node;
                    }
                    if (null != $node_id && $node_id == $node->id) {
                        $currentNode = $node;
                    }
                }
                if (null == $currentNode) {
                    $currentNode = $this->flow->firstNode;
                }
            }
        }
        return $currentNode;
    }

    /**
     * @param Administrator $administrator
     * @return boolean
     */
    public function isSubFor($administrator)
    {
        if (!$administrator->isLeader() && !$administrator->isDepartmentManager()) return false;
        if ($administrator->type == Administrator::TYPE_CLERK) {
            if (0 == $this->clerk_department_id) return false;
            $id = $this->clerk_department_id;
        } elseif ($administrator->type == Administrator::TYPE_CUSTOMER_SERVICE) {
            if (0 == $this->customer_service_department_id) return false;
            $id = $this->customer_service_department_id;
        } elseif ($administrator->type == Administrator::TYPE_SALESMAN) {
            if (0 == $this->salesman_department_id) return false;
            $id = $this->salesman_department_id;
        } elseif ($administrator->type == Administrator::TYPE_SUPERVISOR) {
            if (0 == $this->supervisor_department_id) return false;
            $id = $this->supervisor_department_id;
        } else {
            return false;
        }

        if ($id == $administrator->department_id) return true;

        return 0 < CrmDepartment::find()->where("path like '" . $administrator->department->path . "-%'")
                ->andWhere(['id' => $id])->count();
    }

    /**
     * @param Administrator $administrator
     * @return boolean
     */
    public function isBelongs($administrator)
    {
        if ($administrator->type == Administrator::TYPE_ADMIN) {
            return true;
        } else {
            if ($administrator->type == Administrator::TYPE_CLERK) {
                if ($administrator->clerk->id == $this->clerk_id) return true;
            } elseif ($administrator->type == Administrator::TYPE_CUSTOMER_SERVICE) {
                if ($administrator->customerService->id == $this->customer_service_id) return true;
            } elseif ($administrator->type == Administrator::TYPE_SALESMAN) {
                if ($administrator->id == $this->salesman_aid) return true;
            } elseif ($administrator->type == Administrator::TYPE_SUPERVISOR) {
                if ($administrator->supervisor->id == $this->supervisor_id) return true;
            }
        }

        return $this->isSubFor($administrator);
    }

    /**
     * @param ActiveQuery $query
     * @param Administrator $administrator
     */
    public static function filterRole($query, $administrator)
    {
        if ($administrator->isLeader() || $administrator->isDepartmentManager()) {
//            //判断是否区分公司
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['o.company_id' => $administrator->company_id]);
//            }

            if ($administrator->type == Administrator::TYPE_CLERK) {
//                $clerkDepartments = CrmDepartment::find()->select('id')->where("path like '". $administrator->department->path."-%'")->asArray()->all();
//                $clerkDepartmentIds = ArrayHelper::getColumn($clerkDepartments, 'id');
//                $clerkDepartmentIds[] = $administrator->department_id;
//                $orderIds = Order::find()->select('id')->where(['clerk_id' => $administrator->clerk->id])->andWhere(['not in','company_id',$administrator->company_id])->asArray()->all();//有关系的订单
//                $_orders = Order::find()->select('id')->where(['in', 'clerk_department_id', $clerkDepartmentIds])->asArray()->all();
//                $orderIds = ArrayHelper::merge(ArrayHelper::getColumn($_orders, 'id'),ArrayHelper::getColumn($orderIds, 'id'));
//                $query->andWhere(['in', 'o.id', $orderIds]);
                // 因性能问题，把下面的代码注释掉替换为上面优化过的查询
                $query->leftJoin("(SELECT `id`,`path` FROM " . CrmDepartment::tableName() . " WHERE path LIKE  '" . $administrator->department->path . "-%') as d", 'd.id = o.clerk_department_id');
                $query->andWhere(['or',
                    "d.path like '" . $administrator->department->path . "-%'",
                    ['d.id' => $administrator->department_id],
                    ['o.clerk_department_id' => $administrator->department_id],
                    ['o.clerk_id' => $administrator->clerk->id]]);
            } elseif ($administrator->type == Administrator::TYPE_CUSTOMER_SERVICE) {
//                $customerServiceDepartments = CrmDepartment::find()->select('id')->where("path like '". $administrator->department->path."-%'")->asArray()->all();
//                $customerServiceDepartmentIds = ArrayHelper::getColumn($customerServiceDepartments, 'id');
//                $customerServiceDepartmentIds[] = $administrator->department_id;
//                $orderIds = Order::find()->select('id')->where(['customer_service_id' => $administrator->customerService->id])->andWhere(['not in','company_id',$administrator->company_id])->asArray()->all();//有关系的订单
//                $_orders = Order::find()->select('id')->where(['in', 'customer_service_department_id', $customerServiceDepartmentIds])->asArray()->all();
//                $orderIds = ArrayHelper::merge(ArrayHelper::getColumn($_orders, 'id'),ArrayHelper::getColumn($orderIds, 'id'));
//                $query->andWhere(['in', 'o.id', $orderIds]);
                // 因性能问题，把下面的代码注释掉替换为上面优化过的查询
                $query->leftJoin("(SELECT `id`,`path` FROM " . CrmDepartment::tableName() . " WHERE path LIKE  '" . $administrator->department->path . "-%') as d", 'd.id = o.customer_service_department_id');
                $query->andWhere(['or',
                    "d.path like '" . $administrator->department->path . "-%'",
                    ['o.customer_service_department_id' => $administrator->department_id],
                    ['d.id' => $administrator->department_id],
                    ['o.customer_service_id' => $administrator->customerService->id]]);
            } elseif ($administrator->type == Administrator::TYPE_SALESMAN) {
//                $_orderTeams = OrderTeam::find()->select('order_id')->where(['administrator_id' => $administrator->id])->asArray()->all();
//                $orderIds = Order::find()->select('id')->where(['salesman_aid' => $administrator->id])->andWhere(['not in','company_id',$administrator->company_id])->asArray()->all();//有关系的订单
//                $orderIds = ArrayHelper::merge(ArrayHelper::getColumn($_orderTeams, 'order_id'),ArrayHelper::getColumn($orderIds, 'id'));
//                $salesmanDepartments = CrmDepartment::find()->select('id')->where("path like '". $administrator->department->path."-%'")->asArray()->all();
//                $salesmanDepartmentIds = ArrayHelper::getColumn($salesmanDepartments, 'id');
//                $salesmanDepartmentIds[] = $administrator->department_id;
//                $_orders = Order::find()->select('id')->where(['in', 'salesman_department_id', $salesmanDepartmentIds])->asArray()->all();
//                $orderIds = ArrayHelper::merge(ArrayHelper::getColumn($_orders, 'id'), $orderIds);
//                $query->andWhere(['in', 'o.id', $orderIds]);
                // 因性能问题，把下面的代码注释掉替换为上面优化过的查询
                $query->leftJoin("(SELECT `id`,`path` FROM " . CrmDepartment::tableName() . " WHERE path LIKE '" . $administrator->department->path . "-%') as d", 'd.id = salesman_department_id');
                $query->leftJoin("(SELECT `order_id`,`administrator_id` FROM " . OrderTeam::tableName() . " WHERE administrator_id = " . $administrator->id . ") as ot", '`ot`.order_id = o.id');
                $query->andWhere(['or',
                    "d.path LIKE '" . $administrator->department->path . "-%'",
                    ['d.id' => $administrator->department_id],
                    ['o.salesman_aid' => $administrator->id],
                    ['ot.administrator_id' => $administrator->id],
                    ['o.salesman_department_id' => $administrator->department_id]]);
            } elseif ($administrator->type == Administrator::TYPE_SUPERVISOR) {
//                $supervisorDepartments = CrmDepartment::find()->select('id')->where("path like '". $administrator->department->path."-%'")->asArray()->all();
//                $supervisorDepartmentIds = ArrayHelper::getColumn($supervisorDepartments, 'id');
//                $supervisorDepartmentIds[] = $administrator->department_id;
//                $query->andWhere(['in', 'o.supervisor_department_id', $supervisorDepartmentIds]);

                // 因性能问题，把下面的代码注释掉替换为上面优化过的查询
                $query->leftJoin("(SELECT `id`,`path` FROM " . CrmDepartment::tableName() . " WHERE path LIKE  '" . $administrator->department->path . "-%') as d", 'd.id = o.supervisor_department_id');
                $query->andWhere(['or',
                    "d.path like '" . $administrator->department->path . "-%'",
                    ['d.id' => $administrator->department_id],
                    ['o.supervisor_department_id' => $administrator->department_id],
                    ['o.supervisor_id' => $administrator->supervisor->id]]);
            } elseif ($administrator->type == Administrator::TYPE_ADMIN && $administrator->isBelongCompany() && $administrator->company_id) {
                $query->andWhere(['o.company_id' => $administrator->company_id]);//领导管理员角色区分公司
            }
        } else {
            if ($administrator->type == Administrator::TYPE_CLERK) {
                $query->andWhere(['o.clerk_id' => $administrator->clerk->id]);
            } elseif ($administrator->type == Administrator::TYPE_CUSTOMER_SERVICE) {
                $query->andWhere(['o.customer_service_id' => $administrator->customerService->id]);
            } elseif ($administrator->type == Administrator::TYPE_SALESMAN) {
//                $_orders = Order::find()->select('id')->where(['salesman_aid' => $administrator->id])->asArray()->all();
//                $_orderTeams = OrderTeam::find()->select('order_id')->where(['administrator_id' => $administrator->id])->asArray()->all();
//                $orderIds = ArrayHelper::getColumn($_orderTeams, 'order_id');
//                $orderIds = ArrayHelper::merge($orderIds, ArrayHelper::getColumn($_orders, 'id'));
//                $query->andWhere(['in', 'o.id', $orderIds]);

                // 因性能问题，把下面的代码注释掉替换为上面优化过的查询
                $query->leftJoin("(SELECT `order_id`,`administrator_id` FROM " . OrderTeam::tableName() . " WHERE administrator_id = " . $administrator->id . ") as ot", '`ot`.order_id = o.id');
                $query->andWhere(['or', ['o.salesman_aid' => $administrator->id], ['ot.administrator_id' => $administrator->id]]);
            } elseif ($administrator->type == Administrator::TYPE_SUPERVISOR) {
                $query->andWhere(['o.supervisor_id' => $administrator->supervisor->id]);
            } elseif ($administrator->type == Administrator::TYPE_ADMIN && $administrator->isBelongCompany() && $administrator->company_id) {
                $query->andWhere(['o.company_id' => $administrator->company_id]);//非领导管理员只能看本公司下的订单
            }
        }
    }

    public function getArea()
    {
        if (!empty($this->service_area)) {
            return $this->service_area;
        }
        return $this->province_name . '-' . $this->city_name . '-' . $this->district_name;
    }

    public function isProductOffline()
    {
        if ($this->virtualOrder->isPendingPayment() && $this->product->status == Product::STATUS_OFFLINE) return true;
        return false;
    }

    public function getStatus()
    {
        if ($this->isRefundApply() || $this->isRefundAudit()) {
            $status = $this->getRefundStatusName();
            $status .= '<br>退款原因：' . $this->getRefundReasonText();
            if ($this->isRefundAudit()) {
                $status .= '<br>退款金额：' . Yii::$app->formatter->asCurrency($this->refund_amount);
            }
            if ($this->isRefundApply()) {
                $status .= '<br>要求退款：' . Yii::$app->formatter->asCurrency($this->require_refund_amount);
            }
            if (!empty($this->refund_remark)) {
                $status .= '<br>说明：' . $this->refund_explain;
            }
            if ($this->isRefundAudit() && !empty($this->refund_remark)) {
                $status .= '<br>备注：' . $this->refund_remark;
            }
        } elseif ($this->virtualOrder->isUnpaid()) {
            $not_paid_off = '未付清-已付金额：' . $this->virtualOrder->payment_amount . '未付金额:' . $this->virtualOrder->getPendingPayAmount();
            $status = $not_paid_off;
        } elseif ($this->isInService()) {
            if ($this->flow) {
                $flowHint = $this->getHintOperator($this->getCurrentNode());
                $status = $flowHint['content'];
            } else {
                $status = '服务中';
            }
        } elseif ($this->virtualOrder->isCanceled()) {
            if ($this->isBreakService() && $this->break_reason > 0) {
                $status = $this->getBreakReason();
            } else {
                $status = '已取消';
            }
        } else {
            if ($this->isBreakService() && $this->break_reason > 0) {
                $status = $this->getBreakReason();
            } else {
                $status = $this->getStatusName();

            }

            if ($this->isRefund()) {
                $status .= '【' . $this->getRefundStatusName() . '】';
            }
        }
        return $status;
    }

    public function getProxy()
    {
        if ($this->is_proxy == self::PROXY_DISABLED) {
            return '否';
        }
        return '是';
    }

    public function getServiceDays()
    {
        if ($this->complete_service_time && $this->begin_service_time) {
            return ceil(BC::div(BC::sub($this->complete_service_time, $this->begin_service_time, 0), 86400, 7));
        }
        return null;
    }

    public function getCompleteServiceTime()
    {
        if (!empty($this->complete_service_time)) {
            return Yii::$app->formatter->asDatetime($this->complete_service_time);
        }
        return null;
    }

    public function getBeginServiceTime()
    {
        if (!empty($this->begin_service_time)) {
            return Yii::$app->formatter->asDatetime($this->begin_service_time);
        }
        return null;
    }

    public function isAdjustStatusNotAdjust()
    {
        return $this->adjust_status == AdjustOrderPrice::STATUS_NOT_ADJUST;
    }

    public function isAdjustStatusPending()
    {
        return $this->adjust_status == AdjustOrderPrice::STATUS_PENDING;
    }

    public function isAdjustStatusReject()
    {
        return $this->adjust_status == AdjustOrderPrice::STATUS_REJECT;
    }

    public function isAdjustStatusPass()
    {
        return $this->adjust_status == AdjustOrderPrice::STATUS_PASS;
    }

    public function renewalDate()
    {
        $time = time();
        $oneDay = 86400;
        $days = floor(BC::div(BC::sub($this->end_service_cycle, $time, 2), $oneDay, 2));
        return $days ? $days : '';
    }

    //统计所有商品的交易的排行top-10
    public function tradingVolume($limit = 10, $status = 0)
    {
        $order = self::find()
            ->select(['COUNT(*) AS num,any_value(product_name) as product_name'])
            ->andWhere(['not in', 'status', [Order::STATUS_PENDING_PAY, Order::STATUS_BREAK_SERVICE]])
            ->groupBy('product_id')
            ->orderBy(['num' => SORT_DESC])
            ->limit($limit)
            ->asArray()
            ->all();
        $resultData = $this->handleData($order, $status);
        return $resultData;
    }

    //处理数据
    public function handleData($orders, $status)
    {
        $product_name = [];
        $num = [];
        foreach ($orders as $key => $order) {
            $product_name[] = $order['product_name'];
            $num[] = $order['num'];
        }
        if ($status) {
            return $product_name;
        }
        return $num;
    }

    public function canRefund()
    {
        return
            !$this->isRefund() &&
            !$this->isRefundApply() &&
            !$this->isRefundAudit() &&
            ($this->virtualOrder->canRefundAmount() > 0) &&
            ($this->isPendingAllot() || $this->isPendingService() || $this->isInService());
    }

    public function getRenewalOrdersQuery()
    {
        /** @var RenewalProductRelated $renewalModel */
        $renewalModel = RenewalProductRelated::find()->where(['like', 'product_ids', $this->product_id])->orderBy(['id' => SORT_ASC])->one();
        if (null != $renewalModel) {
            $query = Order::find();
            /** @var ActiveQuery $query */
            $query->andWhere(['user_id' => $this->user_id])//1.当前用户
            ->andWhere(['in', 'product_id', $renewalModel->getProductIds()])//2.此商品关联其他商品对应的订单（关联包内的所有商品）
            ->andWhere(['in', 'status', [Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE, Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE]])//3.已经付款（虚拟订单）
            ->andWhere('created_at >= :time', [':time' => $this->created_at])//4.时间必须大于被续费订单（当前订单，下单时间）
            ->andWhere(['original_order_id' => 0]);//没有被其他订单关联过（原续费订单id，大于0不能被关联续费使用）
            if (!empty($keyword)) {
                $query->andWhere(['like', 'sn', $keyword]);
            }
            $query->andWhere(['not in', 'id', $this->id]);//不能关联自己
            return $query;
        }
        return null;
    }

    /**
     * 待续费订单数量（根据管理员获取）
     * @param Administrator $administrator
     * @return int
     */
    public static function getPendingRenewalCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->andWhere(['o.is_renewal' => Order::RENEWAL_ACTIVE]);
            $query->andWhere(['in', 'o.status', [
                Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE
            ]]);
            $query->andWhere(['o.renewal_status' => Order::RENEWAL_STATUS_PENDING]);
            $query->andWhere('o.renewal_warn_time <' . time());
            $query->andWhere('o.renewal_warn_time > 0');
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;
    }

    /**
     * 已续费订单数量（根据管理员获取）
     * @param Administrator $administrator
     * @return int
     */
    public static function getAlreadyRenewalCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->andWhere(['o.is_renewal' => Order::RENEWAL_ACTIVE]);
            $query->andWhere(['in', 'o.status', [
                Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE
            ]]);
            $query->andWhere(['o.renewal_status' => Order::RENEWAL_STATUS_ALREADY]);
            $query->andWhere('o.renewal_order_id > 0');
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;
    }

    /**
     * 无意向续费订单数量（根据管理员获取）
     * @param Administrator $administrator
     * @return int
     */
    public static function getNoRenewalCount($administrator)
    {
        static $count;
        if (null === $count) {
            $query = Order::find()->alias('o');
            $query->andWhere(['o.is_renewal' => Order::RENEWAL_ACTIVE]);
            $query->andWhere(['in', 'o.status', [
                Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE
            ]]);
            $query->andWhere(['o.renewal_status' => Order::RENEWAL_STATUS_NO]);
            Order::filterRole($query, $administrator);
            $count = $query->count();
        }
        return $count;
    }

    /**
     * @param $user_id
     * @return Query $query
     */
    public static function getPaidQueryByUserId($user_id)
    {
        return Order::find()->alias('o')
            ->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.user_id' => $user_id])
            ->andWhere(['or', ['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT], ['vo.status' => VirtualOrder::STATUS_UNPAID]]);
    }

    /**
     * @param $user_id
     * @return Query $query
     */
    public static function getPendingPayQueryByUserId($user_id)
    {
        return Order::find()->alias('o')
            ->andWhere(['o.user_id' => $user_id])
            ->andWhere(['o.status' => Order::STATUS_PENDING_PAY]);
    }

    /**
     * @param $user_id
     * @return Query $query
     */
    public static function getBreakQueryByUserId($user_id)
    {
        return Order::find()->alias('o')
            ->andWhere(['o.user_id' => $user_id])
            ->andWhere(['o.status' => Order::STATUS_BREAK_SERVICE]);
    }

    /**
     * @param $subject_id
     * @return Query $query
     */
    public static function getPendingPayQueryBySubjectId($subject_id)
    {
        return Order::find()->alias('o')
            ->andWhere(['o.business_subject_id' => $subject_id])
            ->andWhere(['o.status' => Order::STATUS_PENDING_PAY]);
    }

    /**
     * @param $subject_id
     * @return Query $query
     */
    public static function getPaidQueryBySubjectId($subject_id)
    {
        return Order::find()->alias('o')
            ->innerJoinWith(['virtualOrder vo'])
            ->andWhere(['o.business_subject_id' => $subject_id])
            ->andWhere(['or', ['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT], ['vo.status' => VirtualOrder::STATUS_UNPAID]]);
    }

    /**
     * @param $subject_id
     * @return Query $query
     */
    public static function getBreakQueryBySubjectId($subject_id)
    {
        return Order::find()->alias('o')
            ->andWhere(['o.business_subject_id' => $subject_id])
            ->andWhere(['o.status' => Order::STATUS_BREAK_SERVICE]);
    }

    /**
     * 预计利润
     * @return string
     */
    public function expectedTotalProfit()
    {
        if (empty($this->total_cost)) return $this->total_cost;
        $expected_profit = BC::sub($this->price, $this->total_cost);
        if ($this->is_refund) {
            return BC::sub($expected_profit, $this->refund_amount); //如果有退款减掉退款
        }
        return $expected_profit;
    }

    /**
     * 获取负责人的分成比例
     * @return integer
     */
    public function getDivideRate()
    {
        $rate = 0;
        if ($this->orderTeams) {
            foreach ($this->orderTeams as $team) {
                $rate += floatval($team->divide_rate);
            }
        }
        return BC::sub(100, $rate);
    }

    /**
     * 用于订单列表、商机订单-查看详情
     * @return bool
     */
    public function hasDetail()
    {
        if (($this->isRefundApply()
                || $this->isInService()
                || $this->isCompleteService()
                || $this->isBreakService())
            || $this->isPendingService()
            || $this->isPendingAllot()
            && ($this->virtualOrder->payment_amount >= $this->virtualOrder->total_amount || $this->isPayAfterService())) {
            return true;
        }
        return false;
    }

    /**
     * 获取业绩提成金额
     */
    public function getSalary()
    {
        $salary = 0;
        if ($this->performanceStatistics) {
            foreach ($this->performanceStatistics as $item) {
                $salary += floatval($item->performance_reward);
            }
        }
        return $salary;
    }

    public static function getSatisfaction()
    {
        return [
            self::SATISFACTION_FINE => '满意',
            self::SATISFACTION_GENERAL => '一般',
            self::SATISFACTION_NOT_GOOD => '不满意'
        ];
    }

    public static function getServiceStatus()
    {
        return [
            self::SERVICE_NO_BEGIN => '未开始',
            self::SERVICE_HAVE_IN_HAND => '进行中',
            self::SERVICE_COMPLETED => '已完成',
            self::SERVICE_SUSPEND => '暂停',
            self::SERVICE_ABNORMAL => '异常归档',
        ];
    }

    public function getSatisfactionName()
    {
        $satisfaction = self::getSatisfaction();
        if ($this->is_satisfaction) {
            return $satisfaction[$this->is_satisfaction];
        }
        return null;
    }

    public function getServiceStatusName()
    {
        $service_status = self::getServiceStatus();
        if ($this->service_status) {
            return $service_status[$this->service_status];
        }
        return null;
    }


    //订单所剩业绩
    public function getSurplusPerformance()
    {
        $performance = 0;
        if ($this->performanceRecord) {
            foreach ($this->performanceRecord as $item) {
                $performance += $item->lavePerformance();
            }
            return $performance;
        }
        return $performance;
    }

    public function getShareSalesman()
    {
        $data = null;
        if ($this->orderTeams) {
            foreach ($this->orderTeams as $team) {
                $data .= '/' . $team->administrator_name;
            }
        }
        return $data;
    }

    //子订单已付款总数
    public function getTotalAmount()
    {
        $total = Order::find()->where(['virtual_order_id' => $this->virtual_order_id])->sum('payment_amount');
        return $total;
    }

    //当月预计利润结算过之后，更换的业务员需要做下标记。
    public function sign()
    {
        if ($this->settlement_month) {
            $year = substr($this->settlement_month, 0, 4);
            $month = substr($this->settlement_month, 4, 2);
            /** @var MonthProfitRecord $record */
            $record = MonthProfitRecord::find()->where(['year' => $year, 'month' => $month])->limit('1')->one();

            if ($record && $record->isFinish()) {
                $this->sign = 1;
                $this->save(false);
            }
        }
    }

    //获取虚拟订单下子订单预计成本总和
    public function getTotalExpectedCost()
    {
        $total = OrderExpectedCost::find()->where(['virtual_order_id' => $this->virtual_order_id])->sum('cost_price');
        return $total ? $total : 0;
    }

    public function getPendingPayAmount()
    {
        return BC::sub($this->price, $this->payment_amount);
    }

    /**
     * @return bool
     */
    public function isUnpaid()
    {
        return floatval($this->payment_amount) && floatval($this->payment_amount) !== floatval($this->price);
    }

    //子订单预计成本
    public function getExpectedCost()
    {
        $total = OrderExpectedCost::find()->where(['order_id' => $this->id])->sum('cost_price');
        return $total ? $total : null;
    }

    //获取子订单预计利润
    public function getExpectedProfit()
    {
        $expectedProfit = ExpectedProfitSettlementDetail::find()->where(['order_id' => $this->id])->sum('expected_profit');
        return $expectedProfit ? $expectedProfit : 0;
    }

    //获取未分配订单预计成本的订单总金额
    public function getSurplusPrice()
    {
        $order_ids = OrderExpectedCost::find()->select('order_id')->where(['virtual_order_id' => $this->virtual_order_id])->groupBy('order_id')->asArray()->all();
        $ids = ArrayHelper::getColumn($order_ids, 'order_id');
        $totalPrice = Order::find()->where(['not in', 'id', $ids])->andWhere(['virtual_order_id' => $this->virtual_order_id])->sum('price');
        return $totalPrice ? $totalPrice : 0;
    }

    //获取未分配订单成本的订单总金额
    public function getSurplusCostPrice()
    {
        $order_ids = OrderCostRecord::find()->select('order_id')->where(['virtual_order_id' => $this->virtual_order_id])->groupBy('order_id')->asArray()->all();
        $ids = ArrayHelper::getColumn($order_ids, 'order_id');
        $totalPrice = Order::find()->where(['not in', 'id', $ids])->andWhere(['virtual_order_id' => $this->virtual_order_id])->sum('price');
        return $totalPrice ? $totalPrice : 0;
    }

    public function getCalculatePerformance()
    {
        $calculated_performance = PerformanceStatistics::find()->where(['order_id' => $this->id])->sum('calculated_performance');
        return $calculated_performance ? $calculated_performance : 0;
    }

    //子订单剩下的预计利润
    public function getSurplusProfit()
    {
        $expected_profit = $this->getExpectedProfits()->sum('expected_profit');
        $price = $this->getExpectedCost() == null ? $this->price : BC::sub($this->price, $this->getExpectedCost());
        $profits = BC::sub($price, $expected_profit);
        return floatval($profits) ? $profits : 0;
    }

    //子订单成本
    public function getCost()
    {
        $total = OrderCostRecord::find()->where(['order_id' => $this->id])->sum('cost_price');
        return $total ? $total : 0;
    }


    //批量更换客服人员
    public function updateCustomerService()
    {

        $department_id = Administrator::find()->where(['id' => $this->administrator_id])->one();

        $ids = explode(",",$this->order_id);

        $order = Order::find()->select('id,customer_service_id,customer_service_name,customer_service_department_id')->where(['in', 'id', $ids])->all();

        $rs = [];
        foreach ($order as $key => $val) {

            $val->customer_service_id = $this->service_id;
            $val->customer_service_name = $this->service_name;
            $val->customer_service_department_id = $department_id->department_id;
            $rs = $val->save(false);
            $department_name = isset($department_id->department->name) ? $department_id->department->name : '--';
            OrderRecord::create($val->id, '状态-修改客服', "修改客服人员为：{$department_name}的{$department_id->name}；", Yii::$app->user->identity, 0, 1);

        }



        return $rs;

    }


    //批量修改服务人员
    public function updateOrderClerk(){
        $department_id = Administrator::find()->select('id,department_id')->where(['id' => $this->administrator_id])->one();

        $ids = explode(",",$this->order_id);

        $order = Order::find()->select('id,clerk_id,clerk_name,clerk_department_id')->where(['in', 'id', $ids])->all();

        $rs = [];
        foreach ($order as $key => $val) {

            $val->clerk_id = $this->clerk_id;
            $val->clerk_name = $this->clerk_name;
            $val->clerk_department_id = $department_id->department_id;
            $rs = $val->save(false);

            OrderRecord::create($val->id, '修改服务人员', "更换服务人员为：{$this->clerk_name}", Yii::$app->user->identity, 0, 1);

        }
        return $rs;
    }

    //修改服务状态标记
    public function serviceStatusUpdate(){

        $order = Order::find() -> where(['id' => $this -> order_id]) -> one();

        $order -> service_status = $this -> service_status;

        if ($order ->save(false)){
            if ($this -> service_status == 1){
                $service_status = '未开始';
            }elseif ($this -> service_status == 2){
                $service_status = '进行中';
            }elseif ($this -> service_status == 3){
                $service_status = '已完成';
            }elseif ($this -> service_status == 4){
                $service_status = '暂停';
            }elseif ($this -> service_status == 5){
                $service_status = '异常归档';
            }else{
                $service_status  = '--';
            }
            OrderRecord::create($this -> order_id, '修改服务状态标记', "服务状态标记更新为：{$service_status}；", Yii::$app->user->identity, 0, 1);
        }
        
        return $order;
    }


}
