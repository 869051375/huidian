<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "fixed_point".
 *
 * @property integer $id
 * @property string $name
 * @property string $rate
 * @property integer $status
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class FixedPoint extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fixed_point}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rate'], 'number'],
            [['name','rate'], 'required'],
            [['status', 'creator_id', 'created_at'], 'integer'],
            [['name'], 'string', 'max' => 8],
            [['creator_name'], 'string', 'max' => 10],
            [['rate'], 'unique','on' => 'insert'],
            [['rate'], 'validateRate','on' => 'update'],
            ['rate', 'compare', 'compareValue' => 1, 'operator' => '>='],
            ['rate', 'compare', 'compareValue' => 100, 'operator' => '<='],
        ];
    }

    public function validateRate()
    {
        /** @var FixedPoint $model */
        $model = FixedPoint::find()->select('id')->where('rate=:rate',[':rate' => $this->rate])->limit(1)->one();
        if($model && $model->id !== $this->id)
        {
            $this->addError('rate','固定点位的值已经被占用了');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '固定点位名称',
            'rate' => '固定点位',
            'status' => 'Status',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public static function getFixPoint()
    {
        /** @var FixedPoint[] $fixPoints */
        $fixPoints = self::find()->select('id,name,rate')->where(['status' => self::STATUS_ACTIVE])->all();
        $arr = [];
        if($fixPoints == null) return $arr;
        foreach($fixPoints as $point)
        {
            $arr[$point->id] = $point->rate.'% - '.$point->name;
        }
        return $arr;
    }
}
