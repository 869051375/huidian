<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "clerk".
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property integer $province_id
 * @property string $province_name
 * @property integer $city_id
 * @property string $city_name
 * @property integer $district_id
 * @property string $district_name
 * @property integer $status
 * @property integer $creator_id
 * @property integer $administrator_id
 * @property integer $company_id
 * @property string $creator_name
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ClerkItems[] $clerkItems
 * @property ClerkArea[] $clerkArea
 * @property Administrator $administrator
 * @property Province $province
 * @property City $city
 * @property District $district
 */
class Clerk extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    public $password;

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
        return '{{%clerk}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'creator_id', 'created_at', 'id', 'company_id', 'administrator_id', 'updated_at'], 'integer'],

            [['address'], 'required'],
            [['name', 'creator_name'], 'string', 'max' => 10],

            [['province_id', 'city_id', 'district_id'], 'integer'],
            [['province_id', 'city_id', 'district_id'], 'required'],
            [['district_id'], 'integer', 'min' => 1, 'tooSmall' => '请选择所在地区'],
            [['province_name', 'city_name', 'district_name'], 'string', 'max' => 15],

            [['email'], 'string', 'max' => 64],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => Administrator::className(), 'targetAttribute' => 'email', 'on' => 'insert'],

            ['password', 'required', 'on' => 'insert'],
            [['password'], 'string', 'min' => 6],
            [['password'], 'string', 'max' => 16],

            ['phone', 'string', 'max' => 11],

            [['address'], 'string', 'max' => 255],
        ];
    }

    public function getAddressName()
    {
        return $this->province_name . ' ' . $this->city_name . ' ' . $this->district_name . ' ' . $this->address;
    }

    public function beforeDelete()
    {
        $id = $this->id;
        if (parent::beforeDelete()) {
            ClerkItems::deleteAll(['clerk_id' => $id]);
            ClerkArea::deleteAll(['clerk_id' => $id]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'password' => '密码',
            'address' => '详细地址',
            'status' => 'Status',
            'district_id' => '所在地区',
            'province_name' => '',
            'city_name' => '',
            'district_name' => '',
            'category_id' => '服务项目',
            'clerkarea' => '服务区域',
            'creator_id' => 'Creator ID',
            'creator_name' => 'Creator Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere(['city_id' => $this->city_id])
            ->andWhere(['province_id' => $this->province_id])
            ->one();
    }

    public function getAreaList()
    {
        /**@var ClerkArea * */
        return ClerkArea::find()->where(['clerk_id' => $this->id])->orderBy(['city_id' => SORT_ASC])->all();
    }


//    public function saveClerk()
//    {
//        /**@var Clerk  $model**/
//        $model = new Clerk();
//        $model->phone = $this->phone;
//        $model->address = $this->address;
//        $model->name = $this->name;
//        $model->email = $this->email;
//        $model->province_id = $this->district->province_id;
//        $model->city_id = $this->district->city_id;
//        $model->district_id = $this->district->id;
//        $model->province_name = $this->district->province_name;
//        $model->city_name = $this->district->city_name;
//        $model->district_name = $this->district->name;
//        /** @var Administrator $user */
//        $user = Yii::$app->user->identity;
//        $model->creator_id = $user->id;
//        $model->creator_name = $user->name;
//        $administrator = $this->createAdministrator();
//        $model->administrator_id = $administrator->id;
//        return $model->save(false) ? $model : null;
//    }


    public function getAdministrator()
    {
        return static::hasOne(Administrator::className(), ['id' => 'administrator_id']);
    }

    /**
     * @param $category_id
     * @return array
     */
    public function findModel($category_id)
    {
        return Product::find()->where(['category_id' => $category_id])->all();
    }

    public function getClerkItems()
    {
        return self::hasMany(ClerkItems::className(), ['clerk_id' => 'id']);
    }

    public function getClerkArea()
    {
        return self::hasMany(ClerkArea::className(), ['clerk_id' => 'id']);
    }

//    public function getAreaList()
//    {
//        return ClerkArea::find()->where(['clerk_id'=>$this->id])->orderBy(['city_id'=>SORT_ASC])->all();
//    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @param $product_id
     * @param $district_id
     * @return Clerk[]
     */
    public static function findByMatchList($product_id, $district_id)
    {
        $query = Clerk::find()
            ->joinWith(['clerkItems items'], true, 'INNER JOIN')
            ->where(['LIKE', 'items.product_ids', ',' . $product_id . ',']);

        if ($district_id > 0) {
            $query->joinWith(['clerkArea area'], true, 'INNER JOIN')
                ->andWhere(['area.district_id' => $district_id]);
        } else {
            $district_id = 0;
        }

        $query->andWhere(['status' => Clerk::STATUS_ACTIVE]);
        /** @var Clerk[] $list */
        $list = $query->all();

        $data = [];
        foreach ($list as $clerk) {
            if (null == ClerkServicePause::find()->where(['clerk_id' => $clerk->id, 'product_id' => $product_id, 'district_id' => $district_id])->one()) {
                $data[] = $clerk;
            }
        }

        return $data;
    }

    /**
     * @param $category_id
     * @return null|ClerkItems
     */
    public function getClerkItemByCategoryId($category_id)
    {
        /** @var ClerkItems $clerkItems */
        $clerkItems = ClerkItems::find()->where(['clerk_id' => $this->id, 'category_id' => $category_id])->one();
        return $clerkItems;
    }

    //批量获取服务人员列表
    public function getClerk()
    {
        $query = Clerk::find()->select(['id', 'name', 'phone','province_name','city_name','district_name','address','administrator_id']) ->where(['status' => Clerk::STATUS_ACTIVE]);
        return $query -> asArray() -> all();
    }
}
