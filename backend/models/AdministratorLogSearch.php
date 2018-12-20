<?php
namespace backend\models;

use common\models\AdministratorLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class AdministratorLogSearch
 * @package backend\models
 */

class AdministratorLogSearch extends Model
{

    public $keyword;
    public $starting_time;
    public $end_time;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['keyword'], 'string'],
            [['starting_time', 'end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '',
        ];
    }


    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '起始时间不能大于结束时间！');
        }
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
     * @param string $status
     * @return ActiveDataProvider
     */
    public function search($params, $status)
    {
        $query = AdministratorLog::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {

            return $dataProvider;
        }

        if(!empty($status))
        {
            if ($status == 'warning')
            {
                $query->andFilterWhere(['type' => AdministratorLog::TYPE_LOGIN_SUCCESS]);
            }
        }
        if(!empty($this->starting_time))
        {
            $query->andWhere('created_at >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('created_at <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        if(!empty($this->keyword))
        {
            $query->andFilterWhere(['like', 'administrator_name', $this->keyword]);
        }
        $query->orderBy(['created_at' => SORT_DESC]);
        return $dataProvider;
    }

}
