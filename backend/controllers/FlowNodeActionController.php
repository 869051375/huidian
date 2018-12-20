<?php
namespace backend\controllers;


use backend\models\FlowNodeActionForm;
use common\models\FlowNode;
use common\models\FlowNodeAction;
use Yii;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class FlowNodeActionController extends BaseController
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
                'only' => ['ajax-validation', 'ajax-info', 'ajax-delete'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ajax-create', 'ajax-validation'],
                        'allow' => true,
                        'roles' => ['flow/create'],
                    ],
                    [
                        'actions' => ['ajax-update', 'ajax-validation', 'ajax-info', 'ajax-delete'],
                        'allow' => true,
                        'roles' => ['flow/update'],
                    ]
                ]
            ]
        ];
    }

    public function actionAjaxCreate($node_id)
    {
        $node = $this->findNodeModel($node_id);
        $formModel = new FlowNodeActionForm();
        $formModel->setNodeModel($node);
        if($formModel->load(Yii::$app->request->post()) && $formModel->validate())
        {
            $action = $formModel->save();
            if($action)
            {
                if(Yii::$app->request->isAjax)
                {
                    return ['status' => 200, 'action' => $this->serializeData($action)];
                }
                else
                {
                    Yii::$app->session->setFlash('success', '保存成功!');
                    return $this->redirect(['flow-node/list', 'flow_id' => $node->flow_id, 'node_id' => $node->id]);
                }
            }
        }
        if($formModel->hasErrors())
        {
            if(Yii::$app->request->isAjax)
            {
                return ['status' => 400, 'message' => reset($formModel->getFirstErrors())];
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($formModel->getFirstErrors()));
                return $this->redirect(['flow-node/list', 'flow_id' => $node->flow_id, 'node_id' => $node->id]);
            }

        }
        return ['status' => 500];
    }

    public function actionAjaxValidation($node_id)
    {
        $node = $this->findNodeModel($node_id);
        $model = new FlowNodeActionForm();
        $model->setNodeModel($node);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionAjaxUpdate($id)
    {
        $model = $this->findModel($id);
        $formModel = new FlowNodeActionForm();
        $formModel->setModel($model);
        if($formModel->load(Yii::$app->request->post()) && $formModel->validate())
        {
            $nodeAction = $formModel->save();
            if($nodeAction)
            {
                if(Yii::$app->request->isAjax)
                {
                    return ['status' => 200, 'node' => $this->serializeData($nodeAction)];
                }
                else
                {
                    Yii::$app->session->setFlash('success', '更新成功!');
                    return $this->redirect(['flow-node/list', 'flow_id' => $formModel->flow_id, 'node_id'=>$formModel->flow_node_id]);
                }
            }
        }
        if($formModel->hasErrors())
        {
            if(Yii::$app->request->isAjax)
            {
                return ['status' => 400, 'message' => reset($formModel->getFirstErrors())];
            }
            else
            {
                Yii::$app->session->setFlash('error', reset($formModel->getFirstErrors()));
                return $this->redirect(['flow-node/list', 'flow_id' => $formModel->flow_id, 'node_id'=>$formModel->flow_node_id]);
            }
        }
        return ['status' => 500];
    }

    public function actionAjaxInfo($id)
    {
        $model = $this->findModel($id);
        $inputList = $model->getInputList();
        $data = [
            'type' => $model->type,
            'action_label' => $model->action_label,
            'action_hint' => $model->action_hint,
            'input_list' => $inputList['input_list'],
            'is_stay' => $model->is_stay,
            'sms_id' => $model->sms_id,
            'sms_preview' => $model->sms_preview,
            'has_send_var' => $model->isHasSendVar(),
            'hint_customer' => $model->getHintCustomer(),
            'hint_operator' => $model->getHintOperator(),
        ];
        return ['status' => 200, 'nodeAction' => $this->serializeData($data)];
    }

    public function actionAjaxDelete()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200];
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
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

    private function findModel($id)
    {
        $model = FlowNodeAction::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到流程节点的操作数据!');
        }
        return $model;
    }
}