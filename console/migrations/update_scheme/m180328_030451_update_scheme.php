<?php

use common\models\CrmCustomerLog;
use yii\data\ActiveDataProvider;
use yii\db\Migration;

class m180328_030451_update_scheme extends Migration
{
    public function safeUp()
    {
        //所有已转入客户中，operation_time字段为空的数据，进行本字段数据更新为最后跟进时间和操作时间中的最大值
//        $customers = \common\models\CrmCustomer::find()->where(['operation_time' => 0, 'is_receive' => 1])->all();
        $query = \common\models\CrmCustomer::find()->where(['operation_time' => 0, 'is_receive' => 1]);
        $batchNum = 100;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $count = $dataProvider->totalCount;
        if(!empty($count))
        {
            $batch = ceil($count / $batchNum);
            for($i = 0; $i < $batch; $i++)
            {
                /** @var \common\models\CrmCustomer[] $customers */
                $customers = $dataProvider->query->offset($i*$batchNum)->limit($batchNum)->all();
                if($customers)
                {
                    /** @var \common\models\CrmCustomer $customer */
                    foreach ($customers as $customer)
                    {
                        //最后操作记录
                        $lastDoRecord = \common\models\CrmCustomerLog::find()->where(['customer_id' => $customer->id, 'type' => CrmCustomerLog::TYPE_CUSTOMER_DO_RECORD])->orderBy(['created_at' => SORT_DESC])->one();
                        if ($customer->last_record > 0)//crm_customer客户表有最后跟进时间，比较最后跟进时间和最后操作记录时间
                        {
                            if ($lastDoRecord)//有最后操作记录时间
                            {
                                if ($lastDoRecord->created_at > $customer->last_record) {

                                    $customer->operation_time = $lastDoRecord->created_at;
                                    $customer->last_operation_creator_id = $lastDoRecord->creator_id;
                                    $customer->last_operation_creator_name = $lastDoRecord->creator_name;

                                } else {
                                    $customer->operation_time = $customer->last_record;
                                    $customer->last_operation_creator_id = $customer->last_record_creator_id;
                                    $customer->last_operation_creator_name = $customer->last_record_creator_name;
                                }
                            }
                            else//没有最后操作记录时间
                            {
                                $customer->operation_time = $customer->last_record;
                                $customer->last_operation_creator_id = $customer->last_record_creator_id;
                                $customer->last_operation_creator_name = $customer->last_record_creator_name;
                            }
                        }
                        else//crm_customer客户表没有最后跟进时间，比较最后跟进记录时间和最后操作记录时间
                        {
                            //最后跟进记录
                            $lastRecord = CrmCustomerLog::find()->where(['customer_id' => $customer->id,'type' => CrmCustomerLog::TYPE_CUSTOMER_RECORD])
                                ->orderBy(['created_at'=>SORT_DESC])->one();
                            if($lastRecord)//有最后跟进记录
                            {
                                if($lastDoRecord)//有最后操作记录
                                {
                                    if($lastRecord->created_at < $lastDoRecord->created_at)
                                    {
                                        $customer->operation_time = $lastDoRecord->created_at;
                                        $customer->last_operation_creator_id = $lastDoRecord->creator_id;
                                        $customer->last_operation_creator_name = $lastDoRecord->creator_name;
                                    }
                                    else
                                    {
                                        $customer->operation_time = $lastRecord->created_at;
                                        $customer->last_operation_creator_id = $lastRecord->creator_id;
                                        $customer->last_operation_creator_name = $lastRecord->creator_name;
                                    }
                                }
                                else
                                {
                                    $customer->operation_time = $lastRecord->created_at;
                                    $customer->last_operation_creator_id = $lastRecord->creator_id;
                                    $customer->last_operation_creator_name = $lastRecord->creator_name;
                                }
                            }
                            else//没有最后跟进记录
                            {
                                if($lastDoRecord)//有最后操作时间
                                {
                                    $customer->operation_time = $lastDoRecord->created_at;
                                    $customer->last_operation_creator_id = $lastDoRecord->creator_id;
                                    $customer->last_operation_creator_name = $lastDoRecord->creator_name;
                                }
                            }
                        }
                        $customer->save(false);
                    }
                }
            }
        }
    }

    public function safeDown()
    {
        echo "m180328_030451_update_scheme cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180328_030451_update_scheme cannot be reverted.\n";

        return false;
    }
    */
}
