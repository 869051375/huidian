<?php

namespace console\controllers;

use shmilyzxt\queue\base\Job;
use shmilyzxt\queue\base\Queue;
use yii\console\Controller;

class SmsController extends Controller
{
    // 发送短信
    public function actionSend()
    {
        $startTime = time();
        while (true)
        {
            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            if(null == $queue) break;
            try
            {
                $job = $queue->pop('sms');
            }
            catch (\Exception $e)
            {
                throw $e;
            }

            if($job instanceof Job)
            {
                if($job->getAttempts() > 3)
                {
                    $job->failed();
                }
                else
                {
                    try
                    {
                        $job->execute();
                    }
                    catch (\Exception $e)
                    {
                        if (! $job->isDeleted())
                        {
                            $job->release(10);
                        }
                    }
                }
            }
            if(time() - $startTime > 50)
            {
                break;
            }
        }
    }
}
