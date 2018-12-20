<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\Order;
use common\models\OrderRecord;
use Yii;
use yii\base\Model;

class OrderVisitAllocationForm extends Model
{
    public $order_id;
    public $company_id;
    public $administrator_id;
    public $remark;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @var Company
     */
    public $company;

    public function rules()
    {
        return [
            [['order_id', 'company_id', 'administrator_id','remark'], 'required'],
            [['company_id'],'validateCompanyId'],
            [['administrator_id'], 'validateAdministratorId'],
            [['order_id'], 'validateOrderId'],
            [['remark'], 'string','max' => 50],
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

    public function validateCompanyId()
    {
        $this->company = Company::findOne($this->company_id);
        if($this->company == null)
        {
            $this->addError('company_id','所属公司不存在！');
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::find()->where(['id' => $this->administrator_id,'company_id' => $this->company->id])->limit(1)->one();
        if($this->administrator == null)
        {
            $this->addError('administrator_id','指定的业务员不存在！');
        }
    }

    public function attributeLabels()
    {
        return [
            'company_id' => '所属公司',
            'administrator_id' => '指定业务员',
            'remark' => '回访备注',
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function allocation()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            $this->order->company_id = $this->administrator->company_id;
            $this->order->salesman_department_id = $this->administrator->department_id;
            $this->order->salesman_aid = $this->administrator->id;
            $this->order->salesman_name = $this->administrator->name;
            $this->order->save(false);
            OrderRecord::create($this->order->id, '订单回访分配', '订单分配给'.$this->company->name.'的'.$this->administrator->name.'，'.'备注为：'.$this->remark,$admin, 0, OrderRecord::INTERNAL_ACTIVE,0);
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