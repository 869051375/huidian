<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\MessageRemind;
use Yii;
use yii\base\Model;

/**
 * Class CustomerDetailChangeAdministratorForm
 * @package backend\models
 *
 */
class BatchCustomerChangeAdministratorForm extends Model
{
    public $customer_ids;
    public $administrator_id;
    public $company_id;

    /**
     * @var CrmCustomer[]
     */
    public $customers;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['administrator_id','customer_ids'], 'required'],
            [['administrator_id', 'company_id'], 'integer'],
            ['customer_ids', 'validateCustomerIds'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateCustomerIds()
    {
        $ids = explode(',',rtrim($this->customer_ids,','));
        $this->customers = CrmCustomer::find()->where(['in','id',$ids])->all();
        if($this->customers)
        {
            /** @var Administrator $administrator */
            $administrator = \Yii::$app->user->identity;
            foreach ($this->customers as $customer)
            {
                if(null == $customer)
                {
                    $this->addError('customer_id', '客户不存在');
                }
                else if(!$customer->isSubFor($administrator) && !$customer->isReceive())
                {
                    $this->addError('customer_id', '客户未转入，暂不能修改');
                }
                else if(!$customer->isPrincipal($administrator) &&
                    !$customer->isSubFor($administrator))
                {
                    $this->addError('customer_id', '您没有修改该客户的权限');
                }
            }
        }
        else
        {
            $this->addError('customer_id', '客户不存在');
        }
    }

    public function validateAdministratorId()
    {
        $this->administrator = Administrator::findOne($this->administrator_id);
        if(null == $this->administrator)
        {
            $this->addError('administrator_id', '人员信息不存在');
        }
        else if($this->administrator->type != Administrator::TYPE_SALESMAN)
        {
            $this->addError('administrator_id', '该账号非业务人员');
        }
    }

    public function save()
    {
        if(!$this->validate())
        {
            return false;
        }
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach ($this->customers as $customer)
            {
                $oldAdministratorId = $customer->administrator_id;
                CrmCustomerCombine::removeTeam($customer->administrator, $customer);
                CrmCustomerCombine::addTeam($this->administrator, $customer);
                CrmCustomerLog::add('更换客户负责人为：'.$this->administrator->name, $customer->id,false,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
                $customer->administrator_id = $this->administrator->id;
                $customer->company_id = $this->administrator->company_id;
                $customer->department_id = $this->administrator->department_id;
                if($customer->save(false) && $oldAdministratorId != $this->administrator->id)
                {
                    //消息提醒
                    /** @var Administrator $administrator */
                    $administrator = Yii::$app->user->identity;
                    $message = '恭喜您成为客户“'. $customer->name .'”的新负责人，请前往查看！';
                    $popup_message = $message;
                    $type = MessageRemind::TYPE_COMMON;
                    $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
                    $receive_id = $this->administrator->id;
                    $customer_id = $customer->id;
                    $sign = 'f-'.$oldAdministratorId.'-'.$receive_id.'-'.$customer_id.'-'.$type. $type_url;
                    $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
                    if(null == $messageRemind)
                    {
                        MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, 0, $administrator);
                    }
                }
                $customer->save(false);
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

    public function attributeLabels()
    {
        return [
            'administrator_id' => '负责人',
            'company_id' => '所属公司'
        ];
    }

}