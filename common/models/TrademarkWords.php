<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%trademark_words}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property string $company
 */
class TrademarkWords extends ActiveRecord
{
    const TYPE_FAMOUS_TRADEMARK = 1; //著名商标
    const TYPE_WELL_KNOWN_TRADEMARK = 2; //驰名商标

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trademark_words}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['company'], 'string', 'max' => 40],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => '类型',
            'name' => '商标名',
            'company' => 'Company',
        ];
    }

    public function typeName()
    {
        return self::getTypeName($this->type);
    }

    //后台显示状态
    public static function getTypeName($type)
    {
        $statusList = static::getTypeList();
        return isset($statusList[$type]) ? $statusList[$type] : '著/驰名商标';
    }

    //后台显示状态
    public static function getTypeList()
    {
        return [
            self::TYPE_FAMOUS_TRADEMARK => '著名商标',
            self::TYPE_WELL_KNOWN_TRADEMARK => '驰名商标',
        ];
    }
}
