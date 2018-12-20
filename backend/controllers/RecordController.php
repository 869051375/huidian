<?php

namespace backend\controllers;

use common\models\CheckNameRecord;
use common\models\SearchKeywords;
use common\models\TrademarkSearchRecord;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class RecordController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['search'],
                        'allow' => true,
                        'roles' => ['record/search'],
                    ],
                    [
                        'actions' => ['check-name'],
                        'allow' => true,
                        'roles' => ['record/check-name'],
                    ],
                    [
                        'actions' => ['trademark'],
                        'allow' => true,
                        'roles' => ['record/trademark'],
                    ],
                ],
            ],
        ];
    }

    public function actionSearch()
    {
        $query = SearchKeywords::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
        ]);
        return $this->render('search', ['provider' => $provider]);
    }

    public function actionCheckName()
    {
        $query = CheckNameRecord::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
        ]);
        return $this->render('check-name', ['provider' => $provider]);
    }

    public function actionTrademark()
    {
        $query = TrademarkSearchRecord::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
        ]);
        return $this->render('trademark', ['provider' => $provider]);
    }
}