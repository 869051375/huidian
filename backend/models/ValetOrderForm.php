<?php
namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\OrderRecord;
use common\models\Product;
use common\models\ProductPrice;
use common\models\Salesman;
use common\models\User;
use common\models\VirtualOrder;
use common\utils\BC;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

/**
 * Class ValetOrderForm
 * @package backend\models
 */
class ValetOrderForm extends Model
{
    public $user_name;
    public $user_id;
    public $order_time;
    public $subject_info;
    public $salesman_id;

    public $products = [];
    public $items = [];

    public $product_id;
    public $product_price_id;
    public $price;
    public $qty = 1;

    public $commodity = [];

    /**
     * @var User
     */
    public $user;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var array
     */
//    private $products;

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['salesman_id','subject_info','user_id'],'trim'],
            [['user_id','salesman_id','products'], 'required'],
            [['user_name'], 'required', 'message'=>'请选择客户'],
            [['order_time'], 'required', 'message'=>'请选择下单时间'],
            [['subject_info','user_id'],'integer'],
//            ['user_name', 'validateUserId'],
            ['user_id', 'validateUserId'],
            ['salesman_id', 'validateAdministratorId'],
            ['products', 'validateProducts'],
            ['order_time', 'validateOrderTime'],
        ];
    }

    public function validateOrderTime()
    {
        $this->order_time = strtotime($this->order_time);
        if($this->order_time > time())
        {
            $this->addError('order_time', '下单时间不能大于当前时间');
        }
    }

    public function validateUserId()
    {
        if($this->user_id)
        {
            $this->user = User::findOne($this->user_id);
            if(null == $this->user)
            {
                $this->addError('user_id', '找不到客户');
            }
        }
    }

    public function validateAdministratorId()
    {
        if(null == $this->administrator)
        {
            $this->administrator = Administrator::findOne($this->salesman_id);
            if(null == $this->administrator)
            {
                $this->addError('salesman_id', '找不到业务员');
            }
        }
    }

    public function validateProducts()
    {
//        $hasPayAfterService = false;
//        $hasInstallment = false;
        $totalQty = 0;
        $count = count($this->products);
        $this->products = array_values($this->products);
        foreach($this->products as $i => $model)
        {
            $a[] = $i;
            if(!preg_match("/^\d*$/",$model['product_id']))
            {
                $this->addError('product_id', '商品ID必须是整数');
            }
            if(!preg_match("/^\d*$/",$model['product_price_id']))
            {
                $this->addError('product_price_id', '商品地区价格ID必须是整数');
            }
            if(!preg_match('/^[+-]{0,1}[0-9]*\.?[0-9]{0,2}$/',$model['price']))
            {
                $this->addError('price', '变动金额需输入数字，+50为增加50元，-50为减少50元');
            }
            if(!preg_match("/^\d*$/",$model['qty']))
            {
                $this->addError('qty', '商品数量必须是整数');
            }
            /** @var Product $product */
            $product = Product::findOne($model['product_id']);
//            if($model['price'] < 0 && abs($model['price']) > $product->price)
//            {
//                $this->addError('price', '变动金额超出范围');
//            }
            if($product->isPackage() && $model['price'] != 0)
            {
                $this->addError('user_id', '套餐商品不能变动金额！');
            }
            if($product)
            {
//                if($product && $product->isInstallment() && $model['qty'] > 1)
//                {
//                    $this->addError('user_id', '分期付款商品请另外单独创建新订单，若多个分期付款商品，请创建多个新订单');
//                }
                if($count > 1 && $product->isPackage())
                {
                    continue;
                }
                else
                {
//                    if($product->is_pay_after_service)
//                    {
//                        $hasPayAfterService = true;
//                    }
//                    if($product->isInstallment())
//                    {
//                        $hasInstallment = true;
//                    }
                    /** @var ProductPrice $pp */
                    $pp = null;
                    if($product->isAreaPrice())
                    {
                        $pp = $product->getProductPrice($model['product_price_id']);
                        if(floatval(BC::add($pp->price,$model['price'])) < 0)
                        {
                            $this->addError('price', '变动金额超出范围');
                        }
                    }
                    else
                    {
                        if(!$product->isBargain())
                        {
                            if(floatval(BC::add($product->price,$model['price'])) < 0)
                            {
                                $this->addError('price', '变动金额超出范围');
                            }
                        }
                    }
                    if($product->isBargain())
                    {
                        $pp = 0;
                    }
                    $totalQty += $model['qty'];
                    $this->items[] = [
                        'product' => $product,
                        'qty' => (int)$model['qty'],
                        'price' => $product->getPrice($pp),
                        'pp' => $pp,
                        'adjustAmount' => $model['price']
                    ];
                }
            }
            else
            {
                $this->addError('user_id', '编号为'.$i.'的商品信息不正确。');
            }
        }
//        if($hasPayAfterService && $totalQty > 1)
//        {
//            $this->addError('user_id', '先服务后付费商品请另外单独创建新订单，若多个先服务后付费商品，请创建多个新订单');
//        }
//        if($hasInstallment && $totalQty > 1)
//        {
//            $this->addError('user_id', '分期付款商品请另外单独创建新订单，若多个分期付款商品，请创建多个新订单');
//        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => '客户id',
            'user_name' => '客户姓名',
            'items' => '添加商品',
            'qty' => '数量',
            'order_time' => '下单时间',
            'subject_info' => '业务办理主体',
            'salesman_id' => '业务员',
        ];
    }


    public static function getSalesman()
    {
        //上线的所有业务员
        $model = Salesman::find()->where(['status'=>Salesman::STATUS_ACTIVE])->all();
        $salesman = [];
        /**@var $model  Salesman[] **/
        foreach($model as $item)
        {
            if($item->administrator)
            {
                $salesman[$item->administrator->id] = $item->administrator->name;
            }
        }
        return $salesman;
    }

    /**
     * @param VirtualOrder $vo
     * @throws \Exception
     */
    public function adjustPrice($vo)
    {
        $flag = false;
        foreach($vo->orders as $i => $order)
        {
            if(!$vo->packageProduct && isset($this->commodity[$order->id]))
            {
                if($this->commodity[$order->id] != 0)
                {
                    $flag = true;
                    $t = Yii::$app->db->beginTransaction();
                    try
                    {
                        /** @var Administrator $administrator */
                        $administrator = Yii::$app->user->identity;
                        $order->adjust_status = AdjustOrderPrice::STATUS_PENDING;
                        $order->save(false);
                        AdjustOrderPrice::createAdjustPrice($order->id,$order->virtual_order_id,AdjustOrderPrice::STATUS_PENDING,$administrator,
                            $this->commodity[$order->id],'创建订单申请价格变动');
                        $t->commit();
                    }
                    catch (\Exception $e)
                    {
                        $t->rollBack();
                        throw $e;
                    }
                    OrderRecord::create($order->id, '申请金额变动', '订单金额：'.$order->price.'元，变动金额：'.
                        $this->commodity[$order->id].'元，应付金额：'.
                        (BC::add($order->price,$this->commodity[$order->id])).'元，修改说明：创建订单申请价格变动。', $administrator, 0, 1);
                    //新增后台操作日志
                    AdministratorLog::logAdjustOrderPrice($order);
                }
            }
        }

        if($flag)
        {
            //修改价格-提交审核
            try {
                //订单业务员所在部门主管电话
                $phone = $this->administrator->department ? ($this->administrator->department->leader ? $this->administrator->department->leader->phone : null) : null;
                // 发送短信（加入短信队列）
                /** @var Queue $queue */
                $queue = \Yii::$app->get('queue', false);
                if($queue && $phone)
                {
                    // 业务员：{1}，订单号：{2}
                    $queue->pushOn(new SendSmsJob(),[
                        'phone' => $phone,
                        'sms_id' => '258411',//订单价格变动审核 模板id：258411
                        'data' => [$this->administrator->name, $vo->sn]
                    ], 'sms');
                }
            }catch (\Exception $e){
                Yii::getLogger()->log($e, Logger::LEVEL_INFO);
            }
        }
    }

    /**
     * @return null | VirtualOrder
     */
    public function save()
    {
        if(!$this->validate()) return null;
        $items = [];
        foreach($this->items as $item)
        {
            $items[] = [
                'product' => $item['product'],
                'product_price' => $item['pp'],
                'qty' => $item['qty'],
                'adjustAmount' => $item['adjustAmount']
            ];
        }
        $vo = VirtualOrder::createNew($items, $this->user, false, true, $this->administrator, null, null, null, null, null,$this->order_time,$this->subject_info);
        $vo->sumTotalRemitAmount();
        //新增订单记录
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        foreach($vo->orders as $o)
        {
            OrderRecord::create($o->id, '订单提交成功', '', $admin);
        }
        foreach($items['adjustItems'] as $adjustItem)
        {
            $this->commodity[$adjustItem['order_id']] = $adjustItem['adjustAmount'];
        }
        $this->adjustPrice($vo);
        //新增后台操作日志
        AdministratorLog::logSubmitOrder($vo);
        return $vo;
    }

}