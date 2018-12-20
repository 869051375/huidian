<?php

namespace backend\models;

use common\models\Document;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DocumentSearch represents the model behind the search form about `common\models\Document`.
 */
class DocumentSearch extends Document
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
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @param $category_id
     *
     * @return ActiveDataProvider
     */
    public function search($params, $category_id)
    {
        $query = Document::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        if(null != $category_id)
        {
            $query->where(['document_category_id' => $category_id]);
        }
        $query->andFilterWhere(['like', 'title', $this->keyword]);

        return $dataProvider;
    }
}
