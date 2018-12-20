<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\Product;
use common\models\ProductPrice;
use common\models\User;
use common\models\VirtualOrder;
use yii\base\Model;

/**
 * Class OrderForm
 * @package backend\models
 */
class OrderForm extends Model
{
    public $user_name;
    public $user_id;
//    public $is_need_invoice;
//    public $is_need_invoice =1 ;
    public $items;

//    public $invoice_title;
//    public $invoice_addressee;
//    public $invoice_phone;
//    public $invoice_address;

    /**
     * @var User
     */
    public $user;

    /**
     * @var array
     */
    private $products;

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['user_id', 'items'], 'required'],
//            [['is_need_invoice'], 'boolean'],
            ['user_id', 'validateUserId'],
            ['items', 'validateItems'],
//            [['invoice_title', 'invoice_addressee', 'invoice_phone', 'invoice_address'], 'trim'],
//            [['invoice_title'], 'required', 'on' => 'need_invoice', 'message' => '请填写发票抬头。'],
//            [['invoice_addressee'], 'required', 'on' => 'need_invoice', 'message' => '请填写发收件人。'],
//            [['invoice_phone'], 'required', 'on' => 'need_invoice', 'message' => '请填写联系电话。'],
//            [['invoice_address'], 'required', 'on' => 'need_invoice', 'message' => '请填写收件地址。'],
        ];
    }

    public function validateUserId()
    {

        if(null != $this->user)
        {
            $this->user = User::findOne($this->user_id);
            if(null == $this->user)
            {
                $this->addError('user_id', '找不到客户');
            }
        }
    }

    public function validateItems()
    {
        $productIdList = $this->items['product_id'];
        $qtyList = $this->items['qty'];
        $productPriceIds = $this->items['product_price_id'];
        $this->products = [];
        $hasPayAfterService = false;
        $hasInstallment = false;
        $totalQty = 0;
        $count = count($productIdList);
        foreach ($productIdList as $i => $product_id)
        {
            $price = null;
            $qty = $qtyList[$i];
            $product_price_id = $productPriceIds[$i];
            if(isset($product_price_id) && isset($qty) && $qty > 0)
            {
                /** @var Product $product */
                $product = Product::findOne($product_id);
                if(null != $product && $product->isOnline())
                {
                    if($count > 1 && $product->isPackage())
                    {
                        continue;
                    }
                    if($product->isPackage())
                    {
                        $qty = 1;
                    }
                    if($product->isBargain())
                    {
                        $this->addError('items', '价格为面议的商品，暂不支持下单!');
                    }
                    /** @var ProductPrice $pp */
                    $pp = null;
                    if($product->isAreaPrice())
                    {
                        $pp = $product->getProductPrice($product_price_id);
                    }
                    if($product->is_pay_after_service)
                    {
                        $hasPayAfterService = true;
                    }
                    if($product->isInstallment())
                    {
                        $hasInstallment = true;
                    }
                    $totalQty += $qty;
                    $this->products[] = [
                        'product' => $product,
                        'qty' => (int)$qty,
                        'price' => $product->getPrice($pp),
                        'pp' => $pp,
                    ];
                }
                else
                {
                    $this->addError('items', '编号为'.$i.'的商品信息不正确。');
                }
            }
            else
            {
                $this->addError('items', '编号为'.$i.'的商品信息不正确。');
            }
        }
        if($hasPayAfterService && $totalQty > 1)
        {
            $this->addError('items', '先服务后付费商品请另外单独创建新订单，若多个先服务后付费商品，请创建多个新订单');
        }
        if($hasInstallment && $totalQty > 1)
        {
            $this->addError('items', '分期付款商品请另外单独创建新订单，若多个分期付款商品，请创建多个新订单');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => '客户姓名',
            'items' => '添加商品',
            'qty' => '数量',
//            'is_need_invoice' => '开具发票',
//            'invoice_title' => '发票抬头：',
//            'invoice_addressee' => '收件人：',
//            'invoice_phone' => '联系电话：',
//            'invoice_address' => '收件地址：',
        ];
    }

//    public function beforeValidate()
//    {
//        if(parent::beforeValidate())
//        {
//            if($this->is_need_invoice)
//            {
//                $this->setScenario('need_invoice');
//            }
//            return true;
//        }
//        return false;
//    }

    /**
     * @return null | VirtualOrder
     */
    public function save()
    {
        if(!$this->validate()) return null;
        $items = [];
        foreach($this->products as $item)
        {
            $items[] = [
                'product' => $item['product'],
                'product_price' => $item['pp'],
                'qty' => $item['qty'],
            ];
        }
        $vo = VirtualOrder::createNew($items, $this->user, false, true, \Yii::$app->user->identity);
        $vo->sumTotalRemitAmount();
//        if($this->is_need_invoice)
//        {
//            foreach($vo->orders as $order)
//            {
//                $invoice = new Invoice();
//                $invoice->user_id = $vo->user_id;
//                $invoice->order_id = $order->id;
//                $invoice->order_sn = $order->sn;
//                $invoice->virtual_order_id = $vo->id;
//                $invoice->invoice_amount = 0;
//                $invoice->invoice_title = $this->invoice_title;
//                $invoice->addressee = $this->invoice_addressee;
//                $invoice->phone = $this->invoice_phone;
//                $invoice->address = $this->invoice_address;
//                $invoice->status = Invoice::STATUS_SUBMITTED;
//                $invoice->created_at = time();
//                $invoice->save(false);
//            }
//        }
        //新增后台操作日志
        AdministratorLog::logSubmitOrder($vo);
        return $vo;
    }

}