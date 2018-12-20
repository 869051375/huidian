<?php

namespace common\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%administrator_log}}".
 *
 * @property integer $id
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property string $desc
 * @property integer $type
 * @property string $ip
 * @property int $total
 * @property int $sign
 * @property integer $created_at
 */
class AdministratorLog extends ActiveRecord
{
    const TYPE_GENERAL_OPERATION = 1;//一般操作
    const TYPE_LOGIN_SUCCESS = 2;//登录成功
    const TYPE_LOGIN_FAILED = 3;//登录失败
    const TYPE_LOGOUT = 4;//主动退出

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%administrator_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id', 'desc', 'created_at'], 'required'],
            [['administrator_id', 'type', 'created_at', 'total'], 'integer'],
            [['administrator_name'], 'string', 'max' => 10],
            [['desc'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'desc' => 'Desc',
            'type' => 'Type',
            'ip' => 'Ip',
            'total' => 'Total',
            'created_at' => 'Created At',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            if($insert) // 如果是新增，则初始化一下登录次数总计
            {
                if($this->type == AdministratorLog::TYPE_LOGIN_SUCCESS)
                {
                    $maxSort = static::find()->where(['administrator_id' => $administrator->id, 'type' => AdministratorLog::TYPE_LOGIN_SUCCESS])->orderBy(['total' => SORT_DESC])->select('total')->limit(1)->scalar();
                    $this->total = $maxSort + 1; //
                    $this->sign = 1;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param Flow $flow
     * @param string $oldName
     */
    public static function logFlowUpdate($flow, $oldName)
    {
        if(null == $flow) return;
        $desc = '将流程ID为：' .$flow->id. '，名称为：' . $oldName . '，更改为：' . $flow->name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Order $order
     */
    public static function logChangeCustomerService($order)
    {
        if(null == $order) return;
        $desc ='将订单号为：' . $order->sn . '的订单客服修改为：' . $order->customer_service_name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Order $order
     * @param bool $is_cancel
     */
    public static function logAuditRefund($order, $is_cancel)
    {
        if(null == $order) return;
        if($is_cancel) {
            $desc_status = '，并且退款后取消订单';
        }else{
            $desc_status = '，并且退款后不取消订单';
        }
        $desc ='对订单号为：' . $order->sn . '的订单退款审核已通过，退款金额为：' . $order->refund_amount . $desc_status;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param OrderEvaluate $orderEvaluate
     */
    public static function logAuditEvaluate($orderEvaluate)
    {
        if(null == $orderEvaluate) return;
        $desc ='用户ID为：' . $orderEvaluate->user_id . '，评价ID为：' . $orderEvaluate->id .'的订单号：' . $orderEvaluate->order->sn . '的评价已审核通过';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Product $product
     * @param Flow $oldFlow
     */
    public static function logProductUpdate($product, $oldFlow)
    {
        if(null == $product) return;
        if(null != $oldFlow)
        {
            $oldFlowId = $oldFlow->id;
            $oldFlowName = $oldFlow->name;
        }
        else
        {
            $oldFlowId = '';
            $oldFlowName = '';
        }
        $flow = Flow::findOne($product->flow_id);
        if(null != $flow)
        {
            $flowId = $flow->id;
            $flowName = $flow->name;
        }
        else
        {
            $flowId = '';
            $flowName = '';
        }
        $desc = '对商品ID为：' .$product->id. '，名称为：' . $product->name .'的基本信息设置进行了编辑。'. '其中流程id为：'. $oldFlowId . '改为：'. $flowId.'，流程名称：'. $oldFlowName. '改为：'. $flowName;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Product $product
     */
    public static function logPackageProductUpdate($product)
    {
        if(null == $product) return;
        $desc = '对套餐商品ID为：' .$product->id. '，名称为：' . $product->name .'的套餐基本信息设置进行了编辑';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Order $order
     */
    public static function logStartService($order)
    {
        if(null == $order) return;
        $desc ='订单号为：' . $order->sn . '的订单开始服务';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Order $order
     */
    public static function logChangeSalesman($order)
    {
        if(null == $order) return;
        $desc ='将订单号为：' . $order->sn . '的订单业务人员修改为：' . $order->salesman_name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductCategory $productCategory
     */
    public static function logProductCategoryCreate($productCategory)
    {
        if(null == $productCategory) return;
        $desc = '新增分类ID为：'. $productCategory->id .'，名称为：' . $productCategory->name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductCategory $productCategory
     * @param string $oldName
     */
    public static function logProductCategoryUpdate($productCategory, $oldName)
    {
        if(null == $productCategory) return;
        $desc = '将分类ID为：'. $productCategory->id . '，名称为：' . $oldName . '，更改为：' . $productCategory->name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductCategory $productCategory
     */
    public static function logProductCategoryDelete($productCategory)
    {
        if(null == $productCategory) return;
        $desc = '删除分类ID为：'. $productCategory->id . '，名称为：' .  $productCategory->name ;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductPrice $model
     */
    public static function logDistrictPriceStatus($model)
    {
        if(null == $model) return;
        $product = Product::findOne($model->product_id);
        if(null == $product) return;
        $desc = $model->isEnabled() ? '启用' : '禁用';
        $desc = $desc . '商品ID为：'. $model->product_id . '，名称为：' . $product->name . '的商品下的' . $model->province_name .'-'. $model->city_name . '-' . $model->district_name . '的区域及价格';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Product $product
     */
    public static function logProductStatus($product)
    {
        if(null == $product) return;
        $desc = $product->status == 1 ? '上线' : '下线';
        $desc = $desc . 'ID为：'. $product->id .'，名称为：' . $product->name . '的商品';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param int $product_id
     * @param int $product_price_id
     */
    public static function logSavePriceDetail($product_id, $product_price_id)
    {
        if($product_id)
        {
            /** @var Product $model */
            $product = Product::findOne($product_id);
            $desc = '新增商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品非区分区域的价格明细';
        }
        else
        {
            /** @var ProductPrice $model
             * @var Product $product
             */
            $model = ProductPrice::findOne($product_price_id);
            $product = Product::findOne($model->product_id);
            if(null == $model || null == $product) return;
            $desc = '新增商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品下的'. $model->province_name .'-'. $model->city_name . '-' . $model->district_name .'下的价格明细';
        }
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    public static function logDeletePriceDetail($id, $product_price_id)
    {
        if($id)
        {
            /** @var Product $model */
            $product = Product::findOne($id);
            $desc = '删除商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品非区分区域的价格明细';
        }
        else
        {
            /** @var ProductPrice $model
             * @var Product $product
             */
            $model = ProductPrice::findOne($product_price_id);
            $product = Product::findOne($model->product_id);
            if(null == $model || null == $product) return;
            $desc = '删除商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品下的'. $model->province_name .'-'. $model->city_name . '-' . $model->district_name .'下的价格明细';
        }
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductPrice $model
     */
    public static function logDeleteDistrictPrice($model)
    {
        if(null == $model) return;
        $product = Product::findOne($model->product_id);
        if(null == $product) return;
        $desc = '删除商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品下的'. $model->province_name .'-'. $model->city_name . '-' . $model->district_name .'下的价格明细';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductPrice $model
     */
    public static function logUpdateDistrictPrice($model)
    {
        if(null == $model) return;
        $product = Product::findOne($model->product_id);
        if(null == $product) return;
        $desc = '修改商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品下的'. $model->province_name .'-'. $model->city_name . '-' . $model->district_name .'下的区域价格';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Order $order
     */
    public static function logFollowRecord($order)
    {
        if(null == $order) return;
        $desc ='将订单号为：' . $order->sn . '的订单进行跟进记录，下次跟进时间为：' . Yii::$app->formatter->asDatetime($order->next_follow_time);
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductImage $productImage
     */
    public static function logDeleteProductImage($productImage)
    {
        if(null == $productImage) return;
        $type = '';
        if($productImage->type == ProductImage::TYPE_DETAIL)
        {
            $type = '的商品详情页图片';
        }
        elseif($productImage->type == ProductImage::TYPE_LIST)
        {
            $type = '的商品列表页图片';
        }
        elseif($productImage->type == ProductImage::TYPE_CAR)
        {
            $type = '的购物车页面图片';
        }
        elseif($productImage->type == ProductImage::TYPE_HOT)
        {
            $type = '的热门商品图片';
        }
        $desc ='删除商品ID为：'. $productImage->product_id .'，名称为：' . $productImage->product->name . $type;

        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param ProductImage $productImage
     */
    public static function logUploadProductImage($productImage)
    {
        if(null == $productImage) return;
        $type = '';
        if($productImage->type == ProductImage::TYPE_DETAIL)
        {
            $type = '的商品详情页图片';
        }
        elseif($productImage->type == ProductImage::TYPE_LIST)
        {
            $type = '的商品列表页图片';
        }
        elseif($productImage->type == ProductImage::TYPE_CAR)
        {
            $type = '的购物车页面图片';
        }
        elseif($productImage->type == ProductImage::TYPE_HOT)
        {
            $type = '的热门商品图片';
        }
        $desc ='新增商品ID为：'. $productImage->product_id .'，名称为：' . $productImage->product->name . $type;

        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Order $order
     * @param string $oldClerkName
     */
    public static function logChangeOrderClerk($order, $oldClerkName)
    {
        if(null == $order) return;
        if(empty($oldClerkName))
        {
            $desc ='将订单号为：' . $order->sn . '的订单派单给服务人员：' . $order->clerk_name;
        }
        else
        {
            $desc ='将订单号为：' . $order->sn . '的订单服务人员修改为：' . $order->clerk_name . '，上次服务人员为：'. $oldClerkName;
        }
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Flow $model
     */
    public static function logFlowAjaxStatus($model)
    {
        if(null == $model) return;
        $desc = $model->status == 1 ? '启用' : '禁用';
        $desc = $desc . '流程ID为：'. $model->id .'，名称为：' . $model->name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }
    /**
     * @param Flow $model
     */
    public static function logFlowAjaxDelete($model)
    {
        if(null == $model) return;
        $desc = '删除ID为：' . $model->id . '，名为：' . $model->name . '的流程';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Flow $model
     */
    public static function logFlowAjaxPublish($model)
    {
        if(null == $model) return;
        $desc = '发布流程ID为：' . $model->id . '；名为：' . $model->name;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param Product $product
     */
    public static function logUpdateProductPriceType($product)
    {
        if(null == $product) return;
        if($product->is_bargain)
        {
            $desc = '修改商品ID为：'. $product->id .'，名称为：' . $product->name . '，为议价商品';
        }
        elseif($product->is_area_price)
        {
            $desc = '修改商品ID为：'. $product->id .'，名称为：' . $product->name . '，为区分区域商品';
        }
        else
        {
            $desc = '修改商品ID为：'. $product->id .'，名称为：' . $product->name . '，为非议价商品和非区分区域商品';
        }
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param VirtualOrder $vo
     */
    public static function logSubmitOrder($vo)
    {
        if(null == $vo) return;
        foreach($vo->orders as $order)
        {
            $desc ='替ID为：'. $order->user_id . '的客户下单成功，订单号为：' . $order->sn;
            AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
        }
    }

    /**
     * @param Product $product
     * @param ProductPrice $productPrice
     */
    public static function logSaveDistrictPrice($product, $productPrice)
    {
        if(null == $product || null == $productPrice) return;
        $desc = '新增商品ID为：'. $product->id .'，名称为：' . $product->name . '的商品下'. $productPrice->province_name .'-'. $productPrice->city_name . '-' . $productPrice->district_name. '的区域价格';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param RefundRecord $record
     */
    public static function logRefundSuccess($record)
    {
        if(null == $record) return;
        if($record->order)
        {
            $desc ='订单号为：'. $record->order_sn .'退款成功，退款金额为：' . $record->refund_amount .'元。';
        }
        else
        {
//            $sns = [];
//            foreach($record->payRecord->virtualOrder->orders as $order)
//            {
//                $sns[] = $order->sn;
//            }
//            $desc ='订单号为：'. implode(',', $sns) .' 退款成功，退款金额为：' . $record->refund_amount .'元。';
            $desc ='虚拟订单号为：'.$record->payRecord->virtualOrder->sn.' 退款成功，退款金额为：' . $record->refund_amount .'元。';
        }
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }


    /**
     * @param VirtualOrder $virtualOrder
     */
    public static function logCancelVirtualOrder($virtualOrder)
    {
        if(null == $virtualOrder) return;
//        $orderSnList = [];
//        foreach($virtualOrder->orders as $order)
//        {
//            $orderSnList[] = $order->sn;
//        }
//        $desc ='订单号为：' . implode(',', $orderSnList) . '的订单取消成功';
        $desc ='虚拟订单号为：' .$virtualOrder->sn. '的订单取消成功';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param VirtualOrder $virtualOrder
     */
    public static function logConfirmPay($virtualOrder)
    {
        if(null == $virtualOrder) return;
//        $orderSnList = [];
//        foreach($virtualOrder->orders as $order)
//        {
//            $orderSnList[] = $order->sn;
//        }
//        $desc ='订单号为：' . implode(',', $orderSnList) . '的订单确认付款成功';
        $desc ='虚拟订单号为：' .$virtualOrder->sn. '的订单确认付款成功';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param String $username
     */
    public static function logLoginFail($username)
    {
        $desc = $username . '的账号登录失败';
        AdministratorLog::log($desc, AdministratorLog::TYPE_LOGIN_FAILED);
    }

    /**
     * @param String $username
     */
    public static function logLoginSuccess($username)
    {
        $desc = $username . '的账号登录成功';
        AdministratorLog::log($desc, AdministratorLog::TYPE_LOGIN_SUCCESS);
    }

    /**
     * @param Order $order
     */
    public static function logAdjustOrderPrice($order)
    {
        if(null == $order) return;
        $desc ='订单号为：' . $order->sn . '修改价格：'.($order->adjust_amount > 0 ? '+' : '').$order->adjust_amount;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * 主动退出
     * @param Administrator $admin
     */
    public static function logLogout($admin)
    {
        $desc = '退出';
        AdministratorLog::log($desc, AdministratorLog::TYPE_LOGOUT, $admin);
    }

    /**
     * 访问客户详情
     * @param CrmCustomer $customer
     */
    public static function logVisitCustomer($customer)
    {
        $desc = '访问客户'.$customer->name.'/'.$customer->phone;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param AdjustOrderPrice $adjust
     * @param Order $order
     */
    public static function logAdjustOrderPriceReview($order, $adjust)
    {
        if(null == $adjust) return;
        $desc ='订单号为：' . $order->sn . '修改价格审核通过：'.($order->adjust_amount > 0 ? '+' : '').$adjust->adjust_price;
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param $type
     * @param $num
     *
     */
    public static function logExport($type,$num){
        $administrator = Yii::$app->user->identity;
        if(null == $type) return;
        $desc =$administrator->name.'导出'.$type.'记录共计'.$num.'条；';
        AdministratorLog::log($desc, AdministratorLog::TYPE_GENERAL_OPERATION);
    }

    /**
     * @param string $desc
     * @param int $type
     * @param null|Administrator $admin
     * @return bool
     * @throws Exception
     */
    public static function log($desc, $type, $admin = null)
    {
        $administratorLog = new AdministratorLog();
        /** @var \common\models\Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        if($type == AdministratorLog::TYPE_LOGOUT)
        {
            if(null != $admin)
            {
                $administratorLog->administrator_id = $admin->id;
                $administratorLog->administrator_name = $admin->name;
            }
            else
            {
                $administratorLog->administrator_id = 0;
            }
        }
        else
        {
            if($type != AdministratorLog::TYPE_LOGIN_FAILED)
            {
                if($administrator)
                {
                    $administratorLog->administrator_id = $administrator->id;
                    $administratorLog->administrator_name = $administrator->name;
                }
                else
                {
                    if(null == $administrator) throw new Exception('administrator_id 不正确');
                }
            }
            else
            {
                $administratorLog->administrator_id = 0;
            }
        }
        if($type == self::TYPE_LOGIN_SUCCESS)
        {
            /** @var AdministratorLog $adminLog */
            $adminLog = AdministratorLog::find()->where(['administrator_id' => $administrator->id,
                'type' => self::TYPE_LOGIN_SUCCESS,'sign' => 1])->orderBy(['created_at' => SORT_DESC])->limit(1)->one();
            if($adminLog)
            {
                $adminLog->sign = 0;
                $adminLog->save(false);
            }
        }

        $administratorLog->ip = Yii::$app->request->userIP ? Yii::$app->request->userIP : '';
        $administratorLog->desc = $desc;
        $administratorLog->type = $type;
        $administratorLog->created_at = time();
        if(!$administratorLog->save(false)) return false;
        return true;
    }
}
