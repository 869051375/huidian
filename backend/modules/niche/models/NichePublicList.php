<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Yii;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="NichePublicList"))
 */
class NichePublicList extends Model
{

    /**
     * 每页多少条
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page_num;

    /**
     * 页码
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page;


    public function rules()
    {
        return [
            [['page_num','page'], 'required'],
        ];
    }

    public function getNichePublic($administrator)
    {
        $nichePublic = new NichePublic();
        $query = $nichePublic::find();
        $query->select('id,name,type,big_public_extract_max_sum,big_public_not_extract,distribution_move_time,creator_id,creator_name,personal_move_time,have_max_sum,status');
        if(!empty($administrator->company_id) && $administrator->company_id !=0  && !empty($administrator->department_id) && $administrator->department_id != 0){
            $query->where(['company_id'=>$administrator->company_id]);
            /** @var Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            $department_ids = $administrator->getTreeDepartmentId(true);
            $niche_public_id = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->all();
            $niche_public_ids = array_column($niche_public_id,'niche_public_id');
            $query->andWhere(['in','id',$niche_public_ids]);
            $query->orWhere(['type'=>1]);
        }
        $query->orderBy(['type'=>SORT_DESC,'status'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->page_num,
                'page' => $this->page-1,
            ]
        ]);
        return $dataProvider;
    }

}