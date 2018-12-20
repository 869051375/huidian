<?php

namespace backend\modules\niche\models;

use common\models\NichePublic;
use yii\base\Model;

/**
 * 商机公海状态修改
 * @SWG\Definition(required={"id", "status"}, @SWG\Xml(name="NichePublicChangeForm"))
 */
class NichePublicChangeForm extends Model
{
    /**
     * 公海id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 公海状态
     * @SWG\Property(example = 0)
     * @var integer
     */
    public $status;


    public function rules()
    {
        return [
            [['id','status'],'required'],
            [['id'],'validateNichePublicId'],
        ];

    }

    public function validateNichePublicId()
    {
        $model = new NichePublic();
        $data = $model::find()->where(['id'=>$this->id])->one();
        if(empty($data)){
            $this->addError('id',"暂无数据");
        }
        return true;
    }


    public function change()
    {
        $model = new NichePublic();
        $data = $model::findOne($this->id);
        $data->status = $this->status;
        return $data->save(false);
    }
}
