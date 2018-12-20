<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "order_balance_record".
 *
 * @property integer $id
 * @property string $title
 * @property integer $status
 * @property integer $order_id
 * @property string $content
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class OrderBalanceRecord extends \yii\db\ActiveRecord
{
    const STATUS_APPLY = 1;
    const STATUS_REJECT = 2;
    const STATUS_TRUE = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_balance_record}}';
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

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            //业绩记录
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $this->creator_id = $user->id;
            $this->creator_name = $user->name;
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'order_id', 'creator_id', 'created_at'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 15],
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
            'title' => 'Title',
            'status' => 'Status',
            'order_id' => 'Order ID',
            'content' => 'Content',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public static function createRecord($title,$status,$order_id,$content)
    {
        $model = new OrderBalanceRecord();
        $model->title = $title;
        $model->status = $status;
        $model->order_id = $order_id;
        $model->content = $content;
        $model->save(false);
    }
}
