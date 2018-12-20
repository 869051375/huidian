<?php

namespace common\models;

use common\utils\BC;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_evaluate}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $product_id
 * @property integer $package_id
 * @property integer $pro_score
 * @property integer $efficiency_score
 * @property integer $attitude_score
 * @property integer $complex_score
 * @property string $tag
 * @property integer $is_reply
 * @property integer $is_audit
 * @property string $evaluate_content
 * @property string $reply_content
 * @property integer $customer_service_id
 * @property string $customer_service_name
 * @property integer $modify_time
 * @property integer $reply_time
 * @property integer $created_at
 *
 * @property User $user
 * @property CustomerService $customerService
 * @property Product $product
 * @property Order $order
 */
class OrderEvaluate extends ActiveRecord
{
    public $evaluate_id;
    //是否回复
    const REPLY_ACTIVE = 1;//是
    const REPLY_DISABLED = 0;//否

    //是否审核
    const AUDIT_ACTIVE = 1;//是
    const AUDIT_DISABLED = 0;//否

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
    public static function tableName()
    {
        return '{{%order_evaluate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'product_id', 'package_id', 'pro_score', 'efficiency_score', 'attitude_score', 'is_reply', 'is_audit', 'customer_service_id', 'modify_time', 'reply_time', 'created_at'], 'integer'],
            [['order_id', 'pro_score', 'efficiency_score','product_id','customer_service_id','attitude_score'], 'required'],
            [['efficiency_score','pro_score','attitude_score'], 'match','pattern'=>'/^[1-5]$/','message'=>'数据错误，请修改'],
            [['tag'], 'string', 'max' => 72],
            [['evaluate_content', 'reply_content'], 'string', 'max' => 80],
            [['customer_service_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                $model = Order::findOne($this->order_id);
                $model->is_evaluate = 1;
                $model->save(false);
            }
            $this->updateComplexScore();
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pro_score' => '专业程度',
            'efficiency_score' => '服务效率',
            'attitude_score' => '服务态度',
            'evaluate_content' => '评价内容',
        ];
    }

    public function getOrder()
    {
        return self::hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @param array $ids [1, 2, 3, 4]
     * @return bool
     */
    public function auditEvaluate($ids)
    {
        if(empty($ids)){
            return false;
        }
        /** @var OrderEvaluate[] $models */
        $models = OrderEvaluate::find()->where(['in','id',$ids])->orderBy(['created_at' => SORT_DESC])->all();
        foreach ($models as $model)
        {
            /** @var Administrator $admin */
            $admin = Yii::$app->user->identity;
            if($admin->isBelongCompany() && $admin->company_id)
            {
                if($admin->company_id != $model->order->company_id)
                {
                    $this->addError('order_id', $model->order->sn.'属于非本公司的订单评价您无权操作!');
                    return false;
                }
            }
            $model->is_audit = self::AUDIT_ACTIVE;
            $model->save(false);
            //新增后台操作日志
            AdministratorLog::logAuditEvaluate($model);
        }
        return true;
    }

    public function getTagList()
    {
        if(empty($this->tag)) return [];
        return explode(',', $this->tag);
    }

    public function setTagList($tags)
    {
        if(isset($tags))
        {
            $this->tag = implode(',', $tags);
        }
    }

    public function updateComplexScore()
    {
        $this->complex_score = static::calculateComplexScore($this->attitude_score, $this->pro_score, $this->efficiency_score);
    }

    /**
     * @param int $attitude 服务态度评分
     * @param int $pro 专业评分
     * @param int $efficiency 效率评分
     * @return float 综合评分
     */
    public static function calculateComplexScore($attitude, $pro, $efficiency)
    {
        // 综合评价评分=服务态度评分*40%+专业程度评分*30%+办事效率评分*30%
        return BC::add(BC::add(BC::mul($attitude, '0.4'), BC::mul($pro, '0.3')), BC::mul($efficiency, '0.3'));
    }

    /**
     * @param int $limit
     * @return CustomerServiceTag[]
     */
    public function getMostTags($limit = 3)
    {
        /** @var CustomerServiceTag[] $models */
        $models = CustomerServiceTag::find()->where(['customer_service_id' => $this->customer_service_id])
            ->orderBy(['count' => SORT_DESC])->limit($limit)->all();
        return $models;
    }

    public function getUser()
    {
        return self::hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCustomerService()
    {
        return self::hasOne(CustomerService::className(), ['id' => 'customer_service_id']);
    }

    public function getProduct()
    {
        return self::hasOne(Product::className(), ['id' => 'product_id']);
    }

    public static function getLabls()
    {
        return [
            '认真负责',
            '主动热情',
            '很专业',
            '服务态度好',
            '省时省力',
            '很和气',
            '美女',
            '靠谱',
        ];
    }

    /**
     * @param $product_id
     * @param int $limit
     * @return OrderEvaluate[]
     */
    public static function getLast($product_id, $limit = 1)
    {
        $product = Product::findOne($product_id);
        $query = OrderEvaluate::find();
        $query->andWhere(['is_audit' => OrderEvaluate::AUDIT_ACTIVE]);
        if($product->isPackage())
        {
            $query->andWhere(['package_id' => $product_id]);
        }
        else
        {
            $query->andWhere(['product_id' => $product_id]);
        }
        $query->orderBy(['created_at' => SORT_DESC]);
        $query->limit($limit);
        return $query->all();
    }

    public function getTags()
    {
        $tags = $this->getTagList();
        $label = '';
        if(empty($tags))return '';
        foreach($tags as $tag)
        {
            $label.='<li>'.$tag.'</li>';
        }
        return $label;
    }
}
