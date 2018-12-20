<?php
namespace backend\controllers;

use backend\models\MessageRemindDeleteForm;
use backend\models\MessageRemindReadForm;
use common\models\Administrator;
use common\models\MessageRemind;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * MessageRemind controller
 */
class MessageRemindController extends BaseController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['check','list','show','read', 'batch-delete', 'batch-read'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * 客户提醒列表
     */
    public function actionList()
    {
        $query = MessageRemind::find()->where(['receive_id' => Yii::$app->user->id]);
        $query->orderBy(['created_at' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

//查询
    public function actionCheck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;//转json
        /** @var Administrator $administrator */
        $administrator = Yii::$app->user->identity;//获取用户身份
        $query = MessageRemind::find();//获取数据
        //数据的筛选
        $noReadCount = $query->where(['is_read' => 0, 'receive_id' => $administrator->id])->count();
        $totalCount = $query->where(['receive_id' => $administrator->id])->count();
        /** @var MessageRemind[] $notShowModels */
        $notShowModels = $query->where(['is_show' => MessageRemind::STATUS_NOT_SHOW, 'receive_id' => $administrator->id])->orderBy(['created_at' => SORT_ASC])->all();
        $popupData = [];
        if(null != $notShowModels)
        {
            foreach ($notShowModels as $popup)
            {
                $popupData[] = [
                    'popup_id' => $popup->id,
                    'popup_message' => $popup->popup_message,
                ];
            }
        }
        $data = [
            'total_count' => $totalCount,
            'no_read_count' => $noReadCount,
            'popup' => $popupData,
            ];
        return ['status' => 200, 'code' => 200, 'data' => $this->serializeData($data)];
    }

    public function actionShow($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->is_show = MessageRemind::STATUS_SHOW;
        if($model->save(false))
        {
            return ['status' => 200];
        }
        return ['status' => 400];
    }

    // 查看详情已读
    public function actionRead($id)
    {
        $model = $this->findModel($id);
        $model->is_read = MessageRemind::STATUS_READ;
        if($model->save(false))
        {
            if ($model->type_url == MessageRemind::TYPE_URL_ORDER_DETAIL)
            {
                return $this->redirect(['order/info', 'id' => $model->order_id]);
            }
            elseif ($model->type_url == MessageRemind::TYPE_URL_USER_DETAIL)
            {
                return $this->redirect(['crm-vue/#/CusEntDetail', 'id' => $model->customer_id]);
            }
            elseif ($model->type_url == MessageRemind::TYPE_URL_USER_NEED_CONFIRM)
            {
                return $this->redirect(['crm-customer/list', 'range' => 'need_confirm']);
            }
            elseif ($model->type_url == MessageRemind::TYPE_URL_OPPORTUNITY_NEED_CONFIRM)
            {
                return $this->redirect(['opportunity/list', 'range' => 'need_confirm']);
            }
            elseif ($model->type_url == MessageRemind::TYPE_URL_OPPORTUNITY_DETAIL)
            {
                return $this->redirect(['crm-vue/#/OppDetail', 'id' => $model->opportunity_id]);
            }
            elseif ($model->type_url == MessageRemind::TYPE_URL_RECEIPT)
            {
                return $this->redirect(['receipt/list']);
            }
            elseif ($model->type_url == MessageRemind::TYPE_URL_ORDER_LIST)
            {
                return $this->redirect(['order-list/all']);
            }
        }
        return $this->redirect(['list']);
    }

    public function actionBatchDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new MessageRemindDeleteForm();
        if($model->load(Yii::$app->request->post(),''))
        {
            if($model->batchDelete())
            {
                return ['status' => 200];
            }
            else
            {
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        return ['status' => 403, 'message' => '您的操作有误'];
    }

    public function actionBatchRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new MessageRemindReadForm();
        if($model->load(Yii::$app->request->post(),''))
        {
            if($model->batchRead())
            {
                return ['status' => 200];
            }
            else
            {
                $errors = $model->getFirstErrors();
                return ['status' => 400, 'message' => reset($errors)];
            }
        }
        return ['status' => 403, 'message' => '您的操作有误'];
    }

    /**
     * @param int $id
     * @return MessageRemind
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        $model = MessageRemind::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的消息!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}
