<?php
namespace backend\controllers;

use common\models\Channel;
use common\models\Source;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdminChannelController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => [
                    'status',
                    'delete',
                    'sort',
                    'ajax-list',
                    'create',
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'status', 'delete', 'sort'],
                        'allow' => true,
                        'roles' => ['customer-source/list'],
                    ],
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {
        /** @var Query $query */
        $query = Channel::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['sort' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('list', [
            'provider' => $provider,
        ]);
    }

    public function actionCreate($is_validate = 0)
    {
        $model = new Channel();
        $model->loadDefaultValues();
        if($model->load(Yii::$app->request->post()))
        {
            /** @var Channel $max */
            $max = Channel::find()->select('max(sort) as sort')->one();
            $model->sort = $max->sort+1;
            if ($is_validate)
            {
                return ActiveForm::validate($model);
            }
            if($model->save())
            {
                return ['status' => 200];
            }
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->status = $status;
        if($model->validate(['status']))
        {
            $model->save(false);
            return ['status' => 200];
        }
        $errors = $model->getFirstErrors();
        return ['status' => 400, 'message' => reset($errors)];
    }

    public function actionSort()
    {
        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');

        $source = $this->findModel($source_id);
        $target = $this->findModel($target_id);

        // 交换两个的排序序号
        $sort = $target->sort;
        $target->sort = $source->sort;
        $source->sort = $sort;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->hasCustomer())
        {
            return ['status' => 400, 'message' => '对不起，当前来源下存在数据，不能被删除！'];
        }
        if($model->delete())
        {
            return ['status' => 200];
        }
        return ['status' => 400, 'message' => '您的操作有误'];
    }

    public function actionAjaxList($keyword = null, $is_flag = '0')
    {
        $query = Source::find()->select(['id', 'name']);
        //列表查询显示所有来源，其他新增客户和编辑客户时显示生效来源
        if($is_flag == '1')
        {
            $query->andWhere(['status' => Source::STATUS_ACTIVE]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->orderBy(['sort' => SORT_ASC])->all();
        return ['status' => 200, 'source' => $this->serializeData($data)];
    }

    private function findModel($id)
    {
        $model = Channel::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到CRM来源渠道');
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
