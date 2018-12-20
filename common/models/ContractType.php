<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "ContractType".
 *
 * @property integer $id
 * @property string $name
 * @property string $desc
 * @property string $serial_number
 * @property string $is_enable
 * @property integer $status
 * @property integer $company_id
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 */
class ContractType extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    const ENABLE_ACTIVE = 1;
    const ENABLE_DISABLED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contract_type}}';
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
            [['status','is_enable','company_id'], 'integer'],
            [['name'], 'required','message' => '合同名称不能为空，且输入长度不能超过12个文字。'],
            [['serial_number','company_id'], 'required'],
            [['serial_number'], 'match', 'pattern'=>'/^[a-zA-Z][a-zA-Z0-9]{1,7}$/i', 'message'=>'合同编码只能为字母大写或小写开头，且由子母+数字组成，不允许有空格和特殊符号，长度不超过8个字符'],
            [['serial_number'], 'unique','on' => 'insert','message' => '对不起，合同编码字段不能重复！'],
            [['serial_number'], 'validateSerialNumber','on' => 'update'],
            [['name'], 'string', 'max' => 12 , 'message' => '合同名称不能为空，且输入长度不能超过12个文字。'],
            [['desc'], 'string', 'max' => 25 ,'message' => '描述输入长度不能超过25个文字'],
            [['serial_number'], 'string', 'max' => 8],
            [['creator_name'], 'string', 'max' => 10],
        ];
    }

    public function validateSerialNumber()
    {

        /** @var ContractType $model */
        $model = ContractType::find()->select('id')->where('serial_number=:serial_number',[':serial_number' => $this->serial_number])->limit(1)->one();
        if($model && $model->id !== $this->id)
        {
            $this->addError('serial_number','对不起，合同编码字段不能重复！');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => '所属公司',
            'name' => '名称',
            'desc' => '描述',
            'serial_number' => '合同编码',
            'is_enable' => 'Is Enable',
            'status' => 'Status',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
        ];
    }

    public function getCode()
    {
        $date = date('Ymd',time());
        return $this->serial_number.$date;
    }

    public function getFinancialCode()
    {
        if($latter = mb_substr($this->serial_number,0,2))
        {
            return $latter;
        }
        return null;
    }
}