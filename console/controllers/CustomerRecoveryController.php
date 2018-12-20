<?php
namespace console\controllers;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use common\models\CrmCustomerLog;
use common\models\CrmOpportunity;
use common\models\CustomerDepartmentPublic;
use common\models\CustomerFollowRecord;
use common\models\CustomerPublic;
use common\models\Holidays;
use common\models\MessageRemind;
use common\models\Niche;
use common\models\Order;
use Yii;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class CustomerRecoveryController extends Controller
{
    /**
     * 自动检查客户，满足条件放进客户公海
     * @throws \yii\db\Exception
     */
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
            $customers = CrmCustomer::find()->alias('c')
                ->andWhere(['c.customer_public_id' => 0])
                ->andWhere(['c.is_protect' => CrmCustomer::PROTECT_DISABLED])
                ->andWhere(['>', 'c.administrator_id', 0])
                ->andWhere(['not in', 'id', $ids])
                ->offset($i*$batchNum)
                ->limit($batchNum)
                ->all();
            if($customers)
            {
                $t = Yii::$app->db->beginTransaction();
                try{
                    /** @var CrmCustomer $customer */
                    foreach ($customers as $customer)
                    {
                        if($customer != null){
                            //客户在非保护的状态下才可以回收
                            if($customer ->is_protect == 0){
                                /** @var BusinessSubject $business */
                                $business = BusinessSubject::find()->where(['customer_id' => $customer->id])->one();
                                $subject_type = isset($business -> subject_type) ? $business -> subject_type : 0;
                                $where=[];
                                if($subject_type === 0){
                                    $where = [0,2];
                                }else if((isset($business->subject_type) && $business->subject_type == '') || (isset($business->subject_type) && $business->subject_type == 1)){
                                    $where = [0,1];
                                }

                                //首先获取当前客户是否存在部门 不存在获取当前客户负责人的部门
                                /** @var Administrator $admin_id */
                                $admin_id = Administrator::find()->where(['id' => $customer->administrator_id])->one();
                                $department_id = isset($customer->department_id) ? $customer->department_id : ($admin_id -> department_id ? $admin_id -> department_id : 0);
                                //首先根据客户类型查询
                                if($department_id != 0){
                                    //直接查询部门对应的公海并且为启用状态
                                    /** @var CustomerPublic $customer_public */
                                    $customer_public = CustomerPublic::find() -> alias('c') -> leftJoin(['d'=>CustomerDepartmentPublic::find()],'c.id=d.customer_public_id ') -> where(['d.customer_department_id' => $department_id]) ->andWhere(['c.status' => 1])-> andWhere(['in','c.customer_type',$where])->one();
                                    $administrator_id = isset($customer->administrator_id) ? $customer->administrator_id:0;
                                    if($customer_public){
                                        //所负责客户在限定工作日内不维护 移入公海
                                        if($customer_public->move_time > 0) {
                                            $str = explode('.', $customer_public->move_time);
                                            if ($customer->operation_time == 0) {
                                                $n_time = $customer->created_at;
                                            } else {
                                                $n_time = $customer->operation_time;
                                            }
                                            $str_1 = Holidays::getEndTimeByDays($str[0], date('Y-m-d H:00:00', $n_time));
                                            if ($str[1] > 0) {
                                                $str_2 = (floatval('0.' . $str[1])) * 86400;
                                                $str_times = $str_1 + $str_2;
                                            } else {
                                                $str_times = $str_1;
                                            }
                                            if ($str_times <= time()) {
                                                $this->customerUpdate($customer->id, $customer_public->id, $business->id, $administrator_id, $customer_public->name, $type = null);
                                                continue;
                                            }
                                        }
//                                            echo $customer->id."\n\t";
                                        //所创建客户在制定工作日内不创建商机 客户由系统自动放弃到公海
                                        if ($customer_public->opportunity_time > 0) {
                                            /** @var Niche $niche */
                                            $niche = Niche::find()->where(['customer_id' => $customer->id])->orderBy('id DESC')->one();
                                            if (empty($niche)) {
                                                //回收根据 提取时间 分配时间 创建时间 转移时间 获取最大值 比较
                                                $time_data = array($customer->distribution_time, $customer->extract_time, $customer->created_at, $customer->transfer_time);
                                                $times = rsort($time_data);

                                                $niche_str = explode('.',$customer_public->opportunity_time);
                                                $niche_str_1 = Holidays::getEndTimeByDays($niche_str[0],date('Y-m-d H:00:00',$times[0]));
                                                if($niche_str[1] > 0){
                                                    $niche_str_2 = (floatval('0.'.$niche_str[1])) * 86400;
                                                    $niche_str_times = $niche_str_1+$niche_str_2;
                                                }else{
                                                    $niche_str_times = $niche_str_1;
                                                }
                                                if ($niche_str_times <= time()) {
                                                    $this -> customerUpdate($customer->id,$customer_public->id,$business->id,$administrator_id,$customer_public->name,$type='niche');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $t->commit();
                }catch (Exception $e){
                    $t->rollBack();
                    continue;
                }
            }
        }
    }

    /**
     * @param $customer
     * @return bool
     * @throws \yii\db\Exception
     */
    private function deleteCombine($customer)
    {
        $rs = CrmCustomerCombine::find()->createCommand()->delete(CrmCustomerCombine::tableName(), ['and',['customer_id' => $customer->id]])->execute();
        if(!$rs){
            return false;
        }
        return true;
    }

    private function customerUpdate($customer_id,$customer_public_id,$business_id,$administrator_id,$customer_public_name,$type=null){
        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['id' => $customer_id])->one();
        $customer->customer_public_id = $customer_public_id;
        $customer->administrator_id = 0;
        $customer->move_public_time = time();
        $customer->save(false);
        $this->deleteCombine($customer);
        CrmCustomerLog::add('客户移入' . $customer_public_name . '客户公海', $customer->id, 0, 'system', CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD);
        if($type == 'niche'){
            CustomerFollowRecord:: add($business_id, $customer->id, 0, "此客户未在规定时限内成功创建商机，被系统强制放弃到公海！", '其他', 5, 0, 0, 0, 'system');
        }
        //添加消息提醒
        $type = MessageRemind::TYPE_COMMON;
        $type_url = MessageRemind::TYPE_URL_USER_DETAIL;
        $receive_id = $administrator_id;
        $customer_id = $customer->id;
        $sign = 'd-' . $receive_id . '-' . $customer->id . '-' . $type . '-' . $type_url;
        $messageRemind = MessageRemind::find()->where(['sign' => $sign])->orderBy(['created_at' => SORT_DESC])->one();
        if ($customer->id && null == $messageRemind) {
            $message = '您有一个客户被回收至公海，请及时关注所负责的客户哦！';
            $popup_message = '您有一个客户被回收至公海，请及时关注所负责的客户哦！';
            MessageRemind::create($sign, $message, $popup_message, $type, $type_url, $receive_id, $customer_id, 0, 0);
        }
    }
}