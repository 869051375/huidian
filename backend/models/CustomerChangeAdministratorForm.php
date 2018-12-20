<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\MessageRemind;
use Yii;
use yii\base\Model;

/**
 * Class CustomerChangeAdministratorForm
 * @package backend\models
 *
 * @property Company $company
 * @property Administrator $administrator
 */
class CustomerChangeAdministratorForm extends Model
{
    public $id;
    public $administrator_id;
    public $company_id;
    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var Administrator
     */
    public $administrator;

    public function rules()
    {
        return [
            [['administrator_id', 'company_id'], 'integer'],
            [['administrator_id'], 'required'],
            [['id'], 'required','message'=>'客户id不能为空'],         
            ['id', 'validateId'],
            ['administrator_id', 'validateAdministratorId'],
        ];
    }

    public function validateId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer = CrmCustomer::findOne($this->id);

        if(null == $this->customer)
        {
            $this->addError('id', '客户不存在');
        }
        else if($this->customer->isReceive())
        {
            $this->addError('id', '该客户已经被转入，不能进行该操作');
        }
        else if(!$this->customer->isSubFor($administrator) && !$this->customer->isPrincipal($administrator))
        {
            $this->addError('id', '您没有修改该客户的权限');
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

    /**
     * 更换负责人
     * @return bool
     */
    public function change()
    {
        if(!$this->validate())
        {
            return false;
        }
        $oldAdministratorId = $this->customer->administrator_id;
        $this->customer->administrator_id = $this->administrator->id;
        $this->customer->company_id = $this->administrator->company_id;
        $this->customer->department_id = $this->administrator->department_id;

        if($this->customer->save(false) && $oldAdministratorId != $this->administrator->id)
        {
            //消息提醒
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $message = '恭喜您成为客户“'. $this->customer->name .'”的新负责人，请前往查看！';
            $popup_message = $message;
            $type = MessageRemind::TYPE_COMMON;
            $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
            $receive_id = $this->administrator->id;
            $customer_id = $this->customer->id;
            $sign = 'f-'.$oldAdministratorId.'-'.$receive_id.'-'.$customer_id.'-'.$type.'-'.$type_url;
            $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
            if(null == $messageRemind)
            {
                MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, 0, $administrator);
            }
        }
        return $this->customer->save(false);
    }

    public function attributeLabels()
    {
        return [
            'administrator_id' => '负责人',
            'company_id' => '所属公司',
        ];
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()->where(['id' => $this->administrator_id])->one();
    }
}