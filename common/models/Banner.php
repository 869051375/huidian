<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%banner}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $image
 * @property string $url
 * @property integer $sort
 * @property integer $pv
 * @property integer $uv
 * @property integer $is_city
 * @property integer $target
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $updated_at
 */
class Banner extends ActiveRecord
{
    const TARGET_PC  = 1;
    const TARGET_WAP = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%banner}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className()
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'filter', 'filter' => 'trim'],
            [['title', 'image'], 'required'],
            [['title'], 'string', 'max' => 25],
            [['url'], 'string', 'max' => 255],
            [['target'], 'in', 'range' => [self::TARGET_PC, self::TARGET_WAP]],
            ['url', 'url', 'defaultScheme' => 'http'],
            [['is_city'], 'integer']
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
            if($insert) // 如果是新增，则初始化一下排序，默认排到当前的最后一个。
            {
                $maxSort = static::find()->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10; // 加10 表示往后排（因为越大越靠后）
            }
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
            'id' => 'id',
            'title' => '标题',
            'image' => '图片',
            'url' => '链接',
            'is_city' => '区分城市',
            'creator_id' => '管理员id',
            'creator_name' => '管理员姓名',
            'created_at' => '创建时间',
            'updated_at' => '最后修改时间',
        ];
    }

    /**
     * @param int $target
     * @return Banner[]
     */
    public static function getList($target)
    {
        return self::find()->where(['target' => $target])->orderBy([
            'sort' =>SORT_ASC
        ])->all();
    }

    public static function getListByCity($target)
    {
        $cookies = Yii::$app->request->cookies;
        $city_id = $cookies->getValue('cityId') ?  $cookies->getValue('cityId') : 512;
        $city = BannerCity::find()->where(['city_id'=>$city_id])->asArray()->all();
        if(!empty($city)){
            $banner_id = array_column($city,'banner_id');
            return self::find()->where(['target' => $target])
                ->andWhere(['in','id',$banner_id])
                ->orWhere(['is_city'=>0,'target'=>$target])
                ->orderBy(['sort' =>SORT_ASC])
                ->all();

        }
        return self::find()->where(['target' => $target])->orderBy([
            'sort' =>SORT_ASC
        ])->all();

    }

    public function getImageUrl($width=200,$height=50)
    {
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = Yii::$app->get('imageStorage');
        return $imageStorage->getImageUrl($this->image, ['width' => $width, 'height' => $height, 'mode' => 1]);
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

    /**
     * @return array|null|FeaturedItemCity[]
     */
    public function getItemCity()
    {
        $cookie = \Yii::$app->request->cookies;
        $city_id = $cookie->getValue('cityId');
        $itemCity = FeaturedItemCity::find()->where(['featured_id' => $this->id,'city_id' => $city_id])->all();
        return $itemCity ? $itemCity : null;
    }
}
