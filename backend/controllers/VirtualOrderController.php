<?php

namespace backend\controllers;

use common\models\CostItem;
use common\models\ExpectedProfitSettlementDetail;
use common\models\Order;
use common\models\OrderRecord;
use common\models\PerformanceStatistics;
use common\models\VirtualOrder;
use common\utils\BC;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class VirtualOrderController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['order','record','expected-cost'],
                        'allow' => true,
                        'roles' => ['virtual-order-list/list'],
                    ],
                    [
                        'actions' => ['expected-cost'],
                        'allow' => true,
                        'roles' => ['virtual-order/expected-cost-list'],
                    ],
                    [
                        'actions' => ['cost'],
                        'allow' => true,
                        'roles' => ['virtual-order/cost-list'],
                    ],
                    [
                        'actions' => ['turnover','performance-turnover'],
                        'allow' => true,
                        'roles' => ['virtual-order/turnover'],
                    ],
                    [
                        'actions' => ['score'],
                        'allow' => true,
                        'roles' => ['virtual-order/score'],
                    ],
                ],
            ],
        ];
    }

    // 子订单列表
    public function actionOrder($vid, $status = 'pending-payment')
    {
        $model = $this->findModel($vid);
        $query = Order::find()->alias('o')->where(['virtual_order_id' => $model->id]);
        if($status == 'pending-payment')
        {
            $query->andWhere(['o.payment_amount' => 0]);
        }
        elseif($status == 'unpaid')
        {
            $sql = "`o`.`price` <> `o`.`payment_amount`";
            $query->andWhere(['>','o.payment_amount',0])->andWhere($sql);
        }
        elseif($status == 'already-payment')
        {
            $sql = "`o`.`payment_amount`=`o`.`price`";
            $query->andWhere($sql);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('order',['model' => $model,'status' => $status,'provider' => $provider]);
    }

    // 虚拟订单操作记录
    public function actionRecord($vid)
    {
        $model = $this->findModel($vid);
        /** @var Order[] $orders */
        $orders = Order::find()->where(['virtual_order_id' => $model->id])->asArray()->all();
        $order_ids = ArrayHelper::getColumn($orders,'id');
        $query = OrderRecord::find()->where(['in','order_id',$order_ids]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('record',['model' => $model,'provider' => $provider]);
    }

    // 虚拟订单预计利润
    public function actionExpectedCost($vid)
    {
        $model = $this->findModel($vid);
        $query = CostItem::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);
        return $this->render('expected-cost',['model' => $model,'provider' => $provider]);
    }

    // 虚拟订单实际利润
    public function actionCost($vid)
    {
        $model = $this->findModel($vid);
        $query = CostItem::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);
        return $this->render('cost',['model' => $model,'provider' => $provider]);
    }

    // 虚拟订单业绩
    public function actionScore($vid)
    {
        $model = $this->findModel($vid);
        $query = Order::find()->alias('o')->where(['virtual_order_id' => $model->id]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('score',['model' => $model,'provider' => $provider]);
    }

    // 订单流水（虚拟订单）
    public function actionTurnover($vid)
    {
        $model = $this->findModel($vid);
        $query = ExpectedProfitSettlementDetail::find()->where(['virtual_order_id' => $vid]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);
        /** @var ExpectedProfitSettlementDetail[] $records */
        $records = $query->all();
        $total = [];
        if($records)
        {
            foreach ($records as $record)
            {
                $key = $record->administrator_id ? $record->administrator_id : $record->department_id;
                if(!isset($total[$key]))
                {
                    $total[$key] = [
                        'administrator_name' => $record->administrator_name ? $record->administrator_name : $record->department_name,
                        'price' => $record->expected_profit,
                    ];
                }
                else
                {
                    $total[$key]['price'] = BC::add($record->expected_profit,$total[$key]['price']);
                }
            }
        }

        return $this->render('turnover',[
            'total' => $total,
            'model' => $model,
            'provider' => $provider
        ]);
    }

    // 订单业绩流水（虚拟订单）
    public function actionPerformanceTurnover($vid)
    {
        $model = $this->findModel($vid);
        $query = PerformanceStatistics::find()->where(['virtual_order_id' => $vid]);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ],
            'pagination' => [
                'pageSize' => 8,
            ],
        ]);

        //
        $records = $query->all();
        $total = [];
        if($records)
        {
            foreach ($records as $record)
            {
                $key = $record->administrator_id ? $record->administrator_id : $record->department_id;
                if(!isset($total[$key]))
                {
                    $total[$key] = [
                        'administrator_name' => $record->administrator_name ? $record->administrator_name : $record->department_name,
                        'price' => $record->performance_reward,
                    ];
                }
                else
                {
                    $total[$key]['price'] = BC::add($record->performance_reward,$total[$key]['price']);
                }
            }
        }
        return $this->render('performance-turnover',[
            'total' => $total,
            'model' => $model,
            'provider' => $provider
        ]);
    }

    private function findModel($id)
    {
        $model = VirtualOrder::findOne($id);
        if(null == $model)
        {
            throw new NotFoundHttpException('找不到指定的虚拟订单');
        }
        return $model;
    }

}