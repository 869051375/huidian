<?php
namespace backend\models;

use common\models\Clerk;
use common\models\ClerkServicePause;
use yii\base\Model;


class ClerkServiceStatusForm extends Model
{
    public $id;
    public $status;
    public $district_id;

    public function rules()
    {
        return [
            [['id', 'status', 'district_id'], 'required'],
            [['status'], 'boolean'],
        ];
    }

    public function formName()
    {
        return '';
    }

    /**
     * @param Clerk $clerk
     * @return bool
     */
    public function save($clerk)
    {
        if($this->status == '1')
        {
            /** @var ClerkServicePause $model */
            $model = ClerkServicePause::find()->where(['product_id' => $this->id, 'district_id' => $this->district_id, 'clerk_id' => $clerk->id])->one();
            $model && $model->delete();
        }
        else
        {
            $model = new ClerkServicePause();
            $model->product_id = $this->id;
            $model->district_id = $this->district_id;
            $model->clerk_id = $clerk->id;
            $model->created_at = time();
            $model->save(false);
        }
        return true;
    }
}
