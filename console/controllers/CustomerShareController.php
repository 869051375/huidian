<?php

namespace console\controllers;

use common\models\CrmCustomer;
use common\models\CrmCustomerCombine;
use yii\console\Controller;

class CustomerShareController extends Controller
{
    /**
     * @throws \yii\db\Exception
     */
    public function actionCustomerShare()
    {

        $crm_customer_combine = CrmCustomerCombine::find() ->alias('ccc')->select('ccc.customer_id,count(ccc.customer_id) as id_total') -> leftJoin(['c'=>CrmCustomer::tableName()],'c.id=ccc.customer_id')->where(['c.is_share' => 0])->groupBy(['ccc.customer_id']) ->having(['>','count(ccc.customer_id)',1])->asArray() ->all();

        if(!empty($crm_customer_combine)){
            $t = \Yii::$app->db->beginTransaction();
            try{
                foreach($crm_customer_combine as $key => $val){
                    /** @var CrmCustomer $crm_customer */
                    $crm_customer = CrmCustomer::find()->where(['id' => $val['customer_id']])->one();
                    if($val['id_total'] > 1){
                        $crm_customer->is_share = 1;
                        $crm_customer->save(false);
                    }
                }
                $t->commit();
            }catch (\Exception $e){
                $t->rollBack();
            }
        }

    }
}
