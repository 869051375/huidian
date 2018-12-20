<?php

namespace common\models;

use common\utils\ProductUrl;
use imxiangli\image\storage\ImageStorageInterface;

/**
 * This is the model class for table "featured_item".
 *
 * @property integer $id
 * @property string $name
 * @property string $move_front_explain
 * @property string $move_after_explain
 * @property string $link
 * @property integer $is_product
 * @property integer $featured_id
 * @property integer $product_id
 * @property integer $sort
 *
 * @property Product $product
 * @property Featured $featured
 * @property FeaturedImage $featuredImage
 */
class FeaturedItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%featured_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['featured_id', 'product_id', 'sort','is_product'], 'integer'],
            ['product_id', 'required','on'=>'product'],
            [['featured_id'], 'required'],
            [['name','link'], 'required','on'=>'non_commodity'],
            [['product_id'], 'validateProductId','on'=>'product'],
            ['name',  'string', 'max'=>15],
            ['move_front_explain', 'string',  'max'=>50],
            ['move_after_explain', 'string',  'max'=>80],
            ['link', 'string',  'max'=>255],
        ];
    }

    public function validateProductId()
    {
        $model = FeaturedItem::find()->where(['product_id'=>$this->product_id])->andWhere(['featured_id'=>$this->featured_id])->one();
        if($model!=null)
        {
           $this->addError('product_id', '该推荐位已经添加过此商品！');
        }
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert) // 如果是新增，则初始化一下排序，默认排到当前的最后一个。
            {
                $maxSort = static::find()->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10; // 加10 表示往后排（因为越大越靠后）
            }
            return true;
        }
        return false;
    }

    public function getFeatured()
    {
        return static::hasOne(Featured::className(), ['id' => 'featured_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'featured_id' => '',
            'move_after_explain' => '移入后说明',
            'move_front_explain' => '移入前说明',
            'product_id' => '商品',
            'sort' => 'Sort',
            'name' => '名称',
            'link' => '链接',
            'is_product' => '选择商品',
        ];
    }

    public function getProduct()
    {
        return self::hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getFeaturedImage()
    {
        return self::hasOne(FeaturedImage::className(), ['featured_item_id' => 'id']);
    }

    
    public function getImageUrl($width, $height)
    {
        if(empty($this->featuredImage))
        {
            if(!empty($this->product))
            {
                return $this->product->getImageUrl($width,$height,$this->featured->target == Featured::TARGET_WAP ? ProductImage::TYPE_FEATURED_WAP : ProductImage::TYPE_FEATURED);
            }
            return null;
        }
        return $this->featuredImage->getImageUrl($width,$height);
    }

    //针对搜索结果页
    public function getProductImageUrl($width, $height)
    {
        if(empty($this->featuredImage))
        {
            if(!empty($this->product))
            {
                return $this->product->getImageUrl($width,$height, ProductImage::TYPE_LIST);
            }
            return null;
        }
        return $this->featuredImage->getImageUrl($width,$height);
    }

    public function getName()
    {
        if($this->is_product==1 && empty($this->name))
        {
            return $this->product->name;
        }
        return $this->name;
    }

    public function scene()
    {
        if($this->is_product==1)
        {
            $this->setScenario('product');
        }else{
            $this->setScenario('non_commodity');
        }
    }

    public function deleteImage()
    {
        if(empty($this->featuredImage))return null;
        /** @var ImageStorageInterface $imageStorage */
        $imageStorage = \Yii::$app->get('imageStorage');
        $imageStorage->delete($this->featuredImage->image);
        $this->featuredImage->delete();
    }

    public function getLink()
    {
        if($this->is_product==1)
        {
            return ProductUrl::to($this->product);
        }
        return $this->link;
    }
}