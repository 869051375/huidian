<?php

namespace backend\models;

use common\models\Invoice;
use common\models\Order;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class InvoiceSearch
 * @package backend\models
 */

class InvoiceSearch extends Model
{
    public $status;
    public $keyword;
    public $type;

    const TYPE_SN = 1;//订单号
    const TYPE_USER_NAME_PHONE = 2;//客户姓名/手机号
    const TYPE_CUSTOMER_SERVICE_NAME = 3;//客服姓名

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['type'], 'integer'],
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
     *
     * @param $status
     * @return ActiveDataProvider
     */
    public function search($params, $status)
    {
        //todo
        $query = Invoice::find()->alias('i');
        $query->innerJoinWith(['order o']);
        $query->innerJoinWith(['user u']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        if($this->type == self::TYPE_SN){
            //订单号
            $query->andFilterWhere(['like', 'i.order_sn', $this->keyword]);

        } elseif ($this->type == self::TYPE_USER_NAME_PHONE){
            $query->andFilterWhere(['or', ['like', 'u.name', $this->keyword], ['like', 'u.phone', $this->keyword]])
                    ->orFilterWhere(['or', ['like', 'i.phone', $this->keyword],['like', 'i.addressee', $this->keyword]]);

        } elseif ($this->type == self::TYPE_CUSTOMER_SERVICE_NAME){
            $query->andFilterWhere(['like', 'o.customer_service_name', $this->keyword]);
        }

        if($status == 'submitted')
        {
            //发票已提交申请，后台待确认
            $query->andWhere(['and', ['i.status' => Invoice::STATUS_SUBMITTED],
                ['in', 'o.status', [Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE]]]);
        }
        elseif($status == 'confirmed')
        {
            //后台已确认
            $query->andWhere(['and', ['i.status' => Invoice::STATUS_CONFIRMED],
                ['in', 'o.status', [Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE]]]);
        }
        elseif($status == 'invoiced')
        {
            //发票已开具
            $query->andWhere(['i.status' => Invoice::STATUS_INVOICED]);
        }
        elseif($status == 'send')
        {
            //发票已寄送
            $query->andWhere(['i.status' => Invoice::STATUS_SEND]);
        }
        else
        {
            // 全部
            $query->andWhere(['or', ['in', 'i.status', [Invoice::STATUS_SEND, Invoice::STATUS_INVOICED]],
                ['in', 'o.status', [Order::STATUS_IN_SERVICE, Order::STATUS_COMPLETE_SERVICE]]]);
        }
        $query->orderBy(['created_at' => SORT_DESC]);
        return $dataProvider;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_SN => '订单号',
            self::TYPE_USER_NAME_PHONE => '客户姓名/手机号',
            self::TYPE_CUSTOMER_SERVICE_NAME => '客服姓名',
        ];
    }

}
