<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\BaseDataProvider;

/**
 * Class OrderSearch
* @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package frontend\models
 */

class OrderSearch extends Model
{

    public $keyword;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
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
            'keyword' => '关键词',
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
     *
     * @param $status
     * @return BaseDataProvider
     */
    public function search($params, $status)
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        if($user->is_vest) // 处理马甲专用，马甲不能查看自己的订单
        {
            $dataProvider = new ArrayDataProvider([
                'allModels' => [],
            ]);
            return $dataProvider;
        }

        $query = VirtualOrder::find()->alias('vo');
        $query->innerJoinWith(['orders o']);
        $query->andFilterWhere(['o.is_contract_show' => 1]);
        $query->andWhere(['o.user_id' => \Yii::$app->user->id]);
        $query->andFilterWhere(['o.is_contract_show' => 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
//                'pageSize' => 10,//每页显示条数
                'validatePage' => false,
            ],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        //不显示取消，并且是未付款的订单
//        $query->andWhere('vo.status!=:break', [':break' => VirtualOrder::STATUS_BREAK_PAYMENT]);
        if( null != $status ){
            if($status == 'pending-refund'){
                //退款中(订单退款已审核和申请中2种状态)
//                $query->andWhere(['and', 'o.is_refund=:is_refund', ['in', 'o.refund_status',
//                    [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]]], [':is_refund' => Order::REFUND_ACTIVE]);
                $query->andWhere(['in', 'o.refund_status', [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]]);

            }elseif($status == 'pending-pay'){
                //待付款/未付清
                $query->andWhere(['or', ['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT],
                    ['vo.status' => VirtualOrder::STATUS_UNPAID]]);
            }elseif($status == 'paid-portion'){
                //未付清,暂时不需要单独的未付清
//                $query->andWhere(['vo.status' => VirtualOrder::STATUS_UNPAID]);
            }elseif($status == 'paid'){
                //已付款
                $query->andWhere(['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT]);
            }
            elseif($status === Order::REFUND_STATUS_REFUNDED){
                //已退款
                $query->andWhere(['o.refund_status' => Order::REFUND_STATUS_REFUNDED]);
            }
        }
        else
        {
            $query->andWhere(['or',
                ['in', 'o.refund_status', [Order::REFUND_STATUS_AUDITED, Order::REFUND_STATUS_APPLY]],
                ['or', ['vo.status' => VirtualOrder::STATUS_PENDING_PAYMENT], ['vo.status' => VirtualOrder::STATUS_UNPAID]],
                ['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT],
                ['o.refund_status' => Order::REFUND_STATUS_REFUNDED]
                ]);

            //$query->andFilterWhere(['or', ['!=', 'o.status', Order::REFUND_STATUS_NO], ['!=', 'vo.status', VirtualOrder::STATUS_BREAK_PAYMENT]]);
            //$query->andFilterWhere(['or', ['>', 'vo.payment_amount', 0], ['>', 'vo.refund_amount', 0], ['!=', 'vo.status', VirtualOrder::STATUS_BREAK_PAYMENT]]);
        }
        $query->andFilterWhere(['or', ['like', 'o.sn', $this->keyword], ['like', 'o.product_name', $this->keyword]]);
        $query->orderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }

}
