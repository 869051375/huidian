<?php
namespace backend\models;

use common\models\CrmCustomer;
use common\models\User;
use yii\base\Model;

class UserChangeSourceForm extends Model
{
    public $source_id;
    public $source_name;
    public $user_id;

    /**
     * @var User
     */
    public $user;

    public function rules()
    {
        return [
            [['source_id', 'user_id'], 'integer'],
            ['user_id', 'validateUser'],
            ['source_id', 'validateSourceId'],
            ['source_name', 'string', 'max' => 20],
        ];
    }

    public function validateUser()
    {
        $this->user = User::findOne($this->user_id);
        if(empty($this->user))
        {
            $this->addError('user_id','用户不存在');
        }
    }

    public function validateSourceId()
    {
        $source = CrmCustomer::getSourceList();
        if($this->source_id == CrmCustomer::TYPE_SOURCE_OTHER)
        {
            if(empty($this->source_name))
            {
                $this->addError('user_name','');
            }
        }
        else
        {
            if($this->source_id)
            {
                $this->source_name = $source[$this->source_id];
            }
        }
    }

    public function change()
    {
        if(!$this->validate())
        {
            return false;
        }
        $this->user->source_id = $this->source_id;
        $this->user->source_name = $this->source_name;
        $this->user->customer->source = $this->source_id;
        if(isset($this->user->customer))
        {
            $this->user->customer->save(false);
        }
        return $this->user->save(false);
    }

    public function attributeLabels()
    {
        return [
            'source_id' => '',
            'source_name' => ''
        ];
    }
}