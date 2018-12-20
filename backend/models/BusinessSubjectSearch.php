<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\City;
use common\models\CrmCustomerCombine;
use common\models\District;
use common\models\Province;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class BusinessSubjectSearch
 * @package backend\models
 *
 * @property Province $province
 * @property City $city
 * @property District $district
 */

class BusinessSubjectSearch extends Model
{

    const TYPE_COMPANY = 1;//公司名称
    const TYPE_CREDIT = 2; //信用代码
    const TYPE_LEGAL_PERSON = 3; //法人
    const TYPE_REGISTERED_CAPITAL = 4; //注册资本

    const TYPE_NAME = 5; //姓名
    const TYPE_ID_CARD = 6; //身份证

    public $industry_id;  //行业id
    public $province_id;  //省id
    public $city_id;      //市id
    public $district_id;  //区id
    public $starting_time;//成立时间
    public $end_time;     //到期时间
    public $tax_type;     //税务类型
    public $type;         //字段类型
    public $identity_type;//身份类型
    public $keyword;      // 关键词

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['type','industry_id','province_id','city_id','district_id','tax_type','identity_type'], 'integer'],
            [['keyword'], 'string'],
            [['starting_time', 'end_time'], 'date', 'format' => 'yyyy-MM-dd'],
            [['starting_time'], 'validateTimes'],
        ];
    }

    public function validateTimes()
    {
        if($this->starting_time>$this->end_time && $this->end_time)
        {
            $this->addError('starting_time', '开始时间不能大于结束时间！');
        }
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '',
            'type' => '字段类型',
            'industry_id' => '行业类型',
            'province_id' => '注册地址',
            'city_id' => '',
            'district_id' => '',
            'starting_time' => '开始时间',
            'end_time' => '结束时间',
            'tax_type' => '税务类型',
            'identity_type' => '字段类型',
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
     * @param $params
     * @param $subject_type
     * @return ActiveDataProvider
     */
    public function search($params,$subject_type)
    {
        $query = BusinessSubject::find()->alias('b');
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if($administrator->isCompany())
        {
            $crmCustomerCombine = CrmCustomerCombine::find()->select('customer_id')
                ->where(['company_id' => $administrator->company_id])->asArray()->all();
            $ids = ArrayHelper::getColumn($crmCustomerCombine, 'customer_id');
            if($ids)
            {
                $query->andWhere(['in','b.customer_id',$ids]);
            }
            else
            {
                $query->andWhere(['b.customer_id' => 0]);
            }
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $this->load($params);
        if (!$this->validate())
        {
            return $dataProvider;
        }

        $query->andFilterWhere(['b.subject_type'=> $subject_type]);

        if($this->industry_id)
        {
            $query->andFilterWhere(['b.industry_id'=> $this->industry_id]);
        }

        $query->andFilterWhere([
            'b.province_id' => $this->province_id <= 0 ? null : $this->province_id,
            'b.city_id' => $this->city_id <= 0 ? null : $this->city_id,
            'b.district_id' => $this->district_id <= 0 ? null : $this->district_id,
        ]);

        if(!empty($this->starting_time))
        {
            $query->andWhere('b.operating_period_begin >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }

        if(!empty($this->end_time))
        {
            $query->andWhere('b.operating_period_end <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        if($this->industry_id)
        {
            $query->andFilterWhere(['b.industry_id'=> $this->industry_id]);
        }

        if($this->tax_type)
        {
            $query->andFilterWhere(['b.tax_type'=> $this->tax_type]);
        }

        if($this->type)
        {
            if ($this->type == self::TYPE_COMPANY) {
                $query->andFilterWhere(['like', 'b.company_name', $this->keyword]);
            } elseif ($this->type == self::TYPE_CREDIT) {
                $query->andFilterWhere(['like', 'b.credit_code', $this->keyword]);
            } elseif ($this->type == self::TYPE_LEGAL_PERSON) {
                $query->andFilterWhere(['like', 'b.legal_person_name', $this->keyword]);
            } elseif ($this->type == self::TYPE_REGISTERED_CAPITAL) {
                $query->andFilterWhere(['like', 'b.registered_capital', $this->keyword]);
            }
        }

        if($this->identity_type && $subject_type)
        {
            if ($this->identity_type == self::TYPE_NAME)
            {
                $query->andFilterWhere(['like', 'b.region', $this->keyword]);
            }
            elseif ($this->identity_type == self::TYPE_ID_CARD)
            {
                $query->andFilterWhere(['like', 'b.name', $this->keyword]);
            }
        }

        $query->orderBy(['b.created_at' => SORT_DESC]);

        return $dataProvider;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_COMPANY => '公司名称',
            self::TYPE_CREDIT => '信用代码',
            self::TYPE_LEGAL_PERSON => '法定代表人',
            self::TYPE_REGISTERED_CAPITAL => '注册资本',
        ];
    }

    public static function getIdentityTypes()
    {
        return [
            self::TYPE_NAME => '姓名',
            self::TYPE_ID_CARD => '身份证',
        ];
    }

    public function getProvince()
    {
        return Province::find()->where('id=:id', [':id' => $this->province_id])->one();
    }

    public function getCity()
    {
        return City::find()->where(['id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])->one();
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id = :city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id = :province_id', [':province_id' => $this->province_id])
            ->one();
    }


}
