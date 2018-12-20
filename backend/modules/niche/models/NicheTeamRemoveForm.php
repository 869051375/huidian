<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use yii\base\Model;
use common\models\NicheTeam;


/**
 * 移除商机
 * @SWG\Definition(required={"niche_id","administrator_id"}, @SWG\Xml(name="NicheTeamRemoveForm"))
 */
class NicheTeamRemoveForm extends Model
{
    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 团队成员id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $administrator_id;

    public function rules()
    {
        return [
            [['niche_id','administrator_id'],'required'],
            [['niche_id'],'validateTeamID'],
        ];

    }

    public function validateTeamID()
    {
        $model = new NicheTeam();
        $data = $model::find()->where(['niche_id'=>$this->niche_id])->andWhere(['administrator_id'=>$this->administrator_id])->one();
        if(empty($data)){
            $this->addError('niche_id',"暂无数据");
        }
        return true;
    }

    public function remove()
    {
        $model = new NicheTeam();
        $model::deleteAll(['niche_id'=>$this->niche_id,'administrator_id'=>$this->administrator_id]);
        $models = new NicheFunnel();
        $models->del($this->niche_id,$this->administrator_id);
        /** @var Administrator $admin */
        $admin = Administrator::find()->where(['id'=>$this->administrator_id])->one();
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        /** @var \common\models\NicheOperationRecord $record */
        $record = new \common\models\NicheOperationRecord();
        $record->niche_id = $this->niche_id;
        $record->content = "移除商机团队成员为".$admin->name;
        $record->item = "协作商机";
        $record->creator_id = $administrator->id;
        $record->creator_name = $administrator->name;
        $record->created_at = time();
        $record->save(false);
        return true;
    }
    

}