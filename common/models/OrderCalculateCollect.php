<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_calculate_collect".
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
 * @property integer $total_order_price
 * @property integer $total_customer_count
 * @property integer $new_customer_count
 * @property integer $order_count
 * @property integer $refund_order_count
 * @property integer $cancel_order_count
 * @property string $order_expected_amount
 * @property string $correct_expected_amount
 * @property string $knot_expected_amount
 * @property string $correct_front_expected_amount
 * @property integer $expect_profit_time
 * @property CrmDepartment $department
 */
class OrderCalculateCollect extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_calculate_collect}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'administrator_id', 'department_id', 'year', 'month', 'total_order_price', 'total_customer_count', 'new_customer_count', 'order_count', 'refund_order_count', 'cancel_order_count','knot_expected_amount', 'expect_profit_time'], 'integer'],
            [['order_expected_amount', 'correct_expected_amount','correct_front_expected_amount'], 'number'],
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
            'total_order_price' => 'Total Order Price',
            'total_customer_count' => 'Total Customer Count',
            'new_customer_count' => 'New Customer Count',
            'order_count' => 'Order Count',
            'refund_order_count' => 'Refund Order Count',
            'cancel_order_count' => 'Cancel Order Count',
            'order_expected_amount' => 'Order Expected Amount',
            'correct_expected_amount' => 'Correct Expected Amount',
            'knot_expected_amount' => 'Knot Expected Amount',
            'expect_profit_time' => 'Expect Profit Time',
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(CrmDepartment::className(),['id' => 'department_id']);
    }

    public static function getByDepartment($department, $year, $month ,$company_id)
    {
        $table = OrderCalculateCollect::tableName();
        $params = [
            ':did' => $department->id,
            ':year' => $year,
            ':month' => $month,
        ];

        $sql = "SELECT 
                SUM(order_count) AS order_count,
                SUM(refund_order_count) AS refund_order_count,
                SUM(cancel_order_count) AS cancel_order_count,
                SUM(order_expected_amount) AS order_expected_amount,
                SUM(correct_expected_amount) AS correct_expected_amount,
                SUM(knot_expected_amount) AS knot_expected_amount,
                any_value(expect_profit_time) as expect_profit_time
              FROM {$table} 
              WHERE department_id=:did
              AND `year`=:year 
              AND `month`=:month
              and administrator_id=0";

        if($company_id)
        {
            $params[':company_id'] = $company_id;
            $sql .= ' AND `company_id`=:company_id';
        }

        /** @var array $model */
        $model = OrderCalculateCollect::findBySql($sql, $params)->asArray()->one();
        return $model;
    }

    public static function getByDepartmentData($department, $year, $month ,$company_id,$range_end_time)
    {
        $table = OrderCalculateCollect::tableName();
        $params = [
            ':path' => "{$department->path}-%",
            ':did' => $department->id,
//            ':year' => $year,
//            ':month' => $month,
            ':range_end_time' => $range_end_time,
            ':times'=>time(),
        ];

        $sql = "SELECT 
                SUM(order_count) AS order_count,
                SUM(refund_order_count) AS refund_order_count,
                SUM(cancel_order_count) AS cancel_order_count,
                SUM(order_expected_amount) AS order_expected_amount,
                SUM(correct_expected_amount) AS correct_expected_amount,
                SUM(knot_expected_amount) AS knot_expected_amount,
                SUM(total_customer_count) AS total_customer_count,
                SUM(total_order_price) AS total_order_price,
                SUM(new_customer_count) AS new_customer_count
              FROM {$table} 
              WHERE (department_id=:did OR department_path LIKE :path)
              AND `expect_profit_time`>=:range_end_time
              AND `expect_profit_time`<=:times
              and `administrator_id`=0";

        if($company_id)
        {
            $params[':company_id'] = $company_id;
            $sql .= ' AND `company_id`=:company_id';
        }

        /** @var array $model */
        $model = OrderCalculateCollect::findBySql($sql, $params)->asArray()->one();
        $model['department_name'] = $department->name;
        return $model;
    }

}
