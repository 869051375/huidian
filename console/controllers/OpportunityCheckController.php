<?php
namespace console\controllers;

use common\models\CrmCustomer;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\Holidays;
use common\models\OpportunityPublic;
use Yii;
use yii\console\Controller;

class OpportunityCheckController extends Controller
{
    //自动检查商机，满足条件放进商机公海
    public function actionRun()
    {
        $opportunityPublices = OpportunityPublic::find()->all();
        if($opportunityPublices){
            //检查规则，1.商机是20%-80% 2.不受保护的商机
            $count = CrmOpportunity::find()
                ->where(['is_protect' => CrmOpportunity::PROTECT_DISABLED])
                ->andWhere(['opportunity_public_id' => 0])
                ->andWhere(['>=', 'progress', 20])
                ->andWhere(['<=', 'progress', 80])
                ->count();
            $batchNum = 100;
            if(empty($count))
            {
                exit;
            }
            $batch = ceil($count / $batchNum);
            for($i = 0; $i < $batch; $i++)
            {
                /** @var CrmOpportunity[] $opportunities */
                //检查规则，1.商机是20%-80% 2.不受保护的商机
                $opportunities = CrmOpportunity::find()
                    ->where(['is_protect' => CrmOpportunity::PROTECT_DISABLED])
                    ->andWhere(['opportunity_public_id' => 0])
                    ->andWhere(['>=', 'progress', 20])
                    ->andWhere(['<=', 'progress', 80])
                    ->offset($i*$batchNum)
                    ->limit($batchNum)
                    ->all();
                $nowTime = time();
                /** @var CrmOpportunity $opportunity */
                foreach ($opportunities as $opportunity)
                {
                    /** @var OpportunityPublic $opportunityPublic */
                    foreach ($opportunityPublices as $opportunityPublic)
                    {
                        if($opportunity->department)
                        {
                            //判断商机公海对应部门的本部门及下属部门的商机，执行商机公海规则
                            if($opportunity->department->parent_id == $opportunityPublic->department_id || $opportunity->department_id == $opportunityPublic->department_id )
                            {
                                $maxTime = 0;
                                //检查规则
                                // 1.1如果没有最后跟进时间，也没有提取时间，根据创建商机时间
                                if(!$opportunity->last_record && !$opportunity->extract_time)
                                {
                                    $maxTime = $opportunity->created_at;
                                }
                                //1.2有最后跟进时间，没有提取商机时间，根据最后跟进时间
                                elseif ($opportunity->last_record && !$opportunity->extract_time)
                                {
                                    $maxTime = $opportunity->last_record;
                                }
                                //1.3有最后跟进时间也有提取时间，根据最大时间判断
                                elseif ($opportunity->last_record && $opportunity->extract_time)
                                {
                                    $maxTime = $opportunity->extract_time > $opportunity->last_record ? $opportunity->extract_time : $opportunity->last_record;
                                }
                                //1.4没有最后跟进时间但有提取时间，根据提取时间判断
                                elseif (!$opportunity->last_record && $opportunity->extract_time)
                                {
                                    $maxTime = $opportunity->extract_time;
                                }

                                if($maxTime > 0)
                                {
                                    $moveTime = Holidays::workDay($maxTime, $opportunityPublic->move_time);
//                                if($maxTime + $opportunityPublic->move_time * 3600 < $nowTime)
                                    if($moveTime > 0 && $moveTime < $nowTime)
                                    {
                                        $t = Yii::$app->db->beginTransaction();
                                        try
                                        {
                                            //商机对应的客户
                                            $this->customer($opportunity);

                                            $opportunity->opportunity_public_id = $opportunityPublic->id;
                                            $opportunity->administrator_id = 0;
                                            $opportunity->administrator_name = '';
                                            $opportunity->move_public_time = time();
                                            //生成一条商机操作记录，由于商机操作记录获取时没有通过CrmCustomerLog::TYPE_CUSTOMER_OPPORTUNITY过滤，只是根据商机id获取，所以此处2条操作记录实际只需要一条，否则在商机操作中会同时出2条数据，导致重复
//                                        CrmCustomerLog::add('移入商机公海', $opportunity->customer_id, $opportunity->id,'system',CrmCustomerLog::TYPE_CUSTOMER_OPPORTUNITY);
                                            //生成一条客户操作记录(商机操作记录根据记录中是否有商机id获取)
                                            CrmCustomerLog::add('商机"'.$opportunity->id.'"移入'.$opportunityPublic->name, $opportunity->customer_id, $opportunity->id,'system',CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
                                            $opportunity->save(false);
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
    }

    /**
     * 1.如果当前客户除甩出商机之外，还有其他正在跟进中的商机，则合作人表无变化；
        反之，如果当前客户无其他正在跟进的商机，则从合作人表中删除对应合作人！且不影响当前业务员客户下的订单；
        如果当前业务员既是客户合作人又是负责人，则合作人表和负责人表无任何变化；
        (在同一个客户的前提下。如果当前登录用户是客户的合作人，且除去被移入商机公海的商机没有正在跟进中的商机，则当前登录用户将失去本客户
        反之，如果当前登录用户是客户的负责人，且除去被移入商机公海的商机没有正在跟进中的商机，则当前登录用户不失去本客户)
     * @param CrmOpportunity $opportunity
     * @return bool
     */
    private function customer($opportunity)
    {
        $customer = $opportunity->customer;
        /** @var CrmCustomer $customer */
        if($customer)
        {
            //当前administrator不是客户的负责人，仅仅是合伙人时，执行以下流程
            if($customer->administrator_id != $opportunity->administrator_id)
            {
                $count = $customer->getOpportunityFollowingCounts($opportunity);
                if($count <= 0)
                {
                    //仅删除作为合伙人的数据
                    if($opportunity->customerCombine)
                    {
                        if(!$opportunity->customerCombine->delete()) return false;
                    }
                }
            }
        }
        return true;
    }
}