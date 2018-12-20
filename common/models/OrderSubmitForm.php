<?php

namespace common\models;

use common\utils\BC;
use yii\base\Model;

class OrderSubmitForm extends Model
{
    public $item_ids;
//    public $is_need_invoice;
//    public $invoice_title;
//    public $invoice_addressee;
//    public $invoice_phone;
//    public $invoice_address;

    //代客下单时此字段为null
    public $coupon_id;
    public $coupon_code;
    public $mode;
    public $code_type;

    public $coupon_user_id;
    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var ShoppingCart
     */
    private $shoppingCart;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_ids'], 'required'],
//            [['is_need_invoice'], 'boolean'],
            [['item_ids'], 'validateItemIds'],
//            [['is_need_invoice'], 'validateIsNeedInvoice'],
//            [['invoice_title', 'invoice_addressee', 'invoice_phone', 'invoice_address'], 'trim'],
//            [['invoice_title'], 'required', 'on' => 'need_invoice', 'message' => '请填写发票抬头。'],
//            [['invoice_addressee'], 'required', 'on' => 'need_invoice', 'message' => '请填写发收件人。'],
//            [['invoice_phone'], 'required', 'on' => 'need_invoice', 'message' => '请填写联系电话。'],
//            [['invoice_address'], 'required', 'on' => 'need_invoice', 'message' => '请填写收件地址。'],
//            [['invoice_phone'], TelPhoneValidator::className(), 'on' => 'need_invoice'],
//            ['invoice_title', 'string', 'min' => 1, 'max' => 30, 'on' => 'need_invoice'],
//            ['invoice_title', 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}（）\(\)]+$/u', 'message' => '发票抬头只能为中文', 'on' => 'need_invoice'],
            [['item_ids'], 'validateItems'],

