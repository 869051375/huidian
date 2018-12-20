<?php

namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmDepartment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class BillsPersonalSearch
 * @package backend\models
 * @property CrmDepartment $department
 * @property Company $company
 */

class BillsPersonalSearch extends Model
{
    public $company_id;
    public $department_id;
    public $keyword;

    public $date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['keyword'], 'string'],
            [['date'],'date', 'format' => 'yyyy-MM'],
            [['department_id','company_id'], 'integer'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'company_id' => '所属公司',
            'department_id' => '部门',
            'keyword' => '自定义搜索',
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
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Administrator::find()->where(['type' => Administrator::TYPE_SALESMAN]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate())
        {
            return $dataProvider;
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if($administrator->isCompany())
        {
            $query->andWhere(['company_id' => $administrator->company_id]);
        }
        else
        {
            if($this->company_id)
            {
                $query->andWhere(['company_id' => $this->company_id]);
            }
        }
        if($this->department_id)
        {
            $query->andWhere(['department_id' => $this->department_id]);
        }
        $query->andFilterWhere(['or', ['like', 'name', $this->keyword], ['like', 'phone', $this->keyword]]);
        return $dataProvider;
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }
}
