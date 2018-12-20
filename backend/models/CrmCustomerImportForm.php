<?php
namespace backend\models;

use common\models\Administrator;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\User;
use common\validators\TelPhoneValidator;
use Yii;

/**
 * Class CrmCustomerImportForm
 * @package backend\models
 *
 */
class CrmCustomerImportForm extends CrmCustomer
{
    public $phone;
    public $tel;
    public $email;
    public $qq;
    public $wechat;
    public $file;
    public $administrator_id;
    public $level;
    public $name;
    public $birthday;
    public $caller;
    public $source;
    public $gender;

    /**
     * @var Administrator
     */
    public $administrator;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['file', 'file', 'extensions' => ['xlsx', 'xls', 'csv'], 'maxSize' => 2*1024*1024],
            [['name'], 'string', 'max' => 30],
            [['name'],'match','pattern'=>'/^[(\x{4E00}-\x{9FA5})a-zA-Z0-9\-\+\)\()]*$/u','message'=>'只允许输入文字、英文字母（不区分大小写）、数字、+、-、括号，且不允许夹带空格符号'],
            [['phone'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 64],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z0-9]([-_a-zA-Z0-9]{0,19})+$/','message' => '微信只能以字母或数字开头，不能含下划线、减号和数字以外的文本，且包含至多20个字符'],
            [['qq', 'wechat'], 'string', 'max' => 20],
            [['tel','caller'], 'string', 'max' => 15],
            [['birthday'], 'string', 'max' => 10],
            [['phone'], TelPhoneValidator::className(), 'phoneOnly' => true, 'message' => '手机号码错误'],
            [['tel'], TelPhoneValidator::className(), 'telOnly' => true, 'pattern' => '/^(0[0-9]{2,3}\-)([2-9][0-9]{6,7})+$/', 'message' => '办公电话错误，请修改'],
            [['qq'], 'match', 'pattern' => '/^[1-9]\d+$/', 'message' => 'QQ号只能是非0开头的数字'],
//            ['phone', 'unique', 'targetClass' => '\common\models\CrmCustomer', 'message' => '该手机号已存在'],
            ['email', 'email'],
            [['level', 'administrator_id', 'source', 'gender'], 'integer'],
            [["phone", "tel", "email", "qq", "wechat"], "requiredBySpecial", 'skipOnEmpty' => false, 'skipOnError' => false],
            [["administrator_id"], "validateAdministratorId", 'skipOnEmpty' => false, 'skipOnError' => false],
            [["source"], "validateSource", 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function requiredBySpecial($attribute)
    {
        if (empty($this->phone) && empty($this->tel) && empty($this->qq) && empty($this->email) && empty($this->wechat))
        {
            $this->addError($attribute, "客户名称，手机号，微信，邮箱，QQ至少选填一项");
        }
    }

    public function validateSource($attribute)
    {
        if($this->source == '')
        {
            $this->addError($attribute, "客户来源不能为空");
        }
    }

    public function validateAdministratorId()
    {
        if(empty($this->administrator_id))
        {
            $this->administrator = Yii::$app->user->identity;
        }
        else
        {
            $this->administrator = Administrator::findOne((int)$this->administrator_id);
            if(null == $this->administrator)
            {
                $this->addError('administrator_id', '您的操作有误，负责人不存在！');
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
            'file' => '导入文件',
            'administrator_id' => '客户负责人',
            'level' => '客户级别',
            'name' => '客户名称',
            'birthday' => '客户生日',
            'caller' => '来电电话',
            'source' => '客户来源',
            'gender' => '性别',
        ];
    }

    public function getCrmCustomer()
    {
        $query = CrmCustomer::find();

        if(!empty($this->phone))
        {
            $query->orWhere(['phone' => $this->phone]);
        }
        if(!empty($this->tel))
        {
            $query->orWhere(['tel' => $this->tel]);
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

    /**
     * @return CrmCustomer
     * @throws \Exception
     */
    public function saveCustomer()
    {
        $model = new CrmCustomer();
        $model->name = $this->name;
        $model->gender = $this->gender;
        $model->get_way = CrmCustomer::GET_WAY_CRM_INPUT;
        $model->phone = $this->phone;
        $model->tel = $this->tel;
        $model->wechat = $this->wechat;
        $model->email = $this->email;
        $model->qq = $this->qq;
        $model->birthday = $this->birthday;
        $model->caller = $this->caller;
        $model->street = $this->street;
        $model->remark = $this->remark;
        $model->source = $this->source;
        $model->level = $this->level;
        $model->is_receive = CrmCustomer::RECEIVE_ACTIVE;
        $model->creator_id = $this->administrator ? $this->administrator->id : 0;
        $model->creator_name = $this->administrator ? $this->administrator->name : 0;
        $model->administrator_id = $this->administrator ? $this->administrator->id : 0;
        $model->department_id = $this->administrator ? $this->administrator->department_id : 0;
        $model->company_id = $this->administrator ? $this->administrator->company_id : 0;
//        $t = Yii::$app->db->beginTransaction();
//        try{
        $model->save(false);
        //user表添加信息
        $this->generateUser($model);
        $model->user_id = 0;
        CrmCustomerLog::add('创建客户：'.$model->name, $model->id,false,$this->administrator,CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        CrmCustomerCombine::addTeam($model->administrator, $model);
//            $t->commit();
        return $model;
//        }
//        catch (\Exception $e)
//        {
//            $t->rollBack();
//            throw $e;
//        }
    }

    /**
     * @param $customer CrmCustomer
     */
    private function generateUser($customer)
    {
        if(null == $customer->user && !empty($customer->phone))
        {
            $user = new User();
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
}
