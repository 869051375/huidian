<?php

namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmDepartment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class BillsDepartmentSearch
 * @package backend\models
 * @property CrmDepartment $department
 * @property Company $company
 */

class BillsDepartmentSearch extends Model
{
    public $company_id;
    public $status;
    public $keyword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['keyword'], 'string'],
            [['company_id','status'], 'integer'],
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
            'status' => '部门状态',
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
        $query = CrmDepartment::find();

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
        if($this->status == 1)
        {
            $query->andWhere(['status' => 1]);
        }
        else if($this->status == 2)
        {
            $query->andWhere(['status' => 0]);
        }
        $query->andFilterWhere(['like', 'name', $this->keyword]);
        return $dataProvider;
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }
}
