<?php

namespace common\models;

use yii\web\NotAcceptableHttpException;

/**
 * This is the model class for table "{{%province}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $letter
 * @property string $pinyin
 * @property integer $sort
 *
 * @property City[] $cities
 */
class Province extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%province}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 15],
            [['name'], 'required'],
            [['letter'], 'string', 'max' => 10],
            [['code'], 'string', 'max' => 6],
            [['pinyin'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '省份名称',
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
                $maxSort = static::find()->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
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
                throw new NotAcceptableHttpException('当前省份包含城市数据，不能删除。');
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
        if($this->getCities()->count() > 0)
        {
            return false;
        }
        return true;
    }

    public function getCities()
    {
        return static::hasMany(City::className(), ['province_id' => 'id'])->orderBy(['sort' => SORT_ASC]);
    }
}
