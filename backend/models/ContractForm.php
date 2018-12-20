<?php

namespace backend\models;

use common\jobs\SendSmsJob;
use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\AdministratorLog;
use common\models\Contract;
use common\models\ContractRecord;
use common\models\ContractType;
use common\models\CrmCustomer;
use common\models\CrmOpportunity;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOperationRecord;
use common\models\OrderRecord;
use common\models\Product;
use common\models\ProductPrice;
use common\models\VirtualOrder;
use common\utils\BC;
use shmilyzxt\queue\base\Queue;
use Yii;
use yii\base\Model;
use yii\log\Logger;

class ContractForm extends Model
{
    public $name;
    public $serial_number;
    public $contract_no;
    public $contract_type_id;
    public $customer_id;
    public $virtual_order_id;
    public $opportunity_id;
    public $administrator_id;
    public $status;
    public $signing_date;
    public $remark;
    public $creator_id;
    public $creator_name;
    public $file;
    public $file_name;
    public $is_installment;

    public $products = [];


    public $items = [];

    public $commodity = [];

    /**
     * @var CrmOpportunity
     */
    public $opportunity;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var ContractType
     */
    public $contractType;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'virtual_order_id', 'opportunity_id', 'administrator_id', 'status',], 'integer'],
            [['name', 'contract_type_id', 'signing_date', 'customer_id', 'opportunity_id', 'administrator_id','products'], 'required'],
            ['customer_id', 'validateCustomerId'],
            ['administrator_id', 'validateAdministratorId'],
            ['opportunity_id', 'validateOpportunityId'],
            ['products', 'validateProducts'],
            [['remark','file','file_name'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['serial_number'], 'string', 'max' => 25],
            [['contract_no'], 'string', 'max' => 255],
            ['contract_type_id', 'validateContractTypeId'],
            [['signing_date'],'date', 'format' => 'yyyy-MM-dd'],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    public function validateContractTypeId()
    {
        $this->contractType = ContractType::findOne($this->contract_type_id);
        if(null == $this->contractType)
        {
            $this->addError('customer_id','找不到指定的合同类型');
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id','找不到指定的业务员');
        }
    }

    public function validateCustomerId()
    {
        $this->customer = CrmCustomer::findOne($this->customer_id);
        if(null == $this->customer)
        {
            $this->addError('customer_id','找不到指定的客户');
        }
        if(null == $this->customer->user)
        {
            $this->addError('customer_id','合同创建失败，当前客户没有手机号，请补充！');
        }
    }

    public function validateOpportunityId()
    {
        $this->opportunity = Niche::find()->where(['id' => $this->opportunity_id])
            ->andWhere(['customer_id' => $this->customer_id])->limit(1)->one();
        if(null == $this->opportunity)
        {
            $this->addError('customer_id','找不到指定的商机');
        }
    }

    public function validateProducts()
    {
        $totalQty = 0;
        $count = count($this->products);
        $this->products = array_values($this->products);
        foreach($this->products as $i => $model)
        {
            if(!preg_match("/^\d*$/",$model['product_id']))
            {
                $this->addError('products', '商品ID必须是整数');
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
            if($product->isPackage() && $model['price'] != 0)
            {
                $this->addError('products', '套餐商品不能变动金额！');
            }
            if($product)
            {
                if($count > 1 && $product->isPackage())
                {
                    continue;
                }
                else
                {
                    /** @var ProductPrice $pp */
                    $pp = null;
                    if($product->isAreaPrice())
                    {
                        $pp = $product->getProductPrice($model['product_price_id']);
                        if($pp && (floatval(BC::add($pp->price,$model['price'])) < 0))
                        {
                            $this->addError('adjust_price', '变动金额超出范围');
                        }
                    }
                    else
                    {
                        if(!$product->isBargain() && (floatval(BC::add($product->price,$model['price'])) < 0))
                        {
                            $this->addError('adjust_price', '变动金额超出范围');
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
                        'is_installment' => (int)$model['is_installment'],
                        'price' => $product->getPrice($pp),
                        'pp' => $pp,
                        'adjustAmount' => $model['price']
                    ];
                }
            }
            else
            {
                $this->addError('products', '编号为'.$i.'的商品信息不正确。');
            }
//            $this->commodity[$model['product_id']]['price'] = $model['price'];
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '合同名称',
            'serial_number' => 'Serial Number',
            'contract_no' => '合同编码',
            'genre' => '合同类型',
            'customer_id' => '客户',
            'virtual_order_id' => 'Virtual Order ID',
            'opportunity_id' => '商机',
            'administrator_id' => '负责人',
            'status' => 'Status',
            'signing_date' => '签订日期',
            'remark' => '合同备注',
            'file' => '合同附件',
            'products' => '商品',
            'contract_type_id' => '合同类型',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param VirtualOrder $vo
     * @return Contract
     */
    private function create($vo)
    {
        $count = Contract::find()->select('id')->where(['contract_type_id' => $this->contract_type_id])->count();
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $model = new Contract();
        if($this->file)
        {
            $model->addFile($this->file,$this->file_name);
        }
        $model->name = $this->name;
        $model->serial_number = $this->contractType->getFinancialCode().($count+1);//财务编号
        $model->contract_type_id = $this->contract_type_id;
        $model->genre = $this->contractType->name.$this->contractType->desc;
        $model->customer_id = $this->customer_id;
        $model->virtual_order_id = $vo->id;
        $model->opportunity_id = $this->opportunity_id;
        $model->administrator_id = $this->administrator_id;
        $model->company_id = $this->administrator->company_id;
        $model->department_id = $this->administrator->department_id;
        $model->department_path = $this->administrator->department->path;
        $model->status = Contract::STATUS_CONTRACT;
        $model->sign_status = Contract::MODIFY_PENDING_REVIEW;
        $model->remark = $this->remark;
        $model->signing_date = strtotime($this->signing_date);
        $model->creator_id = $admin->id;
        $model->creator_name = $admin->name;
        $model->contract_no = $this->contractType->getCode().($count+1);//合同编号
        $model->save(false);
//        $this->opportunity->contract_id = $model->id;
//        $this->opportunity->virtual_order_id = $vo->id;
//        $this->opportunity->status = CrmOpportunity::STATUS_APPLY;
//        $this->opportunity->progress = 100;
//        $this->opportunity->save(false);
        ContractRecord::CreateRecord($model->id,$admin->name.'创建了合同',$admin);
        ContractRecord::CreateRecord($model->id,$admin->name.'提交了合同签约审批',$admin);
        return $model;
    }

    /**
     * @param VirtualOrder $vo
     * @throws \Exception
     */
    private function adjustPrice($vo)
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
                        $a = AdjustOrderPrice::createAdjustPrice($order->id,$order->virtual_order_id,AdjustOrderPrice::STATUS_PENDING,$administrator,$this->commodity[$order->id],'创建订单申请价格变动');
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
                'is_installment' => $item['is_installment'],
                'qty' => $item['qty'],
                'adjustAmount' => $item['adjustAmount']
            ];
        }

        $time = time();
        $vo = VirtualOrder::createNew($items, $this->customer->user, false, true, $this->administrator, null, null, null, null, null,$time,$this->opportunity->business_subject_id,0);

        $vo->sumTotalRemitAmount();
        //新增订单记录
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        foreach($items['adjustItems'] as $adjustItem)
        {
            $this->commodity[$adjustItem['order_id']] = $adjustItem['adjustAmount'];
        }
        $this->adjustPrice($vo);
        $contract = $this->create($vo);
        foreach($vo->orders as $o)
        {
            //财务明细编号
            $o->financial_code = $contract->serial_number;
            $o->save(false);
            OrderRecord::create($o->id, '订单提交成功', '', $admin);
        }

        //关联到商机
        $niche_c = new NicheContract();
        $niche_c->niche_id = $this->opportunity->id;
        $niche_c->contract_id = $contract->id;
        $niche_c->save(false);

        //添加操作记录
        \backend\modules\niche\models\NicheOperationRecord::create($this->opportunity->id,'创建合同','通过商机创建了合同，合同编码为：'.$contract->contract_no);

        //新增后台操作日志
        AdministratorLog::logSubmitOrder($vo);
        return $vo;
    }
}
