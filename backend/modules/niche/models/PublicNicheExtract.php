<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NicheOperationRecord;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;
use yii\db\Query;


/**
 * 公海商机提取
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="NicheTeamRemoveForm"))
 */
class PublicNicheExtract extends Model
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
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function extract($administrator)
    {
        $niche_id = explode(',',$this->niche_id);
        $count = count($niche_id);
        $department = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
        if(!empty($department) && $department != null){
            $public = NichePublic::find()->where(['id'=>$department->niche_public_id])->one();
            if($public->is_own == 1){
                $niche_count = Niche::find()->where(['administrator_id'=>$administrator->id])->andWhere(['<>','status','2'])->count();
            }else{
                $niche_count = Niche::find()
                   ->andWhere(['and',['administrator_id'=>$administrator->id],['<>','status','2']])
                   ->andWhere(['or',['is_distribution'=>1],['is_extract'=>1],['is_transfer'=>1],['is_cross'=>1]])
                    ->count();

            }
            if($niche_count >= $public->have_max_sum){
                return ['status'=>0,'msg'=>"对不起，当前用户拥有商机已达上限"];
            }else{
                $residue_one = $public->have_max_sum - $niche_count;
            }
            $niche_count_day = Niche::find()->where(['administrator_id'=>$administrator->id])->andWhere(['>','extract_time',time()-86400])->andWhere(['extract_source'=>1])->count();
            if($public->extract_max_sum <= $niche_count_day){
                return ['status'=>0,'msg'=>"对不起，当前用户24小时提取数量已达上限"];
            }else{
                $residue_two = $public->extract_max_sum - $niche_count_day;
            }
            if(isset($residue_one) && isset($residue_two)){
                $residue = $residue_one > $residue_two ? $residue_two : $residue_one;
            }
            if($residue < $count){
                $niche_id = array_slice($niche_id,0,$residue);
            }
        }
        $res = false;
        for($i =0;$i<count($niche_id);$i++) {
            /** @var Niche $niche */
            $niche = Niche::find()->where(['id' => $niche_id[$i]])->one();
            if (empty($niche->niche_public_id)){
                $res = true;
                continue;
            }
            $niche_public = NichePublic::find()->where(['id' => $niche->niche_public_id])->one();
            $niche->niche_public_id = 0;
            $niche->administrator_id = $administrator->id;
            $niche->administrator_name = $administrator->name;
            $niche->progress = 10;
            $niche->status = 1;
            $niche->update_id = $administrator->id;
            $niche->update_name = $administrator->name;
            $niche->stage_update_at = time();
            $niche->is_extract = 1;
            $niche->is_distribution = 0;
            $niche->is_transfer = 0;
            $niche->is_give_up = 0;
            $niche->win_reason = '';
            $niche->win_progress = 0;
            $niche->win_describe = '';
            $niche->lose_reason = '';
            $niche->lose_progress = 0;
            $niche->lose_describe = '';
            $niche->company_id = $administrator->company_id;
            $niche->department_id = $administrator->department_id;
            $niche->extract_time = time();
            $niche->extract_source = 1;
            $niche->updated_at = time();
            $niche->move_public_time = 0;
            $niche->recovery_at = 0;
            $niche->save(false);
            $model = new NicheOperationRecord();
            $model->niche_id = $this->niche_id;
            $model->content = "从" . $niche_public->name . "商机公海提取商机";
            $model->item = "提取商机";
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
            $model->created_at = time();
            $res = $model->save(false);
        }
        if($res){
            $counts = count($niche_id);
            $fail = $count-$counts;
            return ['status'=>1,'msg'=>'所选商机提取成功：'.$counts.'；失败：'.$fail];
        }else{
            return ['status'=>0,'msg'=>"对不起，商机提取失败"];
        }
    }

    public function extract_big_public($administrator)
    {
        $niche_id = explode(',',$this->niche_id);
        $count = count($niche_id);
        /** @var NichePublic $public */
        $public = NichePublic::find()->where(['type'=>1])->one();
        $niche_count_day = Niche::find()->where(['administrator_id'=>$administrator->id])->andWhere(['>','extract_time',time()-86400])->andWhere(['extract_source'=>2])->count();
        if($public->big_public_extract_max_sum <= $niche_count_day){
            return ['status'=>0,'msg'=>"对不起，当前用户24小时从商机大公海提取数量已达上限"];
        }else{
            $residue = $public->big_public_extract_max_sum - $niche_count_day;
        }

        if($residue < $count){
            $niche_id = array_slice($niche_id,0,$residue);
        }
        $res = false;
        for($i =0;$i<count($niche_id);$i++) {
            /** @var Niche $niche */
            $niche = Niche::find()->where(['id' => $niche_id[$i]])->one();
            if (empty($niche->niche_public_id)){
                $res = true;
                continue;
            }
            $niche->niche_public_id = 0;
            $niche->administrator_id = $administrator->id;
            $niche->administrator_name = $administrator->name;
            $niche->progress = 10;
            $niche->status = 1;
            $niche->update_id = $administrator->id;
            $niche->update_name = $administrator->name;
            $niche->stage_update_at = time();
            $niche->is_extract = 1;
            $niche->is_distribution = 0;
            $niche->is_transfer = 0;
            $niche->is_give_up = 0;
            $niche->win_reason = '';
            $niche->win_progress = 0;
            $niche->win_describe = '';
            $niche->lose_reason = '';
            $niche->lose_progress = 0;
            $niche->lose_describe = '';
            $niche->company_id = $administrator->company_id;
            $niche->department_id = $administrator->department_id;
            $niche->extract_time = time();
            $niche->updated_at = time();
            $niche->move_public_time = 0;
            $niche->recovery_at = 0;
            $niche->extract_source = 2;
            $niche->save(false);
            $model = new NicheOperationRecord();
            $model->niche_id = $this->niche_id;
            $model->content = "从商机大公海提取商机";
            $model->item = "提取商机";
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
            $model->created_at = time();
            $res = $model->save(false);
        }
        if($res){
            $counts = count($niche_id);
            $fail = $count-$counts;
            return ['status'=>1,'msg'=>'所选商机提取成功：'.$counts.'；失败：'.$fail];
        }else{
            return ['status'=>0,'msg'=>"对不起，商机提取失败"];
        }
    }



}