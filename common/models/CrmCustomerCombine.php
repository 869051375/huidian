<?php

namespace common\models;

/**
 * This is the model class for table "crm_customer_combine".
 *
 * @property integer $customer_id
 * @property integer $administrator_id
 * @property integer $user_id
 * @property integer $status
  * @property integer $level
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $created_at
 * @property integer $business_subject_id
 *
 * @property Administrator $administrator
 * @property CrmDepartment $crmDepartment
 * @property CrmCustomer $crmCustomer
 * @property Company $company
 */
class CrmCustomerCombine extends \yii\db\ActiveRecord
{
    const STATUS_NOT_RECEIVE = 0; // 待转入
    const STATUS_RECEIVED = 1; // 已转入

    const CUSTOMER_LEVEL_DISABLED = 0; //无效客户
    const CUSTOMER_LEVEL_ACTIVE = 1; //有效客户

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_customer_combine}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'administrator_id','department_id', 'company_id'], 'required'],
            [['customer_id', 'administrator_id', 'user_id', 'status', 'created_at', 'level','company_id','business_subject_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => 'Customer ID',
            'administrator_id' => 'Administrator ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'level' => 'Level',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param $administrator Administrator
     * @param $customer CrmCustomer
     * @return boolean
     */
    public static function addTeam($administrator, $customer)
    {
        /** @var CrmCustomerCombine $c */
        $c = CrmCustomerCombine::find()->where(['customer_id' => $customer->id,
            'administrator_id' => $administrator->id])->one();
        if(null != $c) return true;

        $c = new CrmCustomerCombine();
        $c->level = CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE;
        $c->administrator_id = $administrator->id;
        $c->customer_id = $customer->id;
        $c->company_id = $administrator->company_id;
        $c->department_id = $administrator->department_id;
        $c->user_id = $customer->user_id;
        $c->status = CrmCustomerCombine::STATUS_RECEIVED;
        $c->created_at = time();
        return $c->save(false);
    }



    /**
     * @param $administrator Administrator
     * @param $customer CrmCustomer
     * @param $business BusinessSubject
     * @return boolean
     */
    public static function addCombine($administrator, $customer,$business)
    {
        /** @var CrmCustomerCombine $c */
        $c = CrmCustomerCombine::find()->where(['customer_id' => $customer->id,
            'administrator_id' => $administrator->id])->one();
        if(null != $c) return true;

        $c = new CrmCustomerCombine();
        $c->level = CrmCustomerCombine::CUSTOMER_LEVEL_ACTIVE;
        $c->business_subject_id = $business->id ?  $business->id : 0;
        $c->administrator_id = $administrator->id;
        $c->customer_id = $customer->id;
        $c->company_id = $administrator->company_id;
        $c->department_id = $administrator->department_id;
        $c->user_id = $customer->user_id;
        $c->status = CrmCustomerCombine::STATUS_RECEIVED;
        $c->created_at = time();
        return $c->save(false);
    }

    /**
     * @param $administrator Administrator
     * @param $customer CrmCustomer
     * @return boolean
     */
    public static function removeTeam($administrator, $customer)
    {
        /** @var CrmCustomerCombine $c */
        $c = CrmCustomerCombine::find()->where(['customer_id' => $customer->id,
            'administrator_id' => $administrator->id])->one();
        if(null != $c)
        {
            $countOrder = Order::find()->where(['user_id' => $customer->user_id ,'salesman_aid' => $administrator->id])->count();
            $countOpportunity = CrmOpportunity::find()->where(['customer_id' => $customer->id ,'administrator_id' => $administrator->id])->count();
            return $countOrder <= 0 && $countOpportunity <= 0 && $c->delete();
        }
        return false;
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id'=>'administrator_id']);
    }

    public function getCrmDepartment()
    {
        return $this->hasOne(CrmDepartment::className(),['id'=>'department_id']);
    }

    public function getCrmCustomer()
    {
        return $this->hasOne(CrmCustomer::className(),['id'=>'customer_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @param $status
     * @return int|string
     */
    public function getOpportunityAmount($status)
    {
         $query = CrmOpportunity::find()
            ->where(['customer_id'=>$this->customer_id,'administrator_id' => $this->administrator_id]);
            if($status == 2 || $status == 3)
            {
                $query->andWhere(['status' => $status]);
            }
            else
            {
                $query->andWhere(['not in','status',[CrmOpportunity::STATUS_DEAL, CrmOpportunity::STATUS_FAIL]]);
            }
        return $query->count();
    }

    /**
     * 获取待确认的商机数
     * @return int|string
     */
    public function getOpportunityNoReceive()
    {
        $query = CrmOpportunity::find()
            ->where([
                'customer_id'=>$this->customer_id,
                'administrator_id' => $this->administrator_id,
                'is_receive' => CrmOpportunity::RECEIVE_DISABLED
            ]);
        return $query->count();
    }

    /**
     * @return int|null|string
     * 已付订单数量
     */
    public function getOrderAlreadyAmount()
    {
        if($this->crmCustomer)
        {
            return Order::find()->alias('o')
                    ->innerJoinWith(['virtualOrder vo'])
                    ->andWhere(['o.user_id'=>$this->crmCustomer->user_id, 'o.salesman_aid' => $this->administrator_id])
                    ->andWhere(['or', ['vo.status' => VirtualOrder::STATUS_ALREADY_PAYMENT], ['vo.status' => VirtualOrder::STATUS_UNPAID]])
                    ->count();
        }
        return null;
    }

    /**
     * @return int|null|string
     * 未付订单数量
     */
    public function getOrderUnpaidAmount()
    {
        if($this->crmCustomer)
        {
            return Order::find()
                ->andWhere(['user_id'=>$this->crmCustomer->user_id, 'salesman_aid' => $this->administrator_id])
                ->andWhere(['status' => Order::STATUS_PENDING_PAY])->count();
        }
        return null;
    }

    /**
     * 判断作为客户合伙人下是否有订单
     * @return bool
     */
    public function hasOrder()
    {
        if($this->crmCustomer)
        {
            return Order::find()
                    ->andWhere([
                        'user_id'=>$this->crmCustomer->user_id,
                        'salesman_aid' => $this->administrator_id
                    ])
                    ->count() > 0;
        }
        return false;
    }

    /**
     * 判断作为客户合伙人下是否有商机
     * @return bool
     */
    public function hasOpportunity()
    {
        if($this->crmCustomer)
        {
            return CrmOpportunity::find()
                    ->andWhere([
                        'customer_id'=>$this->crmCustomer->id,
                        'administrator_id' => $this->administrator_id
                    ])
                    ->count() > 0;
        }
        return false;
    }

    public function getLevelName()
    {
        $level = self::getLevel();
        return $level[$this->level];
    }

    public static function getLevel()
    {
        return [
            self::CUSTOMER_LEVEL_DISABLED => '无效客户',
            self::CUSTOMER_LEVEL_ACTIVE => '有效客户',
        ];
    }

}
