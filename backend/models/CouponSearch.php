<?php
namespace backend\models;

use common\models\Coupon;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class CouponSearch
 * @package backend\models
 */
class CouponSearch extends Model
{
    const TYPE_STATUS_DISABLED = 1; //未生效
    const TYPE_STATUS_ACTIVE = 2; //正常使用中
    const TYPE_STATUS_EXPIRED = 3; //已过期
    const TYPE_STATUS_OBSOLETE = 4; //已作废

    public $type;
    public $type_status;
    public $keyword;

    public $code_type;
    public $code_type_status;

    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            [['type', 'type_status', 'code_type', 'code_type_status'], 'integer'],
            ['type_status', 'in', 'range' => [self::TYPE_STATUS_DISABLED, self::TYPE_STATUS_ACTIVE, self::TYPE_STATUS_EXPIRED, self::TYPE_STATUS_OBSOLETE]],
            ['code_type_status', 'in', 'range' => [self::TYPE_STATUS_ACTIVE, self::TYPE_STATUS_EXPIRED, self::TYPE_STATUS_OBSOLETE]],
            ['keyword', 'safe'],
        ];
    }

    public static function getTypeStatusList()
    {
        return [
            self::TYPE_STATUS_DISABLED => '未生效',
            self::TYPE_STATUS_ACTIVE => '正常使用中',
            self::TYPE_STATUS_EXPIRED => '已过期',
            self::TYPE_STATUS_OBSOLETE => '已作废',
        ];
    }

    public static function getCodeTypeStatusList()
    {
        return [
            self::TYPE_STATUS_ACTIVE => '正常使用中',
            self::TYPE_STATUS_EXPIRED => '已过期',
            self::TYPE_STATUS_OBSOLETE => '已作废',
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'type' => '优惠券类型',
            'code_type' => '优惠码类型',
            'type_status' => '优惠券状态',
            'code_type_status' => '优惠码状态',
            'keyword' => '关键词',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string $status
     *
     * @return ActiveDataProvider
     */
    public function search($params, $status = null)
    {
        $query = Coupon::find();

        $query->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {

            return $dataProvider;
        }

        $query->andFilterWhere([
            'type' => $this->type,
        ]);

        $query->andFilterWhere([
            'code_type' => $this->code_type,
        ]);

        if(!empty($this->type_status))
        {
            if($this->type_status == self::TYPE_STATUS_OBSOLETE)
            {
                //已作废
                $query->andFilterWhere(['status' => Coupon::STATUS_OBSOLETED]);
            }
            else
            {
                $query->andFilterWhere(['status' => Coupon::STATUS_ACTIVE]);

                if ($this->type_status == self::TYPE_STATUS_ACTIVE)
                {
                    //正常使用中
                    $query->andFilterWhere(['is_confirm' => Coupon::CONFIRM_ACTIVE])->andFilterWhere(['and', ['<=', 'begin_effect_time', time()], ['>=', 'end_effect_time', time()]]);
                }
                elseif ($this->type_status == self::TYPE_STATUS_EXPIRED)
                {
                    //已过期
                    $query->andFilterWhere(['and', ['< ', 'end_effect_time', time()],['>', 'end_effect_time', 0]]);
                }
                elseif ($this->type_status == self::TYPE_STATUS_DISABLED)
                {
                    //未生效
                    $query->andFilterWhere(['>', 'begin_effect_time', time()])->orFilterWhere(['is_confirm' =>Coupon::CONFIRM_DISABLED]);
                }
            }
        }

        if(!empty($this->code_type_status))
        {
            if($this->code_type_status == self::TYPE_STATUS_OBSOLETE)
            {
                //已作废
                $query->andFilterWhere(['status' => Coupon::STATUS_OBSOLETED]);
            }
            else
            {
                $query->andFilterWhere(['status' => Coupon::STATUS_ACTIVE]);

                if ($this->code_type_status == self::TYPE_STATUS_ACTIVE)
                {
                    //正常使用中
                    $query->andFilterWhere(['and', ['<=', 'begin_effect_time', time()], ['>=', 'end_effect_time', time()]]);
                }
                elseif ($this->code_type_status == self::TYPE_STATUS_EXPIRED)
                {
                    //已过期
                    $query->andFilterWhere(['and', ['< ', 'end_effect_time', time()],['>', 'end_effect_time', 0]]);
                }
            }
        }

        if(null !== $status)
        {
            if($status == Coupon::MODE_COUPON)
            {
                //优惠券
                $query->andWhere(['mode' => Coupon::MODE_COUPON]);

                //优惠券时有效
                $query->andFilterWhere(['or', ['like', 'name', $this->keyword]]);
            }
            else
            {
                //优惠码
                $query->andWhere(['mode' => Coupon::MODE_COUPON_CODE]);
            }
        }

        return $dataProvider;
    }

}