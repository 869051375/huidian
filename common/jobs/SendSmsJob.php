<?php

namespace common\jobs;

use common\components\YunTongXun;
use shmilyzxt\queue\base\JobHandler;
use shmilyzxt\queue\jobs\RedisJob;
use Yii;
use yii\log\Logger;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/4/12
 * Time: 下午5:18
 */

class SendSmsJob extends JobHandler
{
    /**
     * @param RedisJob $job
     * @param $data
     * @return boolean
     */
    public function handle($job, $data)
    {
        if($job->getAttempts() > 3){
            $this->failed($job, $data);
        }
        //$payload = $job->getPayload();
        //$payload即任务的数据，你拿到任务数据后就可以执行发短信了
        /** @var YunTongXun $yunTongXun */
        $yunTongXun = Yii::$app->get('yunTongXun');
        Yii::getLogger()->log(json_encode([$data['phone'], $data['data'], $data['sms_id']]),
            Logger::LEVEL_ERROR);
        $yunTongXun->sendTemplateSMS($data['phone'], $data['data'], $data['sms_id']);
        return false;
    }

    /**
     * @param RedisJob $job
     * @param $data
     */
    public function failed($job, $data)
    {
        $job->delete();
        //die("发了3次都失败了，算了");
    }
}