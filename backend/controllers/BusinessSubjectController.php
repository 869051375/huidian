<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/9/13
 * Time: 下午6:02
 */

namespace backend\controllers;

use backend\models\BusinessSubjectForm;
use backend\models\BusinessSubjectSearch;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\Order;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class BusinessSubjectController extends BaseController
{
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
                'only' => ['validation','detail','info','ajax-create'],
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
                    ],
                    [
                        'actions' => ['update','validation','detail','create','validation', 'ajax-create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['natural-person','subject'],
                        'allow' => true,
                        'roles' => ['business-subject/list'],
                    ],
                    [
                        'actions' => ['info','information','order','opportunity','do-record'],
                        'allow' => true,
                        'roles' => ['business-subject/detail'],
                    ],
                ],
            ],
        ];
    }

    public function actionList($customer_id, $keyword = null)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        /** @var BusinessSubject[] $models */
        $query = BusinessSubject::find()->where(['user_id' => $customer_id])->andWhere(['>','customer_id',0]);
        if(!empty($keyword))
        {
            $query->andWhere(['or', ['like', 'company_name', $keyword], ['like', 'region', $keyword]]);
        }
        $models = $query->all();
        $data = [];
        foreach($models as $model)
        {
            if($model->subject_type)
            {
                $data[] = ['id' => $model->id, 'name' => $model->region,];
            }
            else
            {
                $data[] = ['id' => $model->id, 'name' => $model->company_name,];
            }
        }
        return ['status' => 200, 'data' => $data];
    }

    public function actionSubject($subject_type = 0)
    {
        $searchModel = new BusinessSubjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$subject_type);
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionNaturalPerson($subject_type = 1)
    {
        $searchModel = new BusinessSubjectSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$subject_type);
        return $this->render('natural_person', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate($id = 0,$subject = 0)
    {
        $model = new BusinessSubjectForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $businessSubject = $model->save();
            if($businessSubject)
            {
                Yii::$app->session->setFlash('success', '业务主体保存成功!');
                return $this->redirect(['customer-detail/business-subject','id'=>$id]);
            }
            Yii::$app->session->setFlash('error', '保存失败!');
        }
        if ($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
        }

        $customer = CrmCustomer::findOne($id);
        if(null == $customer)
        {
            throw new NotFoundHttpException('找不到客户');
        }

        return $this->render('create', [
            'id' => $id,
            'subject' => $subject,
            'model' => $model,
            'customer' => $customer,
        ]);
    }

    public function actionUpdate($id,$subject = 0)
    {
        $businessSubject = $this->findModel($id);
        $model = new BusinessSubjectForm();
        $model->setAttributes($businessSubject->attributes);
        if($model->load(Yii::$app->request->post()))
        {
            if($model->update($businessSubject))
            {
                Yii::$app->session->setFlash('success', '更新成功!');
                return $this->redirect(['business-subject/information','id'=>$businessSubject->id]);
            }
            Yii::$app->session->setFlash('error', '更新失败!');
        }
        if ($model->hasErrors())
        {
            Yii::$app->session->setFlash('error', '更新失败, 您的表单填写有误, 请检查!');
        }
        return $this->render('update', [
            'id' => $model->customer_id,
            'subject' => $subject,
            'model' => $model,
            'customer' => $businessSubject->customer,
        ]);
    }

    public function actionAjaxCreate($id = 0,$subject = 0)
    {
        $model = new BusinessSubjectForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            if($model->save())
            {
                return ['status' => 200];
            }
            else
            {
                return ['status' => 400, 'message' => reset($model->getFirstErrors())];
            }
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        return ['status' => 200, 'model' => $model];
    }

    public function actionValidation()
    {
        $model = new BusinessSubjectForm();
        $model->load(Yii::$app->request->post());
        $model->scene();
        return ActiveForm::validate($model);
    }

    private function findModel($id)
    {
        $model = BusinessSubject::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的业务主体!');
        }
        return $model;
    }

    //详细信息
    public function actionInformation($id)
    {
        $subject = $this->findModel($id);
        return $this->render('information', [
            'subject' => $subject,
        ]);
    }

    //关联订单
    public function actionOrder($id, $status = 'paid')
    {
        if(!in_array($status, ['paid', 'pending-pay', 'break']))
        {
            $status = 'paid';
        }
        $subject = $this->findModel($id);
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = null;
        if($subject)
        {
            $query = null;
            if($status == 'paid')
            {
                $query = Order::getPaidQueryBySubjectId($subject->id);
            }
            elseif($status == 'pending-pay')
            {
                $query = Order::getPendingPayQueryBySubjectId($subject->id);
            }
            else
            {
                $query = Order::getBreakQueryBySubjectId($subject->id);
            }
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'validatePage' => false,
                ],
            ]);
        }
        return $this->render('order',[
            'subject' => $subject,
            'status' => $status,
            'dataProvider' => $dataProvider
        ]);
    }

    //关联商机
    public function actionOpportunity($id, $status = '')
    {
        $subject = $this->findModel($id);

        if($status == 'deal')
        {
            $query = CrmOpportunity::find()->where(['status' => CrmOpportunity::STATUS_DEAL, 'business_subject_id' => $id]);
        }
        else
        {
            $query = CrmOpportunity::find()->where(['business_subject_id' => $id])->andWhere(['not in','status',CrmOpportunity::STATUS_DEAL]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('opportunity', [
            'dataProvider' => $dataProvider,
            'subject' => $subject,
        ]);
    }

    //操作记录
    public function actionDoRecord($id)
    {
        $subject = $this->findModel($id);
        $status = $subject->subject_type ? CrmCustomerLog::TYPE_CUSTOMER_PERSON : CrmCustomerLog::TYPE_CUSTOMER_SUBJECT;
        $query = CrmCustomerLog::find()->where(['subject_id' => $subject->id,'type' => $status])
            ->orderBy(['created_at'=>SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'validatePage' => false,
            ],
        ]);
        return $this->render('do-record', [
            'subject' => $subject,
            'dataProvider' => $dataProvider
        ]);
    }

}