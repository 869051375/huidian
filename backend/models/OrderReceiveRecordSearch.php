<?php

namespace backend\models;

use common\models\AdjustOrderPrice;
use common\models\Administrator;
use common\models\City;
use common\models\District;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Province;
use common\models\VirtualOrder;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class OrderSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 * @property Product $product
 */

class OrderReceiveRecordSearch extends Model
{
    public $top_category_id;
    public $category_id;
    public $product_id;
    public $keyword;
    public $type = 6;
    public $province_id;
    public $city_id;
    public $district_id;
    public $status;
    public $starting_time;
    public $end_time;
    public $source_app;
    public $is_proxy;
    public $is_installment;

    public $first_pay_start_time;
    public $first_pay_end_time;

    /**
     * @var Administrator
     */
    public $administrator;

    const TYPE_COMPANY_NAME = 1;//公司名称
    const TYPE_USER_NAME = 2;//客户姓名/昵称
    const TYPE_USER_PHONE = 3;//客户联系方式
    const TYPE_CUSTOMER_SERVICE_NAME = 4;//客服姓名
    const TYPE_CLERK_NAME = 5;//服务人员姓名
    const TYPE_SN = 6;//订单号
    const TYPE_SALESMAN_NAME = 7;//业务人员姓名
    const TYPE_TRADEMARK_APPLY_NO = 8;//商标申请号
    const TYPE_FINANCIAL_CODE = 9;//财务明细编号

    const BREAK_REASON_NOT_FOLLOW = 1;//停止跟进
    const BREAK_REASON_REFUND_AND_CANCEL = 2;//退款并且取消
    const BREAK_REASON_USER_CANCEL = 3;//客户主动取消
    const BREAK_REASON_OVERTIME_CLOSE = 4;//订单关闭(超时)

    const SOURCE_APP_PC  = 1; //PC端下单
    const SOURCE_APP_WAP = 2; //手机端下单
    const SOURCE_APP_WX  = 3; //微信下单

    const EXPECTED_TOTAL_COST = 0;
    const EXPECTED_TOTAL_COST_NOT = 1;//未录入
    const EXPECTED_TOTAL_COST_FINISH = 2;//已录入

