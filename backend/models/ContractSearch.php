<?php

namespace backend\models;

use common\models\Administrator;
use common\models\City;
use common\models\Company;
use common\models\Contract;
use common\models\CrmDepartment;
use common\models\District;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Province;
use common\models\VirtualOrder;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class ContractSearch
 * @property ProductCategory $topCategory
 * @property ProductCategory $category
 * @property Province $province
 * @property City $city
 * @property District $district
 * @package backend\models
 *
 *
 * @property Company $company
 * @property CrmDepartment $department
 * @property Administrator $administrator
 */

class ContractSearch extends Model
{
    public $status;
    public $starting_time; //创建时间
    public $end_time;
    public $contract_code;

    public $keyword;
    public $sign_date;//签订日期
    public $administrator_id;
    public $signature_id;
    public $contract_type;
    public $company_id;
    public $department_id;
    public $type;

    const PENDING_CONTRACT = 1;//待签约
    const ALREADY_SIGN = 2;//已签约
    const SIGN_FAIL = 3;//签约失败
    const PENDING_INVALID = 4;//待作废
    const ALREADY_INVALID = 5;//已作废
    const PENDING_MODIFY = 6;//待变更
    const ALREADY_MODIFY = 7;//已变更

    const TYPE_CONTRACT_NAME = 1; //合同名称
    const TYPE_CUSTOMER_NAME = 2; //客户名称
    const TYPE_CUSTOMER_PHONE = 3;//客户手机号

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['keyword','contract_code'], 'filter', 'filter' => 'trim'],
            [['type','contract_type','company_id','department_id','administrator_id','status','signature_id'], 'integer'],
            [['keyword','contract_code'], 'safe'],
            [['starting_time', 'end_time','sign_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '起始时间不能大于结束时间！');
        }
    }


    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '关键词',
            'type' => '自定义筛选',
            'starting_time' => '下单时间（起）',
            'end_time' => '下单时间（止）',
            'first_pay_start_time' => '首次付款时间（起）',
            'first_pay_end_time' => '首次付款时间（止）',
            'is_proxy' => '',
            'status' => '合同状态',
            'signature_id' => '签章状态',
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param int $range
     * @return ActiveDataProvider
     */
    public function search($params, $range)
    {
        $query = Contract::find()->alias('c')->innerJoinWith(['virtualOrder vo']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params,'');
        if (!$this->validate())
        {
            return $dataProvider;
        }

        if($this->contract_code)
        {
            $query->andFilterWhere(['like','c.contract_no',$this->contract_code]);
        }

        if($this->sign_date)
        {
            $sign_date = strtotime($this->sign_date);
            $query->andFilterWhere(['c.signing_date' => $sign_date]);
        }

        if($this->contract_type)
        {
            $query->andFilterWhere(['c.contract_type_id' => $this->contract_type]);
        }

        if($this->signature_id)
        {
            $query->andFilterWhere(['c.signature' => $this->signature_id]);
        }

        if($this->administrator_id)
        {
            $query->andFilterWhere(['c.administrator_id' => $this->administrator_id]);
        }

        if($this->company_id)
        {
            $query->andFilterWhere(['c.company_id' => $this->company_id]);
        }

        if($this->department_id)
        {
            $query->andFilterWhere(['c.department_id' => $this->department_id]);
        }

        if($range)
        {
            if($range == 'pending-contract')//待签约
            {
                $query->andWhere(['or',
                    ['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_PENDING_REVIEW],
                    ['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_NO_REVIEW],
                    ['c.status' => Contract::STATUS_MODIFY,'c.correct_status' => Contract::MODIFY_NO_REVIEW]]);
            }
            else if($range == 'already-contract')//已签约
            {
                $query->andWhere(['or',['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_PENDING_REVIEW]
                    ,['c.status' => Contract::STATUS_INVALID,'c.invalid_status' => Contract::INVALID_PENDING_REVIEW]
                    ,['c.status' => Contract::STATUS_MODIFY,'c.correct_status' => Contract::MODIFY_PENDING_REVIEW]]);
            }
            else if($range == 'review')//审核中
            {
                $query->andWhere(['or',['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_PENDING_REVIEW]
                    ,['c.status' => Contract::STATUS_INVALID,'c.invalid_status' => Contract::INVALID_PENDING_REVIEW]
                    ,['c.status' => Contract::STATUS_MODIFY,'c.correct_status' => Contract::MODIFY_PENDING_REVIEW]]);
            }
            else if($range == 'receipt-finish')//回款完成
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_ALREADY_REVIEW]);
                $sql = 'vo.total_amount = vo.payment_amount';
                $query->andWhere($sql);
            }
            else if($range == 'pending-receipt')//待回款
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_ALREADY_REVIEW]);
                $sql = 'vo.payment_amount < vo.total_amount';
                $query->andWhere($sql);
            }
            else if($range == 'invalid')//已作废
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_INVALID,'c.invalid_status' => Contract::INVALID_ALREADY_REVIEW]);
            }
        }

        if($this->status)
        {
            if ($this->status == self::PENDING_CONTRACT)//待签约
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_PENDING_REVIEW]);
            }
            elseif ($this->status == self::ALREADY_SIGN)//已签约
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_ALREADY_REVIEW]);
            }
            elseif ($this->status == self::SIGN_FAIL)//签约失败
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_CONTRACT,'c.sign_status' => Contract::SIGN_NO_REVIEW]);
            }
            elseif ($this->status == self::PENDING_INVALID)//待作废
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_INVALID,'c.invalid_status' => Contract::INVALID_PENDING_REVIEW]);
            }
            elseif ($this->status == self::ALREADY_INVALID)//已作废
            {
                $query->andFilterWhere(['c.status' => Contract::STATUS_INVALID,'c.invalid_status' => Contract::INVALID_ALREADY_REVIEW]);
            }
