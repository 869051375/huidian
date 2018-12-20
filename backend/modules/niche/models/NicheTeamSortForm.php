<?php

namespace backend\modules\niche\models;

use yii\base\Model;
use common\models\NicheTeam;


/**
 * 商机排序
 * @SWG\Definition(required={"id", "sort"}, @SWG\Xml(name="NicheTeamSortForm"))
 */
class NicheTeamSortForm extends Model
{
    /**
     * 商机团队id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 商机团对成员id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $administrator_id;

    /**
     * 商机团队排序 （0：上移 1：下移）
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $sort;


    public function rules()
    {
        return [
            [['niche_id','administrator_id','sort'],'required'],
            [['niche_id'],'validateTeamID'],
        ];

    }

    public function validateTeamID()
    {
        $model = new NicheTeam();
        $data =$model::find()->where(['niche_id'=>$this->niche_id])->one();
        if(empty($data)){
            $this->addError('niche_id',"暂无数据");
        }
        return true;
    }

    public function change()
    {
        $model = new NicheTeam();
        $data = $model::find()->where(['niche_id'=>$this->niche_id])->andWhere(['administrator_id'=>$this->administrator_id])->one();
        if($this->sort == 0){
            $sort = $data->sort;
            $over = $model::find()->where(['sort'=>$sort+1])->andWhere(['niche_id'=>$this->niche_id])->one();
            if(empty($over)){
                return true;
            }
            $data->sort = $over->sort;
            $data->updated_at = time();
            $data->save(false);
            $over->sort = $sort;
            $over->updated_at = time();
            $over->save(false);
        }else{
            $sort = $data->sort;
            $next = $model::find()->where(['sort'=>$sort-1])->andWhere(['niche_id'=>$this->niche_id])->one();
            if($next === null){
                return true;
            }
            $data->sort = $next->sort;
            $data->updated_at = time();
            $data->save(false);
            $next->sort = $sort;
            $next->updated_at = time();
            $next->save(false);
        }
        return true;
    }
}
