<?php

namespace common\models;

/**
 * This is the model class for table "clerk_area".
 *
 * @property integer $id
 * @property integer $clerk_id
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property ClerkArea[] $cityList
 *
 *
 */
class ClerkArea extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%clerk_area}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clerk_id', 'province_id', 'city_id', 'district_id'], 'integer'],
            [['province_name', 'city_name', 'district_name'], 'string', 'max' => 15],
        ];
    }

    public function del($city_id,$id)
    {
        return self::deleteAll(['clerk_id'=>$id,'city_id'=>$city_id]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clerk_id' => 'Clerk ID',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
        ];
    }

    public function getFullRegionName()
    {
        return $this->province_name.' '.$this->city_name.' '.$this->district_name;
    }

    public function getCityList()
    {
        return self::find()->where(['clerk_id'=>$this->clerk_id,'city_id'=>$this->city_id])->all();
    }

}
