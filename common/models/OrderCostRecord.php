<?php

namespace common\models;

use common\utils\BC;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "order_cost_record".
 *
 * @property integer $id
 * @property integer $virtual_order_id
 * @property integer $order_id
 * @property string $cost_name
 * @property string $cost_price
 * @property string $remark
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property integer $type
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class OrderCostRecord extends \yii\db\ActiveRecord
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
        return '{{%order_cost_record}}';
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
            //业绩记录
            PerformanceRecord::createRecord($this->virtual_order_id,$this->order_id,0,0,$this->cost_price,0);
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
            [['virtual_order_id', 'order_id', 'year', 'month', 'day', 'type', 'creator_id', 'created_at'], 'integer'],
            [['cost_name','cost_price','virtual_order_id','order_id'], 'required'],
            [['cost_price'], 'number'],
            ['cost_price', 'validateOrderId'],
            [['remark'], 'string', 'max' => 10],
            [['cost_name'], 'string', 'max' => 15],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('order_id','找不到指定的订单');
        }
        else
        {
            $lave = BC::sub($this->order->virtualOrder->getTotalCost(),$this->order->virtualOrder->getOrderCost());
            if($this->cost_price > $lave)
            {
                $this->addError('order_id','对不起，当前无可分配预计成本。注：子订单的成本不可大于虚拟订单的未分配预计成本。');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'virtual_order_id' => 'Virtual Order ID',
            'order_id' => 'Order ID',
            'cost_name' => '成本名称',
            'cost_price' => '成本价格',
            'remark' => '备注',
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'type' => 'Type',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public static function createCost($order_id,$type,$virtual_order_id,$cost_name,$cost_price)
    {
        $model = new OrderCostRecord();
        $model->virtual_order_id = $virtual_order_id;
        $model->order_id = $order_id;
        $model->cost_name = $cost_name;
        $model->cost_price = $cost_price;
        $model->type = $type;
        $model->save(false);
    }

    public function getTypeName()
    {
        return $this->type ? '计算' : '录入';
    }
}
