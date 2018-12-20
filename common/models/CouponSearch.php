<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\BaseDataProvider;

/**
 * Class CouponSearch
 * @package frontend\models
 */

class CouponSearch extends Model
{

    public $keyword;
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['keyword', 'filter', 'filter' => 'trim'],
            ['keyword', 'safe'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'keyword' => '关键词',
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @param $status
     * @param $type
     * @return BaseDataProvider
     */
    public function search($params, $status, $type)
    {
        $query = CouponUser::find()->alias('cu');
        $query -> innerJoinWith('coupon c');//此方法需要建立映射关系
        $query->andWhere(['cu.user_id' => \Yii::$app->user->id]);
        $time = time();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,//每页显示条数
                'validatePage' => false,
            ],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        //使用有效期(开始)时间不能大于当前时间
//        $query -> andWhere(['<=', 'c.begin_effect_time', $time]);
        $query -> andWhere(['c.mode' => Coupon::MODE_COUPON]);

        if(null != $type)
        {
            //折扣券
            if($type == 'discount'){
                $query->andWhere(['c.type' => Coupon::TYPE_DISCOUNT]);
            }elseif($type == 'reduction'){
                //减满券
                $query->andWhere(['c.type' => Coupon::TYPE_REDUCTION]);
            }
        }

        if( null != $status ){
            //未使用，已失效优惠券(过期)
            if($status == 'invalid'){
                $query->andWhere(['and', ['<', 'c.end_effect_time', $time], ['cu.status' => CouponUser::STATUS_ACTIVE]]);
            }elseif($status == 'available'){
                //可使用优惠券
                $query->andWhere(['and', ['>=', 'c.end_effect_time', $time], ['cu.status' => CouponUser::STATUS_ACTIVE]]);
//                $query->andWhere(['cu.status' => CouponUser::STATUS_ACTIVE]);
//                $query->andWhere(['and', ['<=', 'c.begin_effect_time', $time],
//                    ['>=', 'c.end_effect_time', $time]]);
            }elseif($status == 'used'){
                //已使用优惠券
                $query->andWhere(['cu.status' => CouponUser::STATUS_USED]);
            }
        }
        $query->orderBy(['c.end_effect_time' => SORT_ASC]);

        return $dataProvider;
    }

}
