<?php

namespace console\controllers;

use common\jobs\SendSmsJob;
use common\models\User;
use shmilyzxt\queue\base\Queue;
use yii\console\Controller;
use yii\log\Logger;

class NySmsController extends Controller
{
    // 给所有客户发送新年短信（临时使用）
    public function actionDo()
    {
        try
        {
            /** @var Queue $queue */
            $queue = \Yii::$app->get('queue', false);
            $users = User::find()->select(['phone'])->asArray()->all();
            foreach($users as $user)
            {
                try
                {
                    $queue->pushOn(new SendSmsJob(),['phone' => $user['phone'],
                        'sms_id' => '235200', 'data' => []
                    ], 'sms');
                }
                catch (\Exception $e)
                {
                    \Yii::getLogger()->log('这个手机号的新年短信发送失败了：'.$user['phone'], Logger::LEVEL_ERROR);
                }
            }
        }
        catch (\Exception $e)
        {
            \Yii::getLogger()->log('新年节日短信发送失败。', Logger::LEVEL_ERROR);
        }
    }
}
