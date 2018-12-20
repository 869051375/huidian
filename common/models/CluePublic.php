<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clue_public".
 *
 * @property integer $id
 * @property string $name
 * @property integer $company_id
 * @property integer $label_id
 * @property integer $new_move_time
 * @property string $distribution_move_time
 * @property integer $follow_move_time
 * @property integer $personal_move_time
 * @property integer $most_num
 * @property integer $is_own
 * @property integer $administrator_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $create_id
 */
class CluePublic extends \yii\db\ActiveRecord
{
    public $department_id;
    public $administrator_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clue_public';
    }

    //线索公海状态
    const STATUS_OPEN  = 1;     //开启状态
    const STATUS_CLOSE = 0;     //关闭状态

    //获取所有的公海
    public static function getAll()
    {
        $public = CluePublic::find()->all();
        /** @var CrmDataSynchronization $data */
        $data = CrmDataSynchronization::find()->one();
        if (!empty($data)){
            /** @var CluePublic $public_one */
            $public_one = CluePublic::find()->where(['id'=>$data->clue_public_id])->one();
            $array = [];
            if ($public_one)
            {
                $array = [
                    $data->clue_public_id => $public_one->name
                ];
            }
        }
        else{
            $array = [
                0 => '请选择线索公海'
            ];
        }

        foreach ($public as $item){
            $array[$item['id']] = $item['name'];
        }
        return $array;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'label_id', 'new_move_time', 'follow_move_time', 'personal_move_time', 'most_num', 'is_own', 'administrator_id', 'status', 'created_at', 'updated_at','create_id'], 'integer'],
            [['name'], 'string', 'max' => 25],
            [['distribution_move_time'], 'string', 'max' => 10],
            [[ 'name'], 'match', 'pattern' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u','message'=>'编码错误，必须为汉字，字母，数字组成'],
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
            'company_id' => 'Company ID',
            'label_id' => 'Label ID',
            'new_move_time' => 'New Move Time',
            'distribution_move_time' => 'Distribution Move Time',
            'follow_move_time' => 'Follow Move Time',
            'personal_move_time' => 'Personal Move Time',
            'most_num' => 'Most Num',
            'is_own' => 'Is Own',
            'administrator_id' => 'Administrator ID',
            'status' => 'Status',
            'created_at' => 'created_at',
            'updated_at' => 'Updated At',
            'create_id' => 'create_id'
        ];
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'create_id']);
    }


}
