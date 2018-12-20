<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%industry}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort
 * @property integer $created_at
 * @property integer $updated_at
 */
class Industry extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%industry}}';
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
            [['sort', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 15],
            [['name'], 'required'],
        ];
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '行业名称',
            'sort' => 'Sort',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array|null
     */
    public static function getIndustry()
    {
        /**@var Industry[] $data **/
        $data = self::find()->all();
        $industry[0] = '请选择行业类型';
        foreach ($data as $key=>$val)
        {
            $industry[$val->id] = $val->name;
        }
        $industry[999] = '其他类型';
        return $industry;
    }

    /**
     * @param $product_id
     * @return array|null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function getIndustryList($product_id)
    {
        $product = Product::find()->select('industries')->where(['id'=>1])->asArray()->one();
        if($product!=null)
        {
            $id = implode(',',$product);
        }
        else
        {
            return [];
        }

        return self::find()->where(['in','id',$id])->all();
    }
}
