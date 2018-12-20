<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/8/23
 * Time: 下午5:04
 */

namespace console\controllers;


use common\models\ReleaseCoupon;
use common\models\User;
use Yii;
use yii\console\Controller;
use yii\redis\Connection;

class ReleaseCouponController extends Controller
{
    public function actionRelease()
    {
        $time = time();
        while(true)
        {
            //sleep(3);
            if(time() - $time > 59)
            {
                break;
            }
            /** @var Connection $redis */
            $redis = Yii::$app->get('redis');
            $data = unserialize($redis->rpop(ReleaseCoupon::TAKE_COUPON_QUEUE_KEY));
            if($data)
            {
                $model = new ReleaseCoupon();
                $model->user = User::findOne($data['user_id']);
                $model->coupon_id = $data['coupon_id'];
                $model->source = $data['source'];
                if($model->release())
                {
                    if(!empty($data['key']))
                    {
                        $redis->set($data['key'], serialize(['isSuccess' => true]));
                    }
                }
                else
                {
                    if(!empty($data['key']))
                    {
                        $redis->set($data['key'], serialize(['isSuccess' => false, 'message' => reset($model->getFirstErrors())]));
                    }
                }
            }
        }
    }
}