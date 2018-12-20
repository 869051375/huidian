<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmDepartment;
use common\models\CustomerTag;
use common\models\Source;
use common\models\Tag;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class CrmCustomerSearch
 * @package backend\models
 * @property CrmDepartment $department
 * @property CrmCustomerCombine $customerCombine
 * @property Administrator $administrator
 *
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 * @property Tag $customerTag
 * @property Source $customerSource
 */
class CrmCustomerSearch extends Model
{
    public $range = 'need_confirm';
    public $start_date;
    public $end_date;
    public $level;
    public $keyword_field;
    public $keyword;
    public $start_last_record_date;
    public $end_last_record_date;
    public $id;
    public $get_way;
    public $source;
    public $administrator_id;
    public $customer_combine;
    public $leader_id;
    public $collaborator_id;
    public $department_id;
    public $company_id;
    public $level_id;
    public $customer_name;
    public $created;
    public $followed;
    public $scene;
    public $tag_id;

    public $page_size;

    const CUSTOMER_LEVEL_ALL = 0;//全部客户
    const CUSTOMER_LEVEL_DISABLED = 2;//无效客户
    const CUSTOMER_LEVEL_ACTIVE = 1;//有效客户

    const CUSTOMER_EFFECTIVE = 1;//我的客户
    const CUSTOMER_NEED_CONFIRM = 2;//我待确认的客户
    const CUSTOMER_SUB_NEED_CONFIRM = 3;//我下属待确认的客户
    const CUSTOMER_SUB = 4;//我下属负责的客户
    const CUSTOMER_ALL = 0;//全部客户

    public static function getKeywordFields()
    {
        return [
            'name' => '客户名称/昵称',
            '0' => '字段类型',
            'phone' => '手机号',
            'customer_id' => '客户ID',
            'tel' => '联系电话',
            'caller' => '来电电话',
            'wechat' => '微信',
            'qq' => 'QQ',
            'email' => '邮箱',
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
                'effective',
                'deal',
                'sub',
                'invalid',
                'all',
            ]],
            [['keyword_field'], 'in', 'range' => [
                '0',
                'name',
                'phone',
                'customer_id',
                'tel',
                'caller',
                'wechat',
                'qq',
                'email',
                'administrator_id',
                'customer_combine',
            ]],
            [['administrator_id', 'id'], 'integer'],
            [['keyword','created','followed'], 'string'],
            [['customer_name'], 'string'],
            [['level'], 'in', 'range' => [
                'effective',
                'invalid',
            ]],
            [['get_way'], 'in', 'range' => [
                'crm_input',
                'register',
            ]],
//            [['source'], 'in', 'range' => [
//                'source_other',
//                'source_bd',
//                'source_sll',
//                'source_lxb',
//                'source_jrtt',
//                'source_hd',
//                'source_dt',
//                'source_desc',
//                'source_channel',
//                'source_tel_channel',
//                'source_old',
//                'source_yq' ,
//                'source_tel',
//                'source_tq',
//                'source_can',
//                'source_aliyun',
//                'source_push',
//                'source_partnership',
//                'source_franchiser',
//                'source_wk_extension',
//                'source_branch_company',
//                'source_flow_push',
//                'source_tx',
//            ]],
//            [['scene'], 'in', 'range' => [
//                'effective',
//                'need_confirm',
//                'invalid',
//                'sub_need_confirm',
//                'sub',
//                'all',
//            ]],
            [['start_last_record_date', 'end_last_record_date', 'start_date', 'end_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['range'], 'default', 'value' => 'need_confirm'],
            [['leader_id', 'collaborator_id', 'department_id', 'company_id','level_id','scene', 'page_size', 'tag_id','source'], 'integer'],
        ];
    }

    public function search($export = false)
    {
        $this->validate();

        if($export)
        {
            $query = CrmCustomer::find()->distinct()->select(['any_value(c.id) as id','c.created_at','c.name','c.source','c.get_way','c.administrator_id','c.company_id','c.department_id','c.last_record_creator_name','c.last_record','a.name as administrator_name','cs.name as source_name','t.name as tag_name','co.name as company_name','d.name as department_name','ccb.level'])->alias('c')->asArray()
                ->leftJoin(['a'=>Administrator::tableName()],'c.administrator_id = a.id')
                ->leftJoin(['co'=>Company::tableName()],'a.company_id=co.id')
                ->leftJoin(['d' => CrmDepartment::tableName()],'a.department_id=d.id')
                ->leftJoin(['cs'=>Source::tableName()],'c.source = cs.id')
                ->leftJoin(['ct'=>CustomerTag::tableName()],'ct.customer_id = c.id')
                ->leftJoin(['t'=>Tag::tableName()],'ct.tag_id = t.id');
            if($this->level_id == ''){
                $query->leftJoin(['ccb' => CrmCustomerCombine::tableName()],'c.id = ccb.customer_id');
            }
            $query ->andWhere(['c.customer_public_id' => 0]);
        }
        else
        {
            $query = CrmCustomer::find()->distinct()->alias('c')->andWhere(['c.customer_public_id' => 0]);
        }

        /*if(!empty($this->id))
        {
            $query->andWhere(['id' => $this->id]);
        }*/

        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        //过滤合伙人条件
        $administratorId = empty($this->collaborator_id) ? $administrator->id : $this->collaborator_id;

        // sub_need_confirm
        if($this->range == 'sub_need_confirm')
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_DISABLED]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'c.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'effective')
        {
            //我的客户显示:我下面的客户(不包含待确认客户)
            $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_ACTIVE]);
            $query->joinWith(['crmCustomerCombine cc']);
