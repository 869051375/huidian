<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\NotAcceptableHttpException;

/**
 * This is the model class for table "{{%company}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $financial_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CrmDepartment $department
 * @property Administrator $administrator
 * @property Administrator $administratorByFinancial
 * @property CrmOpportunity[] $crmOpportunities
 * @property CustomerPublic $customerPublic
 *
 */
class Company extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            if($insert){
                $this->creator_id = $administrator->id;
                $this->creator_name = $administrator->name;
            }
            else
            {
                $this->updater_id = $administrator->id;
                $this->updater_name = $administrator->name;
            }
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
            [['creator_id', 'updater_id', 'created_at', 'updated_at', 'financial_id'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 6],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '公司名称',
            'financial_id' => '财务提醒人',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public function attributeHints()
    {
        return [
            'financial_id' => '此处设置为消息提醒功能配置(站内消息和邮件)',
        ];
    }
    public function beforeDelete()
    {
        if(parent::beforeDelete())
        {
            // 检查是否能删除
            if($this->hasDepartment())
            {
                throw new NotAcceptableHttpException('当前公司中存在部门，不可删除！');
            }
            else if($this->hasAdministrator())
            {
                throw new NotAcceptableHttpException('当前公司中存在管理人员，不可删除！');
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function countDepartment()
    {
        $count = $this->getDepartment()->count();
        return  $count > 0 ? $count : 0;
    }

    public function hasDepartment()
    {
        return $this->getDepartment()->count() > 0;
    }

    public function hasAdministrator()
    {
        return $this->getAdministrator()->count() > 0;
    }

    public function getDepartment()
    {
//        return CrmDepartment::find()->where(['company_id' => $this->id, 'status' => CrmDepartment::STATUS_ACTIVE]);
        return static::hasMany(CrmDepartment::className(), ['company_id' => 'id'])->where(['status' => CrmDepartment::STATUS_ACTIVE]);
    }

    public function getAdministrator()
    {
        return static::hasMany(Administrator::className(), ['company_id' => 'id']);
//        return Administrator::find()->where(['company_id' => $this->id, 'status' => Administrator::STATUS_ACTIVE]);
    }

    public function getAdministratorByFinancial()
    {
        return Administrator::find()->where(['id' => $this->financial_id, 'status' => Administrator::STATUS_ACTIVE])->one();
    }

    public function getCrmOpportunities()
    {
        return static::hasMany(CrmOpportunity::className(), ['company_id' => 'id']);
    }

    public function getCustomerPublic()
    {
        return static::hasOne(CustomerPublic::className(), ['company_id' => 'id']);
    }

    public static function getCompanyName()
    {
        $company = Company::find()->select('id,name')->asArray()->all();
        $data = [];
        if($company)
        {
            foreach ($company as $item)
            {
                $data[$item['id']] = $item['name'];
            }
        }
        return $data;
    }
}
