<?php

namespace console\controllers;

use backend\modules\niche\models\NicheFunnel;
use backend\modules\niche\models\NichePublic;
use backend\modules\niche\models\NichePublicDeleteForm;
use common\models\Holidays;
use common\models\Niche;
use common\models\NicheOperationRecord;
use common\models\NichePublicDepartment;
use common\models\NicheRecord;
use yii\console\Controller;
use yii\data\ActiveDataProvider;

class NicheController extends Controller
{

    //检查商机 满足条件的移到公海
    public function actionRun()
    {
        /** @var Niche $query */
        $query = Niche::find() -> where(['niche_public_id' => 0]) -> andWhere(['<>','progress',100]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);
        $count = $dataProvider->totalCount;
        $batchNum = 100;
        $batch = ceil($count / $batchNum);

        for($i = 0; $i < $batch; $i++)
        {
            /** @var Niche[] $models */
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach($models as $model){
                //判断当前商机是否是保护状态  保护不回收
                if($model -> is_protect == 0){
                    //判断商机是否有公海有 有回收 没有不回收
                    $niche_public = isset($model->administratorNichePublic) ? $model->administratorNichePublic->nichePublic : '';
                    if(!empty($niche_public)){
                        //商机公海为启用的状态下才会被回收
                        if($niche_public->status == 1){
                            //最后跟进时间对比 获取最大的时间
                            $time = isset($model->nicheRecord->created_at)?(int)$model->nicheRecord->created_at:0;
                            //新规则根据商机的修改时间做回收标准
                            $create_at = isset($model->updated_at) ? (int)$model->updated_at : 0;
                            //判断他人分配的商机在指定工作日内未跟进 回收
                            if($model->is_distribution == 1){
                                //获取最新的跟进时间加上规则的时间 小于当前时间
                                if (Holidays::getEndTimeByDays($model->administratorNichePublic->nichePublic->distribution_move_time,date('Y-m-d H:00:00',$create_at)) <= time()){
                                    $this -> recovery($model->id,$model->administratorNichePublic->nichePublic->id,$niche_public->name);
                                }
                            }
                            //商机在输单列表直接回收
                            if($model->progress == 0){
                                $this -> recovery($model->id,$model->administratorNichePublic->nichePublic->id,$niche_public->name);
                            }

                            //判断个人商机在规定时间内不添加跟进记录 则回收 判断条件：提取时间extract_time、转移时间send_time、分配时间distribution_at、最后跟进时间last_record
                            $arr = array($model->extract_time,$model->send_time,$model->distribution_at,$time,$model->created_at);
                            rsort($arr);
                            $times = $arr[0];
                            if($model->administrator_id != 0 && $model->is_distribution != 1){
                                //获取最新的跟进时间加上规则的时间 小于当前时间
                                if (Holidays::getEndTimeByDays($model->administratorNichePublic->nichePublic->personal_move_time,date('Y-m-d H:00:00',$times)) <= time()){
                                    $this -> recovery($model->id,$model->administratorNichePublic->nichePublic->id,$niche_public->name);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 回收公海
     * @param $niche_id
     * @param $niche_public_id
     * @param $niche_public_name
     * @return bool
     * @throws \yii\db\Exception
     */
    private function recovery($niche_id,$niche_public_id,$niche_public_name)
    {
        /** @var Niche $model */
        $model = Niche::find()->where(['id'=>$niche_id])->one();
        $model -> niche_public_id = $niche_public_id;
        $model -> move_public_time = time();
        $model -> administrator_id = 0;
        $model -> administrator_name = '';
        $model -> is_distribution = 0;
        $model -> recovery_at = time();
        $model -> is_extract = 0;
        $model -> is_transfer = 0;
        //添加操作记录
        /** @var NicheOperationRecord $model_record */
        $model_record = new NicheOperationRecord();
        $model_record->niche_id = $niche_id;
        $model_record->content = '回收商机到'.$niche_public_name.'商机公海';
        $model_record->item = "回收商机";
        $model_record->creator_id = 0;
        $model_record->creator_name = '系统';
        $model_record->created_at = time();

        $t = \Yii::$app->db->beginTransaction();
        try{
            $model->save(false);
            $model_record->save(false);
            $t->commit();
            //回收到公海时，清除所有埋点记录
            $models = new NicheFunnel();
            $models->del($niche_id);
            return true;
        }catch (\Exception $e){
            $t->rollBack();
            return false;
        }

    }
}