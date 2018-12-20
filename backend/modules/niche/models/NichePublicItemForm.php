<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NichePublicItemForm"))
 */
class NichePublicItemForm extends Model
{

    /**
     * @param object $administrator
     * @return array
     */
    public function queryNichePublicItem($administrator)
    {
        $nichePublic = new NichePublic();
        $query = $nichePublic::find();
        $query->select('niche_public.id,name');
        $query->where(['type'=>0]);
        if(!empty($administrator->company_id) && $administrator->company_id != 0 && !empty($administrator->department_id) && $administrator->department_id!=0){
            $query->andWhere(['company_id'=>$administrator->company_id]);
            /** @var NichePublicDepartment $public_department */
            $public_department = NichePublicDepartment::find()->where(['department_id' => $administrator->department_id])->one();
            if($public_department){
                $public = NichePublic::find()->select('id,name')->where(['id'=>$public_department->niche_public_id])->asArray()->one();
            }
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $department_ids = $administrator->getTreeDepartmentId();
            $niche_public_id = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->all();
            $niche_public_ids = array_column($niche_public_id,'niche_public_id');
            $query->andWhere(['in','niche_public.id',$niche_public_ids]);
        }
        $query->andWhere(['status'=>1]);
        $result = $query->orderBy(['id'=>SORT_ASC])->asArray()->all();
        if(isset($public) && !empty($public)){
            array_unshift($result,$public);
        }
        $result = array_unique($result, SORT_REGULAR);
        return $result;
    }

}