<?php

namespace backend\controllers;

use backend\models\HolidaysForm;
use common\models\Holidays;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * HolidaysController implements the CRUD actions for Holidays model.
 */

/**
 * Created by PhpStorm.
 * User: xinqiangWang
 * Date: 2017/2/27
 * Time: 14:28
 */
class HolidaysController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
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
                'only' => ['delete', 'validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'detail'],
                        'allow' => true,
                        'roles' => ['holidays/list'],
                    ],
                    [
                        'actions' => ['create', 'validation'],
                        'allow' => true,
                        'roles' => ['holidays/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['holidays/delete'],
                    ],
                    [
                        'actions' => ['update', 'validation'],
                        'allow' => true,
                        'roles' => ['holidays/update'],
                    ],

                ],
            ],
        ];
    }

    public function actionList($year = null)
    {
        $query = Holidays::find();
        $query->select(['year', 'holidays']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['year' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $holidays = null;
        if($year)
        {
            $holidays = $this->findModel($year);
        }
        return $this->render('list', [
            'provider' => $provider,
            'holidays' => $holidays,
        ]);
    }

    // 新增年度工作日
    public function actionCreate()
    {
        $model = new Holidays();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->setFlash('success', '保存成功!');
            $model->save(false);
            return $this->redirect(['list']);
        }
        $errors = $model->getFirstErrors();
        Yii::$app->session->setFlash('error', reset($errors));
        return $this->redirect(['list']);
    }

    public function actionValidation()
    {
        $model = new Holidays();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($year)
    {
        $model = $this->findModel($year);

        $partnerData = $this->serializeData($model);
        return ['status' => 200, 'model' => $partnerData];
    }

    // 更新年度工作日
    public function actionUpdate()
    {
        $form = new HolidaysForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $model = $this->findModel($form->year);
            $model->setDays($form->holidays);
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
        } else {
            $errors = $form->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list', 'year' => $form->year]);
    }

    // 删除年度工作日
    public function actionDelete()
    {
        $year = Yii::$app->getRequest()->post('year');
        $model = $this->findModel($year);
        $model->delete();
        return ['status' => 200];
    }

    // 加载一个年度工作日，当找不到时抛出异常
    private function findModel($year)
    {
        $model = Holidays::findOne($year);

        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的年度工作日！');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}