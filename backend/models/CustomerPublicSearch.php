<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmDepartment;
use common\models\CustomerPublic;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class CustomerPublicSearch
 * @package backend\models
 * @property CrmDepartment $department
 * @property Administrator $administrator
 *
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 * @property Administrator $lastAdministrator
 * @property CustomerPublic $customerPublic
 */

class CustomerPublicSearch extends Model
{
    public $range = 'need_confirm';
    public $start_last_record_date;
    public $end_last_record_date;
    public $administrator_id;
    public $customer_id;
    public $department_id;
    public $company_id;

    public $customer_phone;
    public $last_record_creator_id;
    public $customer_public_id;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['administrator_id', 'customer_id', 'department_id', 'company_id', 'last_record_creator_id', 'customer_public_id'], 'integer'],
            [['start_last_record_date', 'end_last_record_date'], 'date', 'format' => 'yyyy-MM-dd'],
            ['customer_phone', 'filter', 'filter' => 'trim'],
        ];
    }

    public function search()
    {
        $this->validate();
        $query = CrmCustomer::find()->alias('c');

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query->andWhere(['>','c.customer_public_id',0]);

        //启用公司与部门
        if($administrator->isBelongCompany() && $administrator->company_id)
        {
            $query->andWhere(['c.company_id' => $administrator->company_id]);
        }

        if(!empty($this->start_last_record_date) && !empty($this->end_last_record_date))
        {
            $query->andWhere(['between', 'c.last_record', strtotime($this->start_last_record_date), strtotime($this->end_last_record_date)+86400]);
        }
        else
        {
            if(!empty($this->start_last_record_date))
            {
                $query->andWhere(['>=', 'c.last_record', strtotime($this->start_last_record_date)]);
            }
            if(!empty($this->end_last_record_date))
            {
                $query->andWhere(['<=', 'c.last_record', strtotime($this->end_last_record_date)+86400]);
            }
        }

        if(!empty($this->administrator_id) && $administrator->department && $administrator->department->leader_id == $administrator->id)
        {
            $query->joinWith(['department d']);
            $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
        }

        if(!empty($this->last_record_creator_id))
        {
            $query->andWhere(['c.last_record_creator_id' => $this->last_record_creator_id]);
        }

        if(!empty($this->customer_public_id))
        {
            $query->andWhere(['c.customer_public_id' => $this->customer_public_id]);
        }

        if(!empty($this->customer_phone))
        {
            $query->andWhere(['c.phone'=>$this->customer_phone]);
        }
//        $query->orderBy(['c.id' => SORT_DESC]);
        $query->orderBy(['c.move_public_time' => SORT_DESC]);
        $query->groupBy(['c.id']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()->where(['id' => $this->administrator_id])->one();
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getCompanyDepartment()
    {
        return CrmDepartment::find()
            ->where(['id' => $this->department_id, 'company_id' => $this->company_id])->one();
    }

    public function getLastAdministrator()
    {
        return Administrator::find()->where(['id' => $this->last_record_creator_id])->one();
    }

    public function getCustomerPublic()
    {
        return CustomerPublic::find()->where(['id' => $this->customer_public_id])->one();
    }
}