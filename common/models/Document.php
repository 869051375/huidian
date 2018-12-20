<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%document}}".
 *
 * @property integer $id
 * @property integer $document_category_id
 * @property string $title
 * @property string $content
 * @property integer $sort
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 */
class Document extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            if($insert){
                $this->creator_id = $administrator->id;
                $this->creator_name = $administrator->name;
            }
            else
            {
                $this->updater_id = $administrator->id;
                $this->updater_name = $administrator->name;
            }
            if($this->sort == '')
            {
                $maxSort = static::find()->where('document_category_id=:document_category_id', [':document_category_id' => $this->document_category_id])
                    ->orderBy(['sort' => SORT_DESC])->select('sort')->limit(1)->scalar();
                $this->sort = $maxSort + 5; // 加5 表示往后排（因为越大越靠后）
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['document_category_id', 'sort', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 80],
            [['creator_name', 'updater_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'document_category_id' => 'Document Category ID',
            'title' => 'Title',
            'content' => 'Content',
            'sort' => 'Sort',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