    const INSTALLMENT_ONE = 0;
    const INSTALLMENT_TRUE = 1;//分期付款
    const INSTALLMENT_FALSE = 2;//一次付款

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['top_category_id', 'category_id', 'is_installment','type','product_id','is_proxy', 'source_app','province_id', 'city_id', 'district_id'], 'integer'],
            ['keyword', 'safe'],
            [['starting_time', 'end_time','first_pay_start_time','first_pay_end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
            [['first_pay_start_time'], 'validatePayTime'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '【下单】起始时间不能大于结束时间！');
        }
    }

    public function validatePayTime()
    {
        if($this->first_pay_start_time > $this->first_pay_end_time && $this->first_pay_end_time)
        {
            $this->addError('first_pay_end_time', '【首次付款时间结束】开始时间不能大于结束时间！');
        }
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'top_category_id' => '商品类目',
            'category_id' => '',
            'city_id' => '',
            'district_id' => '',
            'product_id' => '',
            'keyword' => '关键词',
            'type' => '类型',
            'source_app' => '下单来源',
            'province_id' => '地区',
            'starting_time' => '下单时间（起）',
            'end_time' => '下单时间（止）',
            'first_pay_start_time' => '首次付款时间（起）',
            'first_pay_end_time' => '首次付款时间（止）',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param int $status
     * @return ActiveDataProvider
     */
    public function search($params, $status)
    {
        $query = Order::find()->alias('o');
        $query->innerJoinWith(['virtualOrder vo']);
        $query->innerJoinWith(['user u']);
        $query->innerJoinWith(['orderVoucher ov']);

        //$query = Order::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        unset($params['status']);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

//        if($this->administrator->isBelongCompany() && $this->administrator->company_id)
//        {
//            $query->andWhere(['o.company_id'=> $this->administrator->company_id]);
//        }

        $query->andFilterWhere([
            'o.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'o.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'o.product_id' => $this->product_id <= 0 ? null : $this->product_id,
        ]);

        if($this->type == self::TYPE_COMPANY_NAME){
            //公司名称
            $query->andFilterWhere(['like', 'o.company_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_CUSTOMER_SERVICE_NAME){
            //客服姓名
            $query->andFilterWhere(['like', 'o.customer_service_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_CLERK_NAME){
            //服务人员姓名
            $query->andFilterWhere(['like', 'o.clerk_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_SALESMAN_NAME){
            //业务人员姓名
            $query->andFilterWhere(['like', 'o.salesman_name', $this->keyword]);
        } elseif ($this->type == self::TYPE_SN){
            //订单号
            $query->andFilterWhere(['like', 'o.sn', $this->keyword]);
        } elseif ($this->type == self::TYPE_TRADEMARK_APPLY_NO){
            //商标申请号
            $query->andFilterWhere(['like', 'o.trademark_apply_no', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_NAME){
            //客户姓名/昵称
            $query->andFilterWhere(['like', 'u.name', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_PHONE){
            //客户联系方式
            $query->andFilterWhere(['like', 'u.phone', $this->keyword]);
        }elseif($this->type == self::TYPE_FINANCIAL_CODE)
        {
            //财务明细编号
            $query->andFilterWhere(['like', 'o.financial_code', $this->keyword]);
        }

        if( null !== $status )
        {
            if('refund' === $status)
            {
                //退款中包含申请中和已审核2种状态
                $query->andWhere(['in', 'o.refund_status', [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]]);
            }
            elseif($status === Order::STATUS_PENDING_ALLOT)
            {
                //待分配
                $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
                    ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY]]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status === Order::STATUS_PENDING_SERVICE)
            {
                //待服务
                $query->andWhere(['o.status' => Order::STATUS_PENDING_SERVICE]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status === Order::STATUS_IN_SERVICE)
            {
                $query->andWhere(['o.status' => Order::STATUS_IN_SERVICE]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status === Order::STATUS_COMPLETE_SERVICE)
            {
                $query->andWhere(['o.status' => Order::STATUS_COMPLETE_SERVICE]);
            }
            elseif($status === Order::STATUS_BREAK_SERVICE)
            {
                $query->andWhere(['o.status' => Order::STATUS_BREAK_SERVICE]);
                $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
            }
            elseif($status === Order::STATUS_PENDING_PAY)
            {
                //待付款
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]);
            }
            elseif($status === Order::STATUS_UNPAID)
            {
                //未付清
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_UNPAID]);
            }
        }

        //订单来源
        if($this->source_app == self::SOURCE_APP_PC)
        {
            //PC端下单
            $query->andWhere(['o.source_app' => Order::SOURCE_APP_PC]);
        }
        elseif ($this->source_app == self::SOURCE_APP_WAP)
        {
            //手机端下单
            $query->andWhere(['o.source_app' => Order::SOURCE_APP_WAP]);
        }
        elseif ($this->source_app == self::SOURCE_APP_WX)
        {
            //微信下单
            $query->andWhere(['o.source_app' => Order::SOURCE_APP_WX]);
        }

        if($this->is_installment == self::INSTALLMENT_TRUE)
        {
            $query->andWhere(['o.is_installment' => self::INSTALLMENT_TRUE]);
        }
        elseif ($this->is_installment == self::INSTALLMENT_FALSE)
        {
            $query->andWhere(['o.is_installment' => self::INSTALLMENT_ONE]);
        }

        $query->andFilterWhere([
            'o.province_id' => $this->province_id <= 0 ? null : $this->province_id,
            'o.city_id' => $this->city_id <= 0 ? null : $this->city_id,
            'o.district_id' => $this->district_id <= 0 ? null : $this->district_id,
        ]);

        if(!empty($this->starting_time))
        {
            $query->andWhere('o.created_at >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('o.created_at <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        if(!empty($this->first_pay_start_time))
        {
            $query->andWhere('o.first_payment_time >= :start_time', [':start_time' => strtotime($this->first_pay_start_time)]);
        }
        if(!empty($this->first_pay_end_time))
        {
            $query->andWhere('o.first_payment_time <= :end_time', [':end_time' => strtotime($this->first_pay_end_time)+86400]);
        }

        if('warning' !== $status)
        {
            $query->orderBy(['vo.created_at' => SORT_DESC]);
        }
        if($status === 'vest')
        {
            $query->andWhere(['vo.is_vest' => '1']);
        }
        else
        {
            $query->andWhere(['vo.is_vest' => 0]);
        }
        if($this->is_proxy === '2')
        {
            $query->andWhere(['o.is_proxy' => 1]);
        }
        else if($this->is_proxy === '1')
        {
            $query->andWhere(['o.is_proxy' => 0]);
        }
        if($status === 'adjust-price')
        {
            $query->andWhere(['o.adjust_status' => AdjustOrderPrice::STATUS_PENDING, 'vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]);
        }

        return $dataProvider;
    }

    public function getCategory()
    {
        return ProductCategory::find()->where(['id' => $this->category_id])
            ->andWhere('parent_id!=:parent_id', [':parent_id' => '0'])->one();
    }

    public function getTopCategory()
    {
        return ProductCategory::find()->where(['id' => $this->top_category_id, 'parent_id' => '0'])->one();
    }

    public function getProvince()
    {
        return Province::find()->where('id=:id', [':id' => $this->province_id])->one();
    }

    public function getCity()
    {
        return City::find()->where(['id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])->one();
    }

    public function getProduct()
    {
        return Product::find()->where(['id' => $this->product_id])->one();
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id = :city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])
            ->one();
    }

    public static function getTypes()
    {
        return [
            self::TYPE_COMPANY_NAME => '公司名称',
            self::TYPE_FINANCIAL_CODE => '财务明细编号',
            self::TYPE_USER_NAME => '客户姓名/昵称',
            self::TYPE_USER_PHONE => '客户联系方式',
            self::TYPE_CUSTOMER_SERVICE_NAME => '客服姓名',
            self::TYPE_CLERK_NAME => '服务人员姓名',
            self::TYPE_SALESMAN_NAME => '业务人员',
            self::TYPE_SN => '订单号',
            self::TYPE_TRADEMARK_APPLY_NO => '商标申请号',

        ];
    }

    public static function getBreakReasons()
    {
        return [
            self::BREAK_REASON_NOT_FOLLOW => '停止跟进',
            self::BREAK_REASON_REFUND_AND_CANCEL => '退款并且取消',
            self::BREAK_REASON_USER_CANCEL => '客户主动取消',
            self::BREAK_REASON_OVERTIME_CLOSE => '订单关闭',
        ];
    }

    public static function getSourceApps()
    {
        return [
            self::SOURCE_APP_PC => 'PC端下单',
            self::SOURCE_APP_WAP => '手机端下单',
            self::SOURCE_APP_WX => '微信下单',
        ];
    }

    public static function getCost()
    {
        return [
            self::EXPECTED_TOTAL_COST_NOT => '未录入',
            self::EXPECTED_TOTAL_COST_FINISH => '已录入',
        ];
    }

    public static function getInstallment()
    {
        return [
            self::INSTALLMENT_FALSE => '一次付款',
            self::INSTALLMENT_TRUE => '分期付款',
        ];
    }
}
