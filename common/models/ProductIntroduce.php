<?php

namespace common\models;

/**
 * This is the model class for table "product_introduce".
 *
 * @property integer $product_id
 * @property string $guarantee
 * @property string $guarantee_m
 * @property string $description_pc
 * @property string $description_m
 */
class ProductIntroduce extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_introduce}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'integer'],
            [['guarantee', 'guarantee_m', 'description_pc', 'description_m'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Product ID',
            'guarantee' => '电脑版服务保障',
            'guarantee_m' => '移动版服务保障',
            'description_pc' => '电脑版详情',
            'description_m' => '移动版详情',
        ];
    }
}