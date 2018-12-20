<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "{{%niche}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property integer $next_follow_time
 * @property integer $last_record_creator_id
 * @property string $last_record_creator_name
 * @property integer $last_record
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $progress
 * @property integer $status
 * @property integer $label_id
 * @property string $label_name
 * @property integer $customer_id
 * @property integer $source_id
 * @property string $source_name
 * @property integer $channel_id
 * @property string $channel_name
 * @property string $total_amount
 * @property integer $distribution_id
 * @property string $distribution_name
 * @property integer $distribution_at
 * @property integer $stage_update_at
 * @property integer $update_id
 * @property integer $update_name
 * @property integer $is_distribution
 * @property integer $is_extract
 * @property integer $is_transfer
 * @property integer $is_cross
 * @property integer $is_new
 * @property integer $is_protect
 * @property string $win_reason
 * @property integer $win_progress
 * @property string $win_describe
 * @property string $lose_reason
 * @property integer $lose_progress
 * @property string $lose_describe
 * @property string $remark
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $extract_time
 * @property integer $extract_source
 * @property integer $move_public_time
 * @property integer $send_administrator_id
 * @property integer $send_time
 * @property integer $predict_deal_time
 * @property integer $deal_time
 * @property integer $invalid_time
 * @property string $invalid_reason
 * @property CrmCustomer $customer
 * @property integer $user_id
 * @property integer $updated_at
 * @property integer $contacts_id
 * @property integer $is_give_up
 * @property integer $recovery_at
 * @property integer $niche_public_id
 * @property Administrator $administrator
 * @property integer $stage_update_id
 * @property string $stage_update_name
 * @property Source $source
 * @property Channel $channel
 * @property integer $business_subject_id
 * @property BusinessSubject $businessSubject
 * @property NichePublic $nichePublic
 * @property NicheRecord $nicheRecord
 * @property NichePublicDepartment $administratorNichePublic
 * @property NicheProduct $opportunityProducts
 */
class Niche extends \yii\db\ActiveRecord
{
    const PROGRESS_0 = 0;
    const PROGRESS_10 = 10;
    const PROGRESS_30 = 30;
    const PROGRESS_60 = 60;
    const PROGRESS_80 = 80;
    const PROGRESS_100 = 100;

    // 商机状态(默认0未成交、1申请中、2已成交、3已失败)
    const STATUS_NOT_DEAL = 0;
//    const STATUS_APPLY = 1;
    const STATUS_DEAL = 2;
    const STATUS_FAIL = 3;

    //商机进度
    const PROGRESS_ZERO_PERCENT = 0;//进度%0
    const PROGRESS_TWENTY_PERCENT = 10;//进度%10
    const PROGRESS_FORTY_PERCENT = 30;//进度%30
    const PROGRESS_SIXTY_PERCENT = 60;//进度%60
    const PROGRESS_EIGHTY_PERCENT = 80;//进度%80
    const PROGRESS_HUNDRED_PERCENT = 100;//进度%100

    public $customers;
    public $contacts;
    public $users;
    public $labels;

