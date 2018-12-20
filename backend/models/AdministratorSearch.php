<?php

namespace backend\models;

use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class AdministratorSearch
 * @package backend\models
 * @property CrmDepartment $department
 */

class AdministratorSearch extends Model
{

    public $keyword;
    public $department_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['keyword', 'filter', 'filter' => 'trim'],
            [['keyword'], 'string'],
            [['department_id'], 'integer'],

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
            'department_id' => '部门',
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
     * @param string $status
     * @return ActiveDataProvider
     */
    public function search($params, $status)
    {
        $query = Administrator::find()->where(['is_dimission' => Administrator::DIMISSION_DISABLED]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {

            return $dataProvider;
        }

        //获取账号类型
        if(null != $status)
        {
            if($status == 'manager')
            {
                $query->andFilterWhere(['type' => Administrator::TYPE_ADMIN]);
            }
            elseif($status == 'customer-service')
            {
                $query->andFilterWhere(['type' => Administrator::TYPE_CUSTOMER_SERVICE]);
            }
            elseif($status == 'supervisor')
            {
                $query->andFilterWhere(['type' => Administrator::TYPE_SUPERVISOR]);
            }
            elseif($status == 'clerk')
            {
                $query->andFilterWhere(['type' => Administrator::TYPE_CLERK]);
            }
            elseif($status == 'salesman')
            {
                $query->andFilterWhere(['type' => Administrator::TYPE_SALESMAN]);
            }
        }

        if(!empty($this->department_id))
        {

            $query->andWhere(['department_id' => $this->department_id]);
        }
        $query->andFilterWhere(['or', ['like', 'name', $this->keyword], ['like', 'username', $this->keyword], ['like', 'phone', $this->keyword]]);
        return $dataProvider;
    }


    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }
}
