<?php
namespace backend\models;

use yii\base\Model;

class FaqForm extends Model
{
    public $product_id;
    public $answer;
    public $question;

    public function rules()
    {
        return [
            [['product_id'], 'integer'],
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
            'product_id' => ' ',
            'question' => '问题',
            'answer' => '答案',
        ];
    }

}
