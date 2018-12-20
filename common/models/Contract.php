<?php

namespace common\models;

use common\components\OSS;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * This is the model class for table "contract".
 *
 * @property integer $id
 * @property string $name
 * @property string $serial_number
 * @property string $contract_no
 * @property string $contract_type_id
 * @property string $genre
 * @property integer $customer_id
 * @property integer $virtual_order_id
 * @property integer $opportunity_id
 * @property integer $administrator_id
 * @property integer $company_id
 * @property integer $department_id
 * @property string $department_path
 * @property integer $status
 * @property integer $signature
 * @property integer $sign_status
 * @property integer $correct_status
 * @property integer $signing_date
 * @property integer $invalid_status
 * @property integer $invalid_cause
 * @property string $invalid_remark
 * @property string $remark
 * @property string $file
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 *
 * @property VirtualOrder $virtualOrder
 * @property CrmCustomer $customer
 * @property ContractType $contractType
 * @property NicheContract[] $opportunity
 * @property Administrator $administrator
 * @property CrmDepartment $department
 * @property ContractRecord[] $contractRecord
 * @property AdjustOrderPrice $adjustOrderPrice
 */
class Contract extends \yii\db\ActiveRecord
{
    const STATUS_CONTRACT = 0;//签约
    const STATUS_INVALID = 1;//作废
    const STATUS_MODIFY = 2;//变更

    const SIGN_PENDING_REVIEW = 0;//签约待审核
    const SIGN_ALREADY_REVIEW = 1;//签约审核
    const SIGN_NO_REVIEW = 2;//签约未通过审核

    const MODIFY_PENDING_REVIEW = 0;//变更待审核
    const MODIFY_ALREADY_REVIEW = 1;//变更审核
    const MODIFY_NO_REVIEW = 2;//变更审核未通过

    const INVALID_PENDING_REVIEW = 0;//作废待审核
    const INVALID_ALREADY_REVIEW = 1;//作废审核
    const INVALID_NO_REVIEW = 2;//作废审核未通过

