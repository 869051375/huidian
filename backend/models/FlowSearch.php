<?php

namespace backend\models;

use common\models\Flow;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * FlowSearch represents the model behind the search form about `common\models\Flow`.
 */
class FlowSearch extends Flow
{
    public $keyword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '关键词',
        ];
    }

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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Flow::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andWhere(['is_delete' => Flow::DELETE_NOT]);
        $query->andFilterWhere(['like', 'name', $this->keyword]);

        return $dataProvider;
    }
}
