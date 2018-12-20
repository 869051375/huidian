<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CustomerTag;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class CustomerTagNewForm extends Model
{
    public $customer_id;
    public $tag_id;
    public $company_id;
    public $ids;

    /**
     * @var CustomerTag
     */
    public $customer_tag;

    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var CrmCustomer[]
     */
    public $customers = [];

    public function formName()
    {
        return '';
    }

    public function rules()
    {
        return [
            [['tag_id'], 'required', 'message' => '请选择标签', 'on' => ['add']],
            [['tag_id'], 'integer'],
            [['customer_id'],'string'],
            ['customer_id', 'validateCustomerIds', 'on' => ['add']],
            ['customer_id', 'validateCancelIds', 'on' => ['cancel']],
        ];
    }

    public function validateCustomerIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if($administrator->type == 1){
            $administrator_id = [$administrator->id];
        }else{
            if($administrator->isLeader() ||  $administrator -> isDepartmentManager()){
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
            }else{
                $administrator_id = [$administrator->id];
            }
        }
        $this->customer_id = explode(',',$this->customer_id);
        $this->customers = CrmCustomer::find()->where(['in', 'id', $this->customer_id])->all();
        if(empty($this->tag_id))
        {
            $this->addError('tag_id', '请选择标签！');
        }
        if(empty($this->customers))
        {
            $this->addError('ids', '请选择客户！');
        }
        foreach($this->customers as $customer)
        {
            if(!in_array($customer->administrator_id,$administrator_id)){
                $this->addError('customer_id', '标签应用失败，您不是客户负责人！');
            }
        }
    }

    public function validateCancelIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        if($administrator->type == 1){
            $administrator_id = [$administrator->id];
        }else{
            if($administrator->isLeader() ||  $administrator -> isDepartmentManager()){
                $administrator_id = $administrator->getTreeAdministratorId(true,true);
            }else{
                $administrator_id = [$administrator->id];
            }
        }
        $this->customer_id = explode(',',$this->customer_id);
        $this->customers = CrmCustomer::find()->where(['in', 'id', $this->customer_id])->all();
        if(empty($this->customers))
        {
            $this->addError('customer_id', '请选择客户！');
        }
        foreach($this->customers as $customer)
        {
            if(!in_array($customer->administrator_id,$administrator_id)){
                $this->addError('customer_id', '标签取消失败，您不是客户负责人！');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'tag_id' => '标签',
        ];
    }

    /**
     * 应用客户标签
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->ids as $customer_id)
            {
                $customerTag = CustomerTag::find()->where(['customer_id' => $customer_id])->one();
                if($customerTag)
                {
                    $customerTag->tag_id = $this->tag_id;
                    $customerTag->save(false);
                }
                else
                {
                    $customerTag = new CustomerTag();
                    $customerTag->tag_id = $this->tag_id;
                    $customerTag->customer_id = $customer_id;
                    $customerTag->save(false);
                }
            }
            $t->commit();
            return true;
        }
        catch (Exception $e)
        {
            $t->rollback();
            throw $e;
        }
    }

    /**
     * 取消客户标签
     * @return bool
     * @throws Exception
     */
    public function cancel()
    {
        if(!$this->validate())
        {
            return false;
        }
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->customer_id as $customer_id)
            {
                $customerTag = CustomerTag::find()->where(['customer_id' => $customer_id])->one();
                if($customerTag)
                {
                    $customerTag->delete();
                }
            }
            $t->commit();
            return true;
        }
        catch (Exception $e)
        {
            $t->rollback();
            throw $e;
        }
    }
}