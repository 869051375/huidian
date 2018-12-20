<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_follow_record}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $intention_product_id
 * @property string $intention_product_name
 * @property integer $type
 * @property integer $next_follow_time
 * @property integer $is_follow
 * @property string $follow_remark
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class OrderFollowRecord extends ActiveRecord
{
    const FOLLOW_ACTIVE = 1;//继续跟进
    const FOLLOW_DISABLED = 0;//不跟进
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_follow_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'intention_product_id', 'type', 'next_follow_time', 'is_follow', 'creator_id', 'created_at'], 'integer'],
            [['intention_product_name'], 'string', 'max' => 15],
            [['follow_remark'], 'string', 'max' => 80],
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
            'order_id' => 'Order ID',
            'intention_product_id' => 'Intention Product ID',
            'intention_product_name' => 'Intention Product Name',
            'type' => 'Type',
            'next_follow_time' => 'Next Follow Time',
            'is_follow' => 'Is Follow',
            'follow_remark' => 'Follow Remark',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
              'class' => TimestampBehavior::className(),
              'attributes' => [
                  ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
              ],
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {

            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            if($insert)
            {
                $this->creator_id = $user->id;
                $this->creator_name = $user->name;
                return true;
            }
        }
        return false;
    }

    public function isFollow()
    {
        return $this->is_follow == 1;
    }
}
