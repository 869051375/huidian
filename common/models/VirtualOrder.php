<?php

namespace common\models;

use backend\models\OrderExpectedCost;
use backend\models\VirtualOrderCost;
use backend\models\VirtualOrderExpectedCost;
use common\models\Contract;//合同表
use common\utils\BC;
use common\utils\MobileDetect;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "{{%virtual_order}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $package_id
 * @property string $package_name
 * @property string $sn
 * @property integer $status
 * @property integer $total_original_amount
 * @property integer $total_remit_amount
 * @property integer $wx_remit_amount
 * @property integer $coupon_remit_amount
 * @property integer $package_remit_amount
 * @property integer $adjust_amount
 * @property integer $refund_status
 * @property integer $is_need_invoice
 * @property string $total_amount
 * @property string $total_tax
 * @property string $payment_amount
 * @property string $refund_amount
 * @property string $cash
 * @property integer $payment_time
 * @property integer $is_vest
 * @property integer $created_at
 *
 * @property integer coupon_id
 * @property integer mode
 * @property integer code_type
 * @property integer coupon_code
 *
 * @property Order[] $orders
 * @property Order $order
 * @property PayRecord[] $payRecords
 * @property FollowRecord[] $followRecords
 * @property User $user
 *
 * @property Product $packageProduct
 * @property CrmOpportunity[] $opportunities
 * @property Receipt[] $receipt
 * @property Receipt $singleReceipt
 * @property VirtualOrderExpectedCost[] $virtualOrderExpectedCost
 * @property VirtualOrderCost[] $virtualOrderCost
 *
 * @property Contract $contract
 *
 * @property ExpectedProfitSettlementDetail[] $correctRecord
 */
class VirtualOrder extends ActiveRecord
{
    const STATUS_PENDING_PAYMENT = 0;//待付款
    const STATUS_ALREADY_PAYMENT = 1;//已付款
    const STATUS_UNPAID = 2;//未付清
    const STATUS_BREAK_PAYMENT = 3;//已取消

