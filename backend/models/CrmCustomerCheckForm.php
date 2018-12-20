<?php
namespace backend\models;

use common\models\CrmCustomer;
use common\validators\TelPhoneValidator;
use yii\base\Model;

/**
 * Class CrmCustomerCheckForm
 * @package backend\models
 *
 */
class CrmCustomerCheckForm extends Model
{
    public $phone;
    public $tel;
    public $email;
    public $qq;
    public $wechat;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 64],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{0,19})+$/','message' => '微信只能以字母或数字开头，不能含下划线、减号和数字以外的文本，且包含至多20个字符'],
            [['qq', 'wechat'], 'string', 'max' => 20],
            [['phone'], TelPhoneValidator::className(), 'phoneOnly' => true, 'message' => '手机号码错误，请修改'],
            [['tel'], TelPhoneValidator::className(), 'telOnly' => true, 'pattern' => '/^(0[0-9]{2,3}\-)([2-9][0-9]{6,7})+$/', 'message' => '办公电话错误，请修改'],
            [['qq'], 'match', 'pattern' => '/^[1-9]\d+$/', 'message' => 'QQ号只能是非0开头的数字'],
//            ['phone', 'unique', 'targetClass' => '\common\models\CrmCustomer', 'message' => '该手机号已存在'],
            ['email', 'email'],
            [["phone", "tel", "email", "qq", "wechat"], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false],
        ];

    }

    public function requiredBySpecial($attribute)
    {
        if (empty($this->phone) && empty($this->tel) && empty($this->qq) && empty($this->email) && empty($this->wechat))
        {
            $this->addError($attribute, "至少选填一项");
        }
        $query = CrmCustomer::find();
        if(!empty($this->phone))
        {
            $query->where(['phone' => $this->phone]);
        }
        if(!empty($this->tel))
        {
            $query->where(['tel' => $this->tel]);
        }
        if(!empty($this->qq))
        {
            $query->orWhere(['qq' => $this->qq]);
        }
        if(!empty($this->email))
        {
            $query->orWhere(['email' => $this->email]);
        }
        if(!empty($this->wechat))
        {
            $query->orWhere(['wechat' => $this->wechat]);
        }

        /** @var CrmCustomer $customer */
        $customer = $query->one();

        if(null != $customer)
        {
            if($customer->isPublic())
            {
                $this->addError('phone', '该客户已存在于"'. $customer->customerPublic->name.'"中，请前往提取！');
            }
            elseif ($customer->administrator)
            {
                $this->addError('phone', '当前录入客户与"'. $customer->administrator->name.'"客户存在冲突，请谨慎录入商机！');
            }
            else
            {
                $this->addError('phone', '当前录入客户已存在，请谨慎录入商机！');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => '手机号',
            'tel' => '联系座机',
            'email' => '邮箱',
            'qq' => 'QQ',
            'wechat' => '微信',
        ];
    }

    public function getCrmCustomer()
    {
        $query = CrmCustomer::find();
        if(!empty($this->phone))
        {
            $query->where(['phone' => $this->phone]);
        }
        if(!empty($this->tel))
        {
            $query->where(['tel' => $this->tel]);
        }
        if(!empty($this->qq))
        {
            $query->orWhere(['qq' => $this->qq]);
        }
        if(!empty($this->email))
        {
            $query->orWhere(['email' => $this->email]);
        }
        if(!empty($this->wechat))
        {
            $query->orWhere(['wechat' => $this->wechat]);
        }
        /** @var CrmCustomer $customer */
        $customer = $query->one();
        return $customer ? $customer : null;
    }
}
