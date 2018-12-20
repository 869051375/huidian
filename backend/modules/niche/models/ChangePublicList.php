<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="ChangePublicList"))
 */
class ChangePublicList extends Model
{

    /**
     * 类型 (0:商机公海 1：商机大公海)
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $type;

    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'integer']
        ];
    }

    public function publicNicheList($administrator)
    {
        $nichePublic = new NichePublic();
        $query = $nichePublic::find();
        $query->select('niche_public.id,name');
        if($this->type == 1){
            if(!empty($administrator->company_id) && !empty($administrator->department_id)){
                $query->where(['company_id'=>$administrator->company_id]);
                /** @var Administrator $administrator */
                $administrator = Yii::$app->user->identity;
                $department_ids = $administrator->getTreeDepartmentId(true);
                array_push($department_ids,0);
                $niche_public_id = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->all();
                $niche_public_ids = array_column($niche_public_id,'niche_public_id');
                $query->andWhere(['in','niche_public.id',$niche_public_ids]);
            }
        }
        $nichepublic = $query->andWhere(['status'=>1])->orderBy(['status'=>SORT_DESC,'id'=>SORT_DESC])->all();
        return $nichepublic;
    }

}