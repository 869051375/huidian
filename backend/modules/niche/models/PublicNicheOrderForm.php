<?php

namespace backend\modules\niche\models;

use common\models\NicheOrder;
use common\models\VirtualOrder;
use yii\data\ActiveDataProvider;
use yii\base\Model;


/**
 * 订单列表
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="PublicNicheOrderForm"))
 */
class PublicNicheOrderForm extends Model
{
    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    public function rules()
    {
        return [
            [['niche_id'], 'required'],
            [['niche_id'], 'integer']
        ];
    }

    public function getOrder()
    {
        $niche_order = NicheOrder::find()->where(['niche_id'=>$this->niche_id])->all();
        $order_ids = array_column($niche_order,'order_id');
        $query = NicheReleOrder::find()
                ->select('virtual_order.sn as virtual_order_sn,order.virtual_order_id,order.sn,order.id as order_id,order.product_name,order.salesman_name,order.is_invoice,order.status,is_installment,order.price,order.payment_amount')
                ->leftJoin(['virtual_order'=>VirtualOrder::tableName()],'virtual_order.id = order.virtual_order_id')
                ->where(['in','order.id',$order_ids]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        return $dataProvider;
    }
}