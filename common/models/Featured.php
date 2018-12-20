<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "featured".
 *
 * @property integer $id
 * @property string $featured_key
 * @property string $name
 * @property string $remarks
 * @property string $product_ids
 * @property string $status
 * @property string $target
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property FeaturedItem[] $allItems
 *
 */
class Featured extends ActiveRecord
{
    public $product;
    public $featured_id;

    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 0;

    const TARGET_PC = 1; // 电脑网页
    const TARGET_WAP = 2; // 首页网页

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
        return '{{%featured}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['featured_key','name','remarks'], 'trim'],
            [['featured_key','name','remarks'], 'required'],
            [['featured_key'], 'string', 'max' => 128],
            [['featured_key'], 'unique'],
            [['name'], 'string', 'max' => 25],
            [['remarks'], 'string', 'max' => 20],
            ['status', 'boolean'],
            [['target'], 'in', 'range' => [self::TARGET_PC, self::TARGET_WAP]],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $user */
            $user = Yii::$app->user->identity;
            $this->creator_id = $user->id;
            $this->creator_name = $user->name;
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
            'id' => 'ID',
            'featured_key' => '推荐位key',
            'name' => '名称',
            'remarks' => '备注说明',
            'product' => '商品',
            'product_ids' => 'Product ID',
            'featured_id' =>'',
            'status' => 'Status',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @param $key
     * @return Featured
     */
    public static function getFeatured($key)
    {
        /** @var Featured $featured */
        $featured = Featured::find()->where(['featured_key'=>$key,'status'=>1])->one();
        return $featured;
    }

    public function getAllItems()
    {
        return static::hasMany(FeaturedItem::className(), ['featured_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }

    /**
     * @param int $limit
     * @return FeaturedItem[]
     */
    public function getOnlineItems($limit = 0)
    {
        $query = $this->getAllItems()
            ->joinWith(['product p'])
            ->orderBy(['sort'=>SORT_ASC])
            ->where(['or', ['p.status' => Product::STATUS_ONLINE], ['is_product' => '0']]);
        if($limit > 0)
        {
            $query->limit($limit);
        }
        return $query->all();
    }

    public static function getTargetList()
    {
        return [
            self::TARGET_PC => '电脑端',
            self::TARGET_WAP => '手机网页',
        ];
    }

    public function getTargetName()
    {
        $list = static::getTargetList();
        return $list[$this->target];
    }
}