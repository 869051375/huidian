<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use Yii;
use yii\base\Model;


/**
 * 用于更新商机的商机商品
 *  @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="NicheLabelClearForm"))
 */
class NicheLabelClearForm extends Model
{
    /**
     * 商机id
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $niche_id;


    public function rules()
    {
        return [
            [['niche_id'], 'required'],
            [['niche_id'], 'string'],
            ['niche_id', 'validateNicheLabel'],
        ];
    }

    public function validateNicheLabel()
    {
        $model = new Niche();
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $department_ids = $administrator->getTreeDepartmentId(true);
        if($department_ids){
            array_push($department_ids,'0');
        }else{
            $department_ids = [0];
        }
        $ids = explode(',',$this->niche_id);
        $niche = $model::find()->where(['in','id', $ids])->andWhere(['company_id' => $administrator->company_id])->andWhere(['in','department_id',$department_ids])->one();
        if (empty($niche)) {
            $this->addError('niche_id', '您没有权限操作本条商机');
        }

        return true;
    }
    /*
     * 修改商机标签
     * */
    public function save()
    {
        $ids = explode(',',$this->niche_id);
        for ($i=0;$i<count($ids);$i++){
            $niche = Niche::findOne($ids[$i]);
            $niche->label_id = 0;
            $niche->label_name = '';
            $niche->updated_at = time();
            $niche->save(false);
        }
        return true;
    }
}
