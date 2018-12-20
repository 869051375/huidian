<?php

namespace common\models;

use common\utils\MobileDetect;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_login_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $site
 * @property integer $created_at
 */
class UserLoginLog extends \yii\db\ActiveRecord
{
    const SOURCE_APP_PC  = 0; //0:电脑浏览器
    const SOURCE_APP_WAP = 1; //1:移动端
    const SOURCE_APP_WX  = 2; //2:微信公众号
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_login_log}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site', 'created_at'], 'integer'],
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
            'site' => 'Site',
            'created_at' => 'Created At',
        ];
    }

    public static function loginRecord($user_id)
    {
        $detect = new MobileDetect();
        $site = self::SOURCE_APP_PC;       //PC端
        $isWX = $detect->match('MicroMessenger');
        if($isWX)
        {
            $site = self::SOURCE_APP_WX;   //微信

        }
        else if($detect->isMobile())
        {
            $site = self::SOURCE_APP_WAP; //移动端
        }

        $model = new UserLoginLog();
        $model->user_id = $user_id;
        $model->site = $site;
        $model->save(false);
    }

    public function getSite()
    {
        if($this->site == self::SOURCE_APP_PC)
        {
            return 'PC端登录';
        }elseif ($this->site == self::SOURCE_APP_WAP)
        {
            return '移动端登录';
        }
        elseif ($this->site == self::SOURCE_APP_WX)
        {
            return '微信公众号登录';
        }
        return null;
    }
}
