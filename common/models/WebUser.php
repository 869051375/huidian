<?php
namespace common\models;

use yii\web\Cookie;
use yii\web\User;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/20
 * Time: 下午6:02
 */

class WebUser extends User
{
    const SHOPPING_CART_ID = 'shopping-cart-id';

    /**
     * @param bool $autoCreate
     * @return ShoppingCart|null
     */
    public function getShoppingCart($autoCreate = false)
    {
        $shoppingCart = null;
        $cookie = \Yii::$app->request->cookies->get(self::SHOPPING_CART_ID);
        $cid = null;
        if($cookie)
        {
            $cid = $cookie->value;
        }
        if(\Yii::$app->user->isGuest)
        {
            if(!empty($cid))
            {
                $shoppingCart = ShoppingCart::findOne($cid);
            }
        }
        else
        {
            $shoppingCart = ShoppingCart::getByUserId($this->id);
            if(null != $cid)
            {
                if($cid > 0 && null == $shoppingCart)
                {
                    $shoppingCart = ShoppingCart::findOne($cid);
                }
                else if($cid > 0 && $cid != $shoppingCart->id)
                {
                    $tempShoppingCart = ShoppingCart::findOne($cid);
                    if(null != $tempShoppingCart)
                    {
                        // 从未登录购物车合并到已登录购物车
                        $shoppingCart->merge($tempShoppingCart);
                    }
                }
            }
            if(null != $shoppingCart)
            {
                $shoppingCart->user_id = $this->id;
                $shoppingCart->save(false);
            }
        }
        if(null == $shoppingCart && $autoCreate)
        {
            $shoppingCart = ShoppingCart::createByUserId($this->id);
        }
        if(null != $shoppingCart && (null == $cid || $shoppingCart->id != $cid))
        {
            $cookie = new Cookie();
            $cookie->name = self::SHOPPING_CART_ID;
            $cookie->value = $shoppingCart->id;
            $cookie->expire = time()+(10*30*24*60*60);
            \Yii::$app->response->cookies->add($cookie);
        }
        return $shoppingCart;
    }
}