<?php
namespace backend\controllers;


use backend\models\FlowNodeForm;
use common\models\Flow;
use common\models\FlowNode;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FlowNodeController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax-delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['ajax-create', 'ajax-validation', 'ajax-update', 'ajax-info', 'ajax-delete', 'ajax-sequence'],
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
                        'roles' => ['flow/list'],
                    ],
                    [
                        'actions' => ['ajax-create', 'ajax-validation'],
                        'allow' => true,
                        'roles' => ['flow/create'],
                    ],
                    [
                        'actions' => ['ajax-update', 'ajax-validation', 'ajax-info', 'ajax-delete', 'ajax-sequence'],
                        'allow' => true,
                        'roles' => ['flow/update'],
                    ]
                ]
            ]
        ];
    }

    /**
     * @param int $flow_id 节点id
     * @param null $node_id 节点的id，当不传入值时表示当前没有选中节点，如果传入则会调取节点下的操作列表数据
     * @return string
     */
    public function actionList($flow_id, $node_id = null)
    {
        $flowModel = $this->findFlowModel($flow_id);
        $nodeModel = null;
        if(null != $node_id){
            $nodeModel = $this->findModel($flowModel, $node_id);
        }
        return $this->render('list', [
            'flowModel' => $flowModel,
            'nodeModel' => $nodeModel,
        ]);
    }

    // 流程节点排序
    public function actionAjaxSequence($flow_id)
    {
        $flow = $this->findFlowModel($flow_id);
        // post: source_id, target_id
        $source_id = Yii::$app->getRequest()->post('source_id');
        $target_id = Yii::$app->getRequest()->post('target_id');

        $source = $this->findModel($flow, $source_id);
        $target = $this->findModel($flow, $target_id);

        // 交换两个流程节点的排序序号
        $sequence = $target->sequence;
        $target->sequence = $source->sequence;
        $source->sequence = $sequence;
        $target->save(false);
        $source->save(false);
        return ['status' => 200];
    }

    public function actionAjaxCreate($flow_id)
    {
        $flow = $this->findFlowModel($flow_id);
        $formModel = new FlowNodeForm();
        $formModel->setFlow($flow);
        if($formModel->load(Yii::$app->request->post()) && $formModel->validate())
        {
            $node = $formModel->save();
            if($node)
            {
                if(Yii::$app->request->isAjax)
                {
                    return ['status' => 200, 'node' => $this->serializeData($node)];
                }
                else
                {
                    Yii::$app->session->setFlash('success', '保存成功!');
                    return $this->redirect(['flow-node/list', 'flow_id' => $flow->id, 'node_id'=>$node->id]);
                }
            }
        }
        if($formModel->hasErrors())
        {
            if(Yii::$app->request->isAjax)
            {
                $errors = $formModel->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
            else
            {
                $errors = $formModel->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
                return $this->redirect(['flow-node/list', 'flow_id' => $flow->id]);
            }

        }
        return ['status' => 500];
    }

    public function actionAjaxValidation($flow_id)
    {
        $flow = $this->findFlowModel($flow_id);
        $model = new FlowNodeForm();
        $model->setFlow($flow);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionAjaxUpdate($id)
    {
        $nodeModel = $this->findNodeModel($id);
        $formModel = new FlowNodeForm();
        $formModel->setFlow($nodeModel->flow);
        $formModel->setModel($nodeModel); // 先初始化一下现有的数据，然后再load用户提交的数据
        //var_dump(Yii::$app->request->post());die;
        if($formModel->load(Yii::$app->request->post()) && $formModel->validate())
        {
            $node = $formModel->save();
            if($node)
            {
                if(Yii::$app->request->isAjax)
                {
                    return ['status' => 200, 'node' => $this->serializeData($node)];
                }
                else
                {
                    Yii::$app->session->setFlash('success', '更新成功!');
                    return $this->redirect(['flow-node/list', 'flow_id' => $nodeModel->flow_id, 'node_id'=>$node->id]);
                }
            }
        }
        if($formModel->hasErrors())
        {
            if(Yii::$app->request->isAjax)
            {
                $errors = $formModel->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
            else
            {
                $errors = $formModel->getFirstErrors();
                Yii::$app->session->setFlash('error', reset($errors));
                return $this->redirect(['flow-node/list', 'flow_id' => $nodeModel->flow_id]);
            }
        }
        return ['status' => 500];
    }

    public function actionAjaxInfo($id)
    {
        $nodeModel = $this->findNodeModel($id);
        $data = [
            'name' => $nodeModel->name,
            'is_limit_time' => $nodeModel->is_limit_time,
            'limit_work_days' => $nodeModel->limit_work_days,
            'hint_customer' => $nodeModel->getHintCustomer(),
            'hint_operator' => $nodeModel->getHintOperator(),
        ];
        return ['status' => 200, 'node' => $this->serializeData($data)];
    }

    public function actionAjaxDelete()
    {
        $id = Yii::$app->request->post('id');
        $nodeModel = $this->findNodeModel($id);
        $nodeModel->delete();
        return ['status' => 200];
    }

    /**
     * @param $id
     * @return Flow
     * @throws NotFoundHttpException
     */
    private function findFlowModel($id)
    {
        $model = Flow::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到流程数据!');
        }
        return $model;
    }

    /**
     * @param Flow $flowModel
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    private function findModel($flowModel, $id)
    {
        $model = $flowModel->getNode($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到流程节点数据!');
        }
        return $model;
    }

    private function findNodeModel($id)
    {
        $model = FlowNode::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到流程节点数据!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}