<?php

namespace backend\controllers;

use backend\models\OpportunityAssignDepartmentForm;
use common\models\Company;
use common\models\CrmDepartment;
use common\models\OpportunityAssignDepartment;
use common\models\Product;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class OpportunityAssignDepartmentController extends BaseController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete','validation'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'add', 'validation', 'delete'],
                        'allow' => true,
                        'roles' => ['product/update'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $product_id
     * @return string
     */
    public function actionList($product_id)
    {
        $model = $this->findModel($product_id);
        $query = (new Query())
            ->select(['c.name as company_name', 'd.name as department_name', 'o.id', 'o.product_id', 'o.company_id'])
            ->from(['p' => Product::tableName()])
            ->innerJoin(['o' => OpportunityAssignDepartment::tableName()], 'p.id = o.product_id')
            ->innerJoin(['c' => Company::tableName()], 'c.id = o.company_id')
            ->innerJoin(['d' => CrmDepartment::tableName()], 'd.id = o.department_id')
            ->where(['o.product_id' => $model->id]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', ['provider' => $provider, 'product' => $model]);
    }

    /**
     * 新增商机分配部门
     */
    public function actionAdd()
    {
        $model = new OpportunityAssignDepartmentForm();
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate())
        {
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '添加成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($errors) : '添加失败!');
            }
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', $model->hasErrors() ? reset($errors) : '添加失败!');
        }
        return $this->redirect(['list', 'product_id'=> $model->product_id]);
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new OpportunityAssignDepartmentForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDelete()
    {
        $p_id = Yii::$app->getRequest()->post('p_id');
        $company_id = Yii::$app->getRequest()->post('company_id');
        if(OpportunityAssignDepartment::deleteAll(['product_id' => $p_id,'company_id' => $company_id]))
        {
            return ['status' => 200];
        }
        return ['status' => 400];
    }

    /**
     * @param $id
     * @return Product
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = Product::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的商品!');
        }
        return $model;
    }
}