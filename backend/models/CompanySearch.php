<?php

namespace backend\models;

use common\models\Administrator;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class CompanySearch
 * @package backend\models
 */

class CompanySearch extends Model
{
    const ROLE_TYPE_ADMIN = 1;//角色类型 1:管理员
    const ROLE_TYPE_CUSTOMER_SERVICE = 2;//角色类型 2:客服
    const ROLE_TYPE_SUPERVISOR = 3;//角色类型 3:嘟嘟妹
    const ROLE_TYPE_CLERK = 4;//角色类型 4:服务人员
    const ROLE_TYPE_SALESMAN = 5;//角色类型 5:业务人员

    const DEPARTMENT_TYPE_LEADER = 1;//部门职位类型 1:部门负责人
    const DEPARTMENT_TYPE_MANAGER = 2;//部门职位类型 2:部门领导/助理
    const DEPARTMENT_TYPE_ASSIGN = 3;//部门职位类型 3:商机指定分配人

    public $company_id;//公司id，为0时，为总公司（不启用公司与部门）
    public $department_id;//部门id
    public $keyword;//关键字
    public $role_type;//角色类型 全部，1:管理员，2:客服，3:嘟嘟妹，4:服务人员，5:业务人员
    public $department_type;//部门职位类型 全部，1:部门负责人，2:部门领导/助理，3:商机指定分配人

    /**
     * @var Administrator $administrator
     */
    public $administrator;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['keyword', 'department_type'], 'filter', 'filter' => 'trim'],
            [['department_id','role_type', 'department_type', 'company_id'], 'integer'],
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
     * @return ActiveDataProvider
     */
    public function search($params,$status)
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query = Administrator::find()->alias('a');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if($administrator->isBelongCompany() && $administrator->company_id)
        {
            $query->andFilterWhere(['a.company_id' => $administrator->company_id]);
        }

        if(!empty($this->company_id) && $this->company_id != 99999)
        {
            $query->andFilterWhere(['a.company_id' => $this->company_id]);
        }

        if($this->company_id == 99999)
        {
            $query->andFilterWhere(['a.is_belong_company' => Administrator::BELONG_COMPANY_DISABLED]);
        }

        if($status == 'dimission')
        {
            $query->andFilterWhere(['a.is_dimission' => Administrator::DIMISSION_ACTIVE]);
        }
        else
        {
            $query->andFilterWhere(['a.is_dimission' => Administrator::DIMISSION_DISABLED]);
        }

        if(!empty($this->department_id))
        {
            $query->andFilterWhere(['a.department_id' => $this->department_id]);
        }

        if(!empty($this->role_type))
        {
            if ($this->role_type == self::ROLE_TYPE_ADMIN)
            {
                //角色类型 1:管理员
                $query->andFilterWhere(['a.type' => Administrator::TYPE_ADMIN]);
            }
            elseif ($this->role_type == self::ROLE_TYPE_CUSTOMER_SERVICE)
            {
                //角色类型 2:客服
                $query->andFilterWhere(['a.type' => Administrator::TYPE_CUSTOMER_SERVICE]);
            }
            elseif ($this->role_type == self::ROLE_TYPE_SUPERVISOR)
            {
                //角色类型 3:嘟嘟妹
                $query->andFilterWhere(['a.type' => Administrator::TYPE_SUPERVISOR]);
            }
            elseif ($this->role_type == self::ROLE_TYPE_CLERK)
            {
                //角色类型 4:服务人员
                $query->andFilterWhere(['a.type' => Administrator::TYPE_CLERK]);
            }
            elseif ($this->role_type == self::ROLE_TYPE_SALESMAN)
            {
                //角色类型 5:业务人员
                $query->andFilterWhere(['a.type' => Administrator::TYPE_SALESMAN]);
            }
        }

        if(!empty($this->department_type))
        {
            if ($this->department_type == self::DEPARTMENT_TYPE_LEADER)
            {
                //部门职位类型 1:部门负责人
                $query->innerJoinWith('leader l');
            }
            elseif ($this->department_type == self::DEPARTMENT_TYPE_MANAGER)
            {
                //部门职位类型 2:部门领导/助理
                $query->andFilterWhere(['a.is_department_manager' => Administrator::DEPARTMENT_MANAGER_ACTIVE]);
            }
            elseif ($this->department_type == self::DEPARTMENT_TYPE_ASSIGN)
            {
                //部门职位类型 3:商机指定分配人
                $query->innerJoinWith('assignAdministrator aa');
            }
        }

        if(!empty($this->keyword)){

            //人员名称或者手机号
            $query->andFilterWhere(['or', ['like', 'a.phone', $this->keyword], ['like', 'a.name', $this->keyword]]);
        }

        return $dataProvider;
    }
}
