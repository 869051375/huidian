<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%opportunity_public}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $company_id
 * @property integer $department_id
 * @property integer $move_time
 * @property integer $extract_number_limit
 * @property integer $protect_number_limit
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CrmDepartment $department
 * @property CrmOpportunity[] $opportunities
 * @property Company $company
 * @property CrmDepartment $companyDepartment
 */
class OpportunityPublic extends \yii\db\ActiveRecord
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
        return '{{%opportunity_public}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'department_id', 'move_time', 'extract_number_limit', 'protect_number_limit', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 15],
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'required', 'message' => '请必须输入商机公海名称，长度在15个文字之内！'],
            [['company_id'], 'required', 'message' => '请必须选择商机公海的关联公司！'],
            [['department_id'], 'required', 'message' => '请必须选择商机公海的所属产品线！'],
            [['move_time'], 'required'],
            [['move_time'], 'integer', 'min' => 1, 'max' => 366],
            [['extract_number_limit'], 'integer', 'max' => 99999],
            [['protect_number_limit'], 'integer', 'max' => 99999],
            [['department_id'], 'validateDepartmentId','skipOnEmpty' => false, 'skipOnError' => false, 'on'=> 'insert'],
            [['company_id'], 'validateCompanyId', 'skipOnEmpty' => false, 'skipOnError' => false, 'on'=> 'insert'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '商机公海名称',
            'company_id' => '关联公司',
            'department_id' => '关联部门',
            'move_time' => '执行规则',
            'extract_number_limit' => '提取数量上限',
            'protect_number_limit' => '最大保护数量',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function validateCompanyId()
    {
        if($this->company_id <=0)
        {
            $this->addError('company_id','请必须选择商机公海的关联公司！');
        }
    }
    public function validateDepartmentId()
    {
        if($this->department_id <=0)
        {
            $this->addError('department_id','请必须选择商机公海的所属产品线！');
        }
        /** @var OpportunityPublic $opportunityPublic */
        $opportunityPublic = OpportunityPublic::find()->where(['department_id' => $this->department_id])->limit(1)->one();
        if(null != $opportunityPublic)
        {
            $this->addError('department_id','该部门已经存在商机公海，无法添加！');
        }
    }

    public function getDepartment()
    {
        return static::hasOne(CrmDepartment::className(), ['id' => 'department_id'])->andWhere(['status' =>CrmDepartment::STATUS_ACTIVE]);
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getCompanyDepartment()
    {
        return CrmDepartment::find()
            ->where(['id' => $this->department_id, 'company_id' => $this->company_id])->one();
    }

    public function getOpportunities()
    {
        return static::hasMany(CrmOpportunity::className(), ['opportunity_public_id' => 'id']);
    }

    public function defaultValues()
    {
        if(empty($this->extract_number_limit))
        {
            $this->extract_number_limit = 0 ;
        }
        if(empty($this->protect_number_limit))
        {
            $this->protect_number_limit = 0;
        }
    }
}
