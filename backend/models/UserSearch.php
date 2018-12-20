<?php

namespace backend\models;

use common\models\Source;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class UserSearch
 * @package backend\models
 *
 * @property Source $customerSource
 */

class UserSearch extends Model
{

    const TYPE_USER_PHONE = 1;//客户联系方式
    const TYPE_USER_NAME = 2; //客户姓名
    const TYPE_SALESMAN = 3; //业务员姓名

    const TYPE_REGISTER_SELF = 0; //自主注册
    const TYPE_REGISTER_BACKSTAGE = 1; //其他方式

    public $keyword;
    public $starting_time;
    public $end_time;
    public $type;
    public $source;
    public $user_from;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['keyword', 'filter', 'filter' => 'trim'],
            [['type','source','user_from'], 'integer'],
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
            'type' => '类型',
            'source' => '客户来源',
            'user_from' => '注册方式',
            'starting_time' => '开始时间',
            'end_time' => '结束时间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $params
     * @param $vest
     * @return ActiveDataProvider
     */
    public function search($params, $vest)
    {
        $query = User::find()->alias('u');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate())
        {
            return $dataProvider;
        }

        $query->where(['u.is_vest'=>$vest]);

        if(!empty($this->type)&&$vest==0)
        {
            if ($this->type == self::TYPE_USER_NAME)
            {
                $query->andFilterWhere(['like', 'u.name', $this->keyword]);
            } elseif ($this->type == self::TYPE_USER_PHONE)
            {
                $query->andFilterWhere(['like', 'u.phone', $this->keyword]);
            } elseif ($this->type == self::TYPE_SALESMAN)
            {
                $query->andFilterWhere(['like', 'u.creator_name', $this->keyword]);
            }
        }
        else
        {
            $query->andFilterWhere(['or', ['like', 'u.name', $this->keyword], ['like', 'u.phone', $this->keyword]]);
        }

        if(!empty($this->source))
        {
            $query->andFilterWhere(['u.source_id' => $this->source]);
        }

        if(!empty($this->starting_time))
        {
            $query->andWhere('u.created_at >= :start_time', [':start_time' => strtotime($this->starting_time)]);
        }
        if(!empty($this->end_time))
        {
            $query->andWhere('u.created_at <= :end_time', [':end_time' => strtotime($this->end_time)+86400]);
        }

        if($this->user_from == self::TYPE_REGISTER_SELF)
        {
            $query->andFilterWhere(['u.register_mode'=> $this->user_from]);
        }
        elseif($this->user_from == self::TYPE_REGISTER_BACKSTAGE)
        {
            $query->andFilterWhere(['u.register_mode'=> $this->user_from]);
        }

        return $dataProvider;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_USER_NAME => '客户姓名/昵称',
            self::TYPE_USER_PHONE => '客户联系方式',
            self::TYPE_SALESMAN => '业务员姓名',
        ];
    }

    public static function getUserFrom()
    {
        return [
            self::TYPE_REGISTER_SELF => '自主注册',
            self::TYPE_REGISTER_BACKSTAGE => '后台新增',
        ];
    }

    public function getCustomerSource()
    {
        return Source::find()->where(['id' => $this->source])->one();
    }
}
