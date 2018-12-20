<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%property}}".
 *
 * @property string $key
 * @property string $desc
 * @property string $value
 */
class Property extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'desc'], 'string', 'max' => 32],
            [['value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => '属性key',
            'desc' => '属性描述',
            'value' => '属性值',
        ];
    }

    public static function get($key, $defaultValue = null)
    {
        $cache = \Yii::$app->getCache();
        if($cache)
        {
            $data = $cache->get($key);
            if ($data === false) {
                $data = static::dbGet($key, $defaultValue);
                $cache->set($key, $data);
            }
            return $data;
        }
        return static::dbGet($key, $defaultValue);
    }

    private static function dbGet($key, $defaultValue = null)
    {
        /** @var static $model */
        $model = static::find()->where(['key' => $key])->one();
        if($model === null) return $defaultValue;
        return $model->getValue();
    }

    public static function set($key, $value)
    {
        /** @var static $model */
        $model = static::find()->where(['key' => $key])->one();
        if($model === null) $model = new static();
        $model->key = $key;
        $model->value = serialize($value);
        return $model->save(false);
    }

    public function getValue()
    {
        return unserialize($this->value);
    }
}
