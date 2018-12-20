<?php
namespace backend\controllers;


use common\models\Administrator;
//use common\models\MonthProfitRecord;
use yii\filters\AccessControl;

class CrmController extends BaseController
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
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;

        return $this->render('index',[
            'administrator'=>$administrator

        ]);
    }

    private function getYearMonth()
    {
        $data = [];
        $record = MonthProfitRecord::getLastRecord();

        $date = date('Y-m',time());

        $data['range_end_time'] = $record -> range_end_time;
        $data['year'] =  mb_substr($date,0,4);
        $data['month'] = mb_substr($date,5,2);

        return $data;
    }
}