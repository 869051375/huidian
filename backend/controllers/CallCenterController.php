<?php

namespace backend\controllers;

use common\models\CallCenter;
use common\models\CallCenterAssignCompany;
use common\models\Company;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\httpclient\Client;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CallCenterController extends BaseController
{

    public $enableCsrfValidation = false;
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['call','ajax-status','validation','ajax-delete','ajax-company-delete'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list','ajax-status','ajax-delete', 'call', 'validation','update','create','ajax-company-delete'],
                        'allow' => true,
                        'roles' => ['call-center/*'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionList()
    {
        $query = CallCenter::find();
        $query->select(['id','name','status','url']);
        $query->orderBy(['id' => SORT_DESC]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    /**
     * 新增
     * @return Response
     */
    public function actionCreate()
    {
        $model = new CallCenter();
        $model->setScenario('insert');
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            Yii::$app->session->setFlash('success', '保存成功!');
            $model->save(false);
        }
        else
        {
            $errors = $model->getFirstErrors();
            Yii::$app->session->setFlash('error', reset($errors));
        }
        return $this->redirect(['list']);
    }

    /**
     * 编辑更新
     * @param int $id
     * @return string
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('update');
        if($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save(false))
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                $errors = $model->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
            }
        }
        $callCenterAssignCompany = (new Query())
            ->select(['c.name', 'c.id', 'ca.id as aid'])
            ->from(['ca' => CallCenterAssignCompany::tableName()])
            ->innerJoin(['c' => Company::tableName()], 'c.id = ca.company_id')
            ->where(['ca.call_center_id' => $id])->orderBy('aid ASC')->all();
        return $this->render('update',['model' => $model, 'callCenterAssignCompany' => $callCenterAssignCompany]);
    }

    /**
     * @return array
     */
    public function actionCall()
    {
        $data = Yii::$app->request->get();
        if($data)
        {
            $client = new Client();
            $url = trim($data['callUrl']);
            $response = $client->get($url.$data['debugging'])->send();
            if($response->getIsOk())
            {
                $jsonString = $response->getContent();
                if($jsonString == 200)
                {
                    return ['status' => 200, 'message' => '呼叫成功！'];
                }
                else
                {
                    //失败时返回json数据
                    //$jsonDecodeString = json_decode($jsonString);
                    return ['status' => 400, 'message' => '呼叫失败！'];
                }
            }
        }
        return ['status' => 403, 'message' => '呼叫失败！'];
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $id = Yii::$app->request->post('id');
        if($id)
        {
            $model = $this->findModel($id);
            $model->setScenario('update');
        }
        else
        {
            $model = new CallCenter();
            $model->setScenario('insert');
        }
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    /**
     * 状态启用-禁用操作
     * @return array
     */
    public function actionAjaxStatus()
    {
        $status = Yii::$app->getRequest()->post('status');
        $call_center_id = Yii::$app->getRequest()->post('call_center_id');
        $model = $this->findModel($call_center_id);
        if($status>0)
        {
            $model->status = CallCenter::STATUS_OFFLINE;
        }
        else
        {
            $model->status = CallCenter::STATUS_ONLINE;
        }
        if($model->validate(['status']) && $model->save(false))
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => '状态修改失败：'.$model->getFirstError('status')];
    }

    /**
     * @return array
     */
    public function actionAjaxDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost)
        {
            $companies = CallCenterAssignCompany::find()->where(['call_center_id' => $id])->all();
            if(null == $companies)
            {
                if($model->delete())
                {
                    return ['status' => 200];
                }
            }
            else
            {
                $t = Yii::$app->db->beginTransaction();
                try
                {
                    foreach ($companies as $company)
                    {
                        $company->delete();
                    }
                    $model->delete();
                    $t->commit();
                    return ['status' => 200];
                }
                catch (\Exception $e)
                {
                    $t->rollBack();
                    return ['status' => 400, 'message' => '删除失败。'];
                }
            }
        }
        return ['status' => 400, 'message' => '操作错误。'];
    }

    /**
     * @return array
     */
    public function actionAjaxCompanyDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findCallCenterAssignCompany($id);
        if(Yii::$app->request->isPost)
        {
            if($model->delete())
            {
                return ['status' => 200];
            }
        }
        return ['status' => 400, 'message' => '操作错误。'];
    }

    /**
     * @param $id
     * @return CallCenter
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = CallCenter::findOne($id);
        if (null == $model)
        {
            throw new NotFoundHttpException('找不到指定数据！');
        }
        return $model;
    }

    private function findCallCenterAssignCompany($id)
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
