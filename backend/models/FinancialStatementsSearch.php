<?php

namespace backend\models;

use common\models\Administrator;
use common\models\FundsRecord;
use common\models\Order;
use common\models\PayRecord;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class FinancialStatementsSearch extends Model
{
    public $keyword;
    public $type;
    public $payment_method;
    public $starting_time;
    public $end_time;
    public $orientation;
    public $is_proxy;
    public $receipt_date;
    public $receipt_start_date;
    public $receipt_end_date;
    public $pay_method;
    public $is_invoice;
    public $financial_code;
    public $amount;
    public $amount_keyword;

    /**
     * @var Administrator
     */
    public $administrator;

    const VIRTUAL_SN = 1;     //虚拟订单号
    const SUB_ORDER = 2;      //子订单号
    const TYPE_USER_PHONE = 3;//客户联系方式
    const TYPE_USER_NAME = 4; //客户姓名

    const ORIENTATION_ACTIVE = 1;//付款
    const ORIENTATION_DISABLED = 2;//退款

    const FINANCIAL_CODE = 5; //财务明细编号
    const RECEIPT_COMPANY = 6; //收款公司



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter'=> 'trim'],
            [['type','payment_method','orientation','is_proxy','pay_method','is_invoice'], 'integer'],
            ['keyword', 'safe'],
            [['starting_time', 'end_time','receipt_date','receipt_start_date','receipt_end_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time','receipt_start_date'], 'validateTimes'],
            [['financial_code','amount'], 'string'],
            [['amount_keyword'], 'number'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '开始时间不能大于结束时间！');
        }

        if($this->receipt_start_date>$this->receipt_end_date && $this->receipt_end_date)
        {
            $this->addError('receipt_start_date', '开始时间不能大于结束时间！');
        }

    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '关键词',
            'type' => '类型',
            'is_proxy' => '下单方式',
            'orientation' => '交易类型',
            'payment_method' => '付款方式',
            'starting_time' => '开始时间',
            'end_time' => '结束时间',
            'pay_method'=>'线下付款方式',
            'is_invoice'=>'是否开票',
            'financial_code' => '财务明细编号',
            'amount' => '交易金额',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = FundsRecord::find();
        $query->alias('f');
        $query->innerJoinWith(['user u']);
        $query->joinWith(['receipt r']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()){
            return $dataProvider;
        }

        if($this->administrator->isCompany())
        {
            $query->innerJoinWith(['orders orders']);
            $query->andWhere(['orders.company_id'=> $this->administrator->company_id]);
        }

        if($this->type == self::TYPE_USER_NAME){
            $query->andFilterWhere(['like', 'u.name', $this->keyword]);
        } elseif ($this->type == self::TYPE_USER_PHONE){
            $query->andFilterWhere(['like', 'u.phone', $this->keyword]);
        }elseif ($this->type == self::VIRTUAL_SN){
            $query->andFilterWhere(['like', 'f.virtual_sn', $this->keyword]);
        }elseif ($this->type == self::SUB_ORDER){
            $query->andFilterWhere(['like', 'f.order_sn_list', $this->keyword]);
        }elseif ($this->type == self::FINANCIAL_CODE){
            $query->andFilterWhere(['r.financial_code' => $this->keyword]);
        }elseif ($this->type == self::RECEIPT_COMPANY){
            $query->andFilterWhere(['r.receipt_company' => $this->keyword]);
        }

        if(!empty($this->starting_time))
        {
            $query->andWhere('f.trade_time >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('f.trade_time <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }
        $query->andFilterWhere([
            'f.pay_platform' => $this->payment_method,
        ]);
        if($this->orientation==self::ORIENTATION_DISABLED)
        {
            $query->andFilterWhere([
                'f.orientation' => FundsRecord::PAY_MONEY,
            ]);
        }
        else if($this->orientation==self::ORIENTATION_ACTIVE)
        {
            $query->andFilterWhere([
                'f.orientation' => FundsRecord::MONEY_COLLECTION,
            ]);
        }

        //3.8.5新增
        if(!empty($this ->is_proxy)){
            $query->innerJoinWith(['orders orders']);
            if($this->is_proxy == Order::PROXY_ACTIVE){
                $is_proxy = 0;
            }else{
                $is_proxy = 1;
            }
            $query->andWhere([
                'orders.is_proxy' => $is_proxy
            ]);
        }


        if(!empty($this->receipt_start_date) && !empty($this->receipt_end_date) && ($this -> receipt_start_date <= $this->receipt_end_date)){

            $query->andWhere('r.receipt_date >= :receipt_start_date', [':receipt_start_date' => strtotime($this->receipt_start_date)]);

            $query->andWhere('r.receipt_date < :receipt_end_date', [':receipt_end_date' => strtotime($this->receipt_end_date)+86400]);

        }

        if(!empty($this->pay_method)){
            $query->andWhere(['f.pay_method' =>$this->pay_method]);
        }

        if(!empty($this->is_invoice)){
            $query->andWhere(['r.invoice' => $this->is_invoice]);
        }

        if(!empty($this->amount)){
            if($this -> amount == 'gt_amount'){
                $f = '>=';
            }else if($this ->amount == 'lt_amount'){
                $f = '<=';
            }else{
                $f = '=';
            }

            $query ->andWhere([$f,'amount',$this->amount_keyword]);
        }

        $query->orderBy(['f.trade_time' => SORT_DESC]);
        $query->distinct(true);
        return $dataProvider;
    }

    public static function getOrientation()
    {
        return [
            self::ORIENTATION_ACTIVE => '付款',
            self::ORIENTATION_DISABLED => '退款',
        ];
    }

    public static function getTypes()
    {
        return [
            self::VIRTUAL_SN => '虚拟订单号',
            self::SUB_ORDER => '子订单号',
            self::TYPE_USER_NAME => '客户姓名/昵称',
            self::FINANCIAL_CODE => '财务明细编号',
            self::RECEIPT_COMPANY => '收款公司',
        ];
    }

    public static function getPaymentMethod()
    {
        return [
            PayRecord::PAY_PLATFORM_ALIPAY => '支付宝支付',
            PayRecord::PAY_PLATFORM_WX => '微信支付',
            PayRecord::PAY_PLATFORM_CASH => '线下支付',
        ];
    }
    
    //导出功能对二维的处理
    public function getIsProxy($data)
    {
        foreach ($data as $key => $value) {
            return $value->is_proxy ? $value->creator_name.'后台新增' : '客户自主下单';
        }

    }
}
