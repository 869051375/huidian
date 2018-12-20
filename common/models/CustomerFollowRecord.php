<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer_follow_record".
 *
 * @property string $id
 * @property string $subject_id
 * @property string $customer_id
 * @property string $opportunity_id
 * @property string $remark
 * @property string $creator_id
 * @property string $creator_name
 * @property string $created_at
 * @property integer $follow_end_time
 * @property integer $follow_start_time
 * @property integer $next_follow_time
 * @property integer $follow_mode
 * @property integer $follow_mode_id
 * @property CrmCustomer $customer
 */
class CustomerFollowRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_follow_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject_id', 'customer_id', 'opportunity_id', 'creator_id', 'created_at', 'follow_mode_id',], 'integer'],
            [['remark', 'follow_mode', 'follow_end_time', 'follow_start_time', 'next_follow_time'], 'string'],
            [['customer_id', 'remark', 'follow_mode_id', 'follow_mode'], 'required', 'on' => 'add'],
            [['customer_id'], 'required', 'on' => 'list'],
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
            'subject_id' => 'Subject ID',
            'customer_id' => 'Customer ID',
            'opportunity_id' => 'Opportunity ID',
            'remark' => 'Remark',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'follow_end_time' => 'Follow End Time',
            'follow_start_time' => 'Follow Start Time',
        ];
    }

    /**
     * @param $subject_id
     * @param $customer_id
     * @param int $opportunity_id
     * @param $remark
     * @param $follow_mode
     * @param int $follow_mode_id
     * @param int $next_follow_time
     * @param int $follow_end_time
     * @param int $follow_start_time
     * @param null $administrator
     */
    public static function add($subject_id, $customer_id, $opportunity_id, $remark, $follow_mode = 0, $follow_mode_id = 0, $next_follow_time = 0, $follow_end_time = 0, $follow_start_time = 0, $administrator = null)
    {
        $model = new CustomerFollowRecord();
        $model->subject_id = $subject_id;
        $model->customer_id = $customer_id;
        $model->opportunity_id = $opportunity_id ? $opportunity_id : 0;
        $model->remark = $remark;
        $model->follow_mode = $follow_mode;
        $model->follow_mode_id = $follow_mode_id;
        $model->next_follow_time = $next_follow_time;
        $model->follow_end_time = $follow_end_time;
        $model->follow_start_time = $follow_start_time;

        //注意：$administrator为system时，系统操作
        if ($administrator == 'system') {
            $model->creator_id = 0;
            $model->creator_name = '系统';
        } else {
            /** @var Administrator $administrator */
            $administrator = null == $administrator ? Yii::$app->user->identity : $administrator;
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
        }
        $model->created_at = time();
        //注意：$administrator为system时为系统甩出操作，客户无需执行一下流程
        if ($administrator !== 'system') {
            //客户操作
            if ($model->customer->operation_time < time()) {
                $model->customer->operation_time = time();
                $model->customer->last_operation_creator_id = $administrator->id;
                $model->customer->last_operation_creator_name = $administrator->name;
                $model->customer->save(false);
            }
        }
        $model->save(false);

    }
    public function getCustomer()
    {
        return $this->hasOne(CrmCustomer::className(),['id'=>'customer_id']);
    }
}