//            elseif ($this->status == self::PENDING_MODIFY)//待变更
//            {
//                $query->andFilterWhere(['c.status' => Contract::STATUS_MODIFY,'c.correct_status' => Contract::MODIFY_PENDING_REVIEW]);
//            }
//            elseif ($this->status == self::ALREADY_MODIFY)//已变更
//            {
//                $query->andFilterWhere(['c.status' => Contract::STATUS_MODIFY,'c.correct_status' => Contract::MODIFY_ALREADY_REVIEW]);
//            }
        }

        if($this->type == self::TYPE_CONTRACT_NAME){
            $query->andFilterWhere(['like', 'c.name', $this->keyword]);
        } elseif ($this->type == self::TYPE_CUSTOMER_NAME){
            $query->joinWith(['customer cc']);
            $query->andFilterWhere(['like', 'cc.name', $this->keyword]);
        } elseif ($this->type == self::TYPE_CUSTOMER_PHONE){
            $query->joinWith(['customer cc']);
            $query->andFilterWhere(['like', 'cc.phone', $this->keyword]);
        }

        //创建时间
        if(!empty($this->starting_time))
        {
            $query->andWhere('c.created_at >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('c.created_at <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        /** @var \common\models\Administrator $user */
        $user = \Yii::$app->user->identity;
        Contract::filterRole($query, $user);
        $query->orderBy(['c.id' => SORT_DESC]);
        return $dataProvider;
    }

    public static function getTypes()
    {
        return [
            self::PENDING_CONTRACT => '合同名称',
            self::TYPE_CUSTOMER_NAME => '客户名称',
            self::TYPE_CUSTOMER_PHONE => '客户手机号',
        ];
    }

    public static function getStatus()
    {
        return [
            self::PENDING_CONTRACT => '待签约',
            self::ALREADY_SIGN => '已签约',
            self::SIGN_FAIL => '签约失败',
            self::PENDING_INVALID => '待作废',
            self::ALREADY_INVALID => '已作废',
//            self::PENDING_MODIFY => '待变更',
//            self::ALREADY_MODIFY => '已变更',
        ];
    }

    public function getCompany()
    {
        return Company::findOne($this->company_id);
    }

    public function getDepartment()
    {
        return CrmDepartment::findOne($this->department_id);
    }

    public function getAdministrator()
    {
        return Administrator::findOne($this->administrator_id);
    }

}
