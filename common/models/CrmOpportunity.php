<?php

namespace common\models;

use common\utils\BC;
use Yii;
use yii\db\Query;
use yii\redis\Cache;

/**
 * This is the model class for table "crm_opportunity".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $customer_id
 * @property integer $virtual_order_id
 * @property integer $contract_id
 * @property string $customer_name
 * @property string $name
 * @property string $remark
 * @property integer $progress
 * @property integer $status
 * @property string $total_amount
 * @property integer $predict_deal_time
 * @property integer $deal_time
 * @property integer $invalid_time
 * @property string $invalid_reason
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property integer $is_receive
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $send_administrator_id
 * @property integer $send_time
 * @property integer $creator_id
 * @property integer $business_subject_id
 * @property integer $last_record
 * @property integer $last_record_creator_id
 * @property integer $is_protect
 * @property integer $opportunity_public_id
 * @property integer $extract_time
 * @property integer $move_public_time
 * @property string $last_record_creator_name
 * @property integer $next_follow_time
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CrmOpportunityProduct[] $opportunityProducts
 * @property CrmDepartment $department
 * @property Administrator $sendAdministrator
 * @property CrmCustomer $customer
 * @property BusinessSubject $businessSubject
 * @property CrmCustomerLog[] $logs
 * @property Administrator $administrator
 * @property VirtualOrder $virtualOrder
 * @property OpportunityPublic $opportunityPublic
 * @property Company $company
 * @property CrmCustomerCombine $customerCombine
 * @property OpportunityTag $opportunityTag
 * @property Contract $contract
 * @property Contract $onlyContract
 */
class CrmOpportunity extends \yii\db\ActiveRecord
{
    // 商机状态(默认0未成交、1申请中、2已成交、3已失败)
    const STATUS_NOT_DEAL = 0;
    const STATUS_APPLY = 1;
    const STATUS_DEAL = 2;
    const STATUS_FAIL = 3;

    //确认转入商机
    const RECEIVE_DISABLED = 0;//未转入
    const RECEIVE_ACTIVE = 1;//已转入

    //商机是否受保护
    const PROTECT_DISABLED = 0;//否
    const PROTECT_ACTIVE = 1;//是

    //商机进度
    const PROGRESS_ZERO_PERCENT = 0;//进度%0
    const PROGRESS_TWENTY_PERCENT = 20;//进度%20
    const PROGRESS_FORTY_PERCENT = 40;//进度%40
    const PROGRESS_SIXTY_PERCENT = 60;//进度%60
    const PROGRESS_EIGHTY_PERCENT = 80;//进度%80
    const PROGRESS_HUNDRED_PERCENT = 100;//进度%100

    public $crm_customer_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_opportunity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'customer_id', 'progress', 'status', 'predict_deal_time', 'deal_time', 'invalid_time', 'administrator_id','contract_id', 'is_receive',
                'company_id', 'department_id', 'send_administrator_id', 'send_time','business_subject_id','last_record','last_record_creator_id','is_protect',
                'opportunity_public_id','extract_time','move_public_time','next_follow_time','creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['remark', 'invalid_reason','crm_customer_id'], 'string'],
            [['total_amount'], 'number'],
            [['crm_customer_id','invalid_reason'], 'required','on' => 'abandon'],
            [['customer_name', 'administrator_name', 'creator_name', 'updater_name', 'last_record_creator_name'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_name' => '客户名称',
            'name' => '商机名称',
            'progress' => '商机状态',
            'predict_deal_time' => '预计成交时间',
            'business_subject_id' => '关联业务主体',
            'remark' => '商机备注',

            'id' => 'ID',
            'user_id' => 'User ID',
            'customer_id' => 'Customer ID',
            'contract_id' => 'Contract ID',
            'status' => 'Status',
            'total_amount' => 'total_amount',
            'deal_time' => 'Deal Time',
            'invalid_time' => 'Invalid Time',
            'invalid_reason' => '作废原因',
            'administrator_id' => '所属业务员',
            'administrator_name' => '所属业务员',
            'is_receive' => 'Is Receive',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
            'last_record' => '最后一次跟进时间',
            'last_record_creator_id' => 'Last Record Creator Id',
            'last_record_creator_name' => 'Last Record Creator Name',
            'is_protect' => 'Is Protect',
            'opportunity_public_id' => 'Opportunity Public Id',
            'extract_time' => 'Extract Time',
            'move_public_time' => 'Move Public Time',
            'next_follow_time' => '下次跟进时间',
            'send_administrator_id' => 'Send Administrator ID',
            'send_time' => 'Send Time',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getOpportunityProducts()
    {
        return static::hasMany(CrmOpportunityProduct::className(), ['opportunity_id' => 'id']);
    }

    public function getLogs()
    {
//        return static::hasMany(CrmCustomerLog::className(), ['opportunity_id' => 'id'])->where(['type' => CrmCustomerLog::TYPE_CUSTOMER_OPPORTUNITY])->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
        return static::hasMany(CrmCustomerLog::className(), ['opportunity_id' => 'id'])->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'department_id']);
    }

