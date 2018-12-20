<?php
namespace backend\models;

use common\models\Administrator;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * PasswordForm
 */
class PasswordForm extends Model
{
    public $password;
    public $new_password;
    public $second_password;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'min' => 5, 'max' => 18, 'message' => '密码为6-18位字符，请修改'],
            ['password', 'validatePassword'],

            ['new_password', 'required'],
            ['new_password', 'string', 'min' => 5, 'max' => 18, 'message' => '新密码为6-18位字符，请修改'],
            ['new_password', 'validatepwd'],

            ['second_password', 'required'],
            ['second_password', 'string', 'min' => 5, 'max' => 18, 'message' => '确认密码为6-18位字符，请修改'],

        ];
    }

    /**
     * 密码和确认密码
     */
    public function validatepwd()
    {
        if($this->new_password!=$this->second_password)
        {
            $this->addError('tpassword', '密码与第一次输入不一致，请修改');
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->findModel();
            if (!$user->validatePassword($this->password)){
                $this->addError($attribute, '密码输入错误！');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'password' => '密码',
            'new_password' => '新密码',
            'second_password' => '确认密码',
        ];
    }

    public function findModel()
    {
        $model = Administrator::findOne(\Yii::$app->user->id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到该用户！');
        }
        return $model;
    }

    public function update()
    {
        $model = $this->findModel();
        $model->setPassword($this->new_password);
        $model->save(false);
    }

}
