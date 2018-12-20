<?php

namespace common\models;

/**
 * This is the model class for table "clue_channel".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property integer $sort
 */
class Channel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 11],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'sort' => 'Sort',
        ];
    }

    //查询线索来源列表
    public function getList()
    {
        return Channel::find()->where(['status'=>1])->orderBy('sort asc')->all();
    }

    public function hasCustomer()
    {
        if (CrmCustomer::find()->where(['source' => $this->id])->count() > 0){
            return true;
        }
        elseif (CrmClue::find()->where(['source_id' => $this->id])->count() > 0)
        {
            return true;
        }
        elseif (Niche::find()->where(['source_id' => $this->id])->count() > 0)
        {
            return true;
        }
        return false;
    }
}
