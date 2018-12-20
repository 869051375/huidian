<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\CrmDepartment;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;


/**
 * @SWG\Definition(required={"id"}, @SWG\Xml(name="NichePublicDetail"))
 */
class NichePublicDetail extends Model
{
    /**
     * 自增id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $id;


    public function rules()
    {
        return [
            [['id'],'required'],
            [['id'],'validateNichePublicId'],
        ];

    }

    public function validateNichePublicId($administrator)
    {
        $query = NichePublic::find()->where(['id'=>$this->id]);
        if(!empty($administrator->company_id) && !empty($administrator->department_id)){
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $department_ids = $administrator->getTreeDepartmentId(true);
            $query->where(['company_id'=>$administrator['company_id']]);
            $niche_public_id = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->all();
            $niche_public_ids = array_column($niche_public_id,'niche_public_id');
            $query->andWhere(['in','id',$niche_public_ids]);
        }
        return true;
    }

    public function getDetail()
    {
        /** @var NichePublic $public */
        $public = NichePublic::find()->where(['id'=>$this->id])->one();
        /** @var Administrator $admin */
        $admin = Administrator::find()->where(['id'=>$public->creator_id])->one();
        $public->creator_name = isset($admin->name) ? $admin->name:"";
        return $public;
    }

}