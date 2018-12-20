<?php

namespace common\models;

use Yii;
use yii\log\Logger;
use yii\redis\Connection;

/**
 * This is the model class for table "{{%order_status_statistics}}".
 *
 * @property integer $id
 * @property integer $date
 * @property integer $pending_pay_no
 * @property integer $unpaid_no
 * @property integer $pending_allot_no
 * @property integer $pending_service_no
 * @property integer $in_service_no
 * @property integer $complete_service_no
 * @property integer $top_category_id
 * @property integer $category_id
 * @property integer $product_id
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $district_id
 */
class OrderStatusStatistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_status_statistics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date', 'top_category_id','category_id','product_id','province_id','city_id','pending_pay_no', 'unpaid_no','pending_allot_no','pending_service_no','in_service_no','complete_service_no'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    //统计各订单状态的数量
    public static function totalStatusNum($product_id,$region = 0,$attribute)
    {
        $region = $region ? $region : 0 ;
        try
        {
            $order_key = date('Y-m-d').'-'.$product_id.'-'.$region.'-'.$attribute;
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            if ($redis->get($order_key))
            {
                $redis->incrby($order_key, 1);
            } else {
                $redis->set($order_key, 1);
            }
        }
        catch (\Exception $e)
        {
            Yii::getLogger()->log($product_id.'-'.$region.'-'.$attribute.'-'.'统计出现错误',Logger::LEVEL_TRACE);
        }
    }

}
