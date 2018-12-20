<?php

namespace common\models;

/**
 * This is the model class for table "month_performance_rank".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property integer $top_department_id
 * @property string $top_department_name
 * @property integer $department_id
 * @property string $department_name
 * @property string $department_path
 * @property integer $year
 * @property integer $month
 * @property string $calculated_performance
 * @property string $performance_reward
 *
 * @property Administrator $administrator
 */
class MonthPerformanceRank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%month_performance_rank}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id','company_id', 'top_department_id', 'department_id', 'year', 'month'], 'integer'],
            [['calculated_performance', 'performance_reward'], 'number'],
            [['administrator_name'], 'string', 'max' => 10],
            [[ 'top_department_name', 'department_name'], 'string', 'max' => 20],
            [['department_path'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company Id',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'top_department_id' => 'Top Department ID',
            'top_department_name' => 'Top Department Name',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'department_path' => 'Department Path',
            'year' => 'Year',
            'month' => 'Month',
            'calculated_performance' => 'Calculated Performance',
            'performance_reward' => 'Performance Reward',
        ];
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id' => 'administrator_id']);
    }

    /**
     *获取最后一个月的数据
     */
    public static function getLastMonthRank()
    {
        /** @var MonthPerformanceRank $model */
        $model = self::find()->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])->limit('1')->one();
        return $model ? $model : null;
    }

    /**
     * 获得最后一个结算完成的记录
     * @return MonthPerformanceRank|null
     */
    public static function getLastFinishRecord()
    {
        /** @var MonthPerformanceRank|null $model */
        $model = static::find()->limit(1)->one();
        return $model;
    }

    /**
     * @param CrmDepartment $department
     * @param int $year
     * @param int $month
     * @param int $company_id
     * @return array
     */
    public static function getByDepartment($department, $year, $month, $company_id)
    {
        $table = MonthPerformanceRank::tableName();

        $params = [
            ':path' => "{$department->path}-%",
            ':did' => $department->id,
            ':year' => $year,
            ':month' => $month,
            //':company_id' => $company_id,
        ];

        $sql = "SELECT 
                SUM(calculated_performance) AS calculated_performance,
                SUM(performance_reward) AS performance_reward
              FROM {$table} 
              WHERE (department_id=:did OR department_path LIKE :path)
              AND `year`=:year 
              AND `month`=:month";

        if($company_id)
        {
            $params[':company_id'] = $company_id;
            $sql .= ' AND `company_id`=:company_id';
        }
        /** @var array $model */
        $model = MonthPerformanceRank::findBySql($sql, $params)->asArray()->one();
        $model['department_name'] = $department->name;
        return $model;
    }


}