    public function getCustomer()
    {
        return static::hasOne(CrmCustomer::className(), ['id' => 'customer_id']);
    }

    public function getSendAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'send_administrator_id']);
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    public function getBusinessSubject()
    {
        return static::hasOne(BusinessSubject::className(), ['id' => 'business_subject_id']);
    }

    public function getVirtualOrder()
    {
        return static::hasOne(VirtualOrder::className(), ['id' => 'virtual_order_id']);
    }

    public function getOpportunityPublic()
    {
        return static::hasOne(OpportunityPublic::className(), ['id' => 'opportunity_public_id']);
    }

    public function getCompany()
    {
        return static::hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getContract()
    {
        return static::hasOne(Contract::className(), ['opportunity_id' => 'id']);
    }

    public function getOnlyContract()
    {
        return static::hasOne(Contract::className(), ['id' => 'contract_id']);
    }

    public function getCustomerCombine()
    {
        return static::hasOne(CrmCustomerCombine::className(), ['customer_id' => 'customer_id', 'administrator_id' => 'administrator_id']);
    }

    public function getOpportunityTag()
    {
        return $this->hasOne(OpportunityTag::className(), ['opportunity_id' => 'id']);
    }

    public function updateTotalAmount($save = true)
    {
        $totalAmount = 0;
        foreach($this->opportunityProducts as $product)
        {
            $totalAmount = BC::add($totalAmount, BC::mul($product->price, $product->qty));
        }
        $this->total_amount = $totalAmount;
        if($save)
        {
            $this->save(false);
        }
    }

    public function getStatusName()
    {
        if($this->status == self::STATUS_NOT_DEAL)
        {
            return '未成交('.$this->progress.'%)';
        }
        else if($this->status == self::STATUS_APPLY)
        {
            return '申请中('.$this->progress.'%)';
        }
        else if($this->status == self::STATUS_DEAL)
        {
            return '已成交('.$this->progress.'%)';
        }
        else if($this->status == self::STATUS_FAIL)
        {
            return '已失败('.$this->progress.'%)';
        }
        return '--';
    }

    public function getStatus()
    {
        if($this->status == self::STATUS_NOT_DEAL)
        {
            return '未成交';
        }
        else if($this->status == self::STATUS_APPLY)
        {
            return '申请中';
        }
        else if($this->status == self::STATUS_DEAL)
        {
            return '已成交';
        }
        else if($this->status == self::STATUS_FAIL)
        {
            return '已失败';
        }
        return '--';
    }

    public function isStatusNotDeal()
    {
        return $this->status == self::STATUS_NOT_DEAL;
    }

    public function isStatusApply()
    {
        return $this->status == self::STATUS_APPLY;
    }

    public function isStatusDeal()
    {
        return $this->status == self::STATUS_DEAL;
    }

    public function isStatusFail()
    {
        return $this->status == self::STATUS_FAIL;
    }

    public function isProtect()
    {
        return $this->is_protect == self::PROTECT_ACTIVE;
    }

    /**
     * @param $administrator Administrator
     * @return boolean
     */
    public function isSubFor($administrator)
    {
        if(null == $administrator->department) return false;
        if($administrator->department->leader_id != $administrator->id && !$administrator->isDepartmentManager()) return false;
        if($this->department_id == $administrator->department_id) return true;
        return 0 < CrmDepartment::find()->where("path like '". $administrator->department->path."-%'")
                ->andWhere(['id' => $this->department_id])->count();
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countNeedConfirm($administrator)
    {
        $key = 'countNeedConfirm-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmOpportunity::find()->select('id');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['administrator_id' => $administrator->id , 'is_receive' => 0]);
            $query->andWhere(['opportunity_public_id' => 0]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countSubNeedConfirm($administrator)
    {
        $key = 'countSubNeedConfirm-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = 0;
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query = CrmOpportunity::find()->select('o.id')->alias('o');
                $query->andWhere(['o.is_receive' => 0]);
                $query->joinWith(['department d']);
//                $query->leftJoin("(SELECT id,path FROM ".CrmDepartment::tableName()." WHERE path LIKE '".$administrator->department->path."-%') as d",'d.id = o.department_id');
//                if($administrator->isBelongCompany() && $administrator->company_id)
//                {
//                    $query->andWhere(['o.company_id' => $administrator->company_id]);
//                }
//                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['o.department_id' => $administrator->department_id], ['d.id' => $administrator->department_id]]);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
                $query->andWhere(['o.opportunity_public_id' => 0]);
                $count = $query->count();
                $countRedisCache->set($key,$count,30);
            }
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countFollowing($administrator)
    {
        $key = 'countFollowing-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmOpportunity::find()->select('o.id')->alias('o');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['o.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.is_receive' => 1]);
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $query->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countDeal($administrator)
    {
        $key = 'countDeal-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmOpportunity::find()->select('o.id')->alias('o');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['o.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    //todo
    public static function countSubFollowing($administrator)
    {
        $key = 'countSubFollowing-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $count = 0;
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
//                $query = CrmOpportunity::find()->alias('o');
//                $query->joinWith(['department d']);
                $query = CrmOpportunity::find()->select('o.id')->alias('o');
                $query->leftJoin("(SELECT id,path FROM ".CrmDepartment::tableName()." WHERE path LIKE '".$administrator->department->path."-%') as d",'d.id = o.department_id');
//                if($administrator->isBelongCompany() && $administrator->company_id)
//                {
//                    $query->andWhere(['o.company_id' => $administrator->company_id]);
//                }
//                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
                $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['o.department_id' => $administrator->department_id], ['d.id' => $administrator->department_id]]);
                $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
                $query->andWhere(['=', 'o.opportunity_public_id', 0]);
                $count = $query->count();
                $countRedisCache->set($key,$count,30);
            }
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countFail($administrator)
    {
        $key = 'countFail-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
//            $query = CrmOpportunity::find()->alias('o');
            $query = CrmOpportunity::find()->select('o.id')->alias('o');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['o.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countShared($administrator)
    {
        $key = 'countFail-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
//            $query = CrmOpportunity::find()->alias('o');
            $query = CrmOpportunity::find()->select('o.id')->alias('o');
//            if($administrator->isBelongCompany() && $administrator->company_id)
//            {
//                $query->andWhere(['o.company_id' => $administrator->company_id]);
//            }
            $query->andWhere(['o.send_administrator_id' => $administrator->id]);
            $query->andWhere(['not in', 'o.administrator_id', [$administrator->id]]);
            $query->andWhere(['o.opportunity_public_id' => 0]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @return int|string
     */
    public static function countTodayFollow($administrator)
    {
        $key = 'countTodayFollow-administrator-id-'.$administrator->id;
        /** @var cache $countRedisCache */
        $countRedisCache = Yii::$app->get('countRedisCache');
        $count = $countRedisCache->get($key);
        if(null == $count)
        {
            $query = CrmOpportunity::find()->alias('o');
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            $query->andWhere(['in', 'o.status', [CrmOpportunity::STATUS_APPLY, CrmOpportunity::STATUS_NOT_DEAL]]);
            $startTime = strtotime(date('Y-m-d 00:00:00'));
            $endTime = strtotime(date('Y-m-d 00:00:00')) + 86400;
            $query->andWhere(['between', 'o.next_follow_time', $startTime, $endTime]);
            $count = $query->count();
            $countRedisCache->set($key,$count,30);
        }
        return $count;
    }

    /**
     * @param $administrator Administrator
     * @param $status string
     * @return int|string
     */
    public static function getStatusCount($status, $administrator)
    {
        $query = CrmOpportunity::find()->alias('o');

        if($status == 'not_deal_20')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 20]);
        }
        elseif($status == 'not_deal_40')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 40]);
        }
        elseif($status == 'not_deal_60')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 60]);
        }
        elseif($status == 'not_deal_80')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_NOT_DEAL, 'o.progress' => 80]);
        }
        else if($status == 'apply')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_APPLY]);
        }
        else if($status == 'deal')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_DEAL]);
        }
        else if($status == 'fail')
        {
            $query->andWhere(['o.status' => CrmOpportunity::STATUS_FAIL]);
        }

        if($administrator->isLeader() || $administrator->isDepartmentManager())
        {
            $query->joinWith(['department d']);
            $query->andWhere(['or', "d.path like '". $administrator->department->path."-%'", ['d.id' => $administrator->department_id]]);
            return $query->count();
        }
        else
        {
            $query->andWhere(['o.administrator_id' => $administrator->id]);
            return $query->count();
        }
    }

    public function replaceName($name, $num = 1)
    {
        $firstStr = mb_substr($name, 0, $num, Yii::$app->charset);
        return $firstStr . '**';
    }

    public function replaceIphone($iphone)
    {
        $firstStr = mb_substr($iphone, 0, 3, Yii::$app->charset);
        $lastStr = mb_substr($iphone, -4, 4, Yii::$app->charset);
        return $firstStr . str_repeat('*', 4) . $lastStr;
    }

    public function isPublic()
    {
        return self::find()->where(['id' => $this->id])->andWhere(['>', 'opportunity_public_id', 0])->one();
    }

    /**
     * @return string
     */
    public function  getExportNextFollowTime()
    {
        if ($this->next_follow_time > 0 && $this->status != self::STATUS_FAIL)
        {
            return $this->next_follow_time ? Yii::$app->formatter->asDatetime($this->next_follow_time) : '--';
        }
        return '--';
    }
    
    public function getExportSourceName()
    {
        return $this->customer ? $this->customer->getSourceName() : '--';
    }

    public function getExportInvalidReason()
    {
        if($this->status == self::STATUS_FAIL && $this->invalid_time > 0)
        {
            return !empty($this->invalid_reason) ? $this->invalid_reason : '';
        }
        return '--';
    }

    public function getExportCompany()
    {
        //优化查询，减少内存使用
        $company = Company::find()->select('name')->where(['id' => $this->company_id])->one();
        return $company ? $company->name : '--';
//        return $this->company ? $this->company->name : '--';
    }

    public function getExportDepartment()
    {
        //优化查询，减少内存使用
        $department = CrmDepartment::find()->select('name')->where(['id'=>$this->department_id])->one();
        return $department ? $department->name : '--';
        //return $this->department ? $this->department->name : '--';
    }

    public function getExportStatusName()
    {
        if($this->status == self::STATUS_NOT_DEAL)
        {
            return '未成交';
        }
        else if($this->status == self::STATUS_APPLY)
        {
            return '申请中';
        }
        else if($this->status == self::STATUS_DEAL)
        {
            return '已成交';
        }
        else if($this->status == self::STATUS_FAIL)
        {
            return '已失败';
        }
        return '--';
    }

    public function getLastRecord()
    {
        return CrmOpportunityRecord::find()->where(['opportunity_id' => $this->id])->orderBy(['created_at' => SORT_DESC])->one();
    }

        public function getLastRecordContent()
    {
        /** @var NicheRecord $department */
       $department =  NicheRecord::find()->select('content')->where(['niche_id' => $this->id])->orderBy(['created_at' => SORT_DESC])->one();
       return $department ? $department->content : '--';
    }

    public function getTag()
    {
        /** @var Tag $tag */
        $tag = (new Query())
            ->select('t.name')
            ->from(['t' => Tag::tableName()])
            ->innerJoin(['ot' => Niche::tableName()], 'ot.label_id = t.id')
            ->where(['ot.niche_id' => $this->id])->one();
        return $tag ? $tag['name'] : '--';
    }
}
