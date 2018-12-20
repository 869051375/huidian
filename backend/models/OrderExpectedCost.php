<?php

namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\utils\BC;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "order_expected_cost".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $virtual_order_id
 * @property string $cost_name
 * @property string $cost_price
 * @property string $remark
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property integer $type
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 */
class OrderExpectedCost extends \yii\db\ActiveRecord
{
    const TYPE_ENTER = 0;      //录入
    const TYPE_CALCULATION = 1;//计算
    /**
     * @var Order
     */
    public $order;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_expected_cost}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $this->year = date('Y');
            $this->month = date('m');
            $this->day = date('d');
            $this->creator_id = $user->id;
            $this->creator_name = $user->name;
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
            [['order_id', 'virtual_order_id','type'], 'integer'],
            [['order_id', 'virtual_order_id','cost_name','cost_price'], 'required'],
            [['cost_price'], 'number'],
            [['remark'], 'string'],
            [['cost_name'], 'string', 'max' => 15],
            ['order_id', 'validateOrderId'],
            ['cost_price', 'validateCostPrice'],
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
            'virtual_order_id' => 'Virtual Order ID',
            'cost_name' => '成本名称',
            'cost_price' => '成本金额',
            'remark' => '备注',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'type' => 'Type',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function validateOrderId()
    {
       $this->order = Order::findOne($this->order_id);
       if(null == $this->order)
       {
           $this->addError('order_id','订单不存在！');
       }
    }

    public function validateCostPrice()
    {
        $lave = BC::sub($this->order->virtualOrder->getTotalExpectedCost(),$this->order->getTotalExpectedCost());
        if($this->cost_price > $lave)
        {
            $this->addError('order_id','对不起，当前无可分配预计成本。注：子订单的预计成本不可大于虚拟订单的未分配预计成本。');
        }
    }

    public function saveCost()
    {
        if(!$this->validate()) return false;
        $model = new OrderExpectedCost();
        $model->order_id = $this->order->id;
        $model->virtual_order_id = $this->order->virtual_order_id;
        $model->cost_name = $this->cost_name;
        $model->cost_price = $this->cost_price;
        $model->remark = $this->remark;
        if(floatval($this->order->total_cost) <= 0)
        {
            $this->order->total_cost = 1;
            $this->order->save(false);
        }
        $model->save(false);
        return $model;
    }

    public function getTypeName()
    {
        return $this->type ? '计算' : '录入';
    }

    public static function createExpectedCost($order_id,$type,$virtual_order_id,$cost_name,$cost_price)
    {
        $model = new OrderExpectedCost();
        $model->order_id = $order_id;
        $model->type = $type;
        $model->virtual_order_id = $virtual_order_id;
        $model->cost_name = $cost_name;
        $model->cost_price = $cost_price;
        $model->save(false);
    }

}
