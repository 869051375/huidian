<?php

namespace backend\models;

use common\models\CrmDepartment;
use common\models\MonthPerformanceRank;
use common\models\MonthProfitRecord;
use yii\base\Model;

/**
 * Class RankSearch
 * @package backend\models
 * @property CrmDepartment $department
 */

class RankSearch extends Model
{
    public $id;
    public $department_id;   //部门

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['department_id','id'],'integer'],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id' => '选择月份',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param $params
     * @param $status
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function search($params,$status)
    {
        $this->load($params);
        if(empty($this->id))
        {
            $record = MonthProfitRecord::getLastFinishRecord();
        }
        else
        {
            $record = MonthProfitRecord::findOne($this->id);
            if(null == $record || $record->status != MonthProfitRecord::STATUS_SETTLEMENT_FINISH)
            {
                $record = MonthProfitRecord::getLastFinishRecord();
            }
        }

        if(null == $record)
        {
            $cond = ['year' => '0', 'month' => '0'];
        }
        else
        {
            $cond = ['year' => $record->year, 'month' => $record->month];
        }

        $query = MonthPerformanceRank::find()->where($cond);
        if($status)
        {
            $department = CrmDepartment::findOne($this->department_id);
            if($department)
            {
                $query->andWhere(['or', ['department_id' => $this->department_id], 'department_path like :path'], [':path' => $department->path.'-%']);
            }
        }
        $data =  $query->orderBy(['performance_reward'=>SORT_DESC])->all();
        return $data;
    }

    public function getDepartment()
    {
        return CrmDepartment::find()->where(['id' => $this->department_id])->one();
    }
}
