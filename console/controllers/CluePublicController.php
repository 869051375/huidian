<?php
namespace console\controllers;

use common\models\ClueOperationRecord;
use common\models\CrmClue;
use common\models\Holidays;
use common\utils\BC;
use yii\console\Controller;
use yii\data\ActiveDataProvider;

class CluePublicController extends Controller
{

    //自动检查线索，满足条件放进线索公海
    public function actionRun()
    {

        /** @var CrmClue $query */
        $query = CrmClue::find()->where('status != -1')->andWhere("clue_public_id = 0 or clue_public_id is null ")->andWhere("business_subject_id = 0 or business_subject_id is null");

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
            $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
            foreach ($models as $model){
                //此条线索负责人是否有公海
                if (isset($model->administrator->department->cluePublic->id) && $model->administrator->department->cluePublic->id != 0){

                    //分配时间，提取时间，转移时间，最后跟进时间，创建时间 对比 获取最大的时间
                    $time = max($model->distribution_at,$model->extract_time,$model->shift_at,$model->created_at);
                    //如果所在公海是启动的
                    if (isset($model->administrator->department->cluePublic->status) && $model->administrator->department->cluePublic->status == 1)
                    {
                        //新线索多少分钟不添加跟进记录，自动回收至线索公海
                        if (isset($model->administrator->department->cluePublic->new_move_time))//公海回收时间存的时候
                        {
                            //线索创建时间+公海回收时间如果小于当前时间的话，进行回收
                            if (BC::add($time,BC::mul($model->administrator->department->cluePublic->new_move_time,60,0),0) <= time())
                            {
                                //新线索条件
                                if ($model->is_new == 1)
                                {
                                    $this->recovery($model,$model->administrator->department->cluePublic); //移除到公海
                                }
                            }
                        }

                        //他们分配或者自己领取的线索多少个工作日不添加跟进记录，将自动回收到公海
                        if (isset($model->administrator->department->cluePublic->distribution_move_time))
                        {
                            //计算规则之后的几个工作日后的时间戳小于当前时间进行回收
                            if (Holidays::getEndTimeByDays($model->administrator->department->cluePublic->distribution_move_time,date('Y-m-d H:00:00',$time)) <= time())
                            {
                                if ($model->status == 2 || $model->status == 3 || $model->status == 1)
                                {
                                    $this->recovery($model,$model->administrator->department->cluePublic,true); //移除到公海
                                }
                            }
                        }

                        //已跟进的线索几个工作日内不添加跟进记录，将自动回收到公海
                        if (isset($model->administrator->department->cluePublic->follow_move_time))
                        {
                            //获取最新的跟进时间加上规则的时间 小于当前时间
                            if (Holidays::getEndTimeByDays($model->administrator->department->cluePublic->follow_move_time,date('Y-m-d H:00:00',$time)) <= time())
                            {
                                if (isset($model->recordDesc->id)){
                                    $this->recovery($model,$model->administrator->department->cluePublic,true); //移除到公海
                                }
                            }
                        }

                        //个人线索多少个工作日不转化为客户，将自动回收至公海
                        if (isset($model->administrator->department->cluePublic->personal_move_time))
                        {
                            //线索创建时间+规则时间 小于当前时间
                            if (Holidays::getEndTimeByDays($model->administrator->department->cluePublic->personal_move_time,date('Y-m-d H:00:00',$time)) <= time())
                            {
                                $this->recovery($model,$model->administrator->department->cluePublic,true,true); //移除到公海
                            }
                        }
                    }
                }
            }
        }
    }

    //移入公海
    private function recovery($clue,$clue_public,$status = false,$content = false)
    {
        $clue->clue_public_id = $clue_public->id;
        $clue->move_public_time = time();
        $clue->status = 5;  //公海状态
        $clue->recovery_at = time(); 
        $clue->save();
        //添加操作记录
        $operation = new ClueOperationRecord();
        if ($content)
        {
            $operation->create($clue->id,$operation::RECOVERY_CLUE,'此线索未在规定时间内转换为客户，被系统强制放弃到'.$clue_public->name,0,'系统');
        }
        else{
            $operation->create($clue->id,$operation::RECOVERY_CLUE,'回收线索到'.$clue_public->name,0,'系统');
        }
        return true;
    }
}