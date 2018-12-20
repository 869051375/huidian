<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/14
 * Time: 下午1:50
 */

namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmDepartment;
use common\models\CrmOpportunity;
use common\models\CrmOpportunityProduct;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Source;
use common\models\Tag;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class OpportunitySearch
 * @package backend\models
 * @property CrmDepartment $department
 * @property Administrator $administrator
 *
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Product $product
 * @property Tag $opportunityTag
 * @property Source $customerSource
 */

class OpportunitySearch extends Model
{
    public $range = 'need_confirm';
    public $start_date;
    public $end_date;
    public $status;
    public $keyword_field;
    public $keyword;
    public $start_last_record_date;
    public $end_last_record_date;
    public $administrator_id;
    public $customer_id;
    public $amount;
    public $amount_keyword;
    public $department_id;
    public $company_id;
    public $customer_source;
    public $top_category_id;
    public $category_id;
    public $product_id;
    public $scene;
    public $opportunity_name;
    public $opportunity_progress;
    public $followed;
    public $opportunity_progress_twenty;
    public $opportunity_progress_forty;
    public $opportunity_progress_sixty;
    public $opportunity_progress_eighty;
    public $opportunity_progress_hundred;

    public $page_size;
    public $tag_id;

    const STATUS_UNCONFIRMED = 1;//待确认商机
    const STATUS_NOT_DEAL = 2;//跟进中商机
    const STATUS_APPLY = 3;//申请中商机
    const STATUS_FAIL = 4;//已失败商机
    const STATUS_DEAL = 5;//已成交商机

    const OPPORTUNITY_FOLLOWING = 1;//我的商机
    const OPPORTUNITY_SUB_NEED_CONFIRM = 2;//我下属待确认的商机
    const OPPORTUNITY_SUB = 3;//我下属负责的商机
    const OPPORTUNITY_SHARED = 4;//有我分享的商机
    const OPPORTUNITY_ALL = 0;//全部商机

    public static function getKeywordFields()
    {
        return [
            'opportunity_name' => '商机名称',
            'customer_name' => '客户名称/昵称',
            '0' => '字段类型',
            'opportunity_id' => '商机ID',
            'customer_phone' => '手机号',
            'customer_id' => '客户ID',
            'tel' => '联系电话',
            'caller' => '来电电话',
            'wechat' => '微信',
            'qq' => 'QQ',
            'email' => '邮箱',
            // 'scene' => '场景',
        ];
    }

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['range'], 'in', 'range' => [
                'need_confirm',
                'sub_need_confirm',
                'sub_confirm',
                'following',
                'deal',
                'sub',
                'fail',
                'shared',
                'all',
                ]],
            [['keyword_field'], 'in', 'range' => [
                '0',
                'opportunity_name',
                'customer_name',
                'opportunity_id',
                'customer_phone',
                'customer_id',
                'tel',
                'caller',
                'wechat',
                'qq',
                'email',
            ]],
            [['administrator_id', 'customer_id', 'department_id', 'company_id','top_category_id', 'category_id','product_id',], 'integer'],
            [['keyword'], 'string'],
