<?php
namespace backend\models;

use common\models\City;
use common\models\Clerk;
use common\models\ClerkArea;
use common\models\District;
use common\models\Province;
use yii\base\Model;

/**
 * Class ClerkForm
 * @property Province $province
 * @property City $city
 * @property District $district
 */
class ClerkAreaForm extends Model
{
    public $clerk_id;
    public $province_id;
    public $city_id;
    public $district_id;
    public $district_name;

    private $clerk;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['province_id','city_id','district_id'], 'required'],
            [['province_id','city_id'], 'integer'],
            ['clerk_id', 'validateProductId'],
            ['district_id', 'validateDistrict'],
        ];
    }

    //区县去除已经添加过的
    public function validateDistrict()
    {
        /** @var ClerkArea[] $clerk_area */
        $clerk_area = ClerkArea::find()->where(['clerk_id'=>$this->clerk_id,'city_id'=>$this->city_id])->all();
        if(!empty($clerk_area))
        {
            $district = null;
            foreach ($clerk_area as $key => $district_area)
            {
                $district[$key] = $district_area->district_id;
            }
            $district_ids = null;
            foreach ($this->district_id as $k => $id)
            {
                if(!in_array($id, $district))
                {
                    $this->district_id[$k] = $id;
                } else {
                    $this->addError('district_id', '不能重复添加区县。');
                }
            }
        }
    }

    public function validateProductId()
    {
        $this->clerk = Clerk::findOne($this->clerk_id);
        if(null == $this->clerk)
        {
            $this->addError('clerk_id', '找不到服务人员信息。');
        }
    }

    public function getDistrict()
    {
        return District::find()->where(['id' => $this->district_id])
            ->andWhere('city_id!=:city_id', [':city_id' => $this->city_id])
            ->andWhere('province_id!=:province_id', [':province_id' => $this->province_id])
            ->one();
    }

    public function getCity()
    {
        return City::find()->where(['id' => $this->city_id])
            ->andWhere('province_id!=:province_id', [':province_id' => $this->province_id])->one();
    }

    public function getProvince()
    {
        return Province::find()->where(['id' => $this->province_id])->one();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clerk_id' => '服务人员ID',
            'district_id' => '地区',
            'city_id' => '城市',
            'province_id' => '省份',
            'district_name' => '地区名称',
        ];
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        $t = \Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->district_id as $district)
            {
                $districtModel = District::findOne($district);
                $model = new ClerkArea();
                $model->clerk_id = $this->clerk_id;
                $model->province_id = $districtModel->province_id;
                $model->city_id = $districtModel->city_id;
                $model->district_id = $districtModel->id;
                $model->province_name = $districtModel->province_name;
                $model->city_name = $districtModel->city_name;
                $model->district_name = $districtModel->name;
                $model->save(false);
            }
            $t->commit();
            return true;
        }
        catch(\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}