    const REFUND_STATUS_NONE = 0;//无退款
    const REFUND_STATUS_PENDING_REFUND = 2;//待退款
    const REFUND_STATUS_REFUNDED = 3;//退款完成

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%virtual_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'payment_time', 'created_at', 'coupon_id', 'mode', 'code_type', 'coupon_code'], 'integer'],
            [['total_amount', 'total_tax', 'payment_amount', 'refund_amount', 'cash'], 'number'],
            [['sn'], 'string', 'max' => 17],
            [['is_need_invoice', 'is_proxy'], 'boolean'],
            ['is_vest', 'boolean']
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'sn' => 'Sn',
            'status' => 'Status',
            'total_amount' => 'Total Amount',
            'total_tax' => 'Total Tax',
            'payment_amount' => 'Payment Amount',
            'refund_amount' => 'Refund Amount',
            'cash' => 'Cash',
            'is_need_invoice' => 'Is Need Invoice',
            'payment_time' => 'Payment Time',
            'created_at' => 'Created At',
        ];
    }

    public static function findByUserId($id, $user_id)
    {
        return static::find()->where(['id' => $id, 'user_id' => $user_id])->one();
    }

    // 可退款金额
    public function canRefundAmount()
    {
        return BC::sub($this->payment_amount, $this->refund_amount);
    }

    // 待支付金额
    public function getPendingPayAmount()
    {
        if(!($this->isPendingPayment() || $this->isUnpaid())) return 0;
        return BC::sub($this->total_amount, $this->payment_amount);
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
                $this->sn = static::generateSn();
            return true;
        }
        return false;
    }

    /*
     * 虚拟订单状态为待付款
     */
    public function isPendingPayment()
    {
        return $this->status == self::STATUS_PENDING_PAYMENT;

    }

    /*
     * 虚拟订单状态为已付款
     */
    public function isAlreadyPayment()
    {
        return $this->status == self::STATUS_ALREADY_PAYMENT;
    }

    /*
     * 虚拟订单状态为未付清
     */
    public function isUnpaid()
    {
        return $this->status == self::STATUS_UNPAID;
    }

    /*
     * 虚拟订单状态为已取消
     */
    public function isCanceled()
    {
        return $this->status == self::STATUS_BREAK_PAYMENT;
    }


    public static function generateSn()
    {
        list($year, $month, $day, $h, $i, $s) = explode('-', date('y-m-d-H-i-s'));
        // 2分+2日+2月+2时+2年+2秒+4数
        return 'V'.$i.$day.$month.$h.$year.$s.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
    }

    public function getOrders()
    {
        return self::hasMany(Order::className(), ['virtual_order_id' => 'id']);
    }

    public function getCorrectRecord()
    {
        return self::hasMany(ExpectedProfitSettlementDetail::className(), ['virtual_order_id' => 'id'])->where(['type' => ExpectedProfitSettlementDetail::TYPE_CORRECT]);
    }

    /*
     * @param Administrator $administrator
     * @return Order[]
     *
    public function getOrdersByAdministrator($administrator)
    {
        $query = self::hasMany(Order::className(), ['virtual_order_id' => 'id']);
        $categoryIds = $administrator->getProductCategoryIds();
        if(!empty($categoryIds) && $categoryIds[0] != '0')
        {
            if($administrator->type == Administrator::TYPE_CLERK)
            {
                $query->andWhere(['or', ['{{%order}}.clerk_id' => $administrator->clerk->id], ['in', '{{%order}}.top_category_id', $categoryIds]]);
            }
            elseif($administrator->type == Administrator::TYPE_CUSTOMER_SERVICE)
            {
                // 暂时不对客服做订单限制
                // $query->andWhere(['{{%order}}.customer_service_id' => $administrator->customerService->id]);
                $query->andWhere(['in', '{{%order}}.top_category_id', $categoryIds]);
            }
            elseif($administrator->type == Administrator::TYPE_SALESMAN)
            {
                $query->andWhere(['or', ['{{%order}}.creator_id' => $administrator->id], ['{{%order}}.salesman_aid' => $administrator->id], ['in', '{{%order}}.top_category_id', $categoryIds]]);
            }
            elseif($administrator->type == Administrator::TYPE_SUPERVISOR)
            {
                $query->andWhere(['or', ['{{%order}}.supervisor_id' => $administrator->supervisor->id], ['in', '{{%order}}.top_category_id', $categoryIds]]);
            }
            else
            {
                $query->andWhere(['in', '{{%order}}.top_category_id', $categoryIds]);
            }
            return $query->all();
        }
        else
        {
            if($administrator->type == Administrator::TYPE_CLERK)
            {
                $query->andWhere(['{{%order}}.clerk_id' => $administrator->clerk->id]);
            }
            elseif($administrator->type == Administrator::TYPE_CUSTOMER_SERVICE)
            {
                // 暂时不对客服做订单限制
                // $query->andWhere(['{{%order}}.customer_service_id' => $administrator->customerService->id]);
                if(empty($categoryIds))
                    $query->andWhere('1!=1');
            }
            elseif($administrator->type == Administrator::TYPE_SALESMAN)
            {
                $query->andWhere(['or', ['{{%order}}.creator_id' => $administrator->id], ['{{%order}}.salesman_aid' => $administrator->id]]);
            }
            elseif($administrator->type == Administrator::TYPE_SUPERVISOR)
            {
                $query->andWhere(['{{%order}}.supervisor_id' => $administrator->supervisor->id]);
            }
            else
            {
                if(empty($categoryIds))
                    $query->andWhere('1!=1');
            }
            return $query->all();
        }
    }*/

    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrder()
    {
        return self::hasOne(Order::className(), ['virtual_order_id' => 'id']);
    }

    /**
     * @param $payment_amount
     * @param $is_auto
     * is_auto是否自动分配回款
     * @return bool
     */
    public function payment($payment_amount,$is_auto = 1)
    {
        $dailyStatistics = new DailyStatistics();
        $productStatistics = new ProductStatisticsDetailed();
        if($is_auto == Receipt::SEPARATE_MONEY_ACTIVE)
        {
            $this->separateMoney($payment_amount);//是否自动分配回款
        }
        $this->payment_amount = BC::add($payment_amount, $this->payment_amount);
        if($this->payment_amount > $this->total_amount)
        {
            return false;
        }

        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        if(!($admin instanceof Administrator))
        {
            $admin = null;
        }
        if ($this->payment_amount < $this->total_amount)
        {
            // 只付一部分
            $description = '订单本次支付金额：'.$payment_amount.'元，剩余支付金额：'.BC::sub($this->total_amount, $this->payment_amount).'元';
            Remind::create(Remind::CATEGORY_1, '您已支付部分款项', $description, $this->user_id, null);
            $this->status = VirtualOrder::STATUS_UNPAID;
            foreach($this->orders as $order)
            {
                OrderStatusStatistics::totalStatusNum($order->product_id,$order->district_id,'pending_allot_no');//统计订单状态-未付清
                if(empty($order->first_payment_time))
                {
                    $order->first_payment_time = time();
                }
                if($order->is_installment)
                {
                    // 如果是分期付款产品，只要第一笔款项到达将订单变为先服务后付费订单。
                    $order->is_pay_after_service = 1;
                    $order->save(false);
                }
                $order->save(false);
            }
//            if(count($this->orders) == 1 && $this->orders[0]->is_installment)
//            {
//                $order = $this->orders[0];
//                // 如果是分期付款产品，只要第一笔款项到达将订单变为先服务后付费订单。
//                $order->is_pay_after_service = 1;
//                $order->save(false);
//            }
        }
        else
        {
            // 付款完成
            $this->status = VirtualOrder::STATUS_ALREADY_PAYMENT;
            //统计付款数量
            $dailyStatistics->total('pay_success_no');
            //统计付款用户数
            $dailyStatistics->DayUv('pay_user_no');
            foreach($this->orders as $order)
            {
                if($order->status == Order::STATUS_PENDING_PAY)
                {
                    // 处理续费订单逻辑
                    if($order->original_order_id)
                    {
                        $originalOrder = Order::findOne($order->original_order_id);
                        if($originalOrder)
                        {
                            $originalOrder->renewal_order_id = $order->id;
                            $originalOrder->renewal_status = Order::RENEWAL_ACTIVE;
                            $originalOrder->save(false);
                        }
                    }
                    if($order->isRenewal() && $order->renewal_status == Order::RENEWAL_STATUS_ALREADY)
                    {
                        //统计续费订单每天的数量
                        $dailyStatistics->total('renewal_order_no');
                    }
                    if(empty($order->first_payment_time))
                    {
                        $order->first_payment_time = time();
                    }
                    //业绩记录
                    //PerformanceRecord::createRecord($order->virtual_order_id,$order->id,$order->price,0,0,0);
                    //统计付款金额
                    $dailyStatistics->sumPrice('pay_price',$order->price);
                    //商品统计 - 支付成功的订单
                    $productStatistics->total($order->product_id,$order->district_id,'pay_success_num');
                    //商品统计 - 金额
                    $productStatistics->sumPrice($order->product_id,$order->district_id,'total_price',$order->price);
                    $order->status = Order::STATUS_PENDING_ALLOT;
                    OrderStatusStatistics::totalStatusNum($order->product_id,$order->district_id,'pending_allot_no');//统计订单状态-待分配
                    $order->save(false);

                    // 商机状态
                    foreach($this->opportunities as $opportunity)
                    {
                        $opportunity->status = CrmOpportunity::STATUS_DEAL;
                        $opportunity->progress = 100;
                        $opportunity->save(false);
                    }

                    //虚拟订单付款完成，为每条实际订单生成一条站内系统提醒
                    Remind::create(Remind::CATEGORY_2, '订单支付成功', null, $this->user_id, $order);
                }

                //修改库存限制商品库存数量
                if($order->product->isInventoryLimit())
                {
                    $order->product->inventory_qty-=1;
                    if($order->product->inventory_qty <= 0)
                    {
                        $order->product->inventory_qty = 0;
                    }
                    $order->product->save(false);
                    if($order->product->inventory_qty <= 0)
                    {
                        $order->product->status = Product::STATUS_OFFLINE;
                        $order->product->save(false);
                    }
                }
            }
        }
        $this->payment_time = time();
        $this->save(false);
    }

    public function getPayRecords()
    {
        return self::hasMany(PayRecord::className(), ['virtual_order_id' => 'id']);
    }

    public function getFollowRecords()
    {
        return static::hasMany(FollowRecord::className(), ['virtual_order_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    public function hasFollowRecords()
    {
        return static::hasMany(FollowRecord::className(), ['virtual_order_id' => 'id'])->limit(1)->count() > 0;
    }

    public function getReceipt()
    {
        return $this->hasMany(Receipt::className(),['virtual_order_id' => 'id']);
    }

    public function getSingleReceipt()
    {
        return $this->hasOne(Receipt::className(),['virtual_order_id' => 'id'])->where(['status' => Receipt::STATUS_NO])->orderBy(['id' => SORT_DESC]);
    }



    public function getVirtualOrderExpectedCost()
    {
        return $this->hasMany(VirtualOrderExpectedCost::className(),['virtual_order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getVirtualOrderCost()
    {
        return $this->hasMany(VirtualOrderCost::className(),['virtual_order_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getContract()
    {
        return $this->hasOne(Contract::className(),['virtual_order_id' => 'id']);
    }

    public function cancel($break_reason = 0)
    {
        $this->status = static::STATUS_BREAK_PAYMENT;
        $this->save(false);
        foreach ($this->orders as $order)
        {
            $order->cancel($break_reason);
            $order->save(false);
        }
        $this->returnCoupon();
    }

    public function refund()
    {
        $hasRefund = false;
        foreach ($this->payRecords as $payRecord)
        {
            $canRefundAmount = $payRecord->canRefundAmount();
            if($canRefundAmount > 0)
            {
                $payRecord->preRefund($canRefundAmount);
                $hasRefund = true;
            }
        }
        if($hasRefund)
        {
            $this->refund_status = static::REFUND_STATUS_PENDING_REFUND;
            $this->save(false);
        }
    }

    /**
     * @param array $items 一个包含 product、product_price、qty 的一个数组
     * @param User $user
     * @param boolean $isNeedInvoice 是否需要发票，默认不要
     * @param boolean $isProxy 是否为代客下单
     * @param Administrator $administrator 代客下单管理员
     * @param int $coupon_id 优惠券id
     * @param string $coupon_code 固定码/随机码
     * @param int $mode 优惠券类型，1优惠券，2优惠码
     * @param int $code_type 优惠码类型,1固定码，2随机码
     * @param int $created_at 下单时间
     * @param int $coupon_user_id 用户领取优惠券表id
     * @param int $subject_id 主体信息id
     * @param int $is_contract_show 合同类型订单是否显示
     * @return VirtualOrder
     * @throws \Exception
     */
    public static function createNew(&$items, $user, $isNeedInvoice = false, $isProxy = false, $administrator = null, $coupon_id = null, $coupon_code = null, $mode = null, $code_type = null, $coupon_user_id = null ,$created_at = 0, $subject_id = 0 ,$is_contract_show = 1)
    {
        $isPackage = false;
        $package = null;
        $product_price = null;
        foreach($items as $key => $item)
        {
            /** @var Product $product */
            $product = $item['product'];
            if($product->isPackage())
            {
                if(count($items) > 1)
                    throw new BadRequestHttpException('您的请求有误。');
                $isPackage = true;
                $package = $product;
                $product_price = $item['product_price'];
                break;
            }
        }
        if($isPackage)
        {
            $vo = static::createNewPackage($package, $product_price, $user, $isNeedInvoice, $isProxy, $administrator, $coupon_id, $coupon_code, $mode, $code_type, $coupon_user_id,$created_at,$subject_id,$is_contract_show);
        }
        else
        {
            $vo = static::createNewNormal($items, $user, $isNeedInvoice, $isProxy, $administrator, $coupon_id, $coupon_code, $mode, $code_type, $coupon_user_id,$created_at,$subject_id,$is_contract_show);
        }

        if($administrator && !$user->is_vest)
        {
            CrmCustomerCombine::addTeam($administrator, $vo->user->customer);
        }

        return $vo;
    }

    /**
     * @param Product $package
     * @param ProductPrice $productPrice
     * @param User $user
     * @param boolean $isNeedInvoice 是否需要发票，默认不要
     * @param boolean $isProxy 是否为代客下单
     * @param Administrator $administrator 代客下单管理员
     * @return VirtualOrder
     * @throws \Exception
     * @param int $coupon_id 优惠券id
     * @param string $coupon_code 固定码/随机码
     * @param int $mode 优惠券类型，1优惠券，2优惠码
     * @param int $code_type 优惠码类型,1固定码，2随机码
     * @param int $created_at 下单时间
     * @param int $coupon_user_id 用户领取优惠券表id
     * @param int $subject_id 主体信息id
     * @param int $is_contract_show 合同类型订单是否显示
     */
    private static function createNewPackage($package, $productPrice, $user, $isNeedInvoice = false, $isProxy = false, $administrator = null, $coupon_id = null, $coupon_code = null, $mode = null, $code_type = null, $coupon_user_id = null ,$created_at = 0 ,$subject_id = 0,$is_contract_show)
    {
        $productStatistics = new ProductStatisticsDetailed();
        $dailyStatistics = new DailyStatistics();
        $vo = new VirtualOrder();
        $vo->user_id = $user->id;
        $vo->is_vest = $user->is_vest;
        $vo->is_need_invoice = $isNeedInvoice;
        $vo->package_id = $package->id;
        $vo->package_name = $package->name;

        $totalAmount = 0;
        $detect = new MobileDetect();
        $sourceApp = Order::SOURCE_APP_PC;
        $isWX = $detect->match('MicroMessenger');
        if($isWX)
        {
            // 微信下单优惠逻辑
            $sourceApp = Order::SOURCE_APP_WX;
            $vo->wx_remit_amount = $package->wx_remit_amount;
        }
        else if($detect->isMobile())
        {
            $sourceApp = Order::SOURCE_APP_WAP;
        }

        if($package->isAreaPrice())
        {
            $vo->total_amount = BC::add($productPrice->price, $package->deposit);
            $totalTax = $productPrice->tax;
        }
        else
        {
            $vo->total_amount = BC::add($package->price, $package->deposit);
            $totalTax = $package->tax;
        }
        if($isNeedInvoice)
        {
            $vo->total_tax = $totalTax;
            $vo->total_amount = BC::add($vo->total_amount, $vo->total_tax);
        }
        $vo->total_amount = BC::sub($vo->total_amount, $vo->wx_remit_amount);
        if($vo->is_vest)
        {
            $vo->status = VirtualOrder::STATUS_ALREADY_PAYMENT;
            $vo->payment_amount = $vo->total_amount;
        }
        $vo->total_original_amount = 0;
        $vo->created_at = empty($created_at) ? time() : $created_at;
        $vo->save(false);
        $dailyStatistics->DayUv('order_user_no');
        $i = 0;
        foreach($package->packageProductList as $packageProductItem)
        {
            $product = $packageProductItem->product;
            $i++;

            $dailyStatistics->countOrder($sourceApp);
            $order = new Order();
            $order->source_app = $sourceApp;
            $order->wx_remit_amount = 0;
            $order->virtual_order_id = $vo->id;
            $order->user_id = $user->id;
            $order->is_vest = $user->is_vest;
            $order->is_proxy = $isProxy;
            $order->is_contract_show = $is_contract_show;

            $p = null;
            $service_area = '';
            if($product->isAreaPrice())
            {
                $p = $product->getProductPriceByDistrict($productPrice->district_id);
                $order->original_price = $p->price;
                $priceDetail = $p->price_detail;
                $dailyStatistics->sumPrice('order_price',$p->price);
            }
            else
            {
                $order->original_price = $product->price;
                $service_area = $product->service_area;
                $priceDetail = $product->price_detail;
                $dailyStatistics->sumPrice('order_price',$product->price);
            }
            $vo->total_original_amount = BC::add($order->original_price, $vo->total_original_amount);
            //子订单价格，根据套餐销售价比例得出
            $order->price = $package->getPackageProductPrice($product, $productPrice, $isWX);
            //对套餐下的商品价格误差进行操作，把差价加到最后一个子订单价格上(避免计算出现误差的情况)
            $totalAmount = BC::add($order->price, $totalAmount);
            if($i >= count($package->packageProducts))
            {
                $deviation = BC::sub($vo->total_amount, $totalAmount);
                $order->price = BC::add($order->price, $deviation);
            }
            $order->package_remit_amount = BC::sub($order->original_price, $order->price);

            if($isProxy)
            {
                $order->creator_id = $administrator->id; // 代理下单管理员id
                $order->creator_name = $administrator->name; // 代理下单管理员name
                $order->salesman_aid = $administrator->id; // 所属业务管理员id
                $order->company_id = $administrator->company_id; // 所属公司id
                $order->salesman_department_id = $administrator->department_id; // 所属业务管理员id
                $order->salesman_name = $administrator->name; // 所属业务管理员name
            }
            if($product->isRenewal())
            {
                $order->service_cycle = $product->service_cycle;
            }
            else
            {
                $order->service_cycle = 0;
            }
            $order->product_id = $product->id;
            $order->product_name = $product->name;
            $order->flow_id = $product->flow_id;
            $order->is_renewal = $product->is_renewal;
            $order->is_bargain = $product->is_bargain;
            $order->is_pay_after_service = 0;
            $order->is_trademark = $product->is_trademark;
            $order->top_category_id = $product->top_category_id;
            $order->top_category_name = $product->topCategory->name;
            $order->category_id = $product->category_id;
            $order->category_name = $product->category->name;
            $order->deposit = $product->deposit;
            $order->is_installment = $product->is_installment;
            $order->service_area = $service_area;
            if(null != $p)
            {
                $order->province_id = $p->province_id;
                $order->province_name = $p->province_name;
                $order->city_id = $p->city_id;
                $order->city_name = $p->city_name;
                $order->district_id = $p->district_id;
                $order->district_name = $p->district_name;
            }
            if($product->type == Product::TYPE_ADDRESS)
            {
                $order->address_product_id = $product->id;
            }
            else
            {
                $order->address_product_id = 0;
            }

            if($order->is_vest)
            {
                $order->status = Order::STATUS_IN_SERVICE;
                $order->company_name = '';
                $order->apportionCustomerService();
            }

            //选择业务主体
            $businessModel = BusinessSubject::getSubject($user,$subject_id,$isProxy);
            if(isset($businessModel))
            {
                $order->business_subject_id = $businessModel->id;
                if($businessModel->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED)
                {
                    $order->company_name = $businessModel->company_name ? $businessModel->company_name : '';
                }
            }
            else
            {
                $order->company_name = '';
            }

            $order->created_at = empty($created_at) ? time() : $created_at;
            $order->save(false);
            OrderStatusStatistics::totalStatusNum($product->id,$order->district_id,'pending_pay_no');//统计订单状态-待付款
            $productStatistics->total($product->id,$order->district_id,'product_order_num');//商品统计-下单商品数
            OrderData::createData($order->id,$priceDetail);
            //自动分配督导
            $order->autoAssignSupervisor();
            //是代客下单自动该公司下的客户//
            if($isProxy && !$vo->is_vest)
            {
               $order->autoAssignCustomerService($administrator->company_id);
            }
//            if($administrator && $administrator->type == Administrator::TYPE_CUSTOMER_SERVICE && $administrator->customerService && empty($user->customer_service_id))
//            {
//                // 是客服代客下单，并且客户是首次下单，自动分配当前客服为客户的专属客服
//                $order->autoAssignCustomerService($administrator->customerService->id);
//            }
//            else
//            {
//                $order->autoAssignCustomerService();
//            }
            $cmd = Yii::$app->db->createCommand();
            // 更新交易量
            $cmd->update(Product::tableName(), ['traded' => new Expression('traded+:qty', [':qty' => 1])], ['id' => $product->id])->execute();
        }
        $cmd = Yii::$app->db->createCommand();
        // 更新交易量
        $cmd->update(Product::tableName(), ['traded' => new Expression('traded+:qty', [':qty' => 1])], ['id' => $package->id])->execute();

        $vo->package_remit_amount = BC::sub($vo->total_original_amount, (BC::add(BC::add($vo->total_amount, $vo->wx_remit_amount), $vo->coupon_remit_amount)));
        $vo->save(false);
//        $vo->calculationCoupon($vo, $coupon_id, $coupon_code, $mode, $code_type, $coupon_user_id);//调用计算的优惠券
//        $vo->getCouponPrice($vo, $coupon_id);//计算比例
        return $vo;
    }


    /**
     * @param array $items 一个包含 product、product_price、qty、original_order_id 的一个数组
     * @param User $user
     * @param boolean $isNeedInvoice 是否需要发票，默认不要
     * @param boolean $isProxy 是否为代客下单
     * @param Administrator $administrator 代客下单管理员
     * @return VirtualOrder
     * @throws \Exception
     * @param int $coupon_id 优惠券id
     * @param string $coupon_code 固定码/随机码
     * @param int $mode 优惠券类型，1优惠券，2优惠码
     * @param int $code_type 优惠码类型,1固定码，2随机码
     * @param int $created_at 下单时间
     * @param int $coupon_user_id 用户领取优惠券表id
     * @param int $subject_id 主体信息id
     * @param int $is_contract_show 合同是否显示
     */
    private static function createNewNormal(&$items, $user, $isNeedInvoice = false, $isProxy = false, $administrator = null, $coupon_id = null, $coupon_code = null, $mode = null, $code_type = null, $coupon_user_id = null, $created_at = 0,$subject_id = 0,$is_contract_show)
    {
        $adjustItems = [];

        $dailyStatistics = new DailyStatistics();
        $productStatistics = new ProductStatisticsDetailed();
        $vo = new VirtualOrder();
        $vo->user_id = $user->id;
        $vo->is_vest = $user->is_vest;
        $vo->is_need_invoice = $isNeedInvoice;
        $vo->created_at = empty($created_at) ? time() : $created_at;
        $vo->save(false);
        $totalAmount = 0;
        $totalOriginalAmount = 0;
        $totalTax = 0;
        $wxRemitAmount = 0;

        //选择业务主体
        $businessModel = BusinessSubject::getSubject($user,$subject_id,$isProxy);
        foreach ($items as $k => $item)
        {
            $originalPrice = null;
            $wxRemitPrice = 0;
            /** @var Product $product */
            $product = $item['product'];
            $qty = $item['qty'];
            $original_order_id = isset($item['original_order_id']) ? $item['original_order_id'] : 0;
            //面议商品
            if($product->isBargain())
            {
                if(isset($qty) && $qty > 0)
                {
                    for(; $qty > 0 ; $qty--)
                    {
                        $order = new Order();
                        $order->virtual_order_id = $vo->id;
                        $order->user_id = $user->id;
                        $order->product_id = $product->id;
                        $order->product_name = $product->name;
                        $order->product_id = $product->id;
                        $order->product_name = $product->name;
                        $order->flow_id = $product->flow_id;
                        $order->is_renewal = $product->is_renewal;
                        if($product->isRenewal())
                        {
                            $order->service_cycle = $product->service_cycle;
                        }
                        else
                        {
                            $order->service_cycle = 0;
                        }
                        $order->is_contract_show = $is_contract_show;
                        $order->original_order_id = $original_order_id;
                        $order->is_bargain = $product->is_bargain;
                        $order->is_pay_after_service = $product->is_pay_after_service;
                        $order->top_category_id = $product->top_category_id;
                        $order->top_category_name = $product->topCategory->name;
                        $order->category_id = $product->category_id;
                        $order->category_name = $product->category->name;
                        $order->deposit = $product->deposit;
                        $order->is_trademark = $product->is_trademark;
                        $order->is_installment = array_key_exists('is_installment',$item) ? $item['is_installment'] : $product->is_installment;
                        $order->salesman_aid = $administrator->id;
                        $order->is_proxy = $isProxy;
                        $order->company_id = $administrator->company_id;
                        $order->salesman_department_id = $administrator->department_id;
                        $order->salesman_name = $administrator->name;
                        $order->creator_id = $administrator->id;
                        $order->creator_name = $administrator->name;
                        $order->created_at = empty($created_at) ? time() : $created_at;
                        if(isset($businessModel))
                        {
                            $order->business_subject_id = $businessModel->id;
                            if($businessModel->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED)
                            {
                                $order->company_name = $businessModel->company_name ? $businessModel->company_name : '';
                            }
                        }
                        else
                        {
                            $order->company_name = '';
                        }
                        $order->save(false);
                        $adjustItems[] = [
                            'order_id' => $order->id,
                            'adjustAmount' => $item['adjustAmount']
                        ];
                        $order->autoAssignSupervisor();
                        if($vo->is_vest)
                        {
                            $order->apportionCustomerService();
                        }
                        else
                        {
                            $order->autoAssignCustomerService($administrator->company_id);
                        }
//                        if($administrator && $administrator->type == Administrator::TYPE_CUSTOMER_SERVICE && $administrator->customerService && empty($user->customer_service_id))
//                        {
//                            // 是客服代客下单，并且客户是首次下单，自动分配当前客服为客户的专属客服
//                            $order->autoAssignCustomerService();
//                        }
//                        else
//                        {
//                            $order->autoAssignCustomerService();
//                        }
                    }
                }
                //break;
            }
            /** @var ProductPrice $productPrice */
            $productPrice = $item['product_price'];
            $service_area = '';
            if($product->isAreaPrice())
            {
                $price = $originalPrice = BC::add($productPrice->price, $product->deposit);
                $tax = $productPrice->tax;
                $priceDetail = $productPrice->price_detail;
            }
            else
            {
                $price = $originalPrice = BC::add($product->price, $product->deposit);
                $tax = $product->tax;
                $priceDetail = $product->price_detail;
                $service_area = $product->service_area;
            }
            $cmd = Yii::$app->db->createCommand();
            // 更新交易量
            $cmd->update(Product::tableName(), ['traded' => new Expression('traded+:qty', [':qty' => $qty])], ['id' => $product->id])->execute();

            $detect = new MobileDetect();
            $sourceApp = Order::SOURCE_APP_PC;
            if($detect->match('MicroMessenger'))
            {
                // 微信下单优惠逻辑
                $sourceApp = Order::SOURCE_APP_WX;
                $wxRemitPrice = $product->wx_remit_amount;
                $wxRemitAmount = BC::add($wxRemitAmount, $wxRemitPrice);
                $price = BC::sub($originalPrice, $wxRemitPrice);
            }
            else if($detect->isMobile())
            {
                $sourceApp = Order::SOURCE_APP_WAP;
            }

            if(isset($qty) && $qty > 0)
            {
                for(; $qty > 0 ; $qty--)
                {
                    $dailyStatistics->countOrder($sourceApp);
                    $order = new Order();
                    $order->source_app = $sourceApp;
                    $order->original_price = $originalPrice;
                    $order->wx_remit_amount = $wxRemitPrice;
                    $order->virtual_order_id = $vo->id;
                    $order->user_id = $user->id;
                    $order->is_vest = $user->is_vest;
                    $order->is_proxy = $isProxy;
                    if($isProxy)
                    {
                        $order->creator_id = $administrator->id; // 代理下单管理员id
                        $order->creator_name = $administrator->name; // 代理下单管理员name
                        $order->salesman_aid = $administrator->id; // 所属业务管理员id
                        $order->company_id = $administrator->company_id; // 所属公司id
                        $order->salesman_department_id = $administrator->department_id;
                        $order->salesman_name = $administrator->name; // 所属业务管理员name
                    }
                    if($product->isRenewal())
                    {
                        $order->service_cycle = $product->service_cycle;
                    }
                    else
                    {
                        $order->service_cycle = 0;
                    }
                    $order->is_contract_show = $is_contract_show;
                    $order->product_id = $product->id;
                    $order->product_name = $product->name;
                    $order->flow_id = $product->flow_id;
                    $order->is_renewal = $product->is_renewal;
                    $order->original_order_id = $original_order_id;
                    $order->is_bargain = $product->is_bargain;
                    $order->is_pay_after_service = $product->is_pay_after_service;
                    $order->top_category_id = $product->top_category_id;
                    $order->top_category_name = $product->topCategory->name;
                    $order->category_id = $product->category_id;
                    $order->category_name = $product->category->name;
                    $order->deposit = $product->deposit;
                    $order->service_area = $service_area;
                    $order->is_trademark = $product->is_trademark;
                    $order->is_installment = $product->is_installment;
                    if($isNeedInvoice)
                    {
                        $order->price = BC::add($tax, $price);
                        $order->tax = $tax;
                        $dailyStatistics->sumPrice('order_price',$order->price);
                    }
                    else
                    {
                        $order->price = $price;
                        $dailyStatistics->sumPrice('order_price',$price);
                    }
                    if(null != $productPrice)
                    {
                        $order->province_id = $productPrice->province_id;
                        $order->province_name = $productPrice->province_name;
                        $order->city_id = $productPrice->city_id;
                        $order->city_name = $productPrice->city_name;
                        $order->district_id = $productPrice->district_id;
                        $order->district_name = $productPrice->district_name;
                    }

                    if($product->type == Product::TYPE_ADDRESS)
                    {
                        $order->address_product_id = $product->id;
                    }
                    else
                    {
                        $order->address_product_id = 0;
                    }

                    if($order->is_vest)
                    {
                        $order->status = Order::STATUS_IN_SERVICE;
                        $order->company_name = '';
                        $order->apportionCustomerService();
                    }
                    if(isset($businessModel))
                    {
                        $order->business_subject_id = $businessModel->id;
                        if($businessModel->subject_type == BusinessSubject::SUBJECT_TYPE_DISABLED)
                        {
                            $order->company_name = $businessModel->company_name ? $businessModel->company_name : '';
                        }
                    }
                    else
                    {
                        $order->company_name = '';
                    }

                    $order->created_at = empty($created_at) ? time() : $created_at;
                    $order->save(false);
                    $adjustItems[] = [
                        'order_id' => $order->id,
                        'adjustAmount' => $item['adjustAmount']
                    ];
                    OrderStatusStatistics::totalStatusNum($product->id,$order->district_id,'pending_pay_no');//统计订单状态-待付款
                    $productStatistics->total($product->id,$order->district_id,'product_order_num');//商品统计-下单商品数
                    OrderData::createData($order->id,$priceDetail);
                    //自动分配督导
                    $order->autoAssignSupervisor();
                    //是代客下单自动该公司下的客户
                    if($isProxy && !$vo->is_vest)
                    {
                        $order->autoAssignCustomerService($administrator->company_id);
                    }
//                    if($administrator && $administrator->type == Administrator::TYPE_CUSTOMER_SERVICE && $administrator->customerService && empty($user->customer_service_id))
//                    {
//                        // 是客服代客下单，并且客户是首次下单，自动分配当前客服为客户的专属客服
//                        $order->autoAssignCustomerService($administrator->customerService->id);
//                    }
//                    else
//                    {
//                        $order->autoAssignCustomerService();
//                    }
                    $totalAmount = BC::add($price, $totalAmount);
                    $totalOriginalAmount = BC::add($originalPrice, $totalOriginalAmount);
                    $totalTax = BC::add($tax, $totalTax);
                }
            }
        }
        $dailyStatistics->DayUv('order_user_no');
        if($isNeedInvoice)
        {
            $vo->total_tax = $totalTax;
            $vo->total_amount = BC::add($totalAmount, $vo->total_tax);
            $vo->total_original_amount = BC::add($totalOriginalAmount, $vo->total_tax);
        }
        else
        {
            $vo->total_amount = $totalAmount;
            $vo->total_original_amount = $totalOriginalAmount;
        }
        if($vo->is_vest)
        {
            $vo->status = VirtualOrder::STATUS_ALREADY_PAYMENT;
            $vo->payment_amount = $vo->total_amount;
        }
        $vo->wx_remit_amount = $wxRemitAmount;
        $vo->calculationCoupon($vo, $coupon_id, $coupon_code, $mode, $code_type, $coupon_user_id);//调用计算的优惠券
        $vo->save(false);
        $vo->getCouponPrice($vo, $coupon_id);//计算价格
        $items['adjustItems'] = $adjustItems;
        return $vo;
    }

    /**
     * 待付款/未付清(根据用户获取)
     * @param int $user_id
     * @return int
     */
    public static function getPendingPayCount($user_id)
    {
        $query = VirtualOrder::find()->alias('vo');
        $query->innerJoinWith(['orders o']);
        $query->andWhere(['in', 'vo.status', [VirtualOrder::STATUS_PENDING_PAYMENT, VirtualOrder::STATUS_UNPAID]]);
        $query->andWhere(['o.user_id' => $user_id]);
        $count = $query->count();
        return $count ? $count : 0;
    }

    /**
     * 已付款
     * @param int $user_id
     * @return int
     */
    public static function getPaidCount($user_id)
    {
        return Order::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['in', 'status', [
                Order::STATUS_PENDING_ALLOT,
                Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE,
                Order::STATUS_COMPLETE_SERVICE
            ]])
            ->count();
    }

    public function hasRefund()
    {
        return 0 < RefundRecord::find()->where(['virtual_order_id' => $this->id])->count();
    }


    /*
     * 虚拟订单状态为已取消
     */
    public function isPaymentAmount()
    {
        return $this->payment_amount > 0;
    }


    /**
     * 是否已经完成退款
     * @return bool
     */
    public function isRefunded()
    {
        return 0 >= RefundRecord::find()->where(['virtual_order_id' => $this -> id])
                ->andWhere(['in', 'status', [RefundRecord::STATUS_NOT_REFUND, RefundRecord::STATUS_FAIL]])->count();
    }


    /**
     * 是否已经完成退款
     * @return bool
     */
    public function isRefundedTwo()
    {
        return  RefundRecord::find()->where(['virtual_order_id' => $this -> id])
                ->andWhere(['status' => RefundRecord::STATUS_SUCCESS]) -> count();
    }


    /**
     * 是否有带审核的新建回款
     * @return bool
     */
    public function isPendingCheckReceipt()
    {
        return Receipt::find()->where(['virtual_order_id' => $this->id,'status' => Receipt::STATUS_NO])->count() > 0;
    }

    /**
     * @return bool
     */
    public function isOrderPayTimeout()
    {
        $order_pay_timeout = Property::get('order_pay_timeout');
        if((int)$order_pay_timeout <= 0) return false;
        $timeout = BC::add((int)$order_pay_timeout * 3600, $this->created_at, 0);
        if($this->isPendingCheckReceipt()) return false;
        if($this->isPendingPayment() && $this->payment_amount <= 0 && $timeout < time())
        {
            foreach($this->orders as $order)
            {
                if($order->isPayAfterService()) return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 订单的商品是否下线
     * @return bool
     */
    public function hasProductOffline()
    {
        foreach($this->orders as $order)
        {
            if($order->isProductOffline()) return true;
        }
        return false;
    }

    public function sumTotalRemitAmount()
    {
        $this->total_remit_amount = BC::add(BC::add($this->package_remit_amount, $this->wx_remit_amount), $this->coupon_remit_amount);
        $this->save(false);
    }

    public function getPackageProduct()
    {
        return static::hasOne(Product::className(), ['id' => 'package_id']);
    }

    public function getOpportunities()
    {
        return static::hasMany(NicheOrder::className(), ['order_id' => 'id']);
    }

    public function getPayStatus()
{
    if($this->status===self::STATUS_PENDING_PAYMENT){
        return '待付款';
    }elseif ($this->status===self::STATUS_ALREADY_PAYMENT){
        return '已付款';
    }elseif ($this->status===self::STATUS_UNPAID){
        return '未付清';
    }elseif ($this->status===self::STATUS_BREAK_PAYMENT){
        return '已取消';
    }
    return null;
}

    /**
     * 0、根据优惠券id或者优惠码（随机码或固定码）取出以下四个字段数据
     * 1、检查品类、金额是否符合
     * 2、检查优惠券是否过期、作废
     * 3、检查用户是否拥有使用的优惠券
     * 4、判断用户是否使用过（同一优惠券是否使用、同一批次优惠码是否使用过）
     * 5、处理金额
     * 字段1.优惠券id（优惠券或优惠码）*（必填，或者代客下单时为空）
     * 字段2.优惠券类型（优惠券、优惠码）
     * 字段3.优惠码（随机码或固定码）*（必填，或者代客下单时为空）
     * 字段4.优惠码类型（随机码、固定码）
     * @param VirtualOrder $vo
     * @param null $coupon_id
     * @param null $coupon_code
     * @param null $mode
     * @param null $code_type
     * @param null $coupon_user_id
     */
    //处理计算优惠券
    public function calculationCoupon($vo, $coupon_id = null, $coupon_code = null, $mode = null, $code_type = null, $coupon_user_id = null)
    {
        //判断是何种类型的优惠券,处理计算优惠券
        if($coupon_id > 0)
        {
            $coupon = Coupon::findOne($coupon_id);
            //优惠券
            if($mode == Coupon::MODE_COUPON)
            {
                if($coupon->type == Coupon::TYPE_REDUCTION)
                {
                    //减满券
                    $this->coupon_remit_amount = $coupon->remit_amount;
                    $this->total_amount = BC::sub($this->total_amount, $coupon->remit_amount) < 0 ? 0 : BC::sub($this->total_amount, $coupon->remit_amount);
                }
                else
                {
                    //折扣券
                    $totalOriginalPrice = $this->getCouponOrderOriginalPrice($vo, $coupon);//获取到的符合条件的订单的总价，计算出折扣后的优惠金额
                    $discount_total_amount = BC::mul($totalOriginalPrice, BC::div(BC::sub(100, $coupon->discount),100));
                    $this->coupon_remit_amount = $discount_total_amount;
                    $this->total_amount = BC::sub($this->total_amount, $discount_total_amount);
                }
                $cmd = Yii::$app->db->createCommand();
                $cmd->update(Coupon::tableName(), ['qty_used' => new Expression('qty_used+:qty', [':qty' => 1])], ['id' => $coupon->id])->execute();
                $cmd->update(CouponUser::tableName(), ['status' => CouponUser::STATUS_USED], ['user_id' => Yii::$app->user->id, 'coupon_id' => $coupon->id, 'id' => $coupon_user_id])->execute();

                $this->mode = Coupon::MODE_COUPON;
            }
            elseif ($mode == Coupon::MODE_COUPON_CODE)
            {
                //固定码、随机码
                $this->coupon_remit_amount = $coupon->remit_amount;
                $this->total_amount = BC::sub($this->total_amount, $coupon->remit_amount) < 0 ? 0 : BC::sub($this->total_amount, $coupon->remit_amount);
                if($coupon->code_type == Coupon::CODE_TYPE_FIXED)
                {
                    $cmd = Yii::$app->db->createCommand();
                    $cmd->update(Coupon::tableName(), ['qty_used' => new Expression('qty_used+:qty', [':qty' => 1])], ['id' => $coupon->id])->execute();

                    $this->code_type = Coupon::CODE_TYPE_FIXED;
                    $this->coupon_code = $coupon->coupon_code;
                }
                elseif ($coupon->code_type == Coupon::CODE_TYPE_RANDOM)
                {
                    //更新优惠券状态为使用(随机码表和优惠券的使用数量增加)
                    $cmd = Yii::$app->db->createCommand();
                    $cmd->update(Coupon::tableName(), ['qty_used' => new Expression('qty_used+:qty', [':qty' => 1])], ['id' => $coupon->id])->execute();
                    $cmd->update(CouponCode::tableName(), ['status' => CouponCode::STATUS_USED, 'user_id' => Yii::$app->user->id], ['coupon_id' => $coupon->id, 'random_code' => $coupon_code])->execute();

                    $this->code_type = Coupon::CODE_TYPE_RANDOM;
                    $this->coupon_code = $coupon_code;
                }

                $this->mode = Coupon::MODE_COUPON_CODE;
            }
            $this->coupon_id = $coupon_id;
        }
        $this->save(false);
    }

    /**
     * 计算订单的销售价格（当该订单使用了优惠券时，销售价格根据优惠券比例算出）
     * @param VirtualOrder $vo
     * @param $coupon_id
     */
    public function getCouponPrice($vo, $coupon_id = null)
    {
        $coupon = Coupon::findOne($coupon_id);
        if(null != $coupon)
        {

            $totalAmount = 0;
            $totalRemitAmount = 0;
            $i = 0;
            $orders = $this->getCouponOrders($vo, $coupon);
            $totalOriginalPrice = $this->getCouponOrderOriginalPrice($vo,$coupon);//排除不符合条件的订单后的总原价
            /** @var Order $order */
            foreach ($orders as $order)
            {
                $i++;
                $order->coupon_remit_amount = BC::mul($order->price, BC::div($vo->coupon_remit_amount, $totalOriginalPrice, 6));
                $order->price = BC::sub($order->price, $order->coupon_remit_amount);
                //对订单下的价格误差进行操作，把差价加到最后一个订单价格上(避免计算出现误差的情况)
                $totalAmount = BC::add($order->price, $totalAmount);
                $totalRemitAmount = BC::add($order->coupon_remit_amount, $totalRemitAmount);
                if($i >= count($orders))
                {
                    $orderTotalAmount = BC::sub($totalOriginalPrice, $vo->coupon_remit_amount);//排除不符合条件的订单后的总原价减去优惠券金额
                    $deviation = BC::sub($orderTotalAmount, $totalAmount);
                    $order->price = BC::add($order->price, $deviation);

                    $couponDeviation = BC::sub($vo->coupon_remit_amount, $totalRemitAmount);
                    $order->coupon_remit_amount = BC::add($order->coupon_remit_amount, $couponDeviation);
                }
                $order->save(false);
            }
        }
    }

    /**
     * @param VirtualOrder $vo
     * @param Coupon $coupon
     * @return array
     */
    private function getCouponOrders($vo, $coupon)
    {
        $productIds = $coupon->getProductIds();
        $orders = [];
        foreach ($vo->orders as $order)
        {
            if($coupon->isApplyScope())
            {
                if(in_array($order->product_id, $productIds))
                {
                    $orders[] = $order;
                }
            }
            elseif ($coupon->isRemoveScope())
            {
                if(!in_array($order->product_id, $productIds))
                {
                    $orders[] =$order;
                }
            }
        }
        return $orders;
    }

    /**
     * 计算满足优惠条件的订单的总价
     * @param VirtualOrder $vo
     * @param Coupon $coupon
     * @return int|string
     */
    private function getCouponOrderOriginalPrice($vo, $coupon)
    {
        $orders = $this->getCouponOrders($vo, $coupon);
        $totalOriginalPrice = 0;
        /** @var Order $order */
        foreach ($orders as $order)
        {
            $totalOriginalPrice = BC::add($totalOriginalPrice, $order->price);
        }
        return $totalOriginalPrice;
    }

    //返还优惠券
    private function returnCoupon()
    {
        $coupon = Coupon::findOne($this->coupon_id);
        if(null != $coupon && $coupon->canReturn())
        {
            $cmd = Yii::$app->db->createCommand();
            if($coupon->isModeCoupon())
            {
                //优惠券
                $couponUser = CouponUser::find()->where(['coupon_id' => $coupon->id, 'user_id' => $this->user_id, 'status' => CouponUser::STATUS_USED])->orderBy(['take_time' => SORT_ASC])->one();
                if(null != $couponUser)
                {
                    $cmd->update(Coupon::tableName(), ['qty_used' => new Expression('qty_used-:qty', [':qty' => 1])], ['id' => $coupon->id])->execute();
                    $cmd->update(CouponUser::tableName(), ['status' => CouponUser::STATUS_ACTIVE], ['id' => $couponUser->id])->execute();
                }
            }
            //优惠码不退
//            elseif ($coupon->isModeCouponCode())
//            {
//                //优惠码
//                if($coupon->isCodeRandom())
//                {
//                    //优惠码-随机码
//                    $cmd->update(Coupon::tableName(), ['qty_used' => new Expression('qty_used-:qty', [':qty' => 1])], ['id' => $coupon->id])->execute();
//                    $cmd->update(CouponCode::tableName(), ['status' => CouponCode::STATUS_UNUSED, 'user_id' => 0], ['coupon_id' => $coupon->id, 'random_code' => $this->coupon_code])->execute();
//                }
//                elseif ($coupon->isCodeFixed())
//                {
//                    //优惠码-固定码
//                    $cmd->update(Coupon::tableName(), ['qty_used' => new Expression('qty_used-:qty', [':qty' => 1])], ['id' => $coupon->id])->execute();
//                }
//            }
        }
        $this->save(false);
    }

    //统计下单用户数
    public function getUserOrder($date)
    {
        $start_time = mktime(0, 0 , 0,date("m",$date),date("d",$date),date("Y",$date));
        $closure_time = mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date));
        return  $query = self::find()->select(['COUNT(*)'])
                ->andWhere(['is_vest' => 0])
                ->andWhere('created_at >= :start_time', [':start_time' => $start_time])
                ->andWhere('created_at <= :end_time', [':end_time' => $closure_time])
                ->groupBy(['user_id'])
                ->count();
    }

    //统计付款用户数
    public function getPayUserOrder($time)
    {
        $start_time = mktime(0, 0 , 0,date("m",$time),date("d",$time),date("Y",$time));
        $closure_time = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        return self::find()->select(['COUNT(*)'])
            ->andWhere(['status' => self::STATUS_ALREADY_PAYMENT])
            ->andWhere(['is_vest' => 0])
            ->andWhere('payment_time >= :start_time', [':start_time' => $start_time])
            ->andWhere('payment_time <= :end_time', [':end_time' => $closure_time])
            ->groupBy(['user_id'])
            ->count();
    }

    //查询下单付款用户
    public function payOrderUserNo($time)
    {
        $start_time = mktime(0, 0 , 0,date("m",$time),date("d",$time),date("Y",$time));
        $closure_time = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        $data = self::find()->select(['COUNT(*) AS user_id'])
            ->andWhere(['is_vest' => 0])
            ->andWhere(['status' => self::STATUS_ALREADY_PAYMENT])
            ->andWhere('created_at >= :start_time', [':start_time' => $start_time])
            ->andWhere('created_at <= :end_time', [':end_time' => $closure_time])
            ->groupBy(['user_id'])
            ->all();
        return $data;
    }

    //统计二次下单付款用户数
    public function twiceNo($time)
    {
        $data = $this->payOrderUserNo($time);
        $twice = [];
        foreach($data as $v)
        {
            if($v->user_id == 2)
            {
                $twice[] = $v->user_id;
            }
        }
        return count($twice);
    }

    //统计多次下单付款用户数
    public function repeatedlyNo($time)
    {
        $data = $this->payOrderUserNo($time);
        $repeatedly = [];
        foreach($data as $v)
        {
            if($v->user_id > 2)
            {
                $repeatedly[] = $v->user_id;
            }
        }
        return count($repeatedly);
    }
    //统计付款金额-统计退款金额
    public static function countPrice($field)
    {
        return self::find()->where(['status'=>self::STATUS_ALREADY_PAYMENT,'is_vest' => 0])->sum($field);
    }

    //统计退款金额
    public static function countRefundAmount()
    {
        return self::find()
            ->where(['status'=>self::REFUND_STATUS_REFUNDED,'is_vest' => 0])
            ->andWhere('created_at < :start_time', [':start_time' => strtotime(date('Ymd'))])
            ->sum('refund_amount');
    }

    public function hasBargain()
    {
        foreach($this->orders as $order)
        {
            if($order->product->isBargain())
            {
                return true;
            }
        }
        return false;
    }

    public function hasReceiptNeedReview()
    {
        return ($this->isPendingPayment() || $this->isUnpaid()) &&
            (Receipt::find()->where(['virtual_order_id'=>$this->id,'status' => 0])->count() > 0);
    }

    /**
     * @param Administrator $administrator
     * @return int
     */
    public static function getTodayPendingPayCountBySalesman($administrator)
    {
        $begin = strtotime(date('Y-m-d 00:00:00'));
        return Order::find()->where(['salesman_aid' => $administrator->id])->select('virtual_order_id')
            ->andWhere(['status' => Order::STATUS_PENDING_PAY])
            ->andWhere(['between', 'created_at', $begin, $begin+86400])->groupBy('virtual_order_id')->count();
    }

    /**
     * @param Administrator $administrator
     * @return int
     */
    public static function getTodayPaidCountBySalesman($administrator)
    {
        $begin = strtotime(date('Y-m-d 00:00:00'));
        return Order::find()->where(['salesman_aid' => $administrator->id])->select('virtual_order_id')
            ->andWhere(['in', 'status', [Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE, Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE]])
            ->andWhere(['between', 'created_at', $begin, $begin+86400])->groupBy('virtual_order_id')->count();
    }

    /**
     * @param Administrator $administrator
     * @return int
     */
    public static function getPendingPayCountBySalesman($administrator)
    {
        return Order::find()->where(['salesman_aid' => $administrator->id])->select('virtual_order_id')
            ->andWhere(['status' => Order::STATUS_PENDING_PAY])
            ->groupBy('virtual_order_id')->count();
    }

    /**
     * @param Administrator $administrator
     * @return int
     */
    public static function getPaidCountBySalesman($administrator)
    {
        return Order::find()->where(['salesman_aid' => $administrator->id])->select('virtual_order_id')
            ->andWhere(['in', 'status', [Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE, Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE]])
            ->groupBy('virtual_order_id')->count();
    }

    /**
     * @param Administrator $administrator
     * @return int
     */
    public static function getRefundCountBySalesman($administrator)
    {
        return Order::find()->where(['salesman_aid' => $administrator->id])->select('virtual_order_id')
            ->andWhere(['not in', 'refund_status', [Order::REFUND_STATUS_NO, Order::REFUND_STATUS_REFUNDED]])
            ->groupBy('virtual_order_id')->count();
    }

    /**
     * 判断是否存在待审核的订单价格修改
     * @return bool
     */
    public function hasPendingAdjustPriceOrder()
    {
        return Order::find()->where(['virtual_order_id' => $this->id, 'adjust_status' => AdjustOrderPrice::STATUS_PENDING])->count() > 0;
    }

    /**
     * 订单回款操作按钮显示文字内容
     */
    public function getReceiptStatusName()
    {
        /** @var Receipt $receipt */
         $receipt = Receipt::find()->select('status')->where(['virtual_order_id' => $this->id])->orderBy(['created_at' => SORT_DESC])->limit(1)->one();
         if(null == $receipt)
         {
             $status = Receipt::STATUS_YES;
         }
         else
         {
             $status = $receipt->status;
         }
        $receiptStatusList = static::getReceiptStatusList();
        return $receiptStatusList[$status];
    }

    /**
     * 订单回款操作按钮显示文字内容列表
     * @return array
     */
    public static function getReceiptStatusList()
    {
        return [
            Receipt::STATUS_NO => '回款审核中',
            Receipt::STATUS_YES => '新建回款',
            Receipt::STATUS_FAILED => '回款审核失败',
        ];
    }

    //自动分配回款
    public function separateMoney($payment_amount)
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        //一次付清的
        if($this->total_amount == $payment_amount)
        {
            foreach($this->orders as $i => $order)
            {
                $order->payment_amount = $order->price;
                $order->save(false);
                OrderRecord::create($order->id, '订单款项分配成功', '本次分配金额：'.$order->price.'元',$administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
                PerformanceRecord::createRecord($order->virtual_order_id,$order->id,$order->price,0,0,0);
            }
        }
        else
        {
            $total = 0;
            $count = count($this->orders);
            if($count > 1)
            {
                $remainder = BC::sub($this->total_amount,$this->payment_amount,2);
                $isPaid = $payment_amount == $remainder;
                //未付清并且不止一个订单的
                $rate = [];
                foreach($this->orders as $i => $order)
                {
                    if($count > 1 && $i+1 != $count)
                    {
                        //按照子订单应付金额/虚拟订单应付金额
                        $rate[$order->id] = BC::div(BC::sub($order->price,$order->payment_amount),BC::sub($this->total_amount,$this->payment_amount),5);
                    }
                }
                foreach($this->orders as $i => $order)
                {
                    if($count > 1 && $i+1 == $count)
                    {
                        $pay_price = $isPaid ? BC::sub($order->price, $order->payment_amount) : BC::sub($payment_amount,$total);
                        $order->payment_amount = BC::add($order->payment_amount,$pay_price);
                        $order->save(false);
                        OrderRecord::create($order->id, '订单款项分配成功', '本次分配金额：'.$pay_price.'元',$administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
                        $pending_pay = BC::sub($order->price,$order->payment_amount);
                        PerformanceRecord::createRecord($order->virtual_order_id,$order->id,$pay_price,$pending_pay,0,0);
                    }
                    else
                    {
                        $pay_price = $isPaid ? BC::sub($order->price, $order->payment_amount) : round(BC::mul($payment_amount,$rate[$order->id],5),2);
                        $total += $pay_price;
                        $order->payment_amount = BC::add($order->payment_amount,$pay_price);
                        $order->save(false);
                        OrderRecord::create($order->id, '订单款项分配成功', '本次分配金额：'.$pay_price.'元',$administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
                        $pending_pay = BC::sub($order->price,$order->payment_amount);
                        PerformanceRecord::createRecord($order->virtual_order_id,$order->id,$pay_price,$pending_pay,0,0);
                    }
                }
            }
            elseif($count == 1)
            {
                //未付清只有一个订单未付清的情况
                foreach($this->orders as $i => $order)
                {
                    $order->payment_amount = BC::add($order->payment_amount,$payment_amount);
                    $order->save(false);
                    OrderRecord::create($order->id, '订单款项分配成功', '本次分配金额：'.$payment_amount.'元',$administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
                    $pending_pay = BC::sub($order->price,BC::add($order->payment_amount,$payment_amount));
                    PerformanceRecord::createRecord($order->virtual_order_id,$order->id,$payment_amount,$pending_pay,0,0);
                }
            }
        }
    }

    public function getFirstBeginService()
    {
        $time = null;
        foreach($this->orders as $order)
        {
            if($order->begin_service_time)
            {
                $time = date('Y-m-d H:i:s',$order->begin_service_time);
                break;
            }
        }
        return $time;
    }

    //虚拟订单的预计成本总和
    public function getTotalExpectedCost()
    {
       $totalExpectedCost = VirtualOrderExpectedCost::find()->where(['virtual_order_id' => $this->id])->sum('cost_price');
       return $totalExpectedCost ? $totalExpectedCost : 0;
    }

    public function getAdjustTotalPrice()
    {
        $adjust = AdjustOrderPrice::find()->where(['virtual_order_id' => $this->id,'status' => AdjustOrderPrice::STATUS_PENDING])->sum('adjust_price');
        return $adjust ? $adjust : 0;
    }

    //虚拟订单最大可分配数
    public function getTotalAmount()
    {
        $total = Order::find()->where(['virtual_order_id' => $this->id])->sum('payment_amount');
        $max_allocation_number = BC::sub($this->payment_amount,$total);
        return $max_allocation_number;
    }

    //已结转的订单预计成本金额总计
    public function getKnotCostPrice()
    {
        $expectedProfit = ExpectedProfitSettlementDetail::find()
            ->select('order_id')
            ->where(['virtual_order_id' => $this->id,
                'type' => ExpectedProfitSettlementDetail::TYPE_KNOT])
            ->groupBy('order_id')
            ->asArray()->all();
        $order_ids = ArrayHelper::getColumn($expectedProfit,'order_id');
        $cost_price = OrderExpectedCost::find()->where(['in','order_id',$order_ids])->sum('cost_price');
        return $cost_price ? $cost_price : 0;
    }

    //虚拟订单下的子订单预计利润总和
    public function getOrderTotalExpectedCost()
    {
        $totalExpectedCost = OrderExpectedCost::find()->where(['virtual_order_id' => $this->id])->sum('cost_price');
        return $totalExpectedCost ? $totalExpectedCost : null;
    }

    //获取子订单的预计利润结算月
    public function getCalculateMonth()
    {
        $orders = $this->getOrders()->all();
        $month = [];
        foreach($orders as $i => $order)
        {
            if($order->settlement_month && !in_array($order->settlement_month,$month))
            {
                $month[$order->settlement_month] = $order->settlement_month;
            }
        }
        return $month;
    }

    //预计利润
    public function getTotalExpectedProfit()
    {
        $expected_profit = ExpectedProfitSettlementDetail::find()->where(['virtual_order_id' => $this->id])->sum('expected_profit');
        return $expected_profit ? $expected_profit : 0;
    }

    //虚拟订单实际成本
    public function getTotalCost()
    {
        $totalCost = VirtualOrderCost::find()->where(['virtual_order_id' => $this->id])->sum('cost_price');
        return $totalCost ? $totalCost : 0;
    }

    //虚拟订单下子订单实际总成本
    public function getOrderCost()
    {
        $totalOrderCost = OrderCostRecord::find()->where(['virtual_order_id' => $this->id])->sum('cost_price');
        return $totalOrderCost ? $totalOrderCost : 0;
    }

    public function getPerformance()
    {
        $performance = 0;
        foreach($this->orders as $order)
        {
            $performance += $order->getSurplusPerformance();
        }
        return $performance;
    }

    public function getReviewPrice()
    {
        $price = Receipt::find()->where(['virtual_order_id' => $this->id,'status' => Receipt::STATUS_NO])->sum('payment_amount');
        return $price && floatval($price) >= 0 ? $price : '';
    }

    /**
     * @return bool
     */
    public function payRate()
    {
        $pay_rate = Property::get('pay_rate');
        $pay_rate = $pay_rate ? $pay_rate : 30;
        $rate = BC::mul(BC::div($this->payment_amount,$this->total_amount),100);
        return $rate < $pay_rate;
    }

    public function getOrderIds()
    {
        $recordModels = PerformanceRecord::find()->select('order_id')->where(['virtual_order_id' => $this->id])->groupBy('order_id')->asArray()->all();
        $ids = ArrayHelper::getColumn($recordModels,'order_id');
        return $ids;
    }

}
