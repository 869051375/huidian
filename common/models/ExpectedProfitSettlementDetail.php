<?php

namespace common\models;

/**
 * This is the model class for table "{{%expected_profit_settlement_detail}}".
 *
 * @property integer $id
 * @property integer $year
 * @property integer $month
 * @property integer $order_id
 * @property integer $virtual_order_id
 * @property integer $sn
 * @property string  $v_sn
 * @property integer $type
 * @property integer $company_id
 * @property string  $company_name
 * @property string  $title
 * @property string  $remark
 * @property integer $administrator_id
 * @property string  $administrator_name
 * @property integer $department_id
 * @property string  $department_name
 * @property string  $department_path
 * @property string  $expected_profit
 * @property integer $created_at
 * @property integer $creator_id
 * @property string  $creator_name
 *
 * @property Order  $order
 * @property Administrator  $administrator
 */
class ExpectedProfitSettlementDetail extends \yii\db\ActiveRecord
{
    const TYPE_GENERAL = 0;
    const TYPE_CORRECT = 1;
    const TYPE_KNOT = 2;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expected_profit_settlement_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'month', 'order_id', 'administrator_id', 'department_id', 'creator_id', 'created_at'], 'integer'],
            [['expected_profit'], 'number'],
            [['administrator_name','creator_name'], 'string', 'max' => 10],
            [['department_name'], 'string', 'max' => 20],
            [['title'], 'string', 'max' => 10],
            [['remark'], 'string', 'max' => 30],
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
            'year' => 'Year',
            'month' => 'Month',
            'order_id' => 'Order ID',
            'title' => 'title',
            'remark' => 'remark',
            'administrator_id' => 'Administrator ID',
            'administrator_name' => 'Administrator Name',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'department_path' => 'Department Path',
            'expected_profit' => 'Expected Profit',
            'created_at' => 'Created At',
            'creator_id' => 'Creator Id',
            'creator_name' => 'Creator Name',
        ];
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id' => 'administrator_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id' => 'order_id']);
    }

    public function getTypeName()
    {
        $type = [
          self::TYPE_GENERAL => '常规计算',
          self::TYPE_CORRECT => '更正计算',
          self::TYPE_KNOT => '结转计算'
        ];
        return $type[$this->type];
    }
}
