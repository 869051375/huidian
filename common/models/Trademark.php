<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "trademark".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $category_id
 * @property string  $name
 * @property string  $description
 * @property string  $image
 * @property string  $apply_no
 * @property string  $category_name
 * @property integer $created_at
 *
 * @property Order $order
 *
 */
class Trademark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trademark}}';
    }

    /**
     * @inheritdoc
     */
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
            [['id','order_id', 'category_id'], 'integer'],
            ['name', 'string', 'max' => 50],
            ['category_id', 'validateCategoryId'],
            ['category_name', 'string', 'max' => 255],
            [['name','description','category_id','apply_no','image'], 'required'],
            ['description', 'string', 'max' => 255],
            ['apply_no', 'string', 'max' => 64],
            ['image', 'string', 'max' => 64],
        ];
    }

    public function validateCategoryId()
    {
        /**@var $model TrademarkCategory **/
        $model = TrademarkCategory::find()->where(['id'=>$this->category_id])->one();
        if($model == null)
        {
            $this->addError('category_id','商标类别不存！');
        }
        $this->category_name = $model->name;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'=>'商标名称',
            'description'=>'商标说明',
            'category_id'=>'商标类别',
            'image'=>'商标图样',
            'apply_no'=>'申请号',
        ];
    }

    public function getUserId()
    {
        $this->user_id = $this->order->user_id;
    }

    public function getOrder()
    {
        return self::hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getImageUrl($width, $height)
    {
        $is = Yii::$app->get('imageStorage');
        return $is->getImageUrl($this->image, ['width' => $width, 'height' => $height, 'mode' => 0]);
    }
}