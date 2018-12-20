<?php
namespace console\controllers;

use common\models\MessageRemind;
use common\models\Order;
use common\utils\Decimal;
use Yii;
use yii\console\Controller;
use yii\helpers\Url;

class EmailController extends Controller
{
    public function actionSend()
    {
        $messageReminds = MessageRemind::find()
            ->andWhere(['email_status' => MessageRemind::EMAIL_NOT_SEND, 'type' => MessageRemind::TYPE_EMAILS])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit(10)
            ->all();

        if(null != $messageReminds)
        {
            /** @var MessageRemind $messageRemind */
            foreach ($messageReminds as $messageRemind)
            {
                sleep(10);
                if(!empty($messageRemind->email))
                {
                    $order = Order::findOne($messageRemind->order_id);

                    if(null != $order)
                    {
                        $html = '@common/mail/order-info';
                        $params = ['order' => $order, 'messageRemind' => $messageRemind];
                    }
                    else
                    {
                        $html = '@common/mail/need-receipt-review';
                        $params = ['messageRemind' => $messageRemind];
                    }

                    //$user = Yii::$app->user->identity;
                    $mail = Yii::$app->mailer->compose(
                        ['html' => $html],
                        $params
                    )
                        ->setTo($messageRemind->email)
                        ->setSubject($messageRemind->message);
                    if($mail->send())
                    {
                        $messageRemind->email_status = MessageRemind::EMAIL_SEND;
                    }
                    else
                    {
                        $messageRemind->email_status = MessageRemind::EMAIL_FAIL_SEND;
                    }
                    $messageRemind->save(false);
                }
            }
        }
    }
}