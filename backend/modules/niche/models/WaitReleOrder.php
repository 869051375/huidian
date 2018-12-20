<?php

namespace backend\modules\niche\models;

use common\models\CrmCustomer;
use common\models\Niche;
use common\models\NicheOrder;
use common\models\NicheProduct;
use common\models\Order;
use common\models\User;
use common\models\VirtualOrder;
use yii\base\Model;


/**
 *
 * @SWG\Definition(required={"niche_id", "customer_id"}, @SWG\Xml(name="WaitReleOrder"))
 */
class WaitReleOrder extends Model
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


    public function getList($administrator)
    {
        $niche_product = NicheProduct::find()->where(['niche_id'=>$this->niche_id])->asArray()->all();
        /** @var Niche $niche */
        $niche = Niche::find()->where(['id'=>$this->niche_id])->one();
        $product_ids = array_column($niche_product,'product_id');
        $niche_order = NicheOrder::find()->where(['niche_id'=>$this->niche_id])->asArray()->all();
        $order_ids = array_column($niche_order,'order_id');
        $order = Order::find()
                ->select('order.id,order.virtual_order_id,order.sn,order.product_name,order.province_name,order.city_name,order.district_name,order.status,is_invoice,price,product_id,virtual_order_id')
                ->leftJoin(['customer'=>CrmCustomer::tableName()],'customer.user_id = order.user_id')
                ->where(['customer.id'=>$this->customer_id])
                ->andWhere(['salesman_aid'=>$administrator->id])
                ->andWhere(['>','order.created_at',$niche->created_at])
                ->andWhere(['cancel_time'=>0])
                ->andWhere(['!=','status',4])
                ->asArray()
                ->all();
        $data = [];
        foreach ($order as $key=>$value){
            $virtual_order = VirtualOrder::find()->select('id,sn,created_at,total_amount')->where(['id'=>$value['virtual_order_id']])->asArray()->one();
            if(!in_array($value['product_id'],$product_ids) || in_array($value['id'],$order_ids)){
                unset($order[$key]);
            }else{
               if(isset($data['virtual_order'][$value['virtual_order_id']])){
                   array_push($data['virtual_order'][$value['virtual_order_id']]['order'],$value);

               }else{
                   $data['virtual_order'][$virtual_order['id']] = $virtual_order;
                   $data['virtual_order'][$virtual_order['id']]['order'] = [];
                   $data['virtual_order'][$value['virtual_order_id']]['order_id'] = $value['id'];
                   array_push($data['virtual_order'][$value['virtual_order_id']]['order'],$value);

               }
            }
        }
        return $data;

    }
}