<?php

namespace backend\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerPublic;
use Yii;

/**
 * This is the model class for table "crm_customer_combine".
 *
 * @property string $customer_id
 * @property string $business_subject_id
 * @property string $administrator_id
 * @property string $user_id
 * @property string $status
 * @property integer $level
 * @property string $company_id
 * @property string $department_id
 * @property string $created_at
 */
class CrmCustomerCombineShareForm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crm_customer_combine}}';
    }

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var BusinessSubject
     */
    public $business_subject;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['business_subject_id', 'administrator_id', 'user_id', 'status', 'level', 'company_id', 'department_id', 'created_at'], 'integer'],
            [['customer_id', 'administrator_id','company_id'], 'required','on' => 'share'],
            [['customer_id'],'string'],
//            ['customer_id', 'each', 'rule' => ['integer']],
            ['customer_id', 'validateCustomerIds'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_id' => 'Customer ID',
            'business_subject_id' => 'Business Subject ID',
            'administrator_id' => 'Administrator ID',
            'user_id' => 'User ID',
            'status' => 'Status',
            'level' => 'Level',
            'company_id' => 'Company ID',
            'department_id' => 'Department ID',
            'created_at' => 'Created At',
        ];
    }



    public function validateCustomerIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if($administrator->type == 1){
            $administrator_id = [$administrator->id];
        }else{
            if($administrator->isLeader() ||  $administrator -> isDepartmentManager()){
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
            }else{
                $administrator_id = [$administrator->id];
            }
        }
        $id = explode(',',$this->customer_id);

        foreach($id as $key => $val){
            $this->customer = CrmCustomer::find()->where(['in', 'id', $this->customer_id])->one();

            if(empty($this->customer))
            {
                $this->addError('customer_id', '客户不存在，请重新选择！');
            }

            if(!in_array($this->customer->administrator_id,$administrator_id))
            {
                $this->addError('customer_id', '您没有分享该客户的权限！');
            }

            $this->business_subject = BusinessSubject::find()->where(['customer_id' => $val])->one();

            $where = [];

            //判断当前客户是企业还是个人客户 查询对应的公海
            if (!$this->business_subject){
                $where = [0, 1];
            } else if ($this->business_subject->subject_type == 1 || $this->business_subject->subject_type === null) {
                $where = [0, 1];
            } else if ($this->business_subject->subject_type == 0) {
                $where = [0, 2];
            }

            /** @var Administrator $department_public_id */
            $department_public_id = Administrator::find()->where(['id'=>$this->administrator_id])->one();

            /** @var CustomerPublic $customerPublic */
            $customerPublic = CustomerPublic::find()
                ->alias('c')
                ->leftJoin(['d' => CustomerDepartmentPublic::tableName()], 'c.id=d.customer_public_id')
                ->where(['d.customer_department_id' => $department_public_id->department_id])
                ->andWhere(['in', 'c.customer_type', $where])
                ->one();

            if($customerPublic != null){
                if($customerPublic->status ==0){
                    return $this->addError('id', '对不起，您当前所在公海未启用，不能进行保护操作！');
                }
                $customer_query = CrmCustomer::find()->alias('c')
                    ->leftJoin(['b' => BusinessSubject::tableName()], 'c.id=b.customer_id')
                    ->leftJoin(['ccc' => CrmCustomerCombine::tableName()],'c.id=ccc.customer_id')
                    ->where(['ccc.administrator_id' => $this->administrator_id])
                    ->andWhere(['c.customer_public_id' => 0])
                    ->andWhere(['<>','c.administrator_id',0]);

                //判断当前公海是企业还是个人 或者是全部
                if ($customerPublic->customer_type == 1) {
                    $customer_query->andWhere("b.subject_type = 1 OR ISNULL(b.subject_type)");
                } else if ($customerPublic->customer_type == 2) {
                    $customer_query->andWhere(['b.subject_type' => 0]);
                }
                if ($customerPublic->big_customer_status == 1) {
                    //包含我新增的客户
                    $customer_count = $customer_query->count();
                } else {
                    //不包含我新增的客户
                    $customer_count = $customer_query->andWhere(['<>', 'c.creator_id', $this->administrator_id])->count();
                }
                if ($customer_count >= $customerPublic->big_customer) {
                    return $this->addError('id', '对不起，您的最大客户数已达到上限，不能提取！');
                }
            }

        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id', '人员信息不存在');
        }
        else if($this->administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('administrator_id', '该账号非业务人员');
        }
    }

}