//            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE, 'cc.administrator_id' => $administratorId]);
            $query->andWhere(['cc.administrator_id' => $administratorId]);
        }
        else if($this->range == 'sub')
        {
            if($this->level_id == self::CUSTOMER_LEVEL_DISABLED)
            {

                if(!empty($this->collaborator_id))//联动筛选：筛选某个负责人或合作人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCustomerCombine cc']);
                    $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);
                }
                else//单独筛选：筛选属于所有客户负责人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCombine ccb']);
                    $query->andWhere(['ccb.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);
                }
            }
            else if ($this->level_id == self::CUSTOMER_LEVEL_ACTIVE)
            {
                if(!empty($this->collaborator_id))//联动筛选：筛选某个负责人或合作人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCustomerCombine cc']);
                    $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE]);
                }
                else//单独筛选：筛选属于所有客户负责人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCombine ccb']);
                    $query->andWhere(['ccb.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE]);
                }
            }


            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->joinWith(['department d']);
                $query->joinWith(['crmCustomerCombine cc']);
                $query->joinWith(['combineDepartments cd']);
                $query->andWhere(['or', 'd.path like :path', ['cc.department_id' => $administrator->department_id], 'cd.path like :path'], [':path' => $administrator->department->path.'-%']);
                $query->andWhere(['not in', 'cc.administrator_id', [$administrator->id]]);
            }
            else
            {
                $query->andWhere('0=1');
            }
        }
        else if($this->range == 'invalid')
        {
            $query->joinWith(['crmCustomerCombine cc']);
//            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED, 'cc.administrator_id' => $administrator->id]);
            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED, 'cc.administrator_id' => $administratorId]);
        }
        else if($this->range == 'all')
        {
            if($administrator->isBelongCompany() && $administrator->company_id)
            {
//                $query->andWhere(['c.company_id' => $administrator->company_id]);
                //区分公司时，全部客户根据合伙人中对应的公司显示客户
                $query->joinWith(['crmCustomerCombine cc']);
                $query->andWhere(['cc.company_id' => $administrator->company_id]);
            }

            if($this->level_id == self::CUSTOMER_LEVEL_DISABLED)
            {

                if(!empty($this->collaborator_id))//联动筛选：筛选某个负责人或合作人的客户级别为有效或者无效的客户；
                {
                $query->joinWith(['crmCustomerCombine cc']);
                $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);
                }
                else//单独筛选：筛选属于所有客户负责人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCombine ccb']);
                    $query->andWhere(['ccb.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);
                }
            }
            else if ($this->level_id == self::CUSTOMER_LEVEL_ACTIVE)
            {
                if(!empty($this->collaborator_id))//联动筛选：筛选某个负责人或合作人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCustomerCombine cc']);
                    $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE]);
                }
                else//单独筛选：筛选属于所有客户负责人的客户级别为有效或者无效的客户；
                {
                    $query->joinWith(['crmCombine ccb']);
                    $query->andWhere(['ccb.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE]);
                }
            }
        }
        else if($this->range == 'sub_confirm')
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_ACTIVE]);
                $query->joinWith(['department d']);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'c.administrator_id', [$administrator->id]]);
            }
        }
        else // need_confirm
        {
//            $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_DISABLED, 'c.administrator_id' => $administrator->id]);
            $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_DISABLED, 'c.administrator_id' => $administratorId]);
        }

        if(!empty($this->keyword) && !empty($this->keyword_field))
        {
            if($this->keyword_field == 'name')
            {
                $query->andWhere(['like', 'c.name', $this->keyword]);
            }
            else if($this->keyword_field == 'phone')
            {
                $query->andWhere(['like', 'c.phone', $this->keyword]);
            }
            else if($this->keyword_field == 'customer_id')
            {
                $query->andWhere(['c.id' => $this->keyword]);
            }
            else if($this->keyword_field == 'tel')
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
            $query->andWhere(['between', 'c.created_at', strtotime($this->start_date), strtotime($this->end_date)+86400]);
        }
        else
        {
            if(!empty($this->start_date))
            {
                $query->andWhere(['>=', 'c.created_at', strtotime($this->start_date)]);
            }
            if(!empty($this->end_date))
            {
                $query->andWhere(['<=', 'c.created_at', strtotime($this->end_date)+86400]);
            }
        }

        //根据最后维护时间
        if(!empty($this->start_last_record_date) && !empty($this->end_last_record_date))
        {
            $query->andWhere(['between', 'c.operation_time', strtotime($this->start_last_record_date), strtotime($this->end_last_record_date)+86400]);
        }
        else
        {
            if(!empty($this->start_last_record_date))
            {
                $query->andWhere(['>=', 'c.operation_time', strtotime($this->start_last_record_date)]);
            }
            if(!empty($this->end_last_record_date))
            {
                $query->andWhere(['<=', 'c.operation_time', strtotime($this->end_last_record_date)+86400]);
            }
        }

