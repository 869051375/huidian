<?php

namespace common\models;

/**
 * This is the model class for table "{{%district}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property string $letter
 * @property string $pinyin
 * @property integer $sort
 */
class District extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%district}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'required'],
            [['city_id'], 'validateCityId'],
            [['name'], 'string', 'max' => 15],
            [['letter'], 'string', 'max' => 10],
            [['code'], 'string', 'max' => 6],
            [['pinyin'], 'string', 'max' => 30],
        ];
    }

    public function validateCityId()
    {
        $city = City::findOne($this->city_id);
        if(null == $city)
        {
            $this->addError('name', '找不到所在城市');
        }
        else
        {
            $this->city_name = $city->name;
            $this->province_id = $city->province_id;
            $this->province_name = $city->province_name;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '区/县名称',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'letter' => '简拼',
            'pinyin' => '全拼',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            if($insert)
            {
                $maxSort = static::find()->where(['city_id' => $this->city_id])
                    ->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10;
            }
            return true;
        }
        return false;
    }
}
