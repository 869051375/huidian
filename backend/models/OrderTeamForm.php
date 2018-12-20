<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderTeam;
use Yii;
use yii\base\Model;

class OrderTeamForm extends Model
{
    public $order_id;
    public $divide_rate;
    public $administrator_id;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Administrator
     */
    public $administrator;

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['administrator_id', 'order_id','divide_rate'], 'required'],
            [['divide_rate'], 'number'],
            ['order_id', 'validateOrderId'],
            ['divide_rate', 'validateDivideRate'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateOrderId()
    {
        $this->order = Order::findOne($this->order_id);
        if(null == $this->order)
        {
            $this->addError('salesman_id', '订单不存在。');
            return ;
        }
    }

    public function validateDivideRate()
    {
        if($this->divide_rate > $this->order->getDivideRate())
        {
            $this->addError('divide_rate', '业绩分配比例输入不能超过'.$this->order->getDivideRate().'%');
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id', '业务人员不存在。');
            return ;
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'administrator_id' => '业务人员',
            'divide_rate' => '业绩分配比例',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            $department_name = $this->administrator->department?$this->administrator->department->name:'';
            $orderTeam = new OrderTeam();
            $orderTeam->administrator_id = $this->administrator->id;
            $orderTeam->administrator_name = $this->administrator->name;
            $orderTeam->order_id = $this->order->id;
            $orderTeam->department_id = $this->administrator->department_id;
            $orderTeam->department_name = $department_name;
            $orderTeam->department_path = $this->administrator->department?$this->administrator->department->path:'';
            $orderTeam->divide_rate = $this->divide_rate;
            $orderTeam->save(false);
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            OrderRecord::create($this->order->id, '新增共享人', '新增订单共享人为'.$department_name.$this->administrator->name,$admin, 0, OrderRecord::INTERNAL_ACTIVE,0);
            $t->commit();
            return $orderTeam;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}