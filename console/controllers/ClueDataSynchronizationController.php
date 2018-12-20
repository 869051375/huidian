<?php
namespace console\controllers;

use common\models\ClueOperationRecord;
use common\models\CrmClue;
use common\models\CrmDataSynchronization;
use common\models\Order;
use common\models\User;
use yii\console\Controller;
use yii\data\ActiveDataProvider;

class ClueDataSynchronizationController extends Controller
{
    //自动检查线索，满足条件从电商同步到线索
    public function actionRun()
    {
        $time = time()-(30*60);
        //上线时间为2018-12-14 18:00:00 todo 上线的时候记得修改
        $query = User::find()->leftJoin(Order::tableName().' b', '`user`.`id` = `b`.`user_id`')->where(['user.is_synchronization'=>0])->andWhere(['>=','user.created_at','1544781600'])->andWhere(['<=','user.created_at',$time])->andWhere(['user.register_mode'=>0])->andWhere('b.user_id is null');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);
        $count = $dataProvider->totalCount;

        $batchNum = 100;
        $batch = ceil($count / $batchNum);
        /** @var CrmDataSynchronization $data */
        $data = CrmDataSynchronization::find()->one();
        echo date('Y-m-d H:i:s')." 当前队列:".$count."\r\n";
        if (isset($data->clue_public_id) && $data->clue_public_id != 0){

            for($i = 0; $i < $batch; $i++)
            {
                $models = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
                foreach ($models as $model){
                    $connection = \Yii::$app->db;
                    $transaction = $connection->beginTransaction();
                    try {
                        //同步到线索
                        $clue = new CrmClue();
                        $clue->name = $model->name;
                        $clue->user_id = $model->id;
                        $clue->clue_public_id = $data->clue_public_id;
                        $clue->status = CrmClue::STATUS_PUBLIC;
                        $clue->mobile = $model->phone;
                        $clue->created_at = $model->created_at;
                        $clue->source_id = 99;
                        $clue->channel_id = 99;
                        $clue->recovery_at = time();
                        $clue->updated_at = time();
                        $clue->save(false);

                        $model->is_synchronization = 1;
                        $model->save(false);

                        //添加操作记录
                        $operation = new ClueOperationRecord();
                        $operation->create($clue->id,$operation::CREATE_CLUE,'创建了销售线索',0,'系统');
                        $res = true;
                        $transaction->commit();
                    }catch (\Exception $e){
                        echo $e->getMessage();
                        $transaction -> rollBack();
                        $res = false;
                        throw $e;
                    }
                    if ($res){
                        echo "User ID : ".$model->id." 执行成功\r\n";
                    }
                }
            }
        }
        echo date('Y-m-d H:i:s')." 队列结束\r\n";
//        ob_flush();
    }

}