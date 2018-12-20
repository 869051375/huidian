<?php

namespace backend\controllers;

use backend\models\SignupForm;
use backend\models\UserSearch;
use common\models\CrmCustomer;
use common\models\User;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UserController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['user/list'],
                    ],
                    [
                        'actions' => ['vest-list','create'],
                        'allow' => true,
                        'roles' => ['user/vest-list'],
                    ],
                    [
                        'actions' => ['create','validation'],
                        'allow' => true,
                        'roles' => ['user/create'],
                    ],
                    [
                        'actions' => ['info'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * register 客户列表
     */
    public function actionList()
    {
        return $this->searchUser(User::VEST_NO,'list');
    }

    public function actionVestList()
    {
        return $this->searchUser(User::VEST_YES,'vest_list');
    }

    private function searchUser($vest,$path)
    {
        $mode = new SignupForm();
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$vest);
        /** @var Query $query */
        $query = $dataProvider->query;
        $query->orderBy(['u.created_at'=>SORT_DESC]);
        return $this->render($path, [
            'mode' => $mode,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInfo($id)
    {
        /** @var CrmCustomer $c */
        $c = CrmCustomer::find()->where(['user_id' => $id])->one();
        if(null == $c)
        {
            throw new NotFoundHttpException('找不到客户');
        }
        return $this->redirect(['customer-detail/business-subject', 'id' => $c->id]);
    }

    /**
     * @return Response
     */
    public function actionCreate()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()))
        {
            if ($user = $model->signup())
            {
                if (Yii::$app->getUser())
                {
                    Yii::$app->session->setFlash('success', '保存成功!');
                    if($model->is_vest)
                    {
                        return $this->redirect(['vest-list']);
                    }
                    return $this->redirect(['list']);
                }
            }
        }
        if($model->is_vest) return $this->redirect(['vest-list']);
        return $this->redirect(['list']);
    }

    public function actionValidation()
    {
        $model = new SignupForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }
}
