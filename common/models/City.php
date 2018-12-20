<?php

namespace common\models;

use yii\web\NotAcceptableHttpException;

/**
 * This is the model class for table "{{%city}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property integer $province_id
 * @property string $province_name
 * @property string $letter
 * @property string $pinyin
 * @property integer $sort
 *
 * @property District[] $districts
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'filter', 'filter' => 'trim'],
            [['name'], 'required'],
            [['province_id'], 'validateProvinceId'],
            [['name', 'letter'], 'string', 'max' => 10],
            [['code'], 'string', 'max' => 6],
            [['pinyin'], 'string', 'max' => 30],
        ];
    }

    public function validateProvinceId()
    {
        $province = Province::findOne($this->province_id);
        if(null == $province)
        {
            $this->addError('name', '找不到所在省份');
        }
        else
        {
            $this->province_name = $province->name;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '城市名称',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
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
                $maxSort = static::find()->where(['province_id' => $this->province_id])
                    ->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 10;
            }
            return true;
        }
        return false;
    }

    public function beforeDelete()
    {
        if(parent::beforeDelete())
        {
            // 检查是否能删除
            if(!$this->canDelete())
            {
                throw new NotAcceptableHttpException('当前城市包含区县数据，不能删除。');
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
        if($this->getDistricts()->count() > 0)
        {
            return false;
        }
        return true;
    }

    public function getDistricts()
    {
        return static::hasMany(District::className(), ['city_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }
}