//            [['status'], 'in', 'range' => [
//                'not_deal_20',
//                'not_deal_40',
//                'not_deal_60',
//                'not_deal_80',
//                'apply',
//                'deal',
//                'fail',
//            ]],
            [['amount'], 'in', 'range' => [
                'gt_amount',
                'lt_amount',
            ]],
            [['amount_keyword'], 'string'],
            [['amount_keyword'], 'filter', 'filter' => 'trim'],
            [['start_last_record_date', 'end_last_record_date', 'start_date', 'end_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['range'], 'default', 'value' => 'need_confirm'],
//            [['customer_source'], 'in', 'range' => array_keys(CrmCustomer::getSourceList())],
            [['scene', 'status', 'page_size', 'customer_source'], 'integer'],
            [['opportunity_name', 'opportunity_progress', 'opportunity_progress_twenty',
                'opportunity_progress_forty', 'opportunity_progress_sixty', 'opportunity_progress_hundred',
               'opportunity_progress_eighty', 'opportunity_progress_hundred', 'followed', 'tag_id'], 'string'],
        ];
    }

    public function search($query1 = null)
    {

        if(null == $query1) {
            $query = CrmOpportunity::find()->alias('o');
        } else {
            $query = $query1;
        }
        //ActiveDataProvider通过使用$ query执行数据库查询来提供数据。
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate())
        {
            return $dataProvider;
        }
        if(!empty($this->customer_id))
        {
            $query->andWhere(['customer_id' => $this->customer_id]);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        // sub_need_confirm
        if($this->range == 'sub_need_confirm')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'sub_confirm')
        {
            $query->andWhere(['o.opportunity_public_id' => 1]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
        }
        else if($this->range == 'following')
        {
            //最新需求：我的商机，显示我的所有商机数据
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
//            $query->andWhere(['o.is_receive' => 1]);
//            $query->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]]);
        }
        else if($this->range == 'deal')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
        }
        else if($this->range == 'sub')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'fail')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
        }
        else if($this->range == 'shared')
        {
            //有我分享的商机(公海中的商机也需要显示)
            $query->andWhere(['o.send_administrator_id' => $administrator->id]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
        }
        else if($this->range == 'all')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
                $query->andWhere(['o.company_id' => $administrator->company_id]);
            }
        }
        else
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.is_receive' => 0, 'o.administrator_id' => $administrator->id]);
        }

        $query->joinWith(['opportunityProducts p']);
        $query->andFilterWhere([
            'p.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'p.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'p.product_id' => $this->product_id <= 0 ? null : $this->product_id,
        ]);

        if(!empty($this->keyword) && !empty($this->keyword_field))
        {
            if($this->keyword_field == 'opportunity_name')
            {
                $query->andWhere(['like', 'o.name', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_name')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.name', $this->keyword]);
            }else if($this->keyword_field == 'opportunity_id')
            {
                $query->andWhere(['o.id' => $this->keyword]);
            }
            else if($this->keyword_field == 'customer_phone')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.phone', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_id')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['c.id' => $this->keyword]);
            }else if($this->keyword_field == 'tel')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.tel', $this->keyword]);
            }
            else if($this->keyword_field == 'caller')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.caller', $this->keyword]);
            }
            else if($this->keyword_field == 'wechat')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.wechat', $this->keyword]);
            }
            else if($this->keyword_field == 'qq')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.qq', $this->keyword]);
            }
            else if($this->keyword_field == 'email')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.email', $this->keyword]);
            }
        }

        if(!empty($this->start_date) && !empty($this->end_date))
        {
            $query->andWhere(['between', 'o.created_at', strtotime($this->start_date), strtotime($this->end_date)+86400]);
        }
        else
        {
            if(!empty($this->start_date))
            {
                $query->andWhere(['>=', 'o.created_at', strtotime($this->start_date)]);
            }
            if(!empty($this->end_date))
            {
                $query->andWhere(['<=', 'o.created_at', strtotime($this->end_date)+86400]);
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

//        if(!empty($this->administrator_id) && $administrator->department && $administrator->department->leader_id == $administrator->id)
//        {
//            $query->joinWith(['department d']);
//            $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
//            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
//            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
//        }

        if(!empty($this->administrator_id))
        {
            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
        }

//        if(!empty($this->status))
//        {
//            if($this->status == 'not_deal_20')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 20]);
//            }
//            elseif($this->status == 'not_deal_40')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 40]);
//            }
//            elseif($this->status == 'not_deal_60')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 60]);
//            }
//            elseif($this->status == 'not_deal_80')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 80]);
//            }
//            else if($this->status == 'apply')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_APPLY]);
//            }
//            else if($this->status == 'deal')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
//            }
//            else if($this->status == 'fail')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
//            }
//        }

        if(!empty($this->status))
        {
            if($this->status == self::STATUS_UNCONFIRMED)//待确认商机（不在公海里面的商机，且是待确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_DISABLED, 'o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 20]);
            }
            elseif($this->status == self::STATUS_NOT_DEAL)//跟进中商机（不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
            }
            elseif($this->status == self::STATUS_APPLY)//申请中商机（商机状态为申请中100%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_APPLY]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_APPLY]);
            }
            elseif($this->status ==  self::STATUS_FAIL)//已失败商机（商机状态为已失败0%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_FAIL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
            }
            else if($this->status == self::STATUS_DEAL)//已成交商机（商机状态为已成交100%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_DEAL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);

            }
        }

        if(!empty($this->amount) && !empty($this->amount_keyword))
        {
            if($this->amount == 'gt_amount')
            {
                $query->andWhere(['>=', 'o.total_amount', $this->amount_keyword]);
            }
            elseif($this->amount == 'lt_amount')
            {
                $query->andWhere(['<=', 'o.total_amount', $this->amount_keyword]);
            }
        }

        if(!empty($this->company_id))
        {
            $query->andWhere(['o.company_id' => $this->company_id]);
        }

        if(!empty($this->department_id))
        {
            $query->andWhere(['o.department_id' => $this->department_id]);
        }

        if(!empty($this->customer_source))
        {
            $query->joinWith(['customer c']);
            $query->andWhere(['c.source' => $this->customer_source]);
        }

        if(!empty($this->opportunity_name))
        {
            $query->andWhere(['like', 'o.name', $this->opportunity_name]);
        }

        $arr = [];
        if(!empty($this->opportunity_progress_twenty) && $this->opportunity_progress_twenty == 'percent_twenty')
        {
            $arr[] = 20;
        }
        if(!empty($this->opportunity_progress_forty) && $this->opportunity_progress_forty == 'percent_forty')
        {
            $arr[] = 40;
        }
        if(!empty($this->opportunity_progress_sixty) && $this->opportunity_progress_sixty == 'percent_sixty')
        {
            $arr[] = 60;
        }
        if(!empty($this->opportunity_progress_eighty) && $this->opportunity_progress_eighty == 'percent_eighty')
        {
            $arr[] = 80;
        }
        if(!empty($this->opportunity_progress_hundred) && $this->opportunity_progress_hundred == 'percent_hundred')
        {
            $arr[] = 100;
        }

        if(!empty($arr))
        {

            $query->andWhere(['in', 'o.progress', $arr])->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL,CrmOpportunity::STATUS_APPLY,CrmOpportunity::STATUS_FAIL]]);
        }

        if(!empty($this->followed))
        {
            $time = strtotime(date("Y-m-d"),time());
            if($this->followed == 'today')
            {
                //当天跟进的
                $query->andWhere(['between', 'o.last_record', $time, $time + 86399]);
            }
            elseif($this->followed == 'three')
            {
                //近三天跟进的
                $query->andWhere(['between', 'o.last_record', $time - 2*86400, $time + 86399]);
            }
            elseif($this->followed == 'week')
            {
                //近一周跟进的
                $query->andWhere(['between', 'o.last_record', $time - 6*86400, $time + 86399]);
            }
            elseif($this->followed == 'month')
            {
                //近一个月跟进的
                $query->andWhere(['between', 'o.last_record', strtotime("-1 months", $time + 86400), $time + 86399]);
            }
        }

        if(!empty($this->tag_id))
        {
            $query->joinWith(['opportunityTag ot']);
            $query->andWhere(['ot.tag_id' => $this->tag_id]);
        }

        $query->orderBy([new Expression('ISNULL(o.next_follow_time) ASC'), 'o.status' => SORT_ASC, 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
//        $query->orderBy(['o.status' => SORT_ASC, 'o.progress' => SORT_ASC, new Expression('ISNULL(o.next_follow_time) ASC'), 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
//        $query->orderBy([new Expression('ISNULL(o.next_follow_time) DESC'), 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
        $query->groupBy(['o.id']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->page_size,
            ]
        ]);

        return $dataProvider;
    }

    public function excelSearch($query1 = null)
    {
        if(null == $query1) {
            $query = CrmOpportunity::find()->alias('o');
        } else {
            $query = $query1;

        }
        //ActiveDataProvider通过使用$ query执行数据库查询来提供数据。
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate())
        {
            return $dataProvider;
        }
        if(!empty($this->customer_id))
        {
            $query->andWhere(['customer_id' => $this->customer_id]);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        // sub_need_confirm
        if($this->range == 'sub_need_confirm')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'sub_confirm')
        {
            $query->andWhere(['o.opportunity_public_id' => 1]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
        }
        else if($this->range == 'following')
        {
            //最新需求：我的商机，显示我的所有商机数据
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
        }
        else if($this->range == 'deal')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
        }
        else if($this->range == 'sub')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'fail')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
        }
        else if($this->range == 'shared')
        {
            //有我分享的商机(公海中的商机也需要显示)
            $query->andWhere(['o.send_administrator_id' => $administrator->id]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
        }
        else if($this->range == 'all')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
                $query->andWhere(['o.company_id' => $administrator->company_id]);
            }
        }
        else
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.is_receive' => 0, 'o.administrator_id' => $administrator->id]);
        }

        $query->leftJoin(['p' => CrmOpportunityProduct::tableName()],'o.id = p.opportunity_id');
        $query->andFilterWhere([
            'p.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'p.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'p.product_id' => $this->product_id <= 0 ? null : $this->product_id,
        ]);

        if(!empty($this->keyword) && !empty($this->keyword_field))
        {
            if($this->keyword_field == 'opportunity_name')
            {
                $query->andWhere(['like', 'o.name', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_name')
            {
                $query->andWhere(['like', 'c.name', $this->keyword]);
            }else if($this->keyword_field == 'opportunity_id')
            {
                $query->andWhere(['o.id' => $this->keyword]);
            }
            else if($this->keyword_field == 'customer_phone')
            {
                $query->andWhere(['like', 'c.phone', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_id')
            {
                $query->andWhere(['c.id' => $this->keyword]);
            }else if($this->keyword_field == 'tel')
            {
                $query->andWhere(['like', 'c.tel', $this->keyword]);
            }
            else if($this->keyword_field == 'caller')
            {
                $query->andWhere(['like', 'c.caller', $this->keyword]);
            }
            else if($this->keyword_field == 'wechat')
            {
                $query->andWhere(['like', 'c.wechat', $this->keyword]);
            }
            else if($this->keyword_field == 'qq')
            {
                $query->andWhere(['like', 'c.qq', $this->keyword]);
            }
            else if($this->keyword_field == 'email')
            {
                $query->andWhere(['like', 'c.email', $this->keyword]);
            }
        }

        if(!empty($this->start_date) && !empty($this->end_date))
        {
            $query->andWhere(['between', 'o.created_at', strtotime($this->start_date), strtotime($this->end_date)+86400]);
        }
        else
        {
            if(!empty($this->start_date))
            {
                $query->andWhere(['>=', 'o.created_at', strtotime($this->start_date)]);
            }
            if(!empty($this->end_date))
            {
                $query->andWhere(['<=', 'o.created_at', strtotime($this->end_date)+86400]);
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


        if(!empty($this->administrator_id))
        {
            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
        }

        if(!empty($this->status))
        {
            if($this->status == self::STATUS_UNCONFIRMED)//待确认商机（不在公海里面的商机，且是待确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_DISABLED, 'o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
            }
            elseif($this->status == self::STATUS_NOT_DEAL)//跟进中商机（不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
            }
            elseif($this->status == self::STATUS_APPLY)//申请中商机（商机状态为申请中100%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_APPLY]);
            }
            elseif($this->status ==  self::STATUS_FAIL)//已失败商机（商机状态为已失败0%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_FAIL]);
            }
            else if($this->status == self::STATUS_DEAL)//已成交商机（商机状态为已成交100%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_DEAL]);

            }
        }

        if(!empty($this->amount) && !empty($this->amount_keyword))
        {
            if($this->amount == 'gt_amount')
            {
                $query->andWhere(['>=', 'o.total_amount', $this->amount_keyword]);
            }
            elseif($this->amount == 'lt_amount')
            {
                $query->andWhere(['<=', 'o.total_amount', $this->amount_keyword]);
            }
        }

        if(!empty($this->company_id))
        {
            $query->andWhere(['o.company_id' => $this->company_id]);
        }

        if(!empty($this->department_id))
        {
            $query->andWhere(['o.department_id' => $this->department_id]);
        }

        if(!empty($this->customer_source))
        {
            $query->andWhere(['c.source' => $this->customer_source]);
        }

        if(!empty($this->opportunity_name))
        {
            $query->andWhere(['like', 'o.name', $this->opportunity_name]);
        }

        $arr = [];
        if(!empty($this->opportunity_progress_twenty) && $this->opportunity_progress_twenty == 'percent_twenty')
        {
            $arr[] = 20;
        }
        if(!empty($this->opportunity_progress_forty) && $this->opportunity_progress_forty == 'percent_forty')
        {
            $arr[] = 40;
        }
        if(!empty($this->opportunity_progress_sixty) && $this->opportunity_progress_sixty == 'percent_sixty')
        {
            $arr[] = 60;
        }
        if(!empty($this->opportunity_progress_eighty) && $this->opportunity_progress_eighty == 'percent_eighty')
        {
            $arr[] = 80;
        }
        if(!empty($this->opportunity_progress_hundred) && $this->opportunity_progress_hundred == 'percent_hundred')
        {
            $arr[] = 100;
        }

        if(!empty($arr))
        {

            $query->andWhere(['in', 'o.progress', $arr])->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL,CrmOpportunity::STATUS_APPLY,CrmOpportunity::STATUS_FAIL]]);
        }

        if(!empty($this->followed))
        {
            $time = strtotime(date("Y-m-d"),time());
            if($this->followed == 'today')
            {
                //当天跟进的
                $query->andWhere(['between', 'o.last_record', $time, $time + 86399]);
            }
            elseif($this->followed == 'three')
            {
                //近三天跟进的
                $query->andWhere(['between', 'o.last_record', $time - 2*86400, $time + 86399]);
            }
            elseif($this->followed == 'week')
            {
                //近一周跟进的
                $query->andWhere(['between', 'o.last_record', $time - 6*86400, $time + 86399]);
            }
            elseif($this->followed == 'month')
            {
                //近一个月跟进的
                $query->andWhere(['between', 'o.last_record', strtotime("-1 months", $time + 86400), $time + 86399]);
            }
        }

        if(!empty($this->tag_id))
        {
            $query->andWhere(['ot.tag_id' => $this->tag_id]);
        }

        $query->orderBy([new Expression('ISNULL(o.next_follow_time) ASC'), 'o.status' => SORT_ASC, 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
        $query->groupBy(['o.id']);

//        return $query;

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return [
            'keyword_field' => '字段类型',
            'status' => '状态',
            'amount' => '金额',
            'department_id' => '所属部门',
            'administrator_id' => '跟进人',
            'company_id' => '所属部门',
            'customer_source' => '客户来源',
            'top_category_id' => '商品类目',
            'category_id' => '',
            'product_id' => '',
        ];
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }

    public function getAdministrator()
    {
        return administrator::find()->where(['id' => $this->administrator_id])->one();
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

    public function getTopCategory()
    {
        return ProductCategory::find()->where(['id' => $this->top_category_id, 'parent_id' => '0'])->one();
    }

    public function getCategory()
    {
        return ProductCategory::find()->where(['id' => $this->category_id])
            ->andWhere('parent_id!=:parent_id', [':parent_id' => '0'])->one();
    }

    public function getProduct()
    {
        return Product::find()->where(['id' => $this->product_id])->one();
    }

    public static function getStatus()
    {
        return [
            self::STATUS_UNCONFIRMED => '待确认商机',
            self::STATUS_NOT_DEAL => '跟进中商机',
            self::STATUS_APPLY => '申请中商机',
            self::STATUS_DEAL => '已成交商机',
            self::STATUS_FAIL => '已失败商机',
        ];
    }

    public static function getScene()
    {
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $list = [];
        if(Yii::$app->user->can('opportunity/*'))
        {
            $list_1 = [self::OPPORTUNITY_FOLLOWING => '我的商机', self::OPPORTUNITY_SHARED => '由我分享的商机'];
            $list = $list + $list_1;
        }
        if(Yii::$app->user->can('opportunity/*') && ($user->isDepartmentManager() || $user->isLeader()))
        {
            $list_2 = [self::OPPORTUNITY_SUB_NEED_CONFIRM => '我下属待确认的商机', self::OPPORTUNITY_SUB => '我下属负责的商机'];
            $list = $list + $list_2;
        }
        if(Yii::$app->user->can('opportunity/all'))
        {
            $list_3 = [self::OPPORTUNITY_ALL => '全部商机'];
            $list = $list + $list_3;
        }
        return $list;
    }

    public function getOpportunityTag()
    {
        return Tag::find()->where(['id' => $this->tag_id])->one();
    }

    public function getCustomerSource()
    {
        return Source::find()->where(['id' => $this->customer_source])->one();
    }


    //待优化
     public function searchCount()
    {
        $this->validate();

        $query = CrmOpportunity::find()->select('o.total_amount,o.name')->alias('o');
        $count = CrmOpportunity::find()->select('id')->count();
        // echo "$count";die;
         if(!empty($this->customer_id))
        {
            $query->andWhere(['customer_id' => $this->customer_id]);
        }
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        // sub_need_confirm
        if($this->range == 'sub_need_confirm')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'sub_confirm')
        {
            $query->andWhere(['o.opportunity_public_id' => 1]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['o.is_receive' => 0]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
        }
        else if($this->range == 'following')
        {
            //最新需求：我的商机，显示我的所有商机数据
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
//            $query->andWhere(['o.is_receive' => 1]);
//            $query->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]]);
        }
        else if($this->range == 'deal')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
        }
        else if($this->range == 'sub')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'fail')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
        }
        else if($this->range == 'shared')
        {
            //有我分享的商机(公海中的商机也需要显示)
            $query->andWhere(['o.send_administrator_id' => $administrator->id]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
        }
        else if($this->range == 'all')
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
                $query->andWhere(['o.company_id' => $administrator->company_id]);
            }
        }
        else
        {
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['o.is_receive' => 0, 'o.administrator_id' => $administrator->id]);
        }

        $query->joinWith(['opportunityProducts p']);
        $query->andFilterWhere([
            'p.top_category_id' => $this->top_category_id <= 0 ? null : $this->top_category_id,
            'p.category_id' => $this->category_id <= 0 ? null : $this->category_id,
            'p.product_id' => $this->product_id <= 0 ? null : $this->product_id,
        ]);

        if(!empty($this->keyword) && !empty($this->keyword_field))
        {
            if($this->keyword_field == 'opportunity_name')
            {
                $query->andWhere(['like', 'o.name', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_name')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.name', $this->keyword]);
            }else if($this->keyword_field == 'opportunity_id')
            {
                $query->andWhere(['o.id' => $this->keyword]);
            }
            else if($this->keyword_field == 'customer_phone')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.phone', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_id')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['c.id' => $this->keyword]);
            }else if($this->keyword_field == 'tel')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.tel', $this->keyword]);
            }
            else if($this->keyword_field == 'caller')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.caller', $this->keyword]);
            }
            else if($this->keyword_field == 'wechat')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.wechat', $this->keyword]);
            }
            else if($this->keyword_field == 'qq')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.qq', $this->keyword]);
            }
            else if($this->keyword_field == 'email')
            {
                $query->joinWith(['customer c']);
                $query->andWhere(['like', 'c.email', $this->keyword]);
            }
        }

        if(!empty($this->start_date) && !empty($this->end_date))
        {
            $query->andWhere(['between', 'o.created_at', strtotime($this->start_date), strtotime($this->end_date)+86400]);
        }
        else
        {
            if(!empty($this->start_date))
            {
                $query->andWhere(['>=', 'o.created_at', strtotime($this->start_date)]);
            }
            if(!empty($this->end_date))
            {
                $query->andWhere(['<=', 'o.created_at', strtotime($this->end_date)+86400]);
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

//        if(!empty($this->administrator_id) && $administrator->department && $administrator->department->leader_id == $administrator->id)
//        {
//            $query->joinWith(['department d']);
//            $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
//            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
//            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
//        }

        if(!empty($this->administrator_id))
        {
            $query->andWhere(['o.administrator_id' => $this->administrator_id]);
        }

//        if(!empty($this->status))
//        {
//            if($this->status == 'not_deal_20')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 20]);
//            }
//            elseif($this->status == 'not_deal_40')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 40]);
//            }
//            elseif($this->status == 'not_deal_60')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 60]);
//            }
//            elseif($this->status == 'not_deal_80')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 80]);
//            }
//            else if($this->status == 'apply')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_APPLY]);
//            }
//            else if($this->status == 'deal')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
//            }
//            else if($this->status == 'fail')
//            {
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
//            }
//        }

        if(!empty($this->status))
        {
            if($this->status == self::STATUS_UNCONFIRMED)//待确认商机（不在公海里面的商机，且是待确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_DISABLED, 'o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 20]);
            }
            elseif($this->status == self::STATUS_NOT_DEAL)//跟进中商机（不在公海里面的商机，且是已确认商机状态的，且商机状态为20%、40%、60%、80%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL]);
            }
            elseif($this->status == self::STATUS_APPLY)//申请中商机（商机状态为申请中100%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_APPLY]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_APPLY]);
            }
            elseif($this->status ==  self::STATUS_FAIL)//已失败商机（商机状态为已失败0%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_FAIL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
            }
            else if($this->status == self::STATUS_DEAL)//已成交商机（商机状态为已成交100%的商机）
            {
                $query->andWhere(['o.is_receive' => CrmOpportunity::RECEIVE_ACTIVE, 'o.status' => CrmOpportunity::STATUS_DEAL]);
//                $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);

            }
        }

        if(!empty($this->amount) && !empty($this->amount_keyword))
        {
            if($this->amount == 'gt_amount')
            {
                $query->andWhere(['>=', 'o.total_amount', $this->amount_keyword]);
            }
            elseif($this->amount == 'lt_amount')
            {
                $query->andWhere(['<=', 'o.total_amount', $this->amount_keyword]);
            }
        }

        if(!empty($this->company_id))
        {
            $query->andWhere(['o.company_id' => $this->company_id]);
        }

        if(!empty($this->department_id))
        {
            $query->andWhere(['o.department_id' => $this->department_id]);
        }

        if(!empty($this->customer_source))
        {
            $query->joinWith(['customer c']);
            $query->andWhere(['c.source' => $this->customer_source]);
        }

        if(!empty($this->opportunity_name))
        {
            $query->andWhere(['like', 'o.name', $this->opportunity_name]);
        }

        $arr = [];
        if(!empty($this->opportunity_progress_twenty) && $this->opportunity_progress_twenty == 'percent_twenty')
        {
            $arr[] = 20;
        }
        if(!empty($this->opportunity_progress_forty) && $this->opportunity_progress_forty == 'percent_forty')
        {
            $arr[] = 40;
        }
        if(!empty($this->opportunity_progress_sixty) && $this->opportunity_progress_sixty == 'percent_sixty')
        {
            $arr[] = 60;
        }
        if(!empty($this->opportunity_progress_eighty) && $this->opportunity_progress_eighty == 'percent_eighty')
        {
            $arr[] = 80;
        }
        if(!empty($this->opportunity_progress_hundred) && $this->opportunity_progress_hundred == 'percent_hundred')
        {
            $arr[] = 100;
        }

        if(!empty($arr))
        {

            $query->andWhere(['in', 'o.progress', $arr])->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL,CrmOpportunity::STATUS_APPLY,CrmOpportunity::STATUS_FAIL]]);
        }

        if(!empty($this->followed))
        {
            $time = strtotime(date("Y-m-d"),time());
            if($this->followed == 'today')
            {
                //当天跟进的
                $query->andWhere(['between', 'o.last_record', $time, $time + 86399]);
            }
            elseif($this->followed == 'three')
            {
                //近三天跟进的
                $query->andWhere(['between', 'o.last_record', $time - 2*86400, $time + 86399]);
            }
            elseif($this->followed == 'week')
            {
                //近一周跟进的
                $query->andWhere(['between', 'o.last_record', $time - 6*86400, $time + 86399]);
            }
            elseif($this->followed == 'month')
            {
                //近一个月跟进的
                $query->andWhere(['between', 'o.last_record', strtotime("-1 months", $time + 86400), $time + 86399]);
            }
        }

        if(!empty($this->tag_id))
        {
            $query->joinWith(['opportunityTag ot']);
            $query->andWhere(['ot.tag_id' => $this->tag_id]);
        }

        $query->orderBy([new Expression('ISNULL(o.next_follow_time) ASC'), 'o.status' => SORT_ASC, 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
//        $query->orderBy(['o.status' => SORT_ASC, 'o.progress' => SORT_ASC, new Expression('ISNULL(o.next_follow_time) ASC'), 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
//        $query->orderBy([new Expression('ISNULL(o.next_follow_time) DESC'), 'o.next_follow_time' => SORT_ASC, 'o.id' => SORT_DESC]);
        // $query->groupBy(['o.id']);
         $query->groupBy(['o.id']);
        $dataProviderCount = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $count,
            ]
        ]);
            
        return $dataProviderCount;
    }
}