<?php

namespace backend\controllers;

use backend\models\CallCenterCompanyForm;
use common\models\CallCenterAssignCompany;
use common\models\Company;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CallCenterAssignCompanyController extends BaseController
{

    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['ajax-create', 'validation','company'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-create', 'validation', 'company'],
                        'allow' => true,
                        'roles' => ['call-center/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionAjaxCreate()
    {
        $model = new CallCenterCompanyForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save())
            {
                $company = (new Query())
                    ->select(['c.name', 'c.id', 'ca.id as aid'])
                    ->from(['ca' => CallCenterAssignCompany::tableName()])
                    ->innerJoin(['c' => Company::tableName()], 'c.id = ca.company_id')
                    ->where(['ca.call_center_id' => $model->call_center_id])->orderBy('aid ASC')->all();
                return ['status' => 200, 'company' => $this->serializeData($company)];
            }
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionValidation()
    {
        $model = new CallCenterCompanyForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $this->serializeData($model)];
    }

    public function actionAjaxDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->delete())
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => '操作错误。'];
    }

    /**
     * @param $id
     * @return CallCenterAssignCompany
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = CallCenterAssignCompany::findOne($id);
        if (null == $model)
        {
            throw new NotFoundHttpException('找不到指定数据！');
        }
        return $model;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
