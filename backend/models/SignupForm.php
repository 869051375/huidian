<?php
namespace backend\models;

use common\models\Administrator;
use common\validators\TelPhoneValidator;
use Yii;
use yii\base\Model;
use common\models\User;
/**
 * Signup form
 */
class SignupForm extends Model
{
    public $name;
    public $email;
    public $password;
    public $tpassword;
    public $phone;
    public $address;
    public $is_vest = 1;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'filter', 'filter' => 'trim'],
            ['name', 'required'],
            ['name', 'string', 'min' => 2, 'max' => 4],

            ['email', 'trim'],
            //['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => '邮箱已被绑定！'],
            ['email', 'string', 'max' => 255],

            ['password', 'required'],
            [['password'], 'string', 'min' => 6, 'max' => 16, 'message' => '密码是6-16位数字或字母！'],

            ['tpassword', 'required'],
            ['tpassword', 'validatepwd'],
            [['tpassword'], 'string', 'min' => 6, 'max' => 16, 'message' => '密码是6-16位数字或字母！'],

            ['phone', 'required'],
            [['phone'], TelPhoneValidator::className(), 'phoneOnly' => true, 'message' => '请输入11位手机号！'],
            ['phone', 'unique', 'targetClass' => '\common\models\User', 'message' => '手机号已注册！'],

            ['address', 'required'],
            ['address','string', 'min' => 4, 'max' => 255],
            ['is_vest', 'boolean'],
        ];
    }
    //密码和确认密码
    public function validatepwd()
    {
        if($this->password!=$this->tpassword)
        {
            $this->addError('tpassword', '两次密码输入不一致！');
        }
    }

    public function attributeLabels()
    {
        return [
            'username' => '账号',
            'name' => '真实姓名',
            'password' => '密码',
            'tpassword' => '确认密码',
            'email' => '邮箱',
            'phone' => '手机号',
            'address' => '邮寄地址',
            'is_vest' => '',
        ];
    }

    /**
     * @return User|null
     */
    public function signup()
    {
        if (!$this->validate(['name','email','phone','password','tpassword','address'])){
            return null;
        }
        $user = new User();
        if(Yii::$app->user->can('user/create_vest'))
        {
            $user->is_vest = $this->is_vest;
            if($this->is_vest)
            {
                $this->phone = substr($this->phone, 0, 3).'****'.substr($this->phone, 7);
            }
        }
        else
        {
            $user->is_vest = 0;
        }
        $user->username = $this->phone;
        $user->name = $this->name;
        $user->email = $this->email;
        $user->phone = $this->phone;
        $user->register_mode = User::MODE_ACTIVE;
        $user->address = $this->address;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $user->creator_id = $administrator->id;
        $user->creator_name = $administrator->name;
        $user->created_at = time();
        if($user->save())
        {
            $user->sendWelcome();
            $user->generateCustomer();
            return $user;
        }
        return null;
    }
}
