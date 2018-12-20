<?php
namespace backend\models;

use common\models\Administrator;
use common\models\Company;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\Source;
use common\models\User;
use common\validators\TelPhoneValidator;
use Yii;
use yii\base\Model;

/**
 * Class CrmCustomerForm
 * @package backend\models
 *
 * @property Company $company
 * @property Administrator $administrator
 * @property Source $customerSource
 */
class CrmCustomerForm extends Model
{
    /**
     * @var CrmCustomer
     */
    protected $_crmCustomer;
    public $id;
    public $name;
    public $gender;
    public $birthday;
    public $source;
    public $tel;
    public $caller;
    public $phone;
    public $email;
    public $qq;
    public $wechat;
    public $province_id;
    public $province_name;
    public $city_id;
    public $city_name;
    public $district_id;
    public $district_name;
    public $get_way;
    public $street;
    public $remark;

    public $company_id;
    public $administrator_id;

    public $customer_id;
    /**
     * @var CrmCustomer
     */
    public $customer;

    /**
     * @var Administrators
     */
    public $admin;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['qq', 'wechat', 'tel', 'email', 'phone'], 'trimEmptyString'],
            [['name', 'source'], 'required'],
            [['gender', 'source', 'province_id', 'city_id', 'district_id', 'get_way', 'id'], 'integer'],
            [['street', 'remark'], 'string'],
            [['name'],'match','pattern'=>'/^[(\x{4E00}-\x{9FA5})a-zA-Z0-9\-\+\)\()]*$/u','message'=>'只允许输入文字、英文字母（不区分大小写）、数字、+、-、括号，且不允许夹带空格符号'],
            [['name'],'string','max'=>'30','message'=>'只能包含至多30个字符'],
            [['tel', 'caller', 'province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 64],
            [['birthday'],'date', 'format' => 'yyyy-MM-dd'],
            [['qq', 'wechat'], 'string', 'max' => 20],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{0,19})+$/','message' => '微信只能以字母或数字开头，不能含下划线、减号和数字以外的文本，且包含至多20个字符'],
            [['name', 'street', 'remark', 'tel', 'caller', 'province_name', 'city_name', 'district_name', 'phone', 'email', 'qq', 'wechat'], 'filter', 'filter' => 'trim'],

            [['phone'], TelPhoneValidator::className(), 'phoneOnly' => true, 'message' => '手机号码错误，请修改'],
            ['email', 'email'],
            ['id', 'validateCustomerId','on'=>'update'],
            ['phone', 'validatePhone','on'=>'update'],
            [['tel'], TelPhoneValidator::className(), 'telOnly' => true, 'message' => '办公电话错误，请修改'],
            ['tel', 'validateTel','on'=>'update'],
            ['wechat', 'validateWechat','on'=>'update'],
            ['qq', 'validateQq','on'=>'update'],
            ['email', 'validateEmail','on'=>'update'],

            ['phone', 'validatePhoneInsert','on'=>['insert', 'common-insert']],
            ['email', 'validateEmailInsert','on'=>['insert','common-insert']],
            ['qq', 'validateQqInsert','on'=>['insert','common-insert']],
            ['wechat', 'validateWechatInsert','on'=>['insert','common-insert']],
            ['tel', 'validateTelInsert','on'=>['insert','common-insert']],

            [['company_id', 'administrator_id'], 'required', 'on'=>'common-insert'],
            ['administrator_id', 'validateAdministratorId', 'on'=>'common-insert'],
            [["phone", "tel", "email", "qq", "wechat"], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false, 'on' => ['common-insert']],
        ];
    }
    public function trimEmptyString($attribute, $params)
    {
        $this->$attribute = str_replace([' ', '　'], '', $this->$attribute);
    }

    public function validateCustomerId()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->customer = CrmCustomer::findOne($this->id);
        if(null == $this->customer)
        {
            $this->addError('id', '客户不存在');
        }
        else if(!$this->customer->isSubFor($administrator)&&
            !$this->customer->isPrincipal($administrator)
        )
        {
            $this->addError('id', '您没有修改该客户的权限');
        }
    }

    public function validatePhone()
    {
        if($this->customer->phone != $this->phone)
        {
            $model = CrmCustomer::find()->where(['phone' => $this->phone])->one();
            if($model)
            {
                $this->addError('phone','手机号已存在！');
            }
            if($this->customer->isRegister())
            {
                $this->addError('phone','该手机号码已是注册用户使用！');
            }
        }
    }

    public function validateTel()
    {
        if($this->customer->tel != $this->tel)
        {
            /** @var CrmCustomer $model */
            $model = CrmCustomer::find()->where(['tel' => $this->tel])->one();
            if($model && $model->id != $this->customer->id)
            {
                $this->addError('tel','联系座机已存在！');
            }
        }
    }

    public function validateWechat()
    {
        if($this->customer->wechat != $this->wechat)
        {
            $model = CrmCustomer::find()->where(['wechat' => $this->wechat])->one();
            if($model)
            {
                $this->addError('wechat','微信已存在！');
            }
        }
    }

    public function validateQq()
    {
        if($this->customer->qq != $this->qq)
        {
            $model = CrmCustomer::find()->where(['qq' => $this->qq])->one();
            if($model)
            {
                $this->addError('qq','QQ已存在！');
            }
        }
    }

    public function validateEmail()
    {
        if($this->customer->email != $this->email)
        {
            $model = CrmCustomer::find()->where(['email' => $this->email])->one();
            if($model)
            {
                $this->addError('email','邮箱已存在！');
            }
        }
    }

    public function validatePhoneInsert()
    {
        if(!empty($this->phone))
        {
            $model = CrmCustomer::find()->where(['phone' => $this->phone])->one();
            if(null != $model)
            {
                $this->addError('phone', '手机号已存在, 无法提交！');
            }
        }
    }

    public function validateEmailInsert()
    {
        if(!empty($this->email))
        {
            $model = CrmCustomer::find()->where(['email' => $this->email])->one();
            if(null != $model)
            {
                $this->addError('email', '邮箱已存在, 无法提交！');
            }
        }
    }

    public function validateQqInsert()
    {
        if(!empty($this->qq))
        {
            $model = CrmCustomer::find()->where(['qq' => $this->qq])->one();
            if(null != $model)
            {
                $this->addError('qq', '个人QQ已存在, 无法提交！');
            }
        }
    }

    public function validateWechatInsert()
    {
        if(!empty($this->wechat))
        {
            $model = CrmCustomer::find()->where(['wechat' => $this->wechat])->one();
            if(null != $model)
            {
                $this->addError('wechat', '个人微信已存在, 无法提交！');
            }
        }
    }

    public function validateTelInsert()
    {
        if(!empty($this->tel))
        {
            $model = CrmCustomer::find()->where(['tel' => $this->tel])->one();
            if(null != $model)
            {
                $this->addError('tel', '联系座机已存在, 无法提交！');
            }
        }
    }

    public function validateAdministratorId()
    {
        $this->admin = Administrator::findOne($this->administrator_id);
        if(null == $this->admin)
        {
            $this->addError('administrator_id', '您的操作有误');
        }
        else
        {
            if(!$this->admin->department_id)
            {
                $this->addError('administrator_id', '当前负责人未设置部门，无法保存');
            }
        }
    }

    public function requiredBySpecial($attribute)
    {
        if (empty($this->phone) && empty($this->tel) && empty($this->qq) && empty($this->email) && empty($this->wechat))
        {
            $this->addError('error', "(手机号,联系座机,微信,邮箱,QQ)至少选填一项");
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '客户名称',
            'gender' => '性别',
            'birthday' => '客户生日',
            'source' => '客户来源',
            'tel' => '联系座机',
            'caller' => '来电电话',
            'phone' => '手机号',
            'email' => '邮箱',
            'qq' => 'QQ',
            'wechat' => '微信',
            'province_id' => '客户地址',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => '联系地区',
            'district_name' => 'District Name',
            'street' => '具体地址',
            'remark' => '备注描述',
            'get_way' => '获取方式',
            'company_id' => '所属公司',
            'administrator_id' => '客户负责人'
        ];
    }

    /**
     * @return CrmCustomer
     * @throws \Exception
     */
    public function save()
    {
        $model = new CrmCustomer();
        $model->load($this->attributes, '');
        if(empty($model->province_id))
        {
            $model->province_id = 0;
            $model->province_name = '';
        }
        if(empty($model->city_id))
        {
            $model->city_id = 0;
            $model->city_name = '';
        }
        if(empty($model->district_id))
        {
            $model->district_id = 0;
            $model->district_name = '';
        }
        $model->get_way = CrmCustomer::GET_WAY_CRM_INPUT;
        $model->is_receive = CrmCustomer::RECEIVE_ACTIVE;

        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $model->creator_id = $user->id;
        $model->creator_name = $user->name;
        $model->administrator_id = $user->id;
        $model->department_id = $user->department_id;
        $model->company_id = $user->company_id;
//        $department = CrmDepartment::findOne($user->department_id);
//        if(null != $department)
//        {
//            $model->department_id = $department->id;
//        }
//        else
//        {
//            $model->department_id = 0;
//        }

        $t = Yii::$app->db->beginTransaction();
        try{

            $model->save(false);
            $this->generateUser($model);
            $model->user_id = 0;
            CrmCustomerLog::add('创建客户：'.$model->name, $model->id,false,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
            CrmCustomerCombine::addTeam($model->administrator, $model);
            $t->commit();
            return $model;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * @param CrmCustomer $crmCustomer
     * @return null
     */
    public function update($crmCustomer)
    {
        $this->_crmCustomer = $crmCustomer;
        if(!$this->validate()) return false;
        $crmCustomer->load($this->attributes, '');
        if(empty($crmCustomer->province_id))
        {
            $crmCustomer->province_id = 0;
            $crmCustomer->province_name = '';
        }
        if(empty($crmCustomer->city_id))
        {
            $crmCustomer->city_id = 0;
            $crmCustomer->city_name = '';
        }
        if(empty($crmCustomer->district_id))
        {
            $crmCustomer->district_id = 0;
            $crmCustomer->district_name = '';
        }

        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $crmCustomer->updater_id = $user->id;
        $crmCustomer->updater_name = $user->name;
        if($this->getChangeData($crmCustomer))
        {
            CrmCustomerLog::add('编辑：'.$this->getChangeData($crmCustomer), $crmCustomer->id,false,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        }
        if($crmCustomer->update(false))
        {
            $this->generateUser($crmCustomer);
            if(isset($crmCustomer->user) && $crmCustomer->source)
            {
                $crmCustomer->user->source_id = $crmCustomer->source;
                $crmCustomer->user->save(false);
            }
            return true;
        }
        return false;
    }

    /**
     * @param $customer CrmCustomer
     */
    private function generateUser($customer)
    {
        if(null == $customer->user && !empty($customer->phone))
        {
            $user = new User();
            $user->loadDefaultValues();
            $user->customer_id = $customer->id;
            $user->name = $customer->name;
            $user->phone = $customer->phone;
            $user->username = $customer->phone;
            $user->source_id = $customer->source;
            $user->register_mode = User::MODE_ACTIVE;
            $user->source_name = $customer->getSourceName();
            $user->created_at = 0;
            // user 的 created_at 这里不能设置时间戳，因为需要用这个来判断用户是否注册，如果这个值为0，表示用户可以自己注册到该条数据上
            $user->save(false);
            $customer->user_id = $user->id;
            $customer->save(false);
        }
        else if($customer->user && !$customer->isRegister())
        {
            $customer->user->phone = $customer->phone;
            $customer->user->username = $customer->phone;
            $customer->user->save(false);
        }
    }

    /**
     * @param CrmCustomer $crmCustomer
     * @return string
     */
    private function getChangeData($crmCustomer)
    {
        $oldData = $crmCustomer->oldAttributes;
        $data = $crmCustomer->attributes;
        $labels = $crmCustomer->attributeLabels();
        $changeData = null;
        foreach($oldData as $i => $old)
        {
            if($old != $data[$i])
            {
                if(in_array($i,array_keys($labels)) && $data[$i])
                {
                    $changeData .= '"'.$labels[$i].'"';
                }
            }
        }
        return $changeData;
    }

    public function commonSave()
    {
        $model = new CrmCustomer();
        $model->load($this->attributes, '');
        if(empty($model->province_id))
        {
            $model->province_id = 0;
            $model->province_name = '';
        }
        if(empty($model->city_id))
        {
            $model->city_id = 0;
            $model->city_name = '';
        }
        if(empty($model->district_id))
        {
            $model->district_id = 0;
            $model->district_name = '';
        }
        $model->get_way = CrmCustomer::GET_WAY_CRM_INPUT;
        $model->is_receive = CrmCustomer::RECEIVE_ACTIVE;

        /** @var Administrator $user */
        $user = Yii::$app->user->identity;
        $model->creator_id = $user->id;
        $model->creator_name = $user->name;

        $model->administrator_id = $this->admin->id;
        $model->company_id = $this->admin->company_id;
        $model->department_id = $this->admin->department_id;

        $t = Yii::$app->db->beginTransaction();
        try{

            $model->save(false);
            $this->generateUser($model);
            $model->user_id = 0;
            CrmCustomerLog::add('创建客户：'.$model->name, $model->id,0,false,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
            CrmCustomerCombine::addTeam($model->administrator, $model);
            $t->commit();
            return $model;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }

    public function getCompany()
    {
        return Company::find()->where(['id' => $this->company_id])->one();
    }

    public function getAdministrator()
    {
        return Administrator::find()
            ->where(['id' => $this->administrator_id, 'company_id' => $this->company_id])->one();
    }

    public function getCustomerSource()
    {
        return Source::find()->where(['id' => $this->source])->one();
    }
}
