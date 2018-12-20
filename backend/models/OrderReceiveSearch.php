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
 * Class OrderReceiveSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 * @property Product $product
 */

class OrderReceiveSearch extends Model
{
    public $top_category_id;
    public $category_id;
    public $product_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $status;
    public $starting_time;
    public $end_time;

    public $first_pay_start_time;
    public $first_pay_end_time;
    public $source_app;
    public $is_proxy;

    /**
     * @var Administrator
     */
    public $administrator;

    const SOURCE_APP_PC  = 1; //PC端下单
    const SOURCE_APP_WAP = 2; //手机端下单
    const SOURCE_APP_WX  = 3; //微信下单

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['top_category_id', 'category_id','product_id', 'province_id', 'city_id', 'district_id'], 'integer'],
            ['keyword', 'safe'],
            [['starting_time', 'end_time','first_pay_start_time','first_pay_end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
            [['first_pay_start_time'], 'validatePayTime'],
            [['is_proxy'], 'integer'],
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
            $this->addError('end_service_start_time', '【首次付款时间结束】开始时间不能大于结束时间！');
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
            'province_id' => '地区',
            'starting_time' => '下单时间（起）',
            'end_time' => '下单时间（止）',
            'first_pay_start_time' => '首次付款时间（起）',
            'first_pay_end_time' => '首次付款时间（止）',
            'source_app' => '订单来源',
            'is_proxy' => '',
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
        $query = Order::find()->alias('o')->innerJoinWith(['virtualOrder vo'])->innerJoinWith(['user u'])->andWhere(['o.salesman_aid' => 0]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        unset($params['status']);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'o.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'o.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'o.product_id' => $this->product_id <= 0 ? null : $this->product_id,
        ]);

        if( null !== $status )
        {
            if('refund' === $status)
            {
                //退款中包含申请中
                $query->andWhere(['o.refund_status'=> Order::REFUND_STATUS_APPLY]);
            }
            elseif($status === Order::STATUS_PENDING_ALLOT)
            {
                //待分配
                $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
                    ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY]]);
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
        else
        {
            //待分配
            $query->andWhere(['or', ['o.status' => Order::STATUS_PENDING_ALLOT],
                ['o.is_pay_after_service' => Order::PAY_AFTER_SERVICE_ACTIVE, 'o.status' => Order::STATUS_PENDING_PAY],
                ['o.refund_status'=> Order::REFUND_STATUS_APPLY],
                ['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT],
                ['vo.status' => VirtualOrder::STATUS_UNPAID]]);
            $query->andWhere(['not in', 'o.refund_status', [Order::REFUND_STATUS_APPLY, Order::REFUND_STATUS_AUDITED]]);
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

        if($status === 'vest')
        {
            $query->andWhere(['vo.is_vest' => '1']);
        }

        if($this->is_proxy === '2')
        {
            $query->andWhere(['o.is_proxy' => 1]);
        }
        else if($this->is_proxy === '1')
        {
            $query->andWhere(['o.is_proxy' => 0]);
        }

        $query->orderBy(['o.created_at' => SORT_DESC]);
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
}
