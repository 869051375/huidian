<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class InvoiceSearch
 * @package frontend\models
 */

class InvoiceSearch extends Model
{

    public $status;

    public function formName()
    {
        return '';
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
        $query = Invoice::find();
        $query->andWhere(['user_id' => \Yii::$app->user->id]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'validatePage' => false,
            ],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        if( null != $status ){
            if($status == 'submitted'){
                $query->andWhere(['in', 'status', [Invoice::STATUS_SUBMITTED, Invoice::STATUS_CONFIRMED, Invoice::STATUS_INVOICED]]);

            }elseif($status == 'send'){
                $query->andWhere(['status' => Invoice::STATUS_SEND]);
            }
        }
        $query->orderBy(['created_at' => SORT_DESC]);
        return $dataProvider;
    }

}
