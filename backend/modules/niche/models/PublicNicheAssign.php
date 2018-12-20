<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NicheOperationRecord;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use yii\base\Model;
use Yii;



/**
 * 公海商机提取
 * @SWG\Definition(required={"niche_id","administrator_id"}, @SWG\Xml(name="PublicNicheAssign"))
 */
class PublicNicheAssign extends Model
{
    /**
     * 商机id
     * @SWG\Property(example = "1,2,3")
     * @var string
     */
    public $niche_id;

    /**
     * 分配人id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $administrator_id;



    public function rules()
    {
        return [
            [['niche_id','administrator_id'], 'required'],
            [['administrator_id'], 'integer'],
            [['niche_id'],'string']
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function assign($administrator)
    {
        $niche_id = explode(',',$this->niche_id);
        $count = count($niche_id);
        $administrators = Administrator::find()->where(['id'=>$this->administrator_id])->one();
        $department = NichePublicDepartment::find()->where(['department_id'=>$administrators->department_id])->one();
        if($department){
            $public = NichePublic::find()->where(['id'=>$department->niche_public_id])->one();
            if($public->is_own == 1){
                $niche_count = Niche::find()->where(['administrator_id'=>$administrators->id])->andWhere(['<>','status','2'])->count();
            }else{
                $niche_count = Niche::find()
                    ->where(['administrator_id'=>$administrators->id])
                    ->andWhere(['is_distribution'=>1])
                    ->andWhere(['is_extract'=>1])
                    ->andWhere(['is_transfer'=>1])
                    ->andWhere(['is_cross'=>1])
                    ->andWhere(['<>','status','2'])->count();
            }
            if($niche_count >= $public->have_max_sum){
                return ['status'=>0,'msg'=>"对不起，当前用户拥有商机已达上限"];
            }else{
                $residue = $public->have_max_sum - $niche_count;
            }
            if($residue < $count){
                $niche_id = array_slice($niche_id,0,$residue);
            }
        }
        $res = false;
        for($i=0;$i<count($niche_id);$i++) {
            /** @var Niche $niche */
            $niche = Niche::find()->where(['id' => $niche_id[$i]])->one();
            $niche->niche_public_id = 0;
            $niche->administrator_id = $administrators->id;
            $niche->administrator_name = $administrators->name;
            $niche->distribution_id = $administrator->id;
            $niche->distribution_name = $administrator->name;
            $niche->progress = 10;
            $niche->status = 1;
            $niche->update_id = $administrator->id;
            $niche->update_name = $administrator->name;
            $niche->stage_update_at = time();
            $niche->is_distribution = 1;
            $niche->is_extract = 0;
            $niche->is_transfer = 0;
            $niche->is_give_up = 0;
            $niche->win_reason = '';
            $niche->win_progress = 0;
            $niche->win_describe = '';
            $niche->lose_reason = '';
            $niche->lose_progress = 0;
            $niche->lose_describe = '';
            $niche->company_id = $administrators->company_id;
            $niche->department_id = $administrators->department_id;
            $niche->distribution_at = time();
            $niche->updated_at = time();
            $niche->move_public_time = 0;
            $niche->recovery_at = 0;
            $niche->save(false);
            $model = new NicheOperationRecord();
            $model->niche_id = $this->niche_id;
            $model->content = "分配商机新负责人为" . $administrators->name;
            $model->item = "分配商机";
            $model->creator_id = $administrator->id;
            $model->creator_name = $administrator->name;
            $model->created_at = time();
            $res = $model->save(false);
        }
        if($res){
            $counts = count($niche_id);
            $fail = $count-$counts;
            return ['status'=>1,'msg'=>'所选商机分配成功：'.$counts.'；失败：'.$fail];
        }else{
            return ['status'=>0,'msg'=>'所选商机分配成功：0；失败：'.$count];
        }
    }



}