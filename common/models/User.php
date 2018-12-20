<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $phone
 * @property string $password_hash
 * @property string $email
 * @property string $address
 * @property integer $last_login
 * @property string $auth_key
 * @property string $password_reset_token
 * @property string $wechat_open_id
 * @property string $avatar
 * @property string $supervisor_id
 * @property string $customer_service_id
 * @property integer $is_vest
 * @property integer $register_mode
 * @property integer $source_id
 * @property string  $source_name
 * @property integer $customer_id
 * @property integer $created_at
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updated_at
 *
 * @property Supervisor $supervisor
 * @property CustomerService $customerService
 * @property Order[] $order
 * @property CrmCustomer $customer
 */
class User extends ActiveRecord implements IdentityInterface
{
    const VEST_YES = 1; //马甲客户
    const VEST_NO = 0;  //正常客户

    const MODE_ACTIVE = 1;//是
    const MODE_DISABLED = 0;//否
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @param $openid
     * @return array|null|User
     */
    public static function findUser($openid)
    {
        return $openid ? User::find()->where(['wechat_open_id' => $openid])->limit(1)->one() : null;
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => false, // 绝对不能打开这个自动生成创建时间的时间戳
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id','source_id'],'integer'],
            [['username','source_name'], 'string', 'max' => 20],
            ['username', 'unique'],
//            ['username','match','pattern'=>'/^[\u4E00-\u9FA5]{1,6}$/','message'=>'姓名/昵称为1-6位汉字，请修改'],
            ['name', 'string', 'max' => 30],
            [['phone', 'password_hash', 'email','address'], 'string', 'max' => 255],
            [['auth_key', 'password_reset_token', 'wechat_open_id'], 'string', 'max' => 128],
            ['wechat_open_id', 'unique'],
            ['avatar', 'string', 'max' => 64],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => '邮箱已被绑定！'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '用户id',
            'username' => '用户名',
            'name' => '姓名',
            'phone' => '手机号',
            'password_hash' => '密码hash',
            'email' => '邮箱',
            'address' => '邮寄地址',
            'avatar' => '',
            'last_login' => '最后登录时间戳',
            'auth_key' => '备用',
            'password_reset_token' => '重置密码的令牌',
            'wechat_open_id' => '微信OpenID',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getSource()
    {
        if($this->register_mode==self::MODE_DISABLED)
        {
            return '自主注册';
        }
        return '后台新增';
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return Query
     */
    public static function activeQuery()
    {
        return static::find();
    }

    public function getImageUrl($width=200,$height=50)
    {
        $image = $this->avatar;
        if(empty($image)){
            $image = Property::get('default_user_avatar');
        }
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 0]);
    }

    public function getCustomer()
    {
        return $this->hasOne(CrmCustomer::className(), ['user_id' => 'id']);
    }

    public function getSupervisor()
    {
        return $this->hasOne(Supervisor::className(), ['id' => 'supervisor_id']);
    }

    public function getCustomerService()
    {
        return $this->hasOne(CustomerService::className(), ['id' => 'customer_service_id']);
    }

