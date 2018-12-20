<?php

namespace backend\controllers;

use common\components\QCC;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;

class SearchCompanyController extends BaseController
{
    public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['list','detail'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $company
     * @return array
     */
    public function actionList($company)
    {
        /**@var $qcc QCC**/
        $qcc = Yii::$app->get('qcc');
        $resultData = $qcc->apiECISimpleSearch($company);
        if($resultData->status == 200)
        {
            return ['status' => 200,'data' => $resultData->result];
        }
        return ['status' => 400,'data' => $resultData->message];
    }

    /**
     * @param $id
     * @return array
     */
    public function actionDetail($id)
    {
        /**@var $qcc QCC**/
        $qcc = Yii::$app->get('qcc');
        $resultData = $qcc->apiECISimpleGetDetailsByName($id);
        if($resultData)
        {
            return $resultData;
        }
        return ['status' => 400];
    }

}
