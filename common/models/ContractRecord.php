<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "contract_record".
 *
 * @property integer $id
 * @property integer $contract_id
 * @property string $content
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class ContractRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contract_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contract_id', 'creator_id', 'created_at'], 'integer'],
            [['content'], 'required'],
            [['content'], 'string'],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contract_id' => 'Contract ID',
            'content' => '合同动态',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param $contract_id
     * @param $content
     * @param Administrator $admin
     * @return bool
     */
    public static function CreateRecord($contract_id,$content,$admin)
    {
        $model = new ContractRecord();
        $model->contract_id = $contract_id;
        $model->content = $content;
        $model->creator_id = $admin->id;
        $model->creator_name = $admin->name;
        $model->created_at = time();
        $model->save(false);
        return true;
    }
}