//        if(!empty($this->administrator_id) && $administrator->department && $administrator->department->leader_id == $administrator->id)
//        {
//            $query->joinWith(['department d']);
//            $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
//            $query->andWhere(['not in', 'c.administrator_id', [$administrator->id]]);
//            $query->andWhere(['c.administrator_id' => $this->administrator_id]);
//        }

        if(!empty($this->level))
        {
            //我的客户显示:我下面的所有用户
            if($this->level == 'effective')
            {
//                $query->andWhere(['c.is_receive' => CrmCustomer::RECEIVE_ACTIVE]);
                $query->joinWith(['crmCustomerCombine cc']);
//                $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE, 'cc.administrator_id' => $administratorId]);
                $query->andWhere(['cc.administrator_id' => $administratorId]);
            }
            elseif($this->level == 'invalid')
            {
                $query->joinWith(['crmCustomerCombine cc']);
                $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);
            }
        }
        if(!empty($this->get_way))
        {
            if($this->get_way == 'crm_input')
            {
                $query->andWhere(['c.get_way' => CrmCustomer::GET_WAY_CRM_INPUT]);
            }
            elseif($this->get_way == 'register')
            {
                $query->andWhere(['c.get_way' => CrmCustomer::GET_WAY_REGISTER]);
            }
        }
        if(!empty($this->source))
        {
            $query->andWhere(['c.source' => $this->source]);
//            if($this->source == 'source_other')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_OTHER]);
//            }
//            elseif($this->source == 'source_bd')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_BD]);
//            }
//            elseif($this->source == 'source_sll')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_SLL]);
//            }
//            elseif($this->source == 'source_lxb')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_LXB]);
//            }
//            elseif($this->source == 'source_jrtt')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_JRTT]);
//            }
//            elseif($this->source == 'source_hd')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_HD]);
//            }
//            elseif($this->source == 'source_dt')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_DT]);
//            }
//            elseif($this->source == 'source_desc')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_DESC]);
//            }
//            elseif($this->source == 'source_channel')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_CHANNEL]);
//            }
//            elseif($this->source == 'source_partnership')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_PARTNERSHIP]);
//            }
//            elseif($this->source == 'source_old')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_OLD]);
//            }
//            elseif($this->source == 'source_yq')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_YQ]);
//            }
//            elseif($this->source == 'source_tel')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_TEL]);
//            }
//            elseif($this->source == 'source_tq')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_TQ]);
//            }
//            elseif($this->source == 'source_can')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SMALL_CAN]);
//            }
//            elseif($this->source == 'source_aliyun')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_ALIYUN]);
//            }
//            elseif($this->source == 'source_tx')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_TX]);
//            }
//            elseif($this->source == 'source_push')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_PUSH]);
//            }
//            elseif($this->source == 'source_flow_push')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_INFORMATION_FLOW]);
//            }
//            elseif($this->source == 'source_franchiser')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_FRANCHISER]);
//            }
//            elseif($this->source == 'source_wk_extension')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_WK_EXTENSION]);
//            }elseif($this->source == 'source_branch_company')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_BRANCH_COMPANY]);
//            }elseif($this->source == 'source_tel_channel')
//            {
//                $query->andWhere(['c.source' => CrmCustomer::TYPE_SOURCE_TEL_CHANNEL]);
//            }
        }

        if(!empty($this->leader_id))
        {
            $query->andWhere(['c.administrator_id' =>$this->leader_id]);
        }
        
        if(!empty($this->collaborator_id))
        {
            $query->joinWith(['crmCustomerCombine cc']);
            $query->andWhere(['cc.administrator_id' => $this->collaborator_id]);
            //下面代码优化查询合伙人性能
//            $crmCustomerIds = CrmCustomer::customer($this->range);
//            $ids = array_unique(ArrayHelper::getColumn($crmCustomerIds, 'id'));
//            if(!empty($ids))
//            {
//                $query->andWhere(['in','c.id',$ids]);
//            }
        }

        if($this->level_id == 1)
        {
            $query->joinWith(['crmCustomerCombine cc']);
            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE]);// todo
        }
        else if ($this->level_id == 2)
        {
            $query->joinWith(['crmCustomerCombine cc']);
            $query->andWhere(['cc.level' => CrmCustomerCombine::CUSTOMER_LEVEL_DISABLED]);// todo
        }

        if(!empty($this->company_id))
        {
            $query->andWhere(['c.company_id' => $this->company_id]);
        }

        if(!empty($this->department_id))
        {
            $query->andWhere(['c.department_id' => $this->department_id]);
        }

        $query->orderBy(['c.created_at' => SORT_DESC]);
