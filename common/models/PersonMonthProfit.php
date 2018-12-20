<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%person_month_profit}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property string $title
 * @property integer $top_department_id
 * @property string $top_department_name
 * @property string $department_path
 * @property integer $department_id
 * @property string $department_name
 * @property integer $year
 * @property integer $month
 * @property string $order_amount
 * @property integer $order_count
 * @property integer $customer_count
 * @property integer $new_customer_count
 * @property string $refund_amount
 * @property string $receivable
 * @property string $total_cost
 * @property string $reward_proportion
 * @property string $expected_profit
 * @property string $already_payment
 * @property string $correct_front_expected_amount
 */
class PersonMonthProfit extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%person_month_profit}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['administrator_id', 'company_id','top_department_id', 'department_id', 'year', 'month', 'order_count', 'customer_count', 'new_customer_count'], 'integer'],
            [['order_amount', 'refund_amount', 'receivable', 'expected_profit', 'total_cost', 'reward_proportion','already_payment','correct_front_expected_amount'], 'number'],
            [['administrator_name', 'title'], 'string', 'max' => 10],
            [['department_path'], 'string', 'max' => 32],
            [['top_department_name','department_name'], 'string', 'max' => 20],
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
            'department_path' => 'Top Department Path',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'year' => 'Year',
            'month' => 'Month',
            'order_amount' => 'Order Amount',
            'order_count' => 'Order Count',
            'customer_count' => 'Customer Count',
            'new_customer_count' => 'New Customer Count',
            'refund_amount' => 'Refund Amount',
            'receivable' => 'Receivable',
            'expected_profit' => 'Expected Profit',
        ];
    }

    /**
     * 获得业务员某个月的预计利润对象
     * @param $administrator_id
     * @param $year
     * @param $month
     * @return PersonMonthProfit|null
     */
    public static function getProfit($administrator_id, $year, $month)
    {
        /** @var PersonMonthProfit $model */
        $model = self::find()->where(['administrator_id' => $administrator_id, 'year' => $year, 'month' => $month])->one();
        return $model;
    }

    /**
     * @param CrmDepartment $department
     * @param int $year
     * @param int $month
     * @param int $company_id
     * @return array
     */
    public static function getByDepartment($department, $year, $month ,$company_id)
    {
        $table = PersonMonthProfit::tableName();

        $params = [
            ':path' => "{$department->path}-%",
            ':did' => $department->id,
            ':year' => $year,
            ':month' => $month,
            //':company_id' => $company_id,
        ];

        $sql = "SELECT 
                SUM(order_amount) AS order_amount,
                SUM(order_count) AS order_count,
                SUM(customer_count) AS customer_count,
                SUM(new_customer_count) AS new_customer_count,
                SUM(refund_amount) AS refund_amount,
                SUM(receivable) AS receivable,
                SUM(total_cost) AS total_cost,
                SUM(expected_profit) AS expected_profit,
                SUM(already_payment) AS already_payment,
                SUM(correct_front_expected_amount) AS correct_front_expected_amount
              FROM {$table} as p
              WHERE (department_id=:did OR department_path LIKE :path)
              AND `year`=:year 
              AND `month`=:month
              and administrator_id=0";

        if($company_id)
        {
            $params[':company_id'] = $company_id;
            $sql .= ' AND `company_id`=:company_id';
        }

        /** @var array $model */
        $model = PersonMonthProfit::findBySql($sql, $params)->asArray()->one();
        $model['department_name'] = $department->name;
        return $model;
    }

    public function getYearMonth()
    {
        $month = $this->month < 9 ? '0'.$this->month : $this->month;
        return $this->year.$month;
    }
}
