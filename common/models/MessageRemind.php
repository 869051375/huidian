<?php

namespace common\models;

/**
 * This is the model class for table "{{%message_remind}}".
 *
 * @property integer $id
 * @property integer $receive_id
 * @property integer $customer_id
 * @property integer $opportunity_id
 * @property integer $order_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $type
 * @property integer $type_url
 * @property string $popup_message
 * @property integer $is_show
 * @property integer $is_read
 * @property string $email
 * @property integer $email_status
 * @property string $message
 * @property string $sign
 * @property integer $created_at
 *
 * @property Administrator $administrator
 */
class MessageRemind extends \yii\db\ActiveRecord
{
    const TYPE_COMMON  = 0;//普通类型
    const TYPE_EMAILS = 1;//邮件类型

    const STATUS_UNREAD = 0;//未读
    const STATUS_READ = 1;//已读

    const STATUS_NOT_SHOW = 0;//弹框未显示
    const STATUS_SHOW = 1;//弹框已显示

    const EMAIL_NOT_SEND = 0;//邮件未发送
    const EMAIL_SEND = 1;//邮件已发送
    const EMAIL_FAIL_SEND = 2;//邮件发送失败

    const popup_DISABLED = 0;//不是弹窗消息
    const popup_ACTIVE = 1;//弹窗消息

    //查看详情链接类型
    const TYPE_URL_ORDER_DETAIL = 0;//0订单详情页
    const TYPE_URL_USER_DETAIL = 1;//1客户详情页
    const TYPE_URL_USER_NEED_CONFIRM = 2;//2我待确认客户列表
    const TYPE_URL_OPPORTUNITY_NEED_CONFIRM = 3;//3我待确认的商机列表
    const TYPE_URL_OPPORTUNITY_DETAIL = 4;//4商机详情页
    const TYPE_URL_RECEIPT = 5;//5财务确认回款列表
    const TYPE_URL_ORDER_LIST = 6;//6订单列表


    //todo 考虑是否增加操作类型结合增加数据判断是否已经生产提醒消息？？
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message_remind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receive_id', 'customer_id', 'opportunity_id', 'order_id', 'creator_id', 'type', 'type_url', 'is_show', 'is_read', 'email_status', 'created_at'], 'integer'],
            [['creator_name'], 'string', 'max' => 25],
            [['popup_message', 'message', 'sign'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'receive_id' => 'Receive ID',
            'customer_id' => 'Customer ID',
            'opportunity_id' => 'Opportunity ID',
            'order_id' => 'Order ID',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'type' => 'Type',
            'type_url' => 'Type Url',
            'popup_message' => 'Popup Message',
            'is_show' => 'Is Show',
            'is_read' => 'Is Read',
            'email' => 'Email',
            'email_status' => 'Email Status',
            'message' => 'Message',
            'sign' => 'Sign',
            'created_at' => 'Created At',
        ];
    }
    //是否已读
    public function isRead()
    {
        return $this->is_read == self::STATUS_READ;
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'receive_id']);
    }

    /**
     * @param string $sign
     * @param string $message
     * @param int $type
     * @param int $type_url
     * @param int $receive_id
     * @param int $customer_id
     * @param int $order_id
     * @param int $opportunity_id
     * @param string $popup_message
     * @param string $email
     * @param Administrator $administrator
     * @return bool
     */
    public static function create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id = 0, $order_id = 0, $opportunity_id = 0, $administrator = null, $email = null)
    {
        $messageRemind = new MessageRemind();
        if($administrator)
        {
            $messageRemind->creator_id = $administrator->id;
            $messageRemind->creator_name = $administrator->getTypeName().'-'.$administrator->name;
        }
        else
        {
            $messageRemind->creator_name = '系统';
        }

        if($type == MessageRemind::TYPE_EMAILS)
        {
            $messageRemind->email = $email;
            $messageRemind->email_status = MessageRemind::EMAIL_NOT_SEND;
        }
        if(empty($messageRemind->email))
        {
            $messageRemind->email = '';
        }

        $messageRemind->sign = $sign;
        $messageRemind->popup_message = $popup_message;
        $messageRemind->receive_id = $receive_id;
        $messageRemind->customer_id = $customer_id;
        $messageRemind->opportunity_id = $opportunity_id;
        $messageRemind->order_id = $order_id;
        $messageRemind->type = $type;
        $messageRemind->type_url = $type_url;
        $messageRemind->message = $message;
        $messageRemind->created_at = time();
        if(!$messageRemind->save(false)) return false;
        return true;
    }
}
