<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "customer_follow_record".
 *
 * @property integer $id
 * @property integer $subject_id
 * @property integer $customer_id
 * @property integer $opportunity_id
 * @property string $remark
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class CustomerFollowRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_follow_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['subject_id', 'customer_id', 'opportunity_id', 'creator_id', 'created_at'], 'integer'],
            [['remark'], 'string'],
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
        ];
    }


    /**
    跟进记录表 存放操作
    $data['customer_id'] 客户id
     *  $data['remark'] 备注说明  例如 创建客户：测试客户0519031
    **/
    public  static function  Operate($data){
        $info=Yii::$app->user->identity;
        $creator_name=$info->name;
        $creator_id=$info->id;
        $subject_id=$row=(new \yii\db\Query())
            ->from(['business_subject'])
            ->where(['customer_id'=>$data['customer_id']])
            ->select(['id'])
            ->one()['id'];
        if($subject_id==null){
            $subject_id=0;
        }
        $opp_id=$row=(new \yii\db\Query())
            ->from(['crm_opportunity'])
            ->where(['customer_id'=>$data['customer_id']])
            ->select(['id'])
            ->one()['id'];
        if($opp_id==null){
            $opp_id=0;
        }
        OperateCustomerRecord::find()->createCommand()->insert(CustomerFollowRecord::tableName(),[
            'customer_id'=>$data['customer_id'],
            'subject_id'=>$subject_id,
            'opportunity_id'=>$opp_id,
            'remark'=>$data['remark'],
            'creator_id'=>$creator_id,
            'creator_name'=>$creator_name,
            'created_at'=>time()
        ])->execute();
    }
}
