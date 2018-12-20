<?php

namespace common\models;

use common\utils\BC;
use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "customer_service".
 *
 * @property integer $id
 * @property string $name
 * @property string $nickname
 * @property string $email
 * @property string $phone
 * @property string $qq
 * @property integer $service_number
 * @property integer $allot_number
 * @property string $favorable_rate
 * @property string $image
 * @property string $describe
 * @property integer $status
 * @property integer $is_default_allot
 * @property integer $administrator_id
 * @property integer $company_id
 * @property integer $assign_count
 * @property integer $servicing_number
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Administrator $administrator
 */
class CustomerService extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    /**
     * @return array
     * 添加时间
     * 修改时间
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'is_default_allot','creator_id', 'created_at', 'service_number','allot_number','updated_at'], 'integer'],
            [['nickname','describe','service_number','favorable_rate','qq'], 'required'],

            ['favorable_rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['favorable_rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
            [['favorable_rate'], 'number'],

            [['qq'], 'string', 'max' => 20],
            [['nickname'], 'string', 'max' => 30],

            [['describe'], 'string', 'max' => 100],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => ' ',
            'name' => 'Name',
            'nickname' => '昵称',
            'email' => 'Email',
            'phone' => 'Phone',
            'image' => 'Image',
            'qq' => 'QQ',
            'service_number' => '服务单数',
            'allot_number' => '马甲订单分配单数',
            'favorable_rate' => '好评率',
            'describe' => '简介',
            'status' => 'Status',
            'is_default_allot' => '新客户下单默认分配',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    public static function getStatusList()
    {
        return [
            static::STATUS_ACTIVE => '开通服务',
            static::STATUS_DISABLED => '暂停服务',
        ];
    }

    public function getStatusName()
    {
        $list = static::getStatusList();
        if(null === $this->status)
            $this->status = 0;
        return $list[$this->status];
    }

    public function isActive()
    {
        return $this->status == static::STATUS_ACTIVE;
    }

    /**
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getImageUrl($width=100, $height=100)
    {
        $url = $this->administrator->getImageUrl($width, $height);
        if(null == $url)
        {
            /** @var ImageStorageInterface $imageStorage */
            $imageStorage = Yii::$app->get('imageStorage');
            $image = Property::get('default_customer_service_avatar');
            return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 0]);
        }
        return $url;
    }

    /**
     * 获得好评率
     */
    public function getBestEvaluateRate()
    {
        $count = $this->getEvaluateCount();
        $getBestEvaluateCount = $this->getBestEvaluateCount();
        return BC::div($getBestEvaluateCount, $count)*100;
    }

    /**
     * 评价总数
     */
    public function getEvaluateCount()
    {
        // todo 做缓存
        $count = OrderEvaluate::find()->where(['customer_service_id' => $this->id, 'is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->count();
        return $count;
    }

    /**
     * 好评数
     */
    public function getBestEvaluateCount()
    {
        // todo 做缓存
        $count =  OrderEvaluate::find()->where(['customer_service_id' => $this->id, 'is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->andWhere('complex_score >= 4')->count();
        return $count;
    }

    /**
     * 中评数
     */
    public function getNeutralEvaluateCount()
    {
        // todo 做缓存
        $count = OrderEvaluate::find()->where(['customer_service_id' => $this->id, 'is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->andWhere('complex_score >= 3 and complex_score < 4')->count();
        return $count;
    }

    /**
     * 差评数
     */
    public function getBadEvaluateCount()
    {
        // todo 做缓存
        $count = OrderEvaluate::find()->where(['customer_service_id' => $this->id, 'is_audit' => OrderEvaluate::AUDIT_ACTIVE])
            ->andWhere('complex_score < 3')->count();
        return $count;
    }


    //获取客户列表  批量修改
    public function  getService(){

        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $company_id = $admin -> company_id;

        $query = CustomerService::find()->select(['id', 'name', 'servicing_number','phone','administrator_id']) ->where(['status' => CustomerService::STATUS_ACTIVE]);

        if($company_id != '' && $company_id != 0){
            $query -> andWhere(['company_id' => $company_id]);
        }
        return $query -> asArray() -> all();
    }

}
