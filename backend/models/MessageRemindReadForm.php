<?php
namespace backend\models;

use common\models\Administrator;
use common\models\MessageRemind;
use yii\base\Model;

class MessageRemindReadForm extends Model
{
    public $ids;

    /**
     * @var MessageRemind[]
     */
    public $messageReminds = [];

    public function rules()
    {
        return [
            ['ids', 'each', 'rule' => ['integer']],
            ['ids', 'validateIds', ],
        ];
    }

    public function validateIds()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        $this->messageReminds = MessageRemind::find()->where(['in', 'id', $this->ids])->all();

        if(empty($this->messageReminds))
        {
            $this->addError('ids', '请选择消息');
        }

        foreach($this->messageReminds as $messageRemind)
        {
            if($administrator->id != $messageRemind->receive_id)
            {
                $this->addError('ids', '您没有该消息的权限');
            }
        }
    }

    public function batchRead()
    {
        if(!$this->validate())
        {
            return false;
        }
        foreach($this->messageReminds as $messageRemind)
        {
            $messageRemind->is_read = MessageRemind::STATUS_READ;
            $messageRemind->save(false);
        }
        return true;
    }
}