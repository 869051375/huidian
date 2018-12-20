<?php

namespace backend\models;

use common\models\City;
use common\models\District;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Province;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class OrderRenewalSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 * @property Product $product
 */

class OrderRenewalSearch extends Model
{
    public $top_category_id;
    public $category_id;
    public $product_id;
    public $keyword;
    public $type = 6;
    public $status;

    public $renewal_status;

    const TYPE_COMPANY_NAME = 1;//公司名称
    const TYPE_USER_NAME = 2;//客户姓名/昵称
    const TYPE_USER_PHONE = 3;//客户联系方式
    const TYPE_CUSTOMER_SERVICE_NAME = 4;//客服姓名
    const TYPE_CLERK_NAME = 5;//服务人员姓名
    const TYPE_SALESMAN_NAME = 7;//业务人员姓名
    const TYPE_SN = 6;//订单号

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['top_category_id', 'category_id', 'product_id', 'type'], 'integer'],
            ['keyword', 'safe'],
        ];
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
            'product_id' => '',
            'keyword' => '关键词',
            'type' => '类型',
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
        $query->innerJoinWith(['user u']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        unset($params['status']);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andWhere(['o.is_renewal' => Order::RENEWAL_ACTIVE]);
        $query->andWhere(['in', 'o.status', [
                Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE,
                Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE
            ]]);

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
        }elseif ($this->type == self::TYPE_USER_NAME){
            //客户姓名/昵称
            $query->andFilterWhere(['like', 'u.name', $this->keyword]);
        }elseif ($this->type == self::TYPE_USER_PHONE){
            //客户联系方式
            $query->andFilterWhere(['like', 'u.phone', $this->keyword]);
        }

        if( null !== $status )
        {
            if($status === Order::RENEWAL_STATUS_PENDING)
            {
                //待续费
                $query->andWhere(['o.renewal_status' => Order::RENEWAL_STATUS_PENDING]);
                $query->andWhere('o.renewal_warn_time <'. time());
                $query->andWhere('o.renewal_warn_time > 0');
            }
            elseif($status === Order::RENEWAL_STATUS_ALREADY)
            {
                //已续费
                $query->andWhere(['o.renewal_status' => Order::RENEWAL_STATUS_ALREADY]);
                $query->andWhere('o.renewal_order_id > 0');//考虑是否加此条件
            }
            elseif($status === Order::RENEWAL_STATUS_NO)
            {
                //无意向
                $query->andWhere(['o.renewal_status' => Order::RENEWAL_STATUS_NO]);
            }
        }

        /** @var \common\models\Administrator $user */
        $user = \Yii::$app->user->identity;
        Order::filterRole($query, $user);
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

    public function getProduct()
    {
        return Product::find()->where(['id' => $this->product_id])->one();
    }

    public static function getTypes()
    {
        return [
            self::TYPE_COMPANY_NAME => '公司名称',
            self::TYPE_USER_NAME => '客户姓名/昵称',
            self::TYPE_USER_PHONE => '客户联系方式',
            self::TYPE_CUSTOMER_SERVICE_NAME => '客服姓名',
            self::TYPE_CLERK_NAME => '服务人员姓名',
            self::TYPE_SALESMAN_NAME => '业务人员',
            self::TYPE_SN => '订单号',
        ];
    }
}
