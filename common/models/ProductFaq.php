<?php

namespace common\models;

/**
 * This is the model class for table "product_faq".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $question
 * @property string $answer
 */
class ProductFaq extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_faq}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'integer'],
            [['answer', 'question'], 'required'],
            [['answer'], 'string'],
            [['question'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' =>  '',
            'question' => '常见问题',
            'answer' => '答案',
        ];
    }
}