    public function getOrder()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'id']);
    }

    public function sendWelcome()
    {
        Remind::create(Remind::CATEGORY_0, '欢迎来到掘金企服！', '您还没有完善个人资料，快来完善吧！', $this->id, null);
    }

    public static function register($name, $phone, $password)
    {
        $user = User::find()->where(['or', ['phone' => $phone], ['username' => $phone]])
            ->andWhere(['created_at' => '0'])->one();
        if(null == $user)
        {
            $cookies = Yii::$app->request->cookies;
            $open_id = $cookies->getValue('openId');
            $userModel = User::findUser($open_id);//判断是否绑定openId
            $user = new User();
            if(null == $userModel && $open_id)
            {
               $user->wechat_open_id = $open_id;
            }
        }
        $user->name = $name;
        $user->username = $phone;
        $user->phone = $phone;
        $user->created_at = time();
        $user->register_mode = User::MODE_DISABLED;
        $user->setPassword($password);
        $user->generateAuthKey();
        if($user->save())
        {
            // 注册成功时系统自动发放优惠券
            $coupon = new Coupon();
            $coupons = $coupon->getEffectiveCoupons('register');
            if(null != $coupons)
            {
                foreach ($coupons as $v)
                {
                    $form = new ReleaseCoupon();
                    $form->coupon_id = $v->id;
                    $form->user = $user;
                    $form->source = CouponUser::SOURCE_REGISTER;
                    $form->pushToQueue(true, false);
                }
            }

            $user->generateCustomer();

            $user->sendWelcome();
            return $user;
        }
        return null;
    }

    public function generateCustomer()
    {
        if($this->isNewRecord || $this->is_vest) return ;
        $default_customer_principal = Property::get('default_customer_principal', 0);
        $principal = null;
        if($default_customer_principal)
        {
            $principal = Administrator::findOne($default_customer_principal);
        }

        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['phone' => $this->phone])->one();
        if(null == $customer)
        {
            // 自动生成客户customer资料
            $customer = new CrmCustomer();
            $customer->loadDefaultValues();
            $customer->user_id = $this->id;
            $customer->name = $this->name;
            $customer->level = CrmCustomer::CUSTOMER_LEVEL_ACTIVE;
            $customer->phone = $this->phone;
            $customer->email = empty($this->email) ? '' : $this->email;
            $customer->get_way = CrmCustomer::GET_WAY_REGISTER;
            $customer->administrator_id = $principal ? $default_customer_principal : 0;
            $customer->is_receive = CrmCustomer::RECEIVE_DISABLED;
            $customer->department_id = $principal ? $principal->department_id : 0;
            $customer->created_at = $this->created_at;
            $customer->save(false);
            $this->customer_id = $customer->id;
            $this->save(false);

            //生成消息提醒
            $this->messageRemind($customer);
        }
        else
        {
            if(empty($customer->user_id)) // 如果已经存在了客户资料，则直接更新客户资料的user_id
            {
                $customer->user_id = $this->id;
                $customer->save(false);
                $this->customer_id = $customer->id;
                $this->source_id = $customer->source;
                $this->save(false);
            }
        }
    }

    public function getLastTime()
    {
        if(empty($this->last_login))
        {
            return '从未';
        }
        return Yii::$app->formatter->asDatetime($this->last_login);
    }

    //未付款订单（待付款订单）
    public function getPendingOrderCount()
    {
        $key = 'user-getPendingOrderCount-user-id-'.$this->id;
        $count = Yii::$app->cache->get($key);
        if(false === $count)
        {
            $count = Order::getPendingPayQueryByUserId($this->id)->count();
            Yii::$app->cache->set($key, $count);
        }
        return $count;
    }

    //已付款订单(已付款订单，包含未付清和已付全款)
    public function getPaidOrderCount()
    {
        $key = 'user-getPaidOrderCount-user-id-'.$this->id;
        $count = Yii::$app->cache->get($key);
        if(false === $count)
        {
            $count = Order::getPaidQueryByUserId($this->id)->count();
            Yii::$app->cache->set($key, $count);
        }
        return $count;
    }

    //统计客户来源的数量
    public function getUserSourceNo()
    {
        $sources  = CrmCustomer::getSourceList();
        $color = $this->getColor($sources);
        $user_source_no = [];
        $user_source = [];
        foreach($sources as $key => $source)
        {
            $user_source_no['label'] = $source;
            $user_source_no['data'] = intval(self::find()
                ->where(['source_id'=>$key,'is_vest' => self::MODE_DISABLED])
                ->count());
            $user_source_no['color'] = $color[$key];
            $user_source[] = $user_source_no;
        }
        return $user_source;
    }

    //新添加客户来源对应的颜色
    public function getColor($sources)
    {
        $arrColors = [];
        foreach($sources as $key => $source)
        {
            $str = '#';
            for($i = 0 ; $i < 6 ; $i++) {
                $randNum = rand(0 , 15);
                switch ($randNum) {
                    case 10: $randNum = 'A'; break;
                    case 11: $randNum = 'B'; break;
                    case 12: $randNum = 'C'; break;
                    case 13: $randNum = 'D'; break;
                    case 14: $randNum = 'E'; break;
                    case 15: $randNum = 'F'; break;
                }
                $str .= $randNum;
            }
            $arrColors[$key] = $str;
        }
        return $arrColors;

//        return [
//            '#4d3b62',
//            '#4f81bd',
//            '#c0504d',
//            '#9bbb59',
//            '#8064a2',
//            '#4bacc6',
//            '#f79646',
//            '#2c4d75',
//            '#772c2a',
//            '#5f7530',
//            '#CCCC99',
//            '#999999',
//            '#330099',
//            '#FFCCFF',
//            '#4169E1',
//            '#87CEEB',
//            '#5ff561',
//            '#1d2b10',
//            '#2EEACC',
//            '#117733',
//            '#110033',
//            '#EE4000',
//            '#3299CC',
//            '#CCf5a2',
//        ];
    }

    public function sortUserSource()
    {
        $users = $this->getUserSourceNo();
        $ages = array();
        foreach ($users as $user)
        {
            $ages[] = $user['data'];
        }
        array_multisort($ages, SORT_DESC, $users);
        return $users;
    }

    public function isRegister()
    {
        return $this->created_at > 0;
    }

    /**
     * @param CrmCustomer $customer
     */
    private function messageRemind($customer)
    {
        $message = '你有一个新客户“'. $customer->name .'”等待确认，请及时确认处理！';
        $popup_message = '您有一个客户等待确认，请及时查看哦！';
        $type = MessageRemind::TYPE_COMMON;
        $type_url = MessageRemind::TYPE_URL_USER_NEED_CONFIRM;
        $receive_id = $customer->administrator_id;
        $customer_id = $customer->id;
        $sign = 'e-'.$receive_id.'-'.$customer_id.'-'.$type.'-'.$type_url;
        $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
        if(null == $messageRemind)
        {
            MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id);
        }
    }
}
