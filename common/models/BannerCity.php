<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banner_city".
 *
 * @property integer $id
 * @property integer $banner_id
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 */
class BannerCity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner_city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['banner_id', 'province_id', 'city_id'], 'integer'],
            [['province_name', 'city_name'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'banner_id' => '轮播图ID',
            'province_id' => '省 ID',
            'province_name' => '省 ',
            'city_id' => '市 ID',
            'city_name' => '市 Name',
        ];
    }

    public function getCity($banner_id)
    {
        return BannerCity::find()->where(['banner_id'=>$banner_id])->all();
    }

    public function getCityList($banner_id,$province_id)
    {
        return self::find()->where(['banner_id' => $banner_id,'province_id' => $province_id])->all();
    }


}
