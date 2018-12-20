<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "information".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property integer $top_category_id
 * @property string $top_category_name
 * @property integer $category_id
 * @property string $category_name
 * @property integer $is_home
 * @property integer $is_hot
 * @property integer $type
 * @property string $content
 * @property integer $status
 * @property integer $sort
 * @property string $image
 * @property string $reading_quantity
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property integer $creator_id
 * @property string $creator_name
 * @property string $source
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 */
class Information extends \yii\db\ActiveRecord
{
    const TYPE_INFORMATION = 2;
    const TYPE_ENCYCLOPEDIAS = 1;

    const ONLINE = 1;
    const OFFLINE = 0;

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
        return '{{%information}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['top_category_id','category_id', 'is_hot','is_home','type','reading_quantity','status', 'sort', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description','top_category_id','category_id','image','content','source','sort','type'], 'required'],
            [['content'], 'string'],
            [['title', 'seo_title'], 'string', 'max' => 80],
            [['description'], 'string', 'max' => 200],
            [['top_category_name','category_name'], 'string', 'max' => 20],
            [['image'], 'string', 'max' => 64],
            ['top_category_id', 'validateTopCategoryId'],
            ['category_id', 'validateCategoryId'],
            [['seo_keywords'], 'string', 'max' => 100],
            [['seo_description'], 'string', 'max' => 255],
            [['source'], 'string', 'max' => 30],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
        ];
    }

    public function validateTopCategoryId()
    {
        $model = InformationCategory::findOne($this->top_category_id);
        if(null==$model)
        {
            $this->addError('top_category_id', '顶级分类不存在！');
        }
        $this->top_category_name = $model->name;
    }

    public function validateCategoryId()
    {
        $model = InformationCategory::findOne($this->category_id);
        if(null==$model)
        {
            $this->addError('category_id', '分类不存在！');
        }
        $this->category_name = $model->name;
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
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getImageUrl($width=100, $height=100)
    {
        $image = $this->image;
        if(empty($image)) {
            $image = Property::get('default_supervisor_avatar');
        }
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 2]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'description' => '简介',
            'category_id' => '文章分类',
            'category_name' => 'Category Name',
            'is_hot' => '显示为热门文章',
            'is_home' => '显示在首页',
            'type' => '类型',
            'content' => '内容',
            'status' => 'Status',
            'sort' => '排序值',
            'image' => '图片',
            'seo_title' => 'seo标题',
            'seo_keywords' => '关键词',
            'seo_description' => 'seo描述',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'source' => '来源',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getTypes()
    {
        return [
            self::TYPE_ENCYCLOPEDIAS => '百科',
            self::TYPE_INFORMATION => '资讯',
        ];
    }
    public  function getTypeName()
    {
        $types = self::getTypes();
        if($this->type==null)
        {
            return null;
        }
        return $types[$this->type];
    }

    //精选过去一周的排行
    static function getSelected($type,$limit = 6)
    {
        $time = time();
        $seven_day = strtotime ("-1 week");
        $query = self::find()
            ->andWhere('created_at >= :seven_day', [':seven_day' => $seven_day])
            ->andWhere('created_at <= :time', [':time' =>$time])->andWhere(['status'=>self::ONLINE]);
        if($type)
        {
            $query->andWhere(['type' =>$type]);
        }
        return $query->limit($limit)->all();
    }
    //阅读量排行
    static  function  getRanking($type)
    {
        $query = self::find()->andWhere(['status'=>self::ONLINE]);
        if($type)
        {
            $query->andWhere(['type' =>$type]);
        }
        return $query->limit(6)
            ->orderBy(['reading_quantity'=>SORT_DESC])
            ->all();
    }

    public function getDescription($num)
    {
        if(mb_strlen($this->description)>$num)
        {
            return mb_substr($this->description,0,$num).'......';
        }
        return $this->description;
    }

    public function getTitle($num=30)
    {
        if(mb_strlen($this->title) > $num)
        {
            return mb_substr($this->title,0,$num).'......';
        }
        return $this->title;
    }

    public function getReadCount()
    {
        if($this->id%9 == 1)
        {
            $count = ((int)$this->id + 121 + (10000 - ((int)$this->id*100)) + (int)$this->reading_quantity);
        }
        else if($this->id%8 == 1)
        {
            $count = ((int)$this->id + 73 + (7000 - ((int)$this->id*100)) + (int)$this->reading_quantity);
        }
        else if($this->id%7 == 1)
        {
            $count = ((int)$this->id + 66 + (6000 - ((int)$this->id*30)) + (int)$this->reading_quantity);
        }
        else if($this->id%6 == 1)
        {
            $count = ((int)$this->id + 52 + (4000 - ((int)$this->id*50)) + (int)$this->reading_quantity);
        }
        else if($this->id%5 == 1)
        {
            $count = ((int)$this->id + 48 + (7000 - ((int)$this->id*60)) + (int)$this->reading_quantity);
        }
        else if($this->id%4 == 1)
        {
            $count = ((int)$this->id + 12 + (3600 - ((int)$this->id*70)) + (int)$this->reading_quantity);
        }
        else if($this->id%3 == 1)
        {
            $count = ((int)$this->id + 43 + (6100 - ((int)$this->id*80)) + (int)$this->reading_quantity);
        }
        else if($this->id%2 == 1)
        {
            $count = ((int)$this->id + 69 + (2100 - ((int)$this->id*20)) + (int)$this->reading_quantity);
        }
        else
        {
            $count = ((int)$this->id + 169 + (5000 - ((int)$this->id*90)) + (int)$this->reading_quantity);
        }
        if($count > 10000) return abs(floor(($count/10000))).'万+';
        return abs($count);
    }

    /**
     * @return Information
     */
    public function getPrev()
    {
        /** @var Information $model */
        $model =  Information::find()->where('id < :id', [':id' => $this->id])
            ->andWhere(['type' => $this->type, 'status' => self::ONLINE])->orderBy(['id' => SORT_DESC])->one();
        return $model;
    }

    /**
     * @return Information
     */
    public function getNext()
    {
        /** @var Information $model */
        $model = Information::find()->where('id > :id', [':id' => $this->id])
            ->andWhere(['type' => $this->type, 'status' => self::ONLINE])->orderBy(['id' => SORT_ASC])->one();
        return $model;
    }

    /**
     * @param int $limit
     * @param int $type
     * @return Information[]
     */
    public static function getHomeList($limit = 9, $type = self::TYPE_INFORMATION)
    {
        $query = Information::find()->where(['is_home' => 1, 'type' => $type, 'status' => self::ONLINE]);
        if($limit > 0)
        {
            $query->limit($limit);
        }
        return $query->all();
    }

     public  function getLists($limit = 9)
    {
        $query = Information::find()->where(['is_home' => 1, 'type' => 1, 'status' => 1]);
        if($limit > 0)
        {
            $query->orderBy(['created_at' => SORT_DESC]);
            $query->limit($limit);
        }
        return $query->all();
    }
}