    public $customer_name;
    /**
     * @var NicheProduct[]
     */
    public $products;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%niche}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id','business_subject_id', 'niche_public_id','contacts_id','next_follow_time', 'last_record_creator_id', 'last_record', 'creator_id', 'created_at', 'progress', 'status', 'label_id', 'customer_id', 'source_id', 'channel_id', 'distribution_id', 'distribution_at', 'stage_update_at', 'update_id', 'update_name', 'is_distribution', 'is_extract', 'is_transfer', 'is_cross', 'is_new', 'is_protect', 'win_progress', 'lose_progress', 'company_id', 'department_id', 'extract_time', 'extract_source', 'move_public_time', 'send_administrator_id', 'send_time', 'predict_deal_time', 'deal_time', 'invalid_time', 'user_id', 'updated_at','is_give_up','recovery_at','stage_update_id'], 'integer'],
            [['total_amount'], 'number'],
            [['win_describe', 'lose_describe', 'remark', 'invalid_reason'], 'string'],
            [['name', 'customer_name','administrator_name', 'last_record_creator_name', 'creator_name', 'label_name', 'source_name', 'channel_name', 'distribution_name', 'win_reason', 'lose_reason','stage_update_name'], 'string', 'max' => 25]
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_name' => '客户名称',
            'id' => 'ID',
            'name' => 'Name',
            'contacts_id' => '联系人关联ID',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'next_follow_time' => 'Next Follow Time',
            'last_record_creator_id' => 'Last Record Creator ID',
            'last_record_creator_name' => 'Last Record Creator Name',
            'last_record' => 'Last Record',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'progress' => 'Progress',
            'status' => 'Status',
            'label_id' => 'Label ID',
            'label_name' => 'Label Name',
            'customer_id' => 'Customer ID',
            'source_id' => 'Source ID',
            'business_subject_id' => '业务主体ID',
            'source_name' => 'Source Name',
            'channel_id' => 'Channel ID',
            'channel_name' => 'Channel Name',
            'total_amount' => 'Total Amount',
            'distribution_id' => 'Distribution ID',
            'distribution_name' => 'Distribution Name',
            'distribution_at' => 'Distribution At',
            'stage_update_at' => 'Stage Update At',
            'update_id' => 'Update ID',
            'update_name' => 'Update Name',
            'is_distribution' => 'Is Distribution',
            'is_extract' => 'Is Extract',
            'is_transfer' => 'Is Transfer',
            'is_cross' => 'Is Cross',
            'is_new' => 'Is New',
            'is_protect' => 'Is Protect',
            'win_reason' => 'Win Reason',
            'win_progress' => 'Win Progress',
            'win_describe' => 'Win Describe',
            'lose_reason' => 'Lose Reason',
            'lose_progress' => 'Lose Progress',
            'lose_describe' => 'Lose Describe',
            'remark' => 'Remark',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
            'extract_time' => 'Extract Time',
            'move_public_time' => 'Move Public Time',
            'send_administrator_id' => 'Send Administrator ID',
            'send_time' => 'Send Time',
            'predict_deal_time' => 'Predict Deal Time',
            'deal_time' => 'Deal Time',
            'invalid_time' => 'Invalid Time',
            'invalid_reason' => 'Invalid Reason',
            'user_id' => 'User ID',
            'updated_at' => 'Updated At',
            'niche_public_id' => '商机公海',
        ];
    }

    public function getSource()
    {
        return $this->hasOne(Source::className(), ['id' => 'source_id']);
    }

    public function getChannel()
    {
        return $this->hasOne(Channel::className(), ['id' => 'channel_id']);
    }

    public function getBusinessSubject()
    {
        return $this->hasOne(BusinessSubject::className(), ['customer_id' => 'customer_id']);
    }


    public function getCustomer()
    {
        return static::hasOne(CrmCustomer::className(), ['id' => 'customer_id']);
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    public function getNichePublic()
    {
        return static::hasOne(NichePublic::className(), ['id' => 'niche_public_id']);
    }

    public function getNicheRecord()
    {
        return NicheRecord::find()->where(['niche_id'=>$this->id])->orderBy('id desc')->one();
    }

    public function getAdministratorNichePublic()
    {
        return NichePublicDepartment::find()->where(['department_id'=>$this->administrator->department_id])->one();
    }

    public function getOpportunityProducts()
    {
        return static::hasMany(NicheProduct::className(), ['niche_id' => 'id']);
    }


    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->created_at);
        };
        $fields['updated_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->updated_at);
        };
        $fields['next_follow_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->next_follow_time == 0 ? null : $this->next_follow_time);
        };
        $fields['predict_deal_time'] = function() {
            return Yii::$app->formatter->asDate($this->predict_deal_time == 0 ? null : $this->predict_deal_time);
        };
        $fields['extract_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->extract_time == 0 ? null : $this->extract_time);
        };
        $fields['move_public_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->move_public_time == 0 ? null : $this->move_public_time);
        };
        $fields['send_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->send_time == 0 ? null : $this->send_time);
        };
        $fields['last_record'] = function() {
            return Yii::$app->formatter->asDatetime($this->last_record == 0 ? null : $this->last_record);
        };
        $fields['deal_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->deal_time == 0 ? null : $this->deal_time);
        };
        $fields['invalid_time'] = function() {
            return Yii::$app->formatter->asDatetime($this->invalid_time == 0 ? null : $this->invalid_time);
        };
        $fields['stage_update_at'] = function() {
            return Yii::$app->formatter->asDatetime($this->stage_update_at == 0 ? null : $this->stage_update_at);
        };
        return $fields;
    }

    public function getExportStatusName()
    {
        if($this->status == self::STATUS_NOT_DEAL)
        {
            return '未成交';
        }
//        else if($this->status == self::STATUS_APPLY)
//        {
//            return '申请中';
//        }
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

    public function getTag()
    {
        /** @var Tag $tag */
        $tag = (new Query())
            ->select('t.name')
            ->from(['t' => Tag::tableName()])
            ->innerJoin(['ot' => Niche::tableName()], 'ot.label_id = t.id')
            ->where(['ot.id' => $this->id])->one();
        return $tag ? $tag['name'] : '--';
    }

    public function getExportSourceName()
    {
        return $this->customer ? $this->customer->getSourceName() : '--';
    }

    /**
     * @return string
     */
    public function  getExportNextFollowTime()
    {
        if ($this->next_follow_time > 0)
        {
            return $this->next_follow_time ? Yii::$app->formatter->asDatetime($this->next_follow_time) : '--';
        }
        return '--';
    }

    public function getExportInvalidReason()
    {
        if($this->status == self::STATUS_FAIL)
        {
            return !empty($this->lose_reason) ? $this->lose_reason : '';
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
    public function getLastRecordContent()
    {
        $department =  NicheRecord::find()->select('content')->where(['niche_id' => $this->id])->orderBy(['created_at' => SORT_DESC])->one();
        return $department ? $department->content : '--';
    }


}
