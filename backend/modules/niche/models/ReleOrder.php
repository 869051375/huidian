<?php

namespace backend\modules\niche\models;

use common\models\Niche;
use common\models\NicheOrder;
use common\models\VirtualOrder;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 *
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="ReleOrder"))
 */
class ReleOrder extends Model
{
    /**
     * 商机id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 每页多少条
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page_num;

    /**
     * 页码
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page;

    public function rules()
    {
        return [
            [['niche_id','page_num','page'], 'required'],
            [['niche_id'], 'integer'],
            [['niche_id'], 'validateNicheId'],
        ];
    }

    public function validateNicheId()
    {
        $niche = Niche::find()->where(['id' => $this->niche_id])->one();
        if (empty($niche)) {
            $this->addError('id', '暂无数据');
        }

        return true;
    }

    public function getList()
    {
        $nichorder = NicheOrder::find()->where(['niche_id'=>$this->niche_id])->asArray()->all();
        $order_ids = array_column($nichorder,'order_id');
        $order = NicheReleOrder::find()
            ->select('virtual_order.sn as virtual_order_sn,order.sn,order.id as order_id,order.virtual_order_id,order.product_name,order.salesman_name,order.is_invoice,order.status,is_installment,order.price,order.payment_amount')
            ->leftJoin(['virtual_order'=>VirtualOrder::tableName()],'virtual_order.id = order.virtual_order_id')
            ->where(['in','order.id',$order_ids]);
        $dataProvider = new ActiveDataProvider([
            'query' => $order,
            'pagination' => [
                'pageSize' => $this->page_num,
                'page' => $this->page-1,
            ]
        ]);
        return $dataProvider;
    }
}