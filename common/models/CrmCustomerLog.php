<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "crm_customer_log".
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $opportunity_id
 * @property integer $subject_id
 * @property integer $type
 * @property string $remark
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 *
 * @property Administrator $administrator
 * @property CrmCustomer $customer
 */
class CrmCustomerLog extends \yii\db\ActiveRecord
{
    const TYPE_CUSTOMER_RECORD = 0;//跟进记录
    const TYPE_CUSTOMER_PERSON = 1;//自然人记录
    const TYPE_CUSTOMER_SUBJECT = 2;//企业主体记录
    const TYPE_CUSTOMER_DO_RECORD = 3;//客户操作记录
    const TYPE_CUSTOMER_OPPORTUNITY = 4;//商机操作记录

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_customer_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'opportunity_id','subject_id', 'creator_id', 'created_at', 'type'], 'integer'],
            [['remark'], 'string'],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @param $remark
     * @param $customer_id
     * @param int $opportunity_id
     * @param null $administrator
     * @param int $type
     * @param int $subject_id
     */
    public static function add($remark, $customer_id, $opportunity_id = 0, $administrator = null, $type = 0,$subject_id = 0)
    {
        $model = new CrmCustomerLog();
        $model->customer_id = $customer_id;
        $model->remark = $remark;
        $model->opportunity_id = $opportunity_id;
        $model->subject_id = $subject_id;
        $model->type = $type;
        //注意：$administrator为system时，系统操作
        if($administrator == 'system')
        {
            $model->creator_id = 0;
            $model->creator_name = '系统';
        }
        else
        {
            /** @var Administrator $administrator */
            $administrator = null == $administrator ? Yii::$app->user->identity : $administrator;
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
        }
        $model->created_at = time();
        //注意：$administrator为system时为系统甩出操作，客户无需执行一下流程
        if($type == CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD && $administrator !== 'system')
        {
            //客户操作
//            if($model->customer->operation_time < time())
//            {
                $model->customer->operation_time = time();
                $model->customer->last_operation_creator_id = $administrator->id;
                $model->customer->last_operation_creator_name = $administrator->name;
                $model->customer->save(false);
//            }
        }
        $model->save(false);
    }

    public static function logAdd($remark, $customer_id, $opportunity_id = 0, $administrator = null, $type = 0,$subject_id = 0)
    {
        $model = new CrmCustomerLog();
        $model->customer_id = $customer_id;
        $model->remark = $remark;
        $model->opportunity_id = $opportunity_id;
        $model->subject_id = $subject_id;
        $model->type = $type;
        //注意：$administrator为system时，系统操作
        if($administrator == 'system')
        {
            $model->creator_id = 0;
            $model->creator_name = '系统';
        }
        else
        {
            /** @var Administrator $administrator */
            $administrator = null == $administrator ? Yii::$app->user->identity : $administrator;
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
        }
        $model->created_at = time();

        $model->save(false);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'opportunity_id' => 'Opportunity ID',
            'type' => 'Type',
            'remark' => '添加跟进记录',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id'=>'creator_id']);
    }

    public function getCustomer()
    {
        return $this->hasOne(CrmCustomer::className(),['id'=>'customer_id']);
    }
}
