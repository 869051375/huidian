<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use Yii;
use yii\base\Model;

class CustomerLogForm extends Model
{
    public $customer_id;
    public $remark;

    /**
     * @var CrmCustomer
     */
    public $customer;


    public function rules()
    {
        return [
            [['customer_id', 'remark'], 'required'],
            [['customer_id', 'remark'], 'trim'],
            ['customer_id', 'validateCustomerId'],
            [['remark'], 'string', 'max' => 200],
        ];
    }

    public function validateCustomerId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer = CrmCustomer::findOne($this->customer_id);
        if(null == $this->customer)
        {
            $this->addError('customer_id', '客户不存在');
        }
        else if(
            !$this->customer->isPrincipal($administrator) &&
            !$this->customer->isCombine($administrator) &&
            !$this->customer->isSubFor($administrator))
        {
            $this->addError('customer_id', '您没有对该客户操作的权限');
        }
    }

    public function add()
    {
        if(!$this->validate())
        {
            return false;
        }

        $t = Yii::$app->db->beginTransaction();
        try
        {
            CrmCustomerLog::add('添加跟进记录:'.$this->remark,$this->customer->id);

            /** @var Administrator $admin */
            $admin = \Yii::$app->user->identity;
            $this->customer->last_record = time();
            $this->customer->last_record_creator_id = $admin->id;
            $this->customer->last_record_creator_name = $admin->name;
            //判断最后操作时间
            if($this->customer->operation_time < time())
            {
                $this->customer->operation_time = time();
                $this->customer->last_operation_creator_id = $admin->id;
                $this->customer->last_operation_creator_name = $admin->name;
            }
            $this->customer->save(false);

            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
//            throw $e;
            return false;
        }
    }

    public function attributeLabels()
    {
        return [
            'remark' => '跟进记录',
            'customer_id' => '客户id'
        ];
    }
}