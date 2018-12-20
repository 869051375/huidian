<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\OpportunityPublic;
use common\models\OpportunityTag;
use common\models\Source;
use common\models\Tag;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class OpportunityPublicSearch
 * @package backend\models
 * @property CrmDepartment $department
 * @property Administrator $administrator
 *
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 * @property Administrator $lastAdministrator
 * @property OpportunityPublic $opportunityPublic
 */

class OpportunityPublicSearch extends Model
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
    public $opportunity_public_id;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['administrator_id', 'customer_id', 'department_id', 'company_id','last_record_creator_id','opportunity_public_id'], 'integer'],
            [['start_last_record_date', 'end_last_record_date'], 'date', 'format' => 'yyyy-MM-dd'],
            ['customer_phone', 'filter', 'filter' => 'trim'],
        ];
    }

    public function search()
    {
        $this->validate();
        $query = CrmOpportunity::find()->alias('o');
        if(!empty($this->customer_id))
        {
            $query->andWhere(['customer_id' => $this->customer_id]);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $query->andWhere(['>','o.opportunity_public_id',0]);

        //当前登录人与商机同部门或者商机对应部门的下属部门
        if($administrator->isBelongCompany() && $administrator->department_id)
        {
//            $query->andWhere(['or', ['o.department_id' => $administrator->department_id], ['o.department_id' => $administrator->department->parent_id]]);
            //当前登录人如果属于1级部门，则选取当前登录人所对应公司下的所有部门下的公海商机
            if($administrator->company_id && $administrator->department->parent_id == 0)
            {
                $query->joinWith(['company c']);
                $query->andWhere(['o.company_id' => $administrator->company_id]);
            }
            //当前登录人如果属于1级以下部门，则选取相同商机公海的商机
            elseif ($administrator->department->opportunityPublic)
            {
                $query->andWhere(['o.opportunity_public_id' => $administrator->department->opportunityPublic->id]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }

        if(!empty($this->start_last_record_date) && !empty($this->end_last_record_date))
        {
            $query->andWhere(['between', 'o.last_record', strtotime($this->start_last_record_date), strtotime($this->end_last_record_date)+86400]);
        }
        else
        {
            if(!empty($this->start_last_record_date))
            {
                $query->andWhere(['>=', 'o.last_record', strtotime($this->start_last_record_date)]);
            }
            if(!empty($this->end_last_record_date))
            {
                $query->andWhere(['<=', 'o.last_record', strtotime($this->end_last_record_date)+86400]);
            }
        }

        if(!empty($this->administrator_id) && $administrator->department && $administrator->department->leader_id == $administrator->id)
        {
            $query->joinWith(['department d']);
            $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
        }

        if(!empty($this->administrator_id))
        {
            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
        }

        if(!empty($this->company_id))
        {
            $query->andWhere(['o.company_id' => $this->company_id]);
        }

        if(!empty($this->department_id))
        {
            $query->andWhere(['o.department_id' => $this->department_id]);
        }

        if(!empty($this->last_record_creator_id))
        {
            $query->andWhere(['o.last_record_creator_id' => $this->last_record_creator_id]);
        }

        if(!empty($this->opportunity_public_id))
        {
            $query->andWhere(['o.opportunity_public_id' => $this->opportunity_public_id]);
        }

        if(!empty($this->customer_phone))
        {
            $query->joinWith(['customer c']);
            $query->andWhere(['c.phone'=>$this->customer_phone]);
        }
//        $query->orderBy([new Expression('ISNULL(o.next_follow_time) ASC'), 'o.next_follow_time' => SORT_ASC, 'o.created_at' => SORT_DESC]);
        $query->orderBy( ['o.move_public_time' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    //待优化 (获取所有商品的钱数)
    public function searchCount()
    {
        $searchArr['where']=['and'];//查询条件
        $this->validate();
        // $query = CrmOpportunity::find()->select('total_amount')->alias('o');
        if(!empty($this->customer_id))
        {
            array_push($searchArr['where'],['=,','customer_id' ,$this->customer_id]);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
             array_push($searchArr['where'],['>','o.opportunity_public_id',0]);

        //当前登录人与商机同部门或者商机对应部门的下属部门
        if($administrator->isBelongCompany() && $administrator->department_id)
        {
//            $query->andWhere(['or', ['o.department_id' => $administrator->department_id], ['o.department_id' => $administrator->department->parent_id]]);
            //当前登录人如果属于1级部门，则选取当前登录人所对应公司下的所有部门下的公海商机
            if($administrator->company_id && $administrator->department->parent_id == 0)
            {
                // $query->joinWith(['company c']);
              array_push($searchArr['where'],['=','o.company_id', $administrator->company_id]);
            }
            //当前登录人如果属于1级以下部门，则选取相同商机公海的商机
            elseif ($administrator->department->opportunityPublic)
            {
                array_push($searchArr['where'],['=','o.opportunity_public_id', $administrator->department->opportunityPublic->id]);
            }
            else
            {
                // $query->andWhere('0=1');
                // array_push($searchArr['where'], ['=','o'])
            }
        }

        if(!empty($this->start_last_record_date) && !empty($this->end_last_record_date))
        {
            array_push($searchArr['where'],['between', 'o.last_record', strtotime($this->start_last_record_date), strtotime($this->end_last_record_date)+86400]);
        }
        else
        {
            if(!empty($this->start_last_record_date))
            {
                array_push($searchArr['where'],['>=', 'o.last_record', strtotime($this->start_last_record_date)]);
            }
            if(!empty($this->end_last_record_date))
            {
               array_push($searchArr['where'],['<=', 'o.last_record', strtotime($this->end_last_record_date)+86400]);
            }
        }

        if(!empty($this->administrator_id) && $administrator->department && $administrator->department->leader_id == $administrator->id)
        {
            // $query->joinWith(['department d']);
            array_push($searchArr['where'],['or', "d.path like '". $administrator->department->path."-%'", ['=','d.id', $administrator->department_id]]);
            array_push($searchArr['where'],['not in', 'o.administrator_id', [$administrator->id]]);
            array_push($searchArr['where'],['=','o.administrator_id' ,$this->administrator_id]);
        }

        if(!empty($this->administrator_id))
        {
            array_push($searchArr['where'],['=','o.administrator_id' ,$this->administrator_id]);
        }

        if(!empty($this->company_id))
        {
           array_push($searchArr['where'],['=','o.company_id',$this->company_id]);
        }

        if(!empty($this->department_id))
        {
            array_push($searchArr['where'],['=','o.department_id',$this->department_id]);
        }

        if(!empty($this->last_record_creator_id))
        {
            array_push($searchArr['where'],['=','o.last_record_creator_id' ,$this->last_record_creator_id]);
        }

        if(!empty($this->opportunity_public_id))
        {
            array_push($searchArr['where'],['=','o.opportunity_public_id', $this->opportunity_public_id]);
        }

        if(!empty($this->customer_phone))
        {
            // $query->joinWith(['customer c']);
            array_push($searchArr['where'],['=','u.phone',$this->customer_phone]);
        }
//        $query->orderBy([new Expression('ISNULL(o.next_follow_time) ASC'), 'o.next_follow_time' => SORT_ASC, 'o.created_at' => SORT_DESC]);
         $result= CrmOpportunity::find()->select('o.total_amount')->from('crm_opportunity o')->leftJoin('company c','c.id=o.company_id')->leftJoin('crm_department d','d.id=o.department_id')->leftJoin('crm_customer u','u.id=o.customer_id')->where($searchArr['where'])->all();

        //  $dataProviderCount = new ActiveDataProvider([
        //     'query' => $query,
        //      'pagination' => [
        //         'pageSize' => $count,
        //     ]
        // ]);
        return $result;
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()->where(['id' => $this->administrator_id])->one();
    }

    public function getLastAdministrator()
    {
        return Administrator::find()->where(['id' => $this->last_record_creator_id])->one();
    }

    public function getOpportunityPublic()
    {
        return OpportunityPublic::find()->where(['id' => $this->opportunity_public_id])->one();
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



    public function searchExcel($param,$limit=null,$offset=null){

        $this->validate();
        $query = new Query();
        $query ->select('any_value(o.id) as id,any_value(o.created_at) as created_at,any_value(o.name) as name,any_value(o.status) as status,any_value(o.progress) as progress,any_value(o.administrator_name) as administrator_name,any_value(o.creator_name) as creator_name,any_value(t.name) as tag_name,any_value(o.customer_id) as customer_id,any_value(o.customer_name) as customer_name,any_value(o.last_record) as last_record,any_value(o.next_follow_time) as next_follow_time,any_value(o.invalid_reason) as invalid_reason,any_value(cs.name) as source_name,any_value(cp.name) as public_name,any_value(o.move_public_time) as move_public_time');
        $query -> from(['o' => CrmOpportunity::tableName()])
            ->leftJoin(['ot'=>OpportunityTag::tableName()],'o.id = ot.opportunity_id')
            ->leftJoin(['t' => Tag::tableName()],'ot.tag_id  = t.id')
            ->leftJoin(['c'=>CrmCustomer::tableName()],'o.customer_id = c.id')
            ->leftJoin(['cs' => Source::tableName()],'c.source = cs.id')
            ->leftJoin(['cp' => OpportunityPublic::tableName()],'o.opportunity_public_id=cp.id');

        $query -> where(['<>','o.opportunity_public_id',0]);

        if(!empty($this->start_last_record_date) && !empty($this->end_last_record_date))
        {
            $query->andWhere(['between', 'o.last_record', strtotime($this->start_last_record_date), strtotime($this->end_last_record_date)+86400]);
        }
        else
        {
            if(!empty($this->start_last_record_date))
            {
                $query->andWhere(['>=', 'o.last_record', strtotime($this->start_last_record_date)]);
            }
            if(!empty($this->end_last_record_date))
            {
                $query->andWhere(['<=', 'o.last_record', strtotime($this->end_last_record_date)+86400]);
            }
        }

        if(!empty($this->last_record_creator_id))
        {
            $query->andWhere(['o.last_record_creator_id' => $this->last_record_creator_id]);
        }

        if(!empty($this->opportunity_public_id))
        {
            $query->andWhere(['o.opportunity_public_id' => $this->opportunity_public_id]);
        }

        if(!empty($this->customer_phone))
        {
            $query->andWhere(['c.phone'=>$this->customer_phone]);
        }

        if($param ==1 ){
            return $query -> count();
        }else{
            return $query -> limit($limit) -> offset($offset) ->all();
        }

    }
}