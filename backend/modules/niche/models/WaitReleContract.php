<?php

namespace backend\modules\niche\models;

use common\models\Contract;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NicheProduct;
use common\models\Order;
use yii\base\Model;


/**
 *
 * @SWG\Definition(required={"niche_id", "customer_id"}, @SWG\Xml(name="WaitReleContract"))
 */
class WaitReleContract extends Model
{
    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 客户id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $customer_id;

    public function rules()
    {
        return [
            [['niche_id','customer_id'], 'required'],
            [['niche_id','customer_id'], 'integer'],
            [['niche_id'], 'validateNicheId'],
        ];
    }

    public function validateNicheId()
    {
        $niche = Niche::find()->where(['id'=>$this->niche_id])->one();
        if(empty($niche)){
            $this->addError('id','暂无数据');
        }
        return true;
    }


    public function getWaitReleList($administrator)
    {
        $niche_product = NicheProduct::find()->where(['niche_id'=>$this->niche_id])->asArray()->all();
        $product_ids = array_column($niche_product,'product_id');
        $niche = Niche::find()->where(['id'=>$this->niche_id])->asArray()->one();
        $contracts = NicheContract::find()->where(['niche_id'=>$this->niche_id])->asArray()->all();
        $contract_ids = array_column($contracts,'contract_id');
        $contract = Contract::find()
                    ->select('id,name,serial_number,virtual_order_id,contract_no,creator_name,created_at,status')
                    ->where(['customer_id'=>$this->customer_id])
                    ->andWhere(['invalid_status'=>0])
                    ->andWhere(['administrator_id'=>$administrator->id])
                    ->andWhere(['sign_status'=>0])
                    ->andWhere(['>=','created_at',$niche['created_at']])
                    ->all();
        foreach ($contract as $key=>$value){
            $order = Order::find()->where(['virtual_order_id'=>$value->virtual_order_id])->asArray()->all();
            $product_id = array_column($order,'product_id');
            if(!array_intersect($product_ids,$product_id) || in_array($value['id'],$contract_ids)){
                unset($contract[$key]);
            }
        }
        return $contract;
    }

}