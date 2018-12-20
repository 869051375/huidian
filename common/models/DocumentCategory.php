<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\NotAcceptableHttpException;

/**
 * This is the model class for table "{{%document_category}}".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property integer $sort
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $updater_id
 * @property string $updater_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property DocumentCategory[] $children
 * @property DocumentCategory $parent
 */
class DocumentCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_category}}';
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
                $maxSort = static::find()->where('parent_id=:parent_id', [':parent_id' => $this->parent_id])
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
            [['parent_id', 'sort', 'creator_id', 'updater_id', 'created_at', 'updated_at'], 'integer'],
            [['sort'], 'safe'],
            [['name'], 'required'],
            [['name', 'sort'], 'trim'],
            [['name'], 'string', 'max' => 15],
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
            'parent_id' => 'Parent ID',
            'name' => '分类名称',
            'sort' => '排序值',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'updater_id' => 'Updater ID',
            'updater_name' => 'Updater Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function beforeDelete()
    {
        if(parent::beforeDelete())
        {
            // 检查是否能删除
            if($this->hasChildren())
            {
                throw new NotAcceptableHttpException('该文档库下存在子文档库，不可删除！');
            }
            else if($this->hasDocument())
            {
                throw new NotAcceptableHttpException('该文档库下存在文档，不可删除！');
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public function canDelete()
    {
        // 如果有下级分类，则不允许删除
        if($this->hasChildren())
        {
            return false;
        }
        // 分类下有商品，不能删除
        if($this->hasDocument())
        {
            return false;
        }
        return true;
    }

    public function hasChildren()
    {
        return $this->getChildren()->count() > 0;
    }

    public function getChildren()
    {
        return static::hasMany(static::className(), ['parent_id' => 'id'])->orderBy(['sort' => SORT_ASC, 'created_at' => SORT_DESC]);
    }

    public function hasDocument()
    {
        return 0 < Document::find()->where(['document_category_id' => $this->id])->count();
    }
}
