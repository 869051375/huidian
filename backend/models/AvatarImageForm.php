<?php
namespace backend\models;
use yii\base\Model;

class AvatarImageForm extends Model
{
    public $image;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image'], 'file'],
            [['image'], 'image'],
        ];
    }
}
