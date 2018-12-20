<?php

namespace backend\models;

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
 * Class VirtualOrderSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 * @property Product $product
 */

class VirtualOrderSearch extends Model
{

    public $keyword;
    public $type = 3;
    public $status;
    public $source_app;
    public $starting_time;
    public $end_time;

    public $first_pay_start_time;
    public $first_pay_end_time;

    public $is_proxy;

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

    const SOURCE_APP_PC  = 1; //PC端下单
    const SOURCE_APP_WAP = 2; //手机端下单
    const SOURCE_APP_WX  = 3; //微信下单

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['type','is_proxy','source_app'], 'integer'],
            ['keyword', 'safe'],
            [['starting_time', 'end_time','first_pay_start_time','first_pay_end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
            [['first_pay_start_time'], 'validatePayTime'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time > $this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '【下单】起始时间不能大于结束时间！');
        }
    }

    public function validatePayTime()
    {
        if($this->first_pay_start_time > $this->first_pay_end_time && $this->first_pay_end_time)
        {
            $this->addError('first_pay_start_time', '【首次付款时间结束】开始时间不能大于结束时间！');
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
            'starting_time' => '下单时间（起）',
            'end_time' => '下单时间（止）',
            'first_pay_start_time' => '首次付款时间（起）',
            'first_pay_end_time' => '首次付款时间（止）',
            'is_proxy' => '',
            'source_app' => '订单来源',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
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
        $query = VirtualOrder::find()->alias('vo')->innerJoinWith(['orders o']);
        // //关联合同
        // $query->joinWith(['contract c']);
        // //交易流水
        // $query->joinWith(['receipt r']);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        unset($params['status']);
        $this->load($params);
        if (!$this->validate())
        {
            return $dataProvider;
        }
        $query->andFilterWhere(['o.is_contract_show' => 1]);

        if ($this->type == self::TYPE_SN)
        {
            //虚拟订单号
            $query->andFilterWhere(['vo.sn' => $this->keyword]);
        }
        $query->andWhere(['vo.is_vest' => 0]);
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
        } elseif ($this->type == self::TYPE_TRADEMARK_APPLY_NO){
            //商标申请号
            $query->andFilterWhere(['like', 'o.trademark_apply_no', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_NAME){
            //客户姓名/昵称
            $query->innerJoinWith(['user u']);
            $query->andFilterWhere(['like', 'u.name', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_PHONE){
            //客户联系方式
            $query->innerJoinWith(['user u']);
            $query->andFilterWhere(['like', 'u.phone', $this->keyword]);
        }elseif($this->type == self::TYPE_FINANCIAL_CODE)
        {
            //财务明细编号
            $query->andFilterWhere(['=', 'o.financial_code', $this->keyword]);
        }

        if($this->is_proxy === '2')
        {
            $query->andWhere(['o.is_proxy' => 1]);
        }
        else if($this->is_proxy === '1')
        {
            $query->andWhere(['o.is_proxy' => 0]);
        }

        if( null !== $status )
        {
            if($status === VirtualOrder::STATUS_PENDING_PAYMENT)
            {
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]); //待付款
            }
            elseif($status === VirtualOrder::STATUS_UNPAID)
            {
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_UNPAID]); //未付清
            }
            elseif($status === VirtualOrder::STATUS_ALREADY_PAYMENT)
            {
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT]); //已付款
            }elseif($status === VirtualOrder::STATUS_BREAK_PAYMENT)
            {
                $query->andWhere(['o.status' => Order::STATUS_BREAK_SERVICE]); //停止服务取消跟进
            }
        }
        else
        {
            $query->andWhere(['or',['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT]
                ,['vo.status' => VirtualOrder::STATUS_UNPAID]
                ,['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT]
                ,['o.status' => Order::STATUS_BREAK_SERVICE]]);
        }

        //下单时间
        if(!empty($this->starting_time))
        {
            $query->andWhere('vo.created_at >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('vo.created_at <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        //首次付款时间
        if(!empty($this->first_pay_start_time))
        {
            $query->andWhere('o.first_payment_time >= :start_time', [':start_time' => strtotime($this->first_pay_start_time)]);
        }
        if(!empty($this->first_pay_end_time))
        {
            $query->andWhere('o.first_payment_time <= :end_time', [':end_time' => strtotime($this->first_pay_end_time)+86400]);
        }

        /** @var \common\models\Administrator $user */
        $user = \Yii::$app->user->identity;
        Order::filterRole($query, $user);
        $query->groupBy('vo.id')->orderBy(['vo.created_at' => SORT_DESC]);
        return $dataProvider;
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

    public static function getSourceApps()
    {
        return [
            self::SOURCE_APP_PC => 'PC端下单',
            self::SOURCE_APP_WAP => '手机端下单',
            self::SOURCE_APP_WX => '微信下单',
        ];
    }
}
