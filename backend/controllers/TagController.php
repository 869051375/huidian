<?php

namespace backend\controllers;

use backend\models\CustomerTagForm;
use backend\models\OpportunityTagForm;
use backend\models\TagForm;
use common\models\Administrator;
use common\models\Tag;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\Response;


class TagController extends BaseController
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
                'only' => [
                    'add',
                    'validation',
                    'update',
                    'ajax-list',
                    'add-batch-customer-tag',
                    'add-batch-opportunity-tag',
                    'cancel-batch-customer-tag',
                    'cancel-batch-opportunity-tag',
                    'tag-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['add', 'validation', 'update'],
                        'allow' => true,
                        'roles' => ['tag/update'],
                    ],
                    [
                        'actions' => [
                            'tag-list',
                            'ajax-list',
                            'add-batch-customer-tag',
                            'add-batch-opportunity-tag',
                            'cancel-batch-customer-tag',
                            'cancel-batch-opportunity-tag'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 新增标签
     */
    public function actionAdd()
    {
        $model = new TagForm();
        // $model->setScenario('insert');
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->save())
            {
                /** @var Administrator $administrator */
                $administrator = Yii::$app->user->identity;
                $data = Tag::find()->where(['type' => (int)Yii::$app->request->post()['type'], 'company_id' => $administrator->company_id])->all();
                return ['status' => 200, 'data' => $this->serializeData($data)];
            }
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    /**
     * 更新客户标签
     */
    public function actionUpdate()
    {
        $model = new TagForm();
        $model->setScenario('update');
        $data = Yii::$app->request->post();
        $model->full_names = $data['postData'];
        if (Yii::$app->request->isAjax)
        {
            if($model->update())
            {
                /** @var Administrator $administrator */
                $administrator = Yii::$app->user->identity;
                $data = Tag::find()->where(['type' => Tag::TAG_CUSTOMER, 'company_id' => $administrator->company_id])->all();
                return ['status' => 200, 'data' => $this->serializeData($data)];
            }
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionAddBatchCustomerTag()
    {
        $model = new CustomerTagForm();
        $model->setScenario('add');
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->save())
            {
                return ['status' => 200, 'message' => '标签应用成功'];
            }
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionCancelBatchCustomerTag()
    {
        $model = new CustomerTagForm();
        $model->setScenario('cancel');
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->cancel())
            {
                return ['status' => 200, 'message' => '标签取消成功'];
            }
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionAddBatchOpportunityTag()
    {
        $model = new OpportunityTagForm();
        $model->setScenario('add');
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->save())
            {
                return ['status' => 200, 'message' => '标签应用成功'];
            }
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionCancelBatchOpportunityTag()
    {
        $model = new OpportunityTagForm();
        $model->setScenario('cancel');
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->cancel())
            {
                return ['status' => 200, 'message' => '标签取消成功'];
            }
            return ['status' => 400, 'message' => reset($model->getFirstErrors())];
        }
        return ['status' => 403, 'message' => '您的请求有误'];
    }

    public function actionAjaxList($type, $status = '0')
    {
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;
        $query = Tag::find()->where(['type' => (int)$type]);
        if($administrator->company_id > 0)
        {
            //没有公司的人：在所有的场景，显示和应用所有的标签；根据权限可以维护（编辑和新增）所有的标签内容；
            //有公司的人：在所有的场景，显示和应用所有的标签（自己公司的和无公司字段的标签）；根据权限可以编辑和新增自己本公司的标签内容；
            if($status == 1)
            {
                $query->andWhere(['company_id' =>  $administrator->company_id]);
            }
            else
            {
                $query->andWhere(['or', ['company_id' =>  $administrator->company_id], ['company_id' => 0]]);
            }
        }
        $data = $query->all();
        return ['status' => 200, 'data' => $this->serializeData($data)];
    }

    public function actionTagList($type, $keyword = null)
    {
        $query = Tag::find()->select(['id', 'name'])->where(['type' => $type]);
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }

    /**
     * @return array
     */
    public function actionValidation()
    {
        $model = new TagForm();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) return ActiveForm::validate($model);
        return [];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}