    const SIGNATURE_WAIT = 1;//待签章
    const SIGNATURE_ALREADY = 2;//已签章
    const SIGNATURE_RECYCLED = 3;//已回收
    const SIGNATURE_WAIT_RECYCLE = 4;//待回收

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contract}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'virtual_order_id', 'opportunity_id','invalid_cause','correct_status','invalid_status','signature', 'administrator_id','company_id', 'department_id','status','sign_status', 'signing_date','contract_type_id', 'creator_id', 'created_at'], 'integer'],
            [['invalid_remark','remark','file'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['serial_number'], 'string', 'max' => 25],
            [['contract_no'], 'string', 'max' => 255],
            [['genre'], 'string', 'max' => 30],
            [['department_path'], 'string', 'max' => 32],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'serial_number' => 'Serial Number',
            'contract_no' => 'Contract No',
            'genre' => 'Genre',
            'customer_id' => 'Customer ID',
            'virtual_order_id' => 'Virtual Order ID',
            'opportunity_id' => 'Opportunity ID',
            'administrator_id' => 'Administrator ID',
            'status' => 'Status',
            'signature' => 'Signature',
            'sign_status' => 'Sign Status',
            'correct_status' => 'Correct Status',
            'signing_date' => 'Signing Date',
            'remark' => 'Remark',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'contract_type_id' => '合同类型',
        ];
    }

    public function getVirtualOrder()
    {
        return $this->hasOne(VirtualOrder::className(),['id' => 'virtual_order_id']);
    }

    public function getCustomer()
    {
        return $this->hasOne(CrmCustomer::className(),['id' => 'customer_id']);
    }

    public function getContractType()
    {
        return $this->hasOne(ContractType::className(),['id' => 'contract_type_id']);
    }

    public function getOpportunity()
    {
        return $this->hasMany(NicheContract::className(),['contract_id' => 'id']);
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id' => 'administrator_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(CrmDepartment::className(),['id' => 'department_id']);
    }

    public function getContractRecord()
    {
        return $this->hasMany(ContractRecord::className(),['contract_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public static function getUrl($key, $timeout = 3600)
    {
        /** @var OSS $oss */
        $oss = Yii::$app->get('oss');
        return $oss->getUrl($key, $timeout);
    }

    public function isButtonShow()
    {
        if($this->status == Contract::STATUS_CONTRACT && $this->sign_status == Contract::SIGN_NO_REVIEW)
        {
            return true;
        }
        elseif ($this->status == Contract::STATUS_MODIFY &&  $this->correct_status == Contract::MODIFY_NO_REVIEW)
        {
            return true;
        }
        return false;
    }

    public function getButtonName()
    {
        if($this->status == Contract::STATUS_CONTRACT && $this->sign_status == Contract::SIGN_NO_REVIEW)
        {
            return '合同变更';
        }
        elseif ($this->status == Contract::STATUS_MODIFY &&  $this->correct_status == Contract::MODIFY_NO_REVIEW)
        {
            return '变更审核失败';
        }
    }

    /**
     * 审核中按钮字内容显示
     * @param Administrator $administrator
     * @return string
     */
    public function getReviewButtonName($administrator)
    {
        if($this->status == Contract::STATUS_CONTRACT && $this->sign_status == Contract::SIGN_PENDING_REVIEW)
        {
            return ($administrator->isLeader() || $administrator->isDepartmentManager()) && $administrator->isParentDepartment($this->department) ? '
            签约审核' : '签约审核中';
        }
        elseif ($this->status == Contract::STATUS_MODIFY &&  $this->correct_status == Contract::MODIFY_PENDING_REVIEW)
        {
            return ($administrator->isLeader() || $administrator->isDepartmentManager()) && $administrator->isParentDepartment($this->department) ? '
            变更审核' : '变更审核中';
        }
        elseif ($this->status == Contract::STATUS_INVALID &&  $this->invalid_status == Contract::INVALID_PENDING_REVIEW)
        {
            return ($administrator->isLeader() || $administrator->isDepartmentManager()) && $administrator->isParentDepartment($this->department) ? '
            作废审核' : '作废审核中';
        }
    }

    public function getStatus()
    {
        if($this->status == self::STATUS_CONTRACT && $this->sign_status == self::SIGN_PENDING_REVIEW)
        {
            return '待签约';
        }
        elseif ($this->status == self::STATUS_CONTRACT && $this->sign_status == self::SIGN_NO_REVIEW)
        {
            return '<span class="text-danger">签约失败</span>';
        }
        elseif ($this->status == self::STATUS_CONTRACT && $this->sign_status == self::SIGN_ALREADY_REVIEW)
        {
            return '已签约';
        }
        elseif ($this->status == self::STATUS_INVALID && $this->invalid_status == self::INVALID_PENDING_REVIEW)
        {
            return '待作废';
        }
        elseif ($this->status == self::STATUS_INVALID && $this->invalid_status == self::INVALID_NO_REVIEW)
        {
            return '<span class="text-danger">作废失败</span>';
        }
        elseif ($this->status == self::STATUS_INVALID && $this->invalid_status == self::INVALID_ALREADY_REVIEW)
        {
            return '已作废';
        }
        elseif ($this->status == self::STATUS_MODIFY && $this->correct_status == self::MODIFY_PENDING_REVIEW)
        {
            return '待变更';
        }
        elseif ($this->status == self::STATUS_MODIFY && $this->correct_status == self::MODIFY_NO_REVIEW)
        {
            return '<span class="text-danger">签约失败</span>';
        }
        elseif ($this->status == self::STATUS_MODIFY && $this->correct_status == self::MODIFY_ALREADY_REVIEW)
        {
            return '已变更';
        }
        return null;
    }

    /**
     * @param ActiveQuery $query
     * @param Administrator $administrator
     */
    public static function filterRole($query, $administrator)
    {
        if($administrator->isCompany())
        {
            if($administrator->isLeader() || $administrator->isDepartmentManager())
            {
                $query->leftJoin("(SELECT `id`,`path` FROM ".CrmDepartment::tableName()." WHERE path LIKE  '".$administrator->department->path."-%') as d",'d.id = c.department_id');
                $query->andWhere(['or',
                    "d.path like '". $administrator->department->path."-%'",
                    ['d.id' => $administrator->department_id],
                    ['c.department_id' => $administrator->department_id],
                    ['c.administrator_id' => $administrator->id]]);
                $query->andWhere(['c.company_id' => $administrator->company_id]);//领导管理员角色区分公司
            }
            else
            {
                $query->andWhere(['c.administrator_id' => $administrator->id]);
            }
        }
    }

    public function addFile($key, $name)
    {
        $files = [
            'key' => $key,
            'name' => $name
        ];
        $this->setFile($files);
    }

    public function setFile($files)
    {
        $this->file = Json::encode($files);
    }

    public function getFile()
    {
        if(empty($this->file)) return [];
        return Json::decode($this->file);
    }

    public static function getSignature()
    {
        return [
            0 => '请选择签章状态',
            self::SIGNATURE_WAIT => '待签章',
            self::SIGNATURE_ALREADY => '已签章',
            self::SIGNATURE_RECYCLED => '已回收',
            self::SIGNATURE_WAIT_RECYCLE => '待回收',
        ];
    }

    public function getSignatureName()
    {
        $signature = self::getSignature();
        return $signature[$this->signature];
    }

}
