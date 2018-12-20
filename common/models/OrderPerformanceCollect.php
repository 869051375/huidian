<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_performance_collect".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property string $title
 * @property integer $department_id
 * @property string $department_name
 * @property string $department_path
 * @property integer $year
 * @property integer $month
 * @property string $order_amount
 * @property string $correct_amount
 * @property string $ladder_amount
 * @property string $fix_point_amount
 * @property string $total_performance_amount
 * @property integer $performance_time
 *
 * @property CrmDepartment $department
 */
class OrderPerformanceCollect extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_performance_collect}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'administrator_id', 'department_id', 'year', 'month', 'performance_time'], 'integer'],
            [['order_amount', 'correct_amount', 'ladder_amount', 'fix_point_amount', 'total_performance_amount'], 'number'],
            [['administrator_name'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 6],
            [['department_name'], 'string', 'max' => 20],
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
            'company_id' => 'Company ID',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'title' => 'Title',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'department_path' => 'Department Path',
            'year' => 'Year',
            'month' => 'Month',
            'order_amount' => 'Order Amount',
            'correct_amount' => 'Correct Amount',
            'ladder_amount' => 'Ladder Amount',
            'fix_point_amount' => 'Fix Point Amount',
            'total_performance_amount' => 'Total Performance Amount',
            'performance_time' => 'Performance Time',
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(CrmDepartment::className(),['id' => 'department_id']);
    }

    public static function getByDepartment($department, $year, $month ,$company_id)
    {
        $table = OrderPerformanceCollect::tableName();
        $params = [
            ':did' => $department->id,
            ':year' => $year,
            ':month' => $month,
        ];

        $sql = "SELECT 
                SUM(total_performance_amount) AS total_performance_amount,
                SUM(order_amount) AS order_amount,
                SUM(correct_amount) AS correct_amount,
                SUM(ladder_amount) AS ladder_amount,
                SUM(fix_point_amount) AS fix_point_amount,
                any_value(performance_time) as performance_time
              FROM {$table} 
              WHERE `department_id`=:did
              AND `year`=:year 
              AND `month`=:month";

        if($company_id)
        {
            $params[':company_id'] = $company_id;
            $sql .= ' AND `company_id`=:company_id';
        }

        /** @var array $model */
        $model = OrderPerformanceCollect::findBySql($sql, $params)->asArray()->one();
        return $model;
    }

}
