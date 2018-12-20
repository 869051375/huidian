<?php
namespace backend\models;

use common\models\Holidays;
use yii\base\Model;

class HolidaysForm extends Model
{
    public $year;
    public $holidays;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['year'], 'required'],
            [['year'], 'exist', 'targetClass' => Holidays::className()],
            // rememberMe must be a boolean value
            ['holidays', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'holidays' => ''
        ];
    }
}
