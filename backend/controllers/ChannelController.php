<?php
namespace backend\controllers;

use common\models\Channel;
use yii\filters\AccessControl;


class ChannelController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    //渠道列表
    public function actionList()
    {
        $CrmClue =  new Channel;
        $request = $CrmClue->getList ();

        return $this->resPonse('200','查询成功',$request);
    }
}