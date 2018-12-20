<?php
namespace console\controllers;


use common\models\Holidays;
use common\models\Niche;
use common\models\NicheOperationRecord;
use common\models\NichePublic;
use yii\console\Controller;
class MoveNicheToBigPublicController extends Controller
{
    //自动检查线索，满足条件放进商机大公海
    public function actionRun()
    {
        /** @var Niche $niche */
        $niche = Niche::find()->where(['>','niche_public_id','1'])->asArray()->all();
        $public = NichePublic::find()->where(['type'=>1])->asArray()->one();
        if(empty($public) || (isset($public['status']) && $public['status'] == 0)){
            return true;
        }
        foreach ($niche as $key=>$value){
            if(Holidays::getEndTimeByDays($public['big_public_not_extract'],date('Y-m-d H:00:00',$value['move_public_time'])) <= time()){
                /** @var Niche $model */
                $model = Niche::find()->where(['id'=>$value['id']])->one();
                $model->niche_public_id = $public['id'];
                $model->recovery_at = time();
                $model->updated_at = time();
                $model->save(false);
                $models = new NicheOperationRecord();
                $models->niche_id = $value['id'];
                $models->content = "因商机长时间无人提取，系统回收商机到商机大公海";
                $models->item = "回收商机";
                $models->creator_id = 0;
                $models->creator_name = "系统";
                $models->created_at = time();
                $models->save(false);
                echo $value['id'].'移入大公海成功';
            }else{
                echo Holidays::getEndTimeByDays($public['big_public_not_extract'],date('Y-m-d H:00:00',$value['move_public_time']));
            }
        }
        ob_flush();
    }
}