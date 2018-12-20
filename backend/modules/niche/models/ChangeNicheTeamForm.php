<?php

namespace backend\modules\niche\models;

use common\models\NicheTeam;
use Yii;
use yii\base\Model;


/**
 * 用于修改商机团队是否拥有权限
 * @SWG\Definition(required={"id", "is_update"}, @SWG\Xml(name="ChangeNicheTeamForm"))
 */
class ChangeNicheTeamForm extends Model
{
    /**
     * 商机团队id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;

    /**
     * 是否拥有修改权限
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $is_update;

    public function rules()
    {
        return [
            [['id','is_update'],'required'],
            [['id'],'validateTeamID'],
        ];

    }

    public function validateTeamID()
    {
        $model = new NicheTeam();
        $data =$model::find()->where(['id'=>$this->id])->one();
        if(empty($data)){
            return $this->addError('id',"暂无数据");

        }
        return true;
    }

    public function change()
    {
        $model = new NicheTeam();
        $data = $model::findOne($this->id);
        $data->is_update = $this->is_update;
        $data->updated_at = time();
        return $data->save(false);
    }


}
