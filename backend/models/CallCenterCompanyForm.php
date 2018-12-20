<?php

namespace backend\models;

use common\models\CallCenter;
use common\models\CallCenterAssignCompany;
use common\models\Company;
use yii\base\Model;

/**
 * Class CallCenterCompanyForm
 * @package backend\models
 *
 * @property Company $company
 */
class CallCenterCompanyForm extends Model
{
    public $company_id;
    public $call_center_id;

    public function rules()
    {
        return [
            [['company_id', 'call_center_id'], 'required'],
            [['company_id'], 'validateCompanyId'],
        ];
    }

    public function validateCompanyId()
    {
        $model = CallCenter::findOne($this->call_center_id);
        if($this->company_id <= 0)
        {
            $this->addError('company_id', '关联公司能为空！');
        }
        if(null == $model)
        {
            $this->addError('company_id', '您的操作有误！');
        }
        $data = CallCenterAssignCompany::find()
            ->andWhere(['company_id' => $this->company_id])
            ->andWhere(['call_center_id' => $this->call_center_id])
            ->one();
        if(!empty($data))
        {
            $this->addError('company_id', '本公司已被添加过，请检查！');
        }
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $model = new CallCenterAssignCompany();
        $model->company_id = $this->company_id;
        $model->call_center_id = $this->call_center_id;
        $model->save(false);
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => '添加公司',
        ];
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }
}