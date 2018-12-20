<?php
namespace backend\controllers;

use common\models\CrmDepartment;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CrmDepartmentController extends BaseController
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
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['delete', 'update','detail','ajax-list'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['department/list'],
                    ],
                    [
                        'actions' => ['create','upload'],
                        'allow' => true,
                        'roles' => ['department/create'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['department/delete'],
                    ],
                    [
                        'actions' => ['update', 'detail','upload'],
                        'allow' => true,
                        'roles' => ['department/update'],
                    ],
                ],
            ],
        ];
    }

    // 获得所有部门列表，如果传入id，则输出下级部门
    public function actionList($company_id, $id = 0, $child_id = 0)
    {
        /** @var CrmDepartment[] $departments */
        $departments = CrmDepartment::find()->where(['parent_id' => 0, 'company_id' => $company_id])
            ->andWhere(['status' => CrmDepartment::STATUS_ACTIVE])
            ->orderBy(['created_at' => SORT_ASC])->all();

        return $this->render('list', [
            'departments' => $departments,
            'id' => $id,
            'child_id' => $child_id,
            'company_id' => $company_id,
        ]);
    }

    // 创建一个部门
    public function actionCreate()
    {
        $model = new CrmDepartment();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            $model->reward_proportion_id = $model->reward_proportion_id ? $model->reward_proportion_id : 0;
            $model->save(false);
            Yii::$app->session->setFlash('success', '保存成功!');
            $id = $model ? $model->id : 0;
            $child_id = 0;
            if($model->parent)
            {
                $id = $model->parent_id;
                $child_id = $model->id;
                if($model->parent->parent)
                {
                    $id = $model->parent->parent_id;
                    $child_id = $model->parent_id;
                }
            }
            return $this->redirect(['list', 'id' => $id, 'child_id' => $child_id, 'company_id' => $model->company_id]);
        }
        Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));

        return $this->redirect(['list', 'company_id' => $model->company_id]);
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        return [
            'status' => 200,
            'model' => $this->serializeData($model),
            'leader' => $this->serializeData($model->leader),
            'assign_administrator' => $this->serializeData($model->assignAdministrator),
            'departmentManagers' => $this->serializeData($model->departmentManagers),
            'rewardProportion' => $this->serializeData($model->rewardProportion),
        ];
    }

    // 更新部门
    public function actionUpdate($id, $company_id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if($model->save())
            {
                Yii::$app->session->setFlash('success', '保存成功!');
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($model->getFirstErrors()));
            }
        }
        $path = explode('-', $model->path);
        $parent_id = isset($path[0]) ? $path[0] : 0;
        $child_id = isset($path[1]) ? $path[1] : 0;
        return $this->redirect(['list', 'id' => $parent_id, 'child_id' => $child_id, 'company_id' => $company_id]);
//        return $this->redirect(['list', 'id' => !empty($model->parent_id) ? $model->parent_id : $model->id]);
    }

    // 删除部门
    public function actionDelete()
    {
        // post: id
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        if($model->canDisable())
        {
            $model->status = CrmDepartment::STATUS_DISABLED;
            $model->save(false);
            return ['status' => 200];
        }

        return ['status' => 400, 'message' => '该部门不能删除，可能包含子部门或人员'];
    }

    // 加载一个部门，当找不到时抛出异常
    private function findModel($id)
    {
        /** @var CrmDepartment $model */
        $model = CrmDepartment::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的部门!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
    //部门ajax
    public function actionAjaxList($company_id = null, $parent_id = null, $keyword = null, $level = 0)
    {
        $query = CrmDepartment::find()->select(['id', 'name', 'parent_id', 'level'])
            ->where(['status' => CrmDepartment::STATUS_ACTIVE])
            ->orderBy(['path' => SORT_ASC]);
        if(!empty($company_id))
        {
            $query->andWhere(['company_id' => $company_id]);
        }
        if(null !== $parent_id)
        {
            $query->andWhere(['parent_id' => $parent_id]);
        }
        if($level > 0 && $level < 4)
        {
            $query->andWhere(['level' => $level]);
        }
        if(!empty($keyword))
        {
            $query->andWhere(['like', 'name', $keyword]);
        }
        $data = $query->all();
        return ['status' => 200, 'items' => $this->serializeData($data)];
    }
}