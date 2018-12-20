<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CustomerTag;
use Yii;
use yii\base\Model;
use yii\db\Exception;

class CustomerTagForm extends Model
{
    public $customer_id;
    public $tag_id;
    public $company_id;
    public $ids;

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
            [['tag_id', 'customer_id', 'company_id'], 'integer'],
            ['ids', 'each', 'rule' => ['integer']],
            ['ids', 'validateCustomerIds', 'on' => ['add']],
            ['ids', 'validateCancelIds', 'on' => ['cancel']],
        ];
    }

    public function validateCustomerIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customers = CrmCustomer::find()->where(['in', 'id', $this->ids])->all();
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
            if($customer->administrator_id != $administrator->id)
            {
                $this->addError('ids', '标签应用失败，您不是客户负责人！');
            }
        }
    }

    public function validateCancelIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customers = CrmCustomer::find()->where(['in', 'id', $this->ids])->all();
        if(empty($this->customers))
        {
            $this->addError('ids', '请选择客户！');
        }
        foreach($this->customers as $customer)
        {
            if($customer->administrator_id != $administrator->id)
            {
                $this->addError('ids', '标签取消失败，您不是客户负责人！');
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
            foreach($this->ids as $customer_id)
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