            [['code_type'], 'validateCodeType'],
            [['coupon_id'], 'validateCouponId'],
            [['coupon_code'], 'validateCouponCode'],
            [['mode'], 'validateMode'],
            [['coupon_user_id'], 'validateCouponUserId'],

        ];
    }

    public function validateItemIds()
    {
        if(empty($this->item_ids))
        {
            $this->addError('item_ids', '您还没有选定商品。');
        }
    }

    public function validateIsNeedInvoice()
    {

    }

    public function validateItems()
    {
        $items = $this->getItems();
        if(empty($items))
        {
            $this->addError('item_ids', '您的订单提交无效！');
        }
        $hasInstallment = false;
        $totalQty = 0;
        foreach($items as $item)
        {
            $totalQty += $item->qty;
            if($item->product->isInstallment())
            {
                $hasInstallment = true;
            }
            if($item->product->buy_limit > 0)
            {
                if($item->product->isInventoryLimit())
                {
                    if($item->product->inventory_qty > 0)
                    {
                        $amount = $item->product->buy_limit - $item->product->inventory_qty;
                        if($amount < 0)
                        {
                            $count = Order::find()->where(['user_id' => \Yii::$app->user->id, 'product_id' => $item->product_id])
                                ->andWhere(['not', ['status' => Order::STATUS_BREAK_SERVICE]])->count();
                            if($count >= $item->qty)
                            {
                                $item->qty = $item->product->buy_limit-$count;
                                if($item->qty <= 0)
                                {
                                    $this->addError('item_ids', '此商品限购'.$item->product->buy_limit.'件，您不能再购买了哦~');
                                    return ;
                                }
                            }
                        }
                        else
                        {
                            if($item->qty > $item->product->inventory_qty)
                            {
                                $this->addError('item_ids', '此商品库存不足，您不能再购买了哦~');
                                return ;
                            }
                        }
                    }
                    else
                    {
                        $this->addError('item_ids', '此商品库存不足，您不能再购买了哦~');
                        return ;
                    }
                }
                else
                {
                    $count = Order::find()->where(['user_id' => \Yii::$app->user->id, 'product_id' => $item->product_id])
                        ->andWhere(['not', ['status' => Order::STATUS_BREAK_SERVICE]])->count();
                    if($count >= $item->qty)
                    {
                        $item->qty = $item->product->buy_limit-$count;
                        if($item->qty <= 0)
                        {
                            $this->addError('item_ids', '此商品限购'.$item->product->buy_limit.'件，您不能再购买了哦~');
                            return ;
                        }
                    }
                }
            }
            else
            {
                if($item->product->isInventoryLimit())
                {
                    if($item->product->inventory_qty > 0)
                    {
                        if($item->qty > $item->product->inventory_qty)
                        {
                            $this->addError('item_ids', '此商品库存不足，您不能再购买了哦~');
                            return ;
                        }
                    }
                    else
                    {
                        $this->addError('item_ids', '此商品库存不足，您不能再购买了哦~');
                        return ;
                    }
                }
            }
        }
        
    }

    // 校验优惠券是否可用
    public function validateCouponUserId()
    {
        if($this->coupon_id > 0 || !empty($this->mode))
        {
            //套餐不可用
            $items = $this->getItems();
            if(count($items) === 1 && $items[0]->product->isPackage())
            {
                $this->addError('coupon_code', '优惠券不可用。');
            }

            //优惠券
            if($this->mode == Coupon::MODE_COUPON)
            {
                $this->coupon = Coupon::findOne($this->coupon_id);
                if(null == $this->coupon)
                {
                    $this->addError('coupon_id', '优惠券不存在。');
                }
                else
                {
                    if($this->coupon->isModeCoupon())
                    {
                        //1.用户优惠券是否存在（领取过），并且未使用(检查用户是否拥有使用的优惠券)
                        $userCouponCount = CouponUser::find()
                            ->andWhere(['user_id' => \Yii::$app->user->id])
                            ->andWhere('id = :coupon_user_id', [':coupon_user_id' => $this->coupon_user_id])
                            ->andWhere(['status' => CouponUser::STATUS_ACTIVE])->one();
                        if(null == $userCouponCount)
                        {
                            $this->addError('coupon_user_id', '优惠券不可用。');
                        }

                        //2.检查优惠券是否过期或者未生效(作废不影响使用)
                        if(!$this->coupon->isNormal())
                        {
                            $this->addError('coupon_user_id', '优惠券不可用。');
                        }

                        //优惠券是否适用此次订单（检查品类、金额是否符合）
                        $productIdsCount = count($this->coupon->getProductIds());
                        //优惠券应用于商品时
                        if($this->coupon->isApplyScope())
                        {
                            if($productIdsCount <= 0)
                            {
                                //适用于商品为0时
                                $this->addError('coupon_user_id', '优惠券不可用。');
                            }
                            else
                            {
                                //价格满减，判断是否满足满减条件
                                if($this->coupon->order_total_amount > 0)
                                {
                                    //计算使用范围商品总价
                                    $totalPrice = $this->getOrderPrice($this->coupon, false);
                                    if($totalPrice < $this->coupon->order_total_amount)
                                    {
                                        $this->addError('coupon_user_id', '优惠券不可用。');
                                    }
                                }
                            }
                        }
                        elseif ($this->coupon->isRemoveScope())
                        {
                            //排除商品
                            if($this->coupon->order_total_amount > 0)
                            {
                                $surplus = $this->getOrderPrice($this->coupon, true);
                                if($surplus < $this->coupon->order_total_amount)
                                {
                                    $this->addError('coupon_user_id', '优惠券不可用。');
                                }
                            }
                        }
                    }
                }
            }
            elseif ($this->mode == Coupon::MODE_COUPON_CODE)
            {
                //优惠码
                /** @var Coupon $coupon */
                $coupon = Coupon::find()->where(['coupon_code' => $this->coupon_code])->one();//固定码
                /** @var CouponCode $couponRandomCode */
                $couponRandomCode = CouponCode::find()->where(['random_code' => $this->coupon_code])->one();//随机码
                if(null == $coupon && null == $couponRandomCode)
                {
                    $this->addError('coupon_code', '优惠码不可用。');
                }

                //1.固定码
                if(null != $coupon)
                {
                    //是否有效使用期内,并且发布，未作废
                    if(!$coupon->isNormal() || !$coupon->isConfirmed() || $coupon->isObsoleted())
                    {
                        $this->addError('coupon_code', '优惠码不可用。');
                    }
                    //已使用数量大于发行数量
                    if($coupon->qty_used >= $coupon->qty)
                    {
                        $this->addError('coupon_code', '优惠码不可用。');
                    }
                    //校验商品是否支持优惠(满减多少可用)
                    $productIdsCount = count($coupon->getProductIds());
                    //优惠券应用于商品时
                    if($coupon->isApplyScope())
                    {
                        if($productIdsCount <= 0)
                        {
                            //适用于商品为0时
                            $this->addError('coupon_code', '优惠码不可用。');
                        }
                        else
                        {
                            //价格满减，判断是否满足满减条件
                            if($coupon->order_total_amount > 0)
                            {
                                $totalPrice = $this->getOrderPrice($coupon, false);
                                if($totalPrice < $coupon->order_total_amount)
                                {
                                    $this->addError('coupon_id', '优惠码不可用。');
                                }
                            }
                        }
                    }
                    elseif ($coupon->isRemoveScope())
                    {
                        //排除商品
                        if($coupon->order_total_amount > 0)
                        {
                            $surplus = $this->getOrderPrice($coupon, true);
                            if($surplus < $coupon->order_total_amount)
                            {
                                $this->addError('coupon_id', '优惠码不可用。');
                            }
                        }
                    }
                }
                //2.随机码
                elseif (null != $couponRandomCode)
                {
                    $coupon = Coupon::findOne($couponRandomCode->coupon_id);
                    if(null == $coupon)
                    {
                        $this->addError('coupon_code', '您的操作有误。');
                    }
                    //随机码是是否可用
                    if(!$couponRandomCode->isStatusUnused())
                    {
                        $this->addError('coupon_code', '优惠码不可用。');
                    }
                    //同一批次随机码是否使用过
                    $couponRandomCount = CouponCode::find()->where(['random_code' => $this->coupon_code, 'user_id' => \Yii::$app->user->id, 'status' => CouponCode::STATUS_USED])->count();
                    if($couponRandomCount > 0)
                    {
                        $this->addError('coupon_code', '优惠码不可用。');
                    }
                    //校验商品是否支持优惠(满减多少可用)
                    $productIdsCount = count($coupon->getProductIds());
                    //优惠券应用于商品时
                    if($coupon->isApplyScope())
                    {
                        if($productIdsCount <= 0)
                        {
                            //适用于商品为0时
                            $this->addError('coupon_code', '优惠码不可用。');
                        }
                        else
                        {
                            //价格满减，判断是否满足满减条件
                            if($coupon->order_total_amount > 0)
                            {
                                $totalPrice = $this->getOrderPrice($coupon, false);
                                if($totalPrice < $coupon->order_total_amount)
                                {
                                    $this->addError('coupon_id', '优惠码不可用。');
                                }
                            }
                        }
                    }
                    elseif ($coupon->isRemoveScope())
                    {
                        //排除商品
                        if($coupon->order_total_amount > 0)
                        {
                            $surplus = $this->getOrderPrice($coupon,true);
                            if($surplus < $coupon->order_total_amount)
                            {
                                $this->addError('coupon_id', '优惠码不可用。');
                            }
                        }
                    }
                }
            }
        }
    }

    public function validateMode()
    {

    }
    public function validateCouponCode()
    {

    }
    public function validateCouponId()
    {

    }
    public function validateCodeType()
    {

    }

    /**
     * @return ShoppingCartItem[]
     */
    public function getItems()
    {
        return ShoppingCartItem::find()->where(['in', 'id', $this->item_ids])->andWhere(['shopping_cart_id' => $this->shoppingCart->id])->all();
    }

    /**
     * @param ShoppingCart $shoppingCart
     */
    public function setShoppingCart($shoppingCart)
    {
        $this->shoppingCart = $shoppingCart;
    }

    public function attributeLabels()
    {
        return [
            'coupon_id' => '优惠券',
        ];
    }

    public function submitOrder()
    {
        if(!$this->validate()) return null;
        $items = [];
        $cartItems = $this->getItems();
        // 生成订单
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            $count = count($cartItems);
            foreach($cartItems as $cartItem)
            {
                if($count > 1 && $cartItem->product->isPackage())
                {
                    continue;
                }
                //判断是否是餐商品
                if(!$cartItem->product->isBargain())
                {
                    $items[] = [
                        'product' => $cartItem->product,
                        'product_price' => $cartItem->productPrice,
                        'original_order_id' => $cartItem->original_order_id,
                        'qty' => $cartItem->product->isPackage() ? 1 : $cartItem->qty,
                    ];
                    if($cartItem->isAddressValid())
                    {
                        $items[] = [
                            'product' => $cartItem->getRelatedAddress(),
                            'product_price' => $cartItem->getAddressProductPrice(),
                            'original_order_id' => $cartItem->original_order_id,
                            'qty' => $cartItem->product->isPackage() ? 1 : $cartItem->qty,
                        ];
                    }
                }
                $cartItem->delete();
            }
//            $virtualOrder = VirtualOrder::createNew($items, $this->shoppingCart->user, $this->is_need_invoice);
            $virtualOrder = VirtualOrder::createNew($items, $this->shoppingCart->user, false, false, null, $this->coupon_id, $this->coupon_code, $this->mode, $this->code_type, $this->coupon_user_id);
            $virtualOrder->sumTotalRemitAmount();
            // 创建发票
//            if($this->is_need_invoice)
//            {
//                foreach($virtualOrder->orders as $order)
//                {
//                    $invoice = new Invoice();
//                    $invoice->user_id = $virtualOrder->user_id;
//                    $invoice->order_id = $order->id;
//                    $invoice->order_sn = $order->sn;
//                    $invoice->virtual_order_id = $virtualOrder->id;
//                    $invoice->invoice_amount = 0;
//                    $invoice->invoice_title = $this->invoice_title;
//                    $invoice->addressee = $this->invoice_addressee;
//                    $invoice->phone = $this->invoice_phone;
//                    $invoice->address = $this->invoice_address;
//                    $invoice->status = Invoice::STATUS_SUBMITTED;
//                    $invoice->created_at = time();
//                    $invoice->save(false);
//                }
//            }
            //创建订单记录
            foreach($virtualOrder->orders as $order)
            {
                OrderRecord::create($order->id, '订单提交成功', '', null);
            }
            $this->shoppingCart->updateItemQty();
            //0元订单状态直接改为已支付，不包含面议商品才会自动支付。
            if($virtualOrder && $virtualOrder->total_amount <= 0 && !$virtualOrder->hasBargain())
            {
                $virtualOrder->payment($virtualOrder->total_amount,Receipt::SEPARATE_MONEY_ACTIVE);
            }
            $t->commit();
            return $virtualOrder;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

//    public function beforeValidate()
//    {
//        if(parent::beforeValidate())
//        {
//            if($this->is_need_coupon)
//            {
//                $this->setScenario('need_coupon');
//            }
//            return true;
//        }
//        return false;
//    }

    public function couponCountQuery()
    {
        $query = CouponUser::find()->alias('cu');
        $query -> innerJoinWith('coupon c');
        $query->andWhere(['cu.user_id' => \Yii::$app->user->id]);
        return $query;
    }

//    /**
//     * @param Coupon $coupon
//     * @param bool $flag
//     * @return int|string
//     */
    /**
     * @param Coupon $coupon
     * @param bool $flag true时为排除商品, false为应用于商品
     * @param null $cartItems
     * @return int|string
     */
    public function getOrderPrice($coupon, $flag = false, $cartItems = null)
    {
        if(null == $cartItems)
        {
            $cartItems = $this->getItems();
        }
        $count = count($cartItems);
        $totalPrice = 0;
        $surplus = 0;
        $total_amount = 0;
        foreach($cartItems as $cartItem)
        {
            if($count >= 1)
            {
                if($cartItem->product->isPackage())
                {
                    //套餐商品
//                    $i = 0;
//                    $totalAmount = 0;
//                    foreach ($cartItem->product->packageProductList as $packageProductItem)
//                    {
//                        $i++;
//                        $packageProduct = $packageProductItem->product;//套餐下的普通商品
//                        $price = $cartItem->getPackageProductPrice($packageProduct);
//                        $totalAmount = BC::add($price, $totalAmount);
//                        if($i >= count($cartItem->product->packageProductList))
//                        {
//                            $deviation = BC::sub($cartItem->getPrice(), $totalAmount);
//                            $price = BC::add($price, $deviation);//$price按比例后计算出来的普通商品价格
//                        }
//
//                        if(in_array($packageProduct->id, $coupon->getProductIds()))
//                        {
//                            $productPrice = 0;
//                            $addressProductPrice= 0;
//                            if(!$packageProduct->isBargain())
//                            {
//                                $qty = $packageProduct->isPackage() ? 1 : $cartItem->qty;
//                                $productPrice = BC::mul($price, $qty);
//                                if($cartItem->isAddressValid())
//                                {
//                                    $addressQty = $packageProduct->isPackage() ? 1 : $cartItem->qty;
//                                    $addressProductPrice = BC::mul($packageProduct->getPrice($cartItem->getAddressProductPrice(), false), $addressQty);
//                                }
//                            }
//                            $totalPrice += BC::add($productPrice, $addressProductPrice);
//                        }
//                    }
                    $totalPrice = 0;
                    if($flag)
                    {
//                        $total_amount += $cartItem->getAmount();
//                        $surplus = BC::sub($total_amount, $totalPrice);
                        $surplus = 0;
                    }
                }
                else
                {
                    //非套餐商品
                    if(in_array($cartItem->product_id, $coupon->getProductIds()))
                    {
                        $productPrice = 0;
                        $addressProductPrice= 0;
                        if(!$cartItem->product->isBargain())//不是议价时的价格
                        {
                            $qty = $cartItem->product->isPackage() ? 1 : $cartItem->qty;
                            $productPrice = BC::mul($cartItem->product->getPrice($cartItem->productPrice, false), $qty);
                            if($cartItem->isAddressValid())
                            {
                                $addressQty = $cartItem->product->isPackage() ? 1 : $cartItem->qty;
                                $addressProductPrice = BC::mul($cartItem->product->getPrice($cartItem->getAddressProductPrice(), false), $addressQty);
                            }
                        }
                        $totalPrice += BC::add($productPrice, $addressProductPrice);
                    }

                    if($flag)
                    {
                        $total_amount += $cartItem->getAmount();
                        $surplus = BC::sub($total_amount, $totalPrice);
                    }
                }

            }
        }

        if($flag) return $surplus;
        return $totalPrice;
    }

    /**
     * @param Coupon $coupon
     * @param ShoppingCartItem[] $items
     * @return bool
     */
    public function isCouponAvailable($coupon, $items)
    {
        $productIdsCount = count($coupon->getProductIds());
        //优惠券应用于商品时
        if($coupon->isApplyScope())
        {
            if($productIdsCount <= 0)
            {
                //适用于商品为0时
                return false;
            }
            else
            {
                //价格满减，判断是否满足满减条件
                if($coupon->order_total_amount > 0)
                {
                    //计算使用范围商品总价
                    $totalPrice = $this->getOrderPrice($coupon, false, $items);
                    if($totalPrice < $coupon->order_total_amount)
                    {
                        return false;
                    }
                }
            }
        }
        elseif ($coupon->isRemoveScope())
        {
            //排除商品
            if($coupon->order_total_amount > 0)
            {
                $surplus = $this->getOrderPrice($coupon, true, $items);
                if($surplus < $coupon->order_total_amount)
                {
                    return false;
                }
            }
        }
        return true;
    }

}
