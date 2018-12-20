<?php

namespace common\models;

use imxiangli\image\storage\ImageStorageInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "Supervisor".
 *
 * @property integer $id
 * @property string $name
 * @property string $nickname
 * @property string $email
 * @property string $phone
 * @property string $image
 * @property string $describe
 * @property integer $status
 * @property integer $administrator_id
 * @property integer $assign_count
 * @property integer $servicing_number
 * @property integer $creator_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Administrator $administrator
 */
class Supervisor extends ActiveRecord
{
    /**
     * @return array
     * 添加时间
     * 修改时间
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%supervisor}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'administrator_id', 'assign_count', 'servicing_number', 'creator_id', 'created_at', 'updated_at'], 'integer'],
            [['name', 'nickname', 'creator_name'], 'string', 'max' => 10],
            [['email', 'image'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 11],
            [['describe'], 'string', 'max' => 100],

            [['nickname','describe'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => ' ',
            'nickname' => '昵称',
            'describe' => '简介',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'image' => 'Image',
            'status' => 'Status',
            'administrator_id' => 'Administrator ID',
            'assign_count' => 'Assign Count',
            'servicing_number' => 'Servicing Number',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    /**
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public function getImageUrl($width=100, $height=100)
    {
        $url = $this->administrator->getImageUrl($width, $height);
        if(null == $url)
        {
            /** @var ImageStorageInterface $imageStorage */
            $imageStorage = Yii::$app->get('imageStorage');
            $image = Property::get('default_supervisor_avatar');
            return $imageStorage->getImageUrl($image, ['width' => $width, 'height' => $height, 'mode' => 0]);
        }
        return $url;
    }
}
