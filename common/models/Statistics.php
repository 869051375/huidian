<?php
namespace common\models;

use common\utils\BC;

/**
 * This is the model class for table "{{%statistics}}".
 *
 * @property string $key
 * @property string $name
 * @property string $content
 */
class Statistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistics}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['key', 'string', 'max' => 80],
            ['name', 'string', 'max' => 50],
            ['content', 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => 'key',
            'name' => '名称',
            'content' => '内容',
        ];
    }

    static function getContent($key)
    {
        $model = self::find()->where(['key'=>$key])->one();
        return $model;
    }


    //商标查询次数计算
    public function Calculation($no)
    {
        $frequency = BC::add($this->content,$no,0);
        return $frequency;
    }

}
