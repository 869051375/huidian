<?php

namespace common\models;

use common\utils\BC;
use yii\base\Model;

class OrderConfirmForm extends Model
{
    public $item_ids;

    /**
     * @var ShoppingCart
     */
    private $shoppingCart;

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_ids'], 'required'],
            [['item_ids'], 'validateItemIds'],
        ];
    }

    public function validateItemIds()
    {
        $items = $this->getItems();
        if(empty($this->item_ids) || empty($items))
        {
            $this->addError('item_ids', '您还没有选定商品。');
        }
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

    /**
     * 获取可用优惠券，并且符合订单条件，区分出此时的订单可用的优惠券和不可用的优惠券
     * @param CouponUser[] $userCoupons
     * @return array
     */
    public function getEffectCoupons($userCoupons)
    {
        $userEffectCoupon = [];
        $orderSubmitForm = new OrderSubmitForm();
        $cartItems = $this->getItems();
        foreach ($userCoupons as $userCoupon) {
            if ($orderSubmitForm->isCouponAvailable($userCoupon->coupon, $cartItems))
            {

                $orderSubmitForm = new OrderSubmitForm();//表单模型的公共方法需要独立出来，待优化
                $remit_amount = 0;
                if($userCoupon->coupon->isTypeDiscount())
                {
                    //排除
                    if ($userCoupon->coupon->scope == Coupon::SCOPE_REMOVE) {
                        $totalAmount = $orderSubmitForm->getOrderPrice($userCoupon->coupon, true, $cartItems);
                    } else {
                        $totalAmount = $orderSubmitForm->getOrderPrice($userCoupon->coupon, false, $cartItems);
                    }

                    $discountAmount = BC::mul($totalAmount, BC::div($userCoupon->coupon->discount, 100));
                    $remit_amount = BC::sub($totalAmount, $discountAmount);
                }
                elseif ($userCoupon->coupon->isTypeReduction())
                {
                    $remit_amount = $userCoupon->coupon->remit_amount;
                }
                $userEffectCoupon['available'][] = ['coupon' => $userCoupon->coupon, 'coupon_money' => $remit_amount, 'coupon_user_id' => $userCoupon->id];

            } else {

                $userEffectCoupon['unavailable'][] = $userCoupon->coupon;
            }
        }

        if(isset($userEffectCoupon['available']))
        {
            uasort($userEffectCoupon['available'],function ($a,$b){
                 if($a['coupon_money'] == $b['coupon_money']) return 0;
                 return $a['coupon_money'] > $b['coupon_money']? -1 : 1;
             });
        }
        return $userEffectCoupon;
    }
}
