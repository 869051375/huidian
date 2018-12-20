<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\NotAcceptableHttpException;

/**
 * This is the model class for table "{{%product_category}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $sort
 * @property string $title
 * @property string $is_show_nav
 * @property string $is_show_list
 * @property string $image
 * @property string $icon_image
 * @property string $banner_image
 * @property string $banner_url
 * @property string $keywords
 * @property string $description
 * @property string $customer_service_link
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ProductCategory[] $children
 * @property ProductCategory $parent
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    const IS_SHOW_NAV_FALSE = 0;//不显示在首页
    const IS_SHOW_NAV_TRUE = 1;//显示在首页

    const IS_SHOW_LIST_FALSE = 0;//不显示在列表页
    const IS_SHOW_LIST_TRUE = 1;//显示在列表页
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_category}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => 'trim'],
            [['is_show_nav','is_show_list'], 'boolean'],
            [['name'], 'required'],
            [['image', 'icon_image', 'banner_image', 'banner_url'], 'string'],
            [['parent_id'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DISABLED]],
            [['name'], 'string', 'max' => 8],
            [['title'], 'string', 'max' => 80],
            [['keywords'], 'string', 'max' => 140],
            [['description'], 'string', 'max' => 200],
            [['banner_url'], 'string', 'max' => 255],
            [['customer_service_link'], 'string'],
            [['is_show_nav','is_show_list'], 'default', 'value' => 0],
            [['content'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '分类名称',
            'title' => '标题',
            'image' => '图片',
            'icon_image' => '图标',
            'banner_image' => 'banner图片',
            'banner_url' => 'banner链接',
            'is_show_nav' => '显示在首页',
            'is_show_list' => '显示在列表页',
            'keywords' => '关键词',
            'description' => '描述',
            'customer_service_link' => '客服链接',
            'parent_id' => '',
            'sort' => 'Sort',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'content' => '页面底部描述',
        ];
    }

    public function getChildren()
    {
        return static::hasMany(static::className(), ['parent_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }

    public function getLastSort()
    {
        return ProductCategory::find()
            ->where('parent_id=:parent_id', [':parent_id' => $this->parent_id])
            ->orderBy(['sort' => SORT_DESC])
            ->select('sort')
            ->limit(1)
            ->scalar();
    }

    public function beforeDelete()
    {
        if(parent::beforeDelete())
        {
            // 检查是否能删除
            if($this->hasChildren())
            {
                throw new NotAcceptableHttpException('该分类下存在子分类，不可删除！');
            }
            else if($this->hasProduct())
            {
                throw new NotAcceptableHttpException('该分类下存在商品，不可删除！');
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert) // 如果是新增，则初始化一下排序，默认排到当前同一层级分类的最后一个。
            {
                $maxSort = static::find()->where('parent_id=:parent_id', [':parent_id' => $this->parent_id])
                    ->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10; // 加10 表示往后排（因为越大越靠后）
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function canDelete()
    {
        // 如果有下级分类，则不允许删除
        if($this->hasChildren())
        {
            return false;
        }
        // 分类下有商品，不能删除
        if($this->hasProduct())
        {
            return false;
        }
        return true;
    }

    public function hasChildren()
    {
        return $this->getChildren()->count() > 0;
    }

    public function hasProduct()
    {
        return 0 < Product::find()->where(['top_category_id' => $this->id])->orWhere(['category_id' => $this->id])->count();
    }

    /**
     * @param int $limit
     * @return ProductCategory[]
     */
    public static function getList($limit = 4)
    {
        return self::find()
            ->where(['parent_id'=>0])
            ->orderBy(['sort' =>SORT_ASC])
            ->limit($limit)
            ->all();
    }


    public static function getContent($id)
    {
        return self::find()->asArray()->select('content')->where(['=','id',$id])->orderBy([
            'sort' => SORT_ASC
        ])->all();
    }

    /**
     * @param int $limit
     * @return ProductCategory[]
     */
    public static function getNavList($limit = 0)
    {
        $result = static::getDb()->cache(function ($db) use ($limit) {
            $query = self::find()->where(['is_show_nav' => self::IS_SHOW_NAV_TRUE, 'parent_id' => '0'])->orderBy(['sort' =>SORT_ASC]);
            if($limit > 0) $query->limit($limit);
            return $query->all();
        });
        return $result;
    }

    public static function getTopCategory($top_category_id)
    {
        $result = static::getDb()->cache(function ($db) use ($top_category_id) {
            return self::find()->where(['id'=>$top_category_id,'is_show_list' => self::IS_SHOW_LIST_TRUE])->limit('1')->one();
        });
        return $result;
    }

    /**
     * @param $top_category_id
     * @param int $limit
     * @return ProductCategory[]
     */
    public static function getCategoryList($top_category_id,$limit=3)
    {
        $result = static::getDb()->cache(function ($db) use ($top_category_id, $limit) {
            return self::find()
                ->where(['parent_id'=>$top_category_id])
                ->orderBy(['sort' =>SORT_ASC])
                ->limit($limit)
                ->all();
        });
        return $result;

    }

    /**
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getImageUrl($width=100, $height=100)
    {
        $image = $this->image;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 1]);
    }

    /**
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getBannerImageUrl($width=0, $height=0)
    {
        $image = $this->banner_image;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 1]);
    }

    /**
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getIconImageUrl($width=0, $height=0)
    {
        $image = $this->icon_image;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 1]);
    }

    public function getCategoryIds()
    {
        /** @var ProductCategory[] $category */
        $category = self::find()->select('id')->where(['parent_id' => $this->id,'is_show_list' => self::IS_SHOW_LIST_FALSE])->all();
        $ids = [];
        foreach ($category as $item)
        {
            $ids[] = $item->id;
        }
        return $ids;
    }

    public function getSeoTitle()
    {
        return $this->title ? $this->title : $this->name;
    }

    public function getSeoKeywords()
    {
        return $this->keywords ? $this->keywords : $this->name;
    }

    public function getSeoDescription()
    {
        return $this->description ? $this->description : $this->name;
    }



}
