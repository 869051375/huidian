<?php
namespace console\controllers;

use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\CustomerPublic;
use common\models\Holidays;
use common\models\Order;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class CustomerCheckController extends Controller
{
    //自动检查客户，满足条件放进客户公海
    public function actionRun()
    {
        //有商机的客户(未成交和申请中),并且商机不在公海
        $count = CrmCustomer::find()->select('c.id')->alias('c')
            ->innerJoinWith(['opportunities p'])
            ->andWhere(['<=','p.opportunity_public_id', 0])
            ->andWhere(['<=','c.customer_public_id', 0])
            ->andWhere(['in', 'p.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]])
            ->groupBy('c.id')
            ->count();

        $customerIds = [];
        if($count > 0)
        {
            $batchNum = 25;
            $batch = ceil($count / $batchNum);
            for($i = 0; $i < $batch; $i++)
            {
                /** @var CrmCustomer[] $models */
                $models = CrmCustomer::find()->select('c.id')->alias('c')
                    ->innerJoinWith(['opportunities p'])
                    ->andWhere(['<=','p.opportunity_public_id', 0])
                    ->andWhere(['<=','c.customer_public_id', 0])
                    ->andWhere(['in', 'p.status', [CrmOpportunity::STATUS_NOT_DEAL, CrmOpportunity::STATUS_APPLY]])
                    ->groupBy('c.id')
                    ->offset($i*$batchNum)
                    ->limit($batchNum)
                    ->all();
                $customerIds = array_merge($customerIds, ArrayHelper::getColumn($models, 'id'));
            }
            unset($models);
        }

        //有订单的客户(除了服务终止和已完成之外的订单。0:待付款、1:待分配、2:待服务、3:服务中的订单)
        $count = CrmCustomer::find()->select(['cc.id'])->alias('cc')
            ->andWhere(['cc.customer_public_id' => 0])
            ->innerJoinWith(['orders o'])
            ->andWhere(['in', 'o.status', [Order::STATUS_PENDING_PAY, Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE,Order::STATUS_IN_SERVICE, Order::STATUS_UNPAID]])
            ->groupBy('cc.id')
            ->count();

        $orderCustomerIds = [];
        if($count > 0)
        {
            $batchNum = 1000;
            $batch = ceil($count / $batchNum);
            for($i = 0; $i < $batch; $i++)
            {
                //有订单的客户(除了服务终止和已完成之外的订单。0:待付款、1:待分配、2:待服务、3:服务中的订单)
                /** @var CrmCustomer[] $models */
                $models = CrmCustomer::find()->select(['cc.id'])->alias('cc')
                    ->innerJoinWith(['orders o'])
                    ->andWhere(['cc.customer_public_id' => 0])
                    ->andWhere(['in', 'o.status', [Order::STATUS_PENDING_PAY, Order::STATUS_PENDING_ALLOT, Order::STATUS_PENDING_SERVICE,Order::STATUS_IN_SERVICE, Order::STATUS_UNPAID]])
                    ->groupBy('cc.id')
                    ->offset($i*$batchNum)
                    ->limit($batchNum)
                    ->all();
                $orderCustomerIds = array_merge($orderCustomerIds, ArrayHelper::getColumn($models, 'id'));
            }
            unset($models);
        }
        $mergeIds = ArrayHelper::merge($customerIds, $orderCustomerIds);
        unset($orderCustomerIds);
        unset($customerIds);
        $ids = array_flip(array_flip($mergeIds));
        unset($mergeIds);
        //符合条件甩出的客户
        $count = CrmCustomer::find()
            ->andWhere(['is_protect' => CrmCustomer::PROTECT_DISABLED])
            ->andWhere(['customer_public_id' => 0])
            ->andWhere(['>', 'administrator_id', 0])
            ->andWhere(['not in', 'id', $ids])
            ->count();
        $batchNum = 100;
        if($count == 0)
        {
            exit;
        }
        $batch = ceil($count / $batchNum);
        for($i = 0; $i < $batch; $i++)
        {
            /** @var CrmCustomer[] $models */
            //符合条件甩出的客户
//            $customers = CrmCustomer::find()->select(['id', 'is_receive','created_at','operation_time'])
            $customers = CrmCustomer::find()
                ->andWhere(['customer_public_id' => 0])
                ->andWhere(['is_protect' => CrmCustomer::PROTECT_DISABLED])
                ->andWhere(['>', 'administrator_id', 0])
                ->andWhere(['not in', 'id', $ids])
                ->offset($i*$batchNum)
                ->limit($batchNum)
                ->all();
            if($customers)
            {
                $nowTime = time();
                /** @var CrmCustomer $customer */
                foreach ($customers as $customer)
                {
                    if($customer->crmCustomerCombine)
                    {
                        //返回的move_time最大值时的公海， 根据客户合伙人对应公司的客户公海来执行甩客户
                        if(!$customer->isReceive())//待确认的客户多长时间没确认，自动移入公海中；
                        {
                            $maxConfirmTimeoutTimePublic = $this->maxPublic($customer->crmCustomerCombine);
                            if(!empty($maxConfirmTimeoutTimePublic) && $maxConfirmTimeoutTimePublic['confirm_timeout_time'] > 0)
                            {
                                $moveTime = Holidays::workDay($customer->created_at, $maxConfirmTimeoutTimePublic['confirm_timeout_time']);
//                            if($maxConfirmTimeoutTimePublic['confirm_timeout_time'] > 0 && $customer->created_at + $maxConfirmTimeoutTimePublic['confirm_timeout_time'] * 3600 < $nowTime)
                                if($moveTime > 0 && $moveTime < $nowTime)
                                {
                                    $customer->customer_public_id = $maxConfirmTimeoutTimePublic['id'];
                                    $customer->administrator_id = 0;
                                    $customer->is_receive = CrmCustomer::RECEIVE_DISABLED;
                                    $customer->level = CrmCustomer::CUSTOMER_LEVEL_ACTIVE;
                                    $customer->move_public_time = time();
                                    $t = Yii::$app->db->beginTransaction();
                                    try
                                    {
                                        $this->deleteCombine($customer);
                                        CrmCustomerLog::add('客户移入'. $maxConfirmTimeoutTimePublic['name'] .'客户公海', $customer->id, 0,'system',CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
                                        $customer->save(false);
                                        $t->commit();
                                    }
                                    catch (\Exception $e)
                                    {
                                        $t->rollBack();
                                        continue;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $maxMoveTimePublic = $this->maxPublic($customer->crmCustomerCombine, 'move_time');
                            if(!empty($maxMoveTimePublic) && $maxMoveTimePublic['move_time'] > 0)
                            {
                                $moveTime = Holidays::workDay($customer->operation_time, $maxMoveTimePublic['move_time']);
//                            if($maxMoveTimePublic['move_time'] > 0 && $customer->operation_time + $maxMoveTimePublic['move_time'] * 3600 < $nowTime )
                                if($moveTime > 0 && $moveTime < $nowTime )
                                {
                                    $customer->customer_public_id = $maxMoveTimePublic['id'];
                                    $customer->administrator_id = 0;
                                    $customer->is_receive = CrmCustomer::RECEIVE_DISABLED;
                                    $customer->level = CrmCustomer::CUSTOMER_LEVEL_ACTIVE;
                                    $customer->move_public_time = time();
                                    $t = Yii::$app->db->beginTransaction();
                                    try
                                    {
                                        $this->deleteCombine($customer);
                                        CrmCustomerLog::add('客户移入'.$maxMoveTimePublic['name'] .'客户公海', $customer->id, 0,'system',CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
                                        $customer->save(false);
                                        $t->commit();
                                    }
                                    catch (\Exception $e)
                                    {
                                        $t->rollBack();
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param CrmCustomer $customer
     * @return bool
     */
    private function deleteCombine($customer)
    {
        if(null != $customer->crmCustomerCombine)
        {
            foreach ($customer->crmCustomerCombine as $crmCustomerCombine)
            {
                if(!$crmCustomerCombine->delete()) return false;
            }
        }
        return true;
    }

    //获取客户合伙人中对应的公司-客户公海中的最大的那个执行时间move_time
    private function maxPublic($crmCustomerCombines, $type = null)
    {
        $public = [];
        /** @var CrmCustomerCombine $crmCustomerCombine */
        foreach ($crmCustomerCombines as $crmCustomerCombine)
        {
            $a = [];
            $b = [];
            if($crmCustomerCombine->company)
            {
                if($crmCustomerCombine->company->customerPublic)
                {
                    $public[] = $crmCustomerCombine->company->customerPublic;
                    if(!empty($public[0]))
                    {
                        /** @var CustomerPublic $customerPublic */
                        foreach ($public as $customerPublic)
                        {
                            $a['id'] = $customerPublic->id;
                            $a['move_time'] = $customerPublic->move_time;
                            $a['confirm_timeout_time'] = $customerPublic->confirm_timeout_time;
                            $a['name'] = $customerPublic->name;
                            $b[] = $a;
                        }
                    }
                }
            }
            $max = $b;
        }
        if(!empty($max))
        {
            if($type == 'move_time')
            {
                $new = array_column($max, 'move_time');
            }
            else
            {
                $new = array_column($max, 'confirm_timeout_time');
            }
            array_multisort($new,SORT_DESC,$max);
            return $max[0];
        }
        return [];
    }
}