//        $query->groupBy(['c.id']);
        //$query->select(['any_value(c.*)']);

        if(!empty($this->customer_name))
        {
            $query->andWhere(['like', 'c.name', $this->customer_name]);
        }

        if(!empty($this->created))
        {
            $time = strtotime(date("Y-m-d"),time());
            if($this->created == 'today')
            {
                //当天创建的
                $query->andWhere(['between', 'c.created_at', $time, $time + 86399]);
            }
            elseif($this->created == 'three')
            {
                //近三天创建的
                $query->andWhere(['between', 'c.created_at', $time - 2*86400, $time + 86399]);
            }
            elseif($this->created == 'week')
            {
                //近一周创建的
                $query->andWhere(['between', 'c.created_at', $time - 6*86400, $time + 86399]);
            }
            elseif($this->created == 'month')
            {
                //近一个月创建的
                $query->andWhere(['between', 'c.created_at', strtotime("-1 months", $time + 86400), $time + 86399]);
            }
        }
        if(!empty($this->followed))
        {
            $time = strtotime(date("Y-m-d"),time());
            if($this->followed == 'today')
            {
                //当天跟进的
                $query->andWhere(['between', 'c.operation_time', $time, $time + 86399]);
            }
            elseif($this->followed == 'three')
            {
                //近三天跟进的
                $query->andWhere(['between', 'c.operation_time', $time - 2*86400, $time + 86399]);
            }
            elseif($this->followed == 'week')
            {
                //近一周跟进的
                $query->andWhere(['between', 'c.operation_time', $time - 6*86400, $time + 86399]);
            }
            elseif($this->followed == 'month')
            {
                //近一个月跟进的
                $query->andWhere(['between', 'c.operation_time', strtotime("-1 months", $time + 86400), $time + 86399]);
            }
        }

        if(!empty($this->tag_id))
        {
            $query->joinWith(['customerTag ct']);
            $query->andWhere(['ct.tag_id' => $this->tag_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->page_size,
            ],
        ]);
        
        return $dataProvider;
    }

    public function attributeLabels()
    {
        return [
            'keyword_field' => '搜索字段',
            'level' => '客户状态',
            'get_way' => '获取方式',
            'source' => '客户来源',
            'leader_id' => '负责人',
            'collaborator_id' => '合作人',
            'company_id' => '所属公司部门',
            'level_id' => '客户级别',
            'scene' => '场景',
        ];
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }

    public function getCustomerCombine()
    {
        return CrmCustomerCombine::find()->where(['administrator_id' => $this->collaborator_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()->where(['id' => $this->leader_id])->one();
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getCustomerSource()
    {
        return Source::find()->where(['id' => $this->source])->one();
    }

    public function getCompanyDepartment()
    {
        return CrmDepartment::find()
            ->where(['id' => $this->department_id, 'company_id' => $this->company_id])->one();
    }

    public static function getLevel()
    {
        return [
            self::CUSTOMER_LEVEL_ACTIVE => '有效客户',
            self::CUSTOMER_LEVEL_DISABLED => '无效客户',
        ];
    }

    public static function getScene()
    {
        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $list = [];
        if(Yii::$app->user->can('customer/*'))
        {
            $list_1 = [self::CUSTOMER_EFFECTIVE => '我的客户', self::CUSTOMER_NEED_CONFIRM => '我待确认的客户'];
            $list = $list + $list_1;
        }
        if(Yii::$app->user->can('customer/*') && ($user->isDepartmentManager() || $user->isLeader()))
        {
            $list_2 = [self::CUSTOMER_SUB_NEED_CONFIRM => '我下属待确认的客户', self::CUSTOMER_SUB => '我下属负责的客户'];
            $list = $list + $list_2;
        }
        if(Yii::$app->user->can('customer/all'))
        {
            $list_3 = [self::CUSTOMER_ALL => '全部客户'];
            $list = $list + $list_3;
        }
        return $list;
    }

    public function getCustomerTag()
    {
        return Tag::find()->where(['id' => $this->tag_id])->one();
    }

}