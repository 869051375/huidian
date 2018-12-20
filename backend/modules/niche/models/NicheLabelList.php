<?php

namespace backend\modules\niche\models;


use common\models\Tag;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheLabelList"))
 */
class NicheLabelList extends Model
{

    /**
     * @param object $administrator
     * @return array
     */
    public function getNicheLabel($administrator)
    {
        $query = Tag::find()->where(['type'=>NicheLabel::NICHE_TYPE]);
        if($administrator->company_id != 0){
            $query->andWhere(['company_id'=>$administrator->company_id]);
        }
        $list = $query->all();
        return $list;
    }

}