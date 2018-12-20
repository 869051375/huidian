<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Order;
use common\models\OrderRecord;
use common\models\OrderTeam;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class OrderReplaceTeamForm extends Model
{
    public $order_id;
    public $company_id;

    public $salesman_admin = [];
    public $admin_id;
    public $rate;
    /**
     * @var Order[]
     */
    public $orders;

    /**
     * @var Administrator
     */
    public $administrator;

    public $order_ids;

    public function rules()
    {
        return [
            [['order_id'], 'required','message' => '请至少选择一个子订单！'],
            [['salesman_admin'], 'required'],
            ['order_id', 'validateOrderId'],
            [['salesman_admin'], 'validateAdmin'],
        ];
    }

    public function validateAdmin()
    {
        if(empty($this->salesman_admin))
        {
            $this->addError('salesman_id', '请至少添加一名业务员。');
        }
        $this->salesman_admin = array_values($this->salesman_admin);
        $rate = 0;
        $a_id = 0;
        foreach($this->salesman_admin as $i => $item)
        {
            if($a_id != $item['admin_id'])
            {
                $a_id = $item['admin_id'];
            }
            else
            {
                $this->addError('admin_id', '共享业务员不能重复添加');
            }
            if(null == $item['rate'])
            {
                $this->addError('rate', '业绩分配比例不能为空');
            }
            if(!preg_match("/^\d*$/",$item['rate']))
            {
                $this->addError('rate', '业绩分配比例必须是整数');
            }
            if($this->order_ids && in_array($item['admin_id'],$this->order_ids))
            {
                $this->addError('admin_id', '子订单负责业务员不能做共享业务员');
            }
            $admin = Administrator::findOne($item['admin_id']);
            if(null == $admin)
            {
                $this->addError('admin_id', '共享业务员找不到');
            }
            $rate += $item['rate'];
        }
        if($rate > 100)
        {
            $this->addError('rate', '业绩分配比例输入不能超过100');
        }

    }

    public function validateOrderId()
    {
        $order_ids = explode(',',rtrim($this->order_id,','));
        $orders = Order::find()->where(['in','id',$order_ids])->asArray()->all();
        $this->order_ids = ArrayHelper::getColumn($orders,'salesman_aid');
        $this->orders = Order::find()->where(['in','id',$order_ids])->all();
        if(count($this->order_ids) != count($this->orders))
        {
            $this->addError('order_id','多选中有无效的订单id存在');
        }
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单',
            'salesman_admin' => '共享业务员',
        ];
    }

    public function save()
    {
        if(!$this->validate()) return false;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            foreach($this->orders as $order)
            {
                OrderTeam::deleteAll(['order_id' => $order->id]);
                foreach($this->salesman_admin as $item)
                {
                    /** @var Administrator $admin */
                    $admin = Administrator::findOne($item['admin_id']);
                    $orderTeam = new OrderTeam();
                    $orderTeam->administrator_id = $admin->id;
                    $orderTeam->administrator_name = $admin->name;
                    $orderTeam->order_id = $order->id;
                    $orderTeam->department_id = $admin->department_id;
                    $orderTeam->department_name = $admin->department ? $admin->department->name : '';
                    $orderTeam->department_path = $admin->department ? $admin->department->path : '';
                    $orderTeam->divide_rate = $item['rate'];
                    $orderTeam->save(false);
                    $company_name = $admin->company ? $admin->company->name.'的' : '';
                    OrderRecord::create($order->id, '批量替换订单共享人', '订单共享人更新为：'.$company_name.$admin->name,$administrator, 0, OrderRecord::INTERNAL_ACTIVE,0);
                }
            }
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}