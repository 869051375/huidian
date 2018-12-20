<?php

namespace backend\models;

use common\models\Order;
use common\models\OrderEvaluate;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class EvaluateSearch
 * @package backend\models
 */

class EvaluateSearch extends Model
{
    public $keyword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['keyword', 'filter', 'filter' => 'trim'],
            [['keyword'], 'string'],

        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [

            'keyword' => '订单号',
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
     * @param $status
     * @return ActiveDataProvider
     */
    public function search($status)
    {
        /** @var \common\models\Administrator $admin */
        $admin = \Yii::$app->user->identity;
        $query = OrderEvaluate::find();
        $query->alias('e');
        $query->innerJoinWith(['order o']);
        $query->innerJoinWith(['customerService']);
        //判断是否区分公司
        if($admin->isBelongCompany() && $admin->company_id)
        {
            $query->andWhere(['o.company_id' => $admin->company_id]);
        }
        //$query->joinWith(['order.clerk']);
        $query->andFilterWhere(['like', 'o.sn', $this->keyword]);
        $query->andFilterWhere(['e.is_audit'=>$status]);
        $query->andFilterWhere(['o.is_evaluate'=>Order::EVALUATE_ACTIVE]);
        $query->orderBy(['e.created_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

}
