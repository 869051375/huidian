<?php

namespace common\models;
use Yii;

/**
 * This is the model class for table "order_team".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $administrator_id
 * @property string $administrator_name
 * @property integer $department_id
 * @property string $department_name
 * @property string $department_path
 * @property string $divide_rate
 *
 * @property Order $order
 * @property Administrator $administrator
 */
class OrderTeam extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_team}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'administrator_id', 'department_id'], 'integer'],
            [['divide_rate'], 'number'],
            [['administrator_name'], 'string', 'max' => 10],
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
            'order_id' => 'Order ID',
            'administrator_id' => '业务人员',
            'administrator_name' => 'Administrator Name',
            'department_id' => 'Department ID',
            'department_name' => 'Department Name',
            'department_path' => 'Department Path',
            'divide_rate' => 'Divide Rate',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(),['id' => 'order_id']);
    }

    public function getAdministrator()
    {
        return $this->hasOne(Administrator::className(),['id' => 'administrator_id']);
    }

}
