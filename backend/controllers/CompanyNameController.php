<?php
namespace backend\controllers;

use common\models\CompanyName;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyNameController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'delete',
                ],
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
                        'roles' => ['company-name/list'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['company-name/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['company-name/delete'],
                    ],
                ],
            ],
        ];
    }

    /*
     * 列表
     */
    public function actionList($category_id)
    {
        /** @var Query $query */
        $query = CompanyName::find();
        $query->where(['category_id' => $category_id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
            'category_id' => $category_id,
        ]);
    }

    /*
     * 新增
     */
    public function actionCreate($is_validate = 0)
    {
        $model = new CompanyName();
        $model->loadDefaultValues();
        if($model->load(Yii::$app->request->post()))
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($is_validate)
            {
                return ActiveForm::validate($model);
            }
            if($model->save())
            {
                return ['status' => 200];
            }
            $errors = $model->getFirstErrors();
            return ['status' => 400, 'message' => reset($errors)];
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /*
     * 删除
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    private function findModel($id)
    {
        $model = CompanyName::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到公司名'.$id);
        }
        return $model;
    }
}
