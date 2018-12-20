<?php

namespace backend\controllers;

use common\models\CostItem;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OrderCostController extends BaseController
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'delete' => ['POST'],
                ],
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'only' => ['create','delete', 'validation', 'detail'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'validation','delete','update', 'validation', 'detail'],
                        'allow' => true,
                        'roles' => ['order-cost/*'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate($cid = 0)
    {
        if($cid)
        {
            $costItem = $this->findModel($cid);
            $costItem->setScenario('edit');
        }
        else
        {
            $costItem = new CostItem();
            $costItem->setScenario('insert');
        }
        $save = Yii::$app->request->post('save');
        if($costItem->load(Yii::$app->request->post()) && $costItem->validate())
        {
            if($costItem->save())
            {
                return ['status' => 200,'item' => $costItem,'num' => CostItem::getCostCount(), 'save' => $save ,'cid' => $cid];
            }
        }
        $errors = $costItem->getFirstErrors();
        return ['status' => 400,'message' => reset($errors)];
    }

    public function actionValidation()
    {
        $model = new CostItem();
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())){
            return ActiveForm::validate($model);
        }
        return [];
    }

    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $proportionData = $this->serializeData($model);
        return ['status' => 200, 'model' => $proportionData];
    }

    // 删除成本类型
    public function actionDelete()
    {
        $id = Yii::$app->getRequest()->post('id');
        $model = $this->findModel($id);
        $model->delete();
        return ['status' => 200,'num' => CostItem::getCostCount()];
    }

    // 加载一个成本类型时，当找不到时抛出异常
    private function findModel($id)
    {
        $model = CostItem::findOne($id);
        if (null == $model) {
            throw new NotFoundHttpException('找不到指定的成本类型!');
        }
        return $model;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}