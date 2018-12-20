<?php
namespace backend\controllers;

use common\models\Source;
use yii\filters\AccessControl;


class ClueSourceController extends ApiController
{

    public $enableCsrfValidation = false;
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
        $CrmClue =  new Source();
        $request = $CrmClue->find()->where(['status'=>1])->orderBy('sort asc')->all();

        return $this->resPonse(self::SUCCESS,'查询成功',$request);
    }

}