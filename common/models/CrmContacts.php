<?php

namespace common\models;

use Yii;


/**
 * This is the model class for table "crm_contacts".
 *
 * @property string $id
 * @property integer $customer_id
 * @property integer $user_id
 * @property string $contact_id
 * @property string $name
 * @property integer $gender
 * @property string $birthday
 * @property string $contact_hobby
 * @property string $source
 * @property integer $channel_id
 * @property string $tel
 * @property string $caller
 * @property string $phone
 * @property string $email
 * @property string $qq
 * @property string $wechat
 * @property string $province_id
 * @property string $province_name
 * @property string $city_id
 * @property string $city_name
 * @property string $district_id
 * @property string $district_name
 * @property string $street
 * @property string $remark
 * @property string $position
 * @property string $department
 * @property string $customer_hobby
 * @property string $native_place
 */
class CrmContacts extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'crm_contacts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'gender', 'source', 'channel_id', 'province_id', 'city_id', 'district_id','user_id'], 'integer'],
            [['street', 'remark'], 'string'],
            [['name'], 'string', 'max' => 30],
            [['birthday'], 'string', 'max' => 10],
            [['customer_hobby','native_place'], 'string', 'max' => 60],
            [['tel', 'caller', 'province_name', 'city_name', 'district_name', 'position', 'department'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 64],
            [['qq', 'wechat'], 'string', 'max' => 20],
            [['name', 'province_name', 'district_name', 'city_name'], 'match', 'pattern' => '/^[ \x{4e00}-\x{9fa5}]+$/u', 'message' => '参数错误，必须为汉字', 'on' => ['create', 'update']],
            [['phone'], 'match', 'pattern' => '/^1[34578]\d{9}$/', 'message' => '手机号格式错误', 'on' => ['create', 'update']],
            [['wechat'], 'match', 'pattern' => '/^[a-zA-Z]([-_a-zA-Z0-9]{5,19})+$/', 'message' => '微信号格式错误', 'on' => ['create', 'update']],
            [['qq'], 'match', 'pattern' => '/^[1-9]*[1-9][0-9]*$/', 'message' => 'QQ号码格式错误', 'on' => ['create', 'update']],
            [['tel'], 'match', 'pattern' => '/^([0-9]{3,4}-)?[0-9]{7,8}$/', 'message' => '电话号格式错误', 'on' => ['create', 'update']],
            [['email'], 'match', 'pattern' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', 'message' => '邮箱号格式错误', 'on' => ['create', 'update']],
            [['birthday'], 'match', 'pattern' => '/^[1-2][\d]{3}\-(0\d|1[0-2])\-([0-2]\d|3[0-1])$/', 'message' => '生日参数格式错误', 'on' => ['create', 'update']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'name' => 'Name',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'customer_hobby' => 'Customer Hobby',
            'source' => 'Source',
            'channel_id' => 'Channel ID',
            'tel' => 'Tel',
            'caller' => 'Caller',
            'phone' => 'Phone',
            'email' => 'Email',
            'qq' => 'Qq',
            'wechat' => 'Wechat',
            'province_id' => 'Province ID',
            'province_name' => 'Province Name',
            'city_id' => 'City ID',
            'city_name' => 'City Name',
            'district_id' => 'District ID',
            'district_name' => 'District Name',
            'street' => 'Street',
            'remark' => 'Remark',
            'position' => 'Position',
            'department' => 'Department',
        ];
    }
}
