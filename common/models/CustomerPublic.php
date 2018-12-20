<?php
namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_public}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $administrator_name
 * @property string $administrator_department
 * @property string $administrator_title
 * @property integer $status
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $move_time
 * @property integer $release_time
 * @property integer $extract_number_limit
 * @property integer $protect_number_limit
 * @property integer $confirm_timeout_time
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $customer_type
 * @property integer $opportunity_time
 * @property integer $big_customer
 * @property integer $big_customer_status
 * @property integer $administrator_id
 *
 * @property Company $company
 * @property CrmCustomer[] $customers
 * @property CustomerDepartmentPublic[] $customerDepartmentPublic
 */
class CustomerPublic extends ActiveRecord
{
    /**
     * @return array
     * 添加时间
     * 修改时间
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_public}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'extract_number_limit', 'protect_number_limit', 'confirm_timeout_time', 'created_at', 'updated_at','customer_type','big_customer','big_customer_status','administrator_id'], 'integer'],
            [['name'], 'string', 'max' => 15],
            [['administrator_name','administrator_department','administrator_title','department_id'], 'string'],
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'required', 'message' => '请必须输入客户公海名称，长度在15个文字之内！'],
            [['customer_type'], 'required', 'message' => '请必须选择使用对象！'],
//            [['company_id'], 'required', 'message' => '请必须选择客户公海的所属公司！'],
            [['department_id'], 'required', 'message' => '请必须选择客户公海的所属部门！'],
            [['move_time','department_id','release_time','opportunity_time','protect_number_limit','extract_number_limit','big_customer','big_customer_status'], 'required'],
            [['move_time','opportunity_time'], 'number'],
            [['release_time'], 'number'],
            [['confirm_timeout_time'], 'integer'],
            [['extract_number_limit'], 'integer'],
            [['protect_number_limit'], 'integer'],
            [['company_id'], 'validateCompanyId', 'on'=> 'insert'],
            [['release_time'], 'validateReleaseTime', 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '客户公海名称',
            'company_id' => '关联公司',
            'department_id' => 'Department ID',
            'move_time' => '执行规则',
            'release_time' => '已提取客户主动释放时间限制',
            'extract_number_limit' => '提取数量上限',
            'protect_number_limit' => '最大保护数量',
            'confirm_timeout_time' => '待确认客户超时限制',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function validateCompanyId()
    {
        /** @var CustomerPublic $opportunityPublic */
        $customerPublic = CustomerPublic::find()->where(['company_id' => $this->company_id])->limit(1)->one();
        if(null != $customerPublic)
        {
            $this->addError('company_id','该部门已经存在客户公海，无法添加。');
        }
    }

    public function validateReleaseTime()
    {
        if($this->release_time > 0)
        {
            if($this->move_time <= $this->release_time)
            {
                $this->addError('release_time','已释放客户提取时间必须要小于执行规则工作日。');
            }
        }
    }

    public function getCompany()
    {
        return static::hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getCustomers()
    {
        return static::hasMany(CrmCustomer::className(), ['customer_public_id' => 'id']);
    }

    public function getCustomerDepartmentPublic()
    {
        return static::hasOne(CustomerDepartmentPublic::className(), ['customer_public_id' => 'id']);
    }


    public function defaultValues()
    {
        if(empty($this->extract_number_limit))
        {
            $this->extract_number_limit = 0;
        }
        if(empty($this->protect_number_limit))
        {
            $this->protect_number_limit = 0;
        }
        if(empty($this->confirm_timeout_time))
        {
            $this->confirm_timeout_time = 0;
        }
        if(empty($this->release_time))
        {
            $this->release_time = 0;
        }
    }
    public static function getAll(){
        $arr = CustomerPublic::find()->all();
        /** @var CrmDataSynchronization $data */
        $data = CrmDataSynchronization::find()->one();
        if(!empty($data)){
            /** @var CustomerPublic $public */
            $public = CustomerPublic::find()->where(['id'=>$data->customer_public_id])->one();
            $array = [];
            if($public){
                $array = [$data->customer_public_id => $public->name];
            }
        }else{
            $array = [
                0 => '请选择线索公海'
            ];
        }

        foreach ($arr as $item){
            $array[$item['id']] = $item['name'];
        }

        return $array;
    }


}
