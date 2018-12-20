<?php

namespace common\models;

use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%remind}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property string $order_sn
 * @property integer $is_read
 * @property integer $category
 * @property string $message
 * @property string $description
 * @property integer $created_at
 * @property mixed orderRemindMessage
 */
class Remind extends ActiveRecord
{
    //是否已读
    const STATUS_UNREAD = 0;//未读
    const STATUS_READ = 1;//已读

    const CATEGORY_0 = 0;//用户注册成功
    const CATEGORY_1 = 1;//用户支付部分款项
    const CATEGORY_2 = 2;//用户订单付款成功
    const CATEGORY_3 = 3;//服务人员点击开始服务
    const CATEGORY_5 = 5;//服务人员点击订单流程节点下按钮
    const CATEGORY_6 = 6;//操作人员在后台发票列表处点击【已寄送】
    const CATEGORY_7 = 7;//用户提交退款申请
    const CATEGORY_8 = 8;//财务人员退款成功后点击【确认退款】
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'is_read', 'category', 'created_at'], 'integer'],
            [['order_sn'], 'string', 'max' => 16],
            [['message'], 'string', 'max' => 80],
            [['description'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'order_sn' => 'Order Sn',
            'is_read' => 'Is Read',
            'category' => 'Category',
            'message' => 'Message',
            'description' => 'Description',
            'created_at' => 'Created At',
        ];
    }

    //注册成功
    public function isRegisterSuccess()
    {
        return $this->category == self::CATEGORY_0;
    }

    //支付部分款项，未付清
    public function isUnpaid()
    {
        return $this->category == self::CATEGORY_1;
    }


    /**
     * 系统提醒数
     * @param int $user_id
     * @return int
     */
    public static function getSystemMessageCount($user_id)
    {
         $count = Remind::find()->where(['user_id' => $user_id, 'is_read' => Remind::STATUS_UNREAD])
            ->andWhere(['in','category', [
                Remind::CATEGORY_0,
                Remind::CATEGORY_1,
                Remind::CATEGORY_2,
                Remind::CATEGORY_7,
                Remind::CATEGORY_8,
            ]])
            ->count();
        return $count ? $count : 0;
    }

    /**
     * 订单进度提醒数
     * @param int $user_id
     * @return int
     */
    public static function getOrderMessageCount($user_id)
    {
        $count = Remind::find()->where(['user_id' => $user_id, 'is_read' => Remind::STATUS_UNREAD])
            ->andWhere(['in','category', [
                Remind::CATEGORY_3,
                Remind::CATEGORY_5,
                Remind::CATEGORY_6,
            ]])
            ->count();
        return $count ? $count : 0;
    }

    /**
     * @param int $limit
     * @param int $user_id
     * @return Remind[]
     */
    public static function getLastSystemMessage($user_id, $limit)
    {
        return Remind::find()->where(['user_id' => $user_id])
                            ->andWhere(['in','category', [
                                Remind::CATEGORY_0,
                                Remind::CATEGORY_1,
                                Remind::CATEGORY_2,
                                Remind::CATEGORY_7,
                                Remind::CATEGORY_8,
                            ]])
                            ->orderBy(['created_at' => SORT_DESC])
                            ->limit($limit)
                            ->all();
    }

    /**
     * @param int $limit
     * @param int $user_id
     * @return Remind[]
     */
    public static function getLastOrderMessage($user_id, $limit)
    {
        return Remind::find()->where(['user_id' => $user_id])
                            ->andWhere(['in','category', [
                                Remind::CATEGORY_3,
                                Remind::CATEGORY_5,
                                Remind::CATEGORY_6,
                            ]])
                            ->orderBy(['created_at' => SORT_DESC])
                            ->limit($limit)
                            ->all();
    }


    /**
     * 加入站内提醒
     * @param int $category
     * @param string $message
     * @param string $description
     * @param int $user_id
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    public static function create($category, $message, $description = null, $user_id = null, $order = null)
    {
        $remind = new Remind();
        if($order)
        {
            $remind->order_id = $order->id;
            $remind->order_sn = $order->sn;
            $remind->user_id = $order->user_id;
        }
        else
        {
            if(null == $user_id) throw new Exception('user_id 不正确');
            $remind->user_id = $user_id;
        }
        $remind->category = $category;
        $remind->message = $message;
        $remind->description = $description;
        $remind->created_at = time();

        if(!$remind->save(false)) return false;
        return true;
    }
}
