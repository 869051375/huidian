<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\Niche;
use common\models\NicheContract;
use common\models\NicheOrder;
use common\models\NichePublicDepartment;
use common\models\Order;
use Yii;
use yii\base\Model;


/**
 * 商机关联订单接口
 * @SWG\Definition(required={}, @SWG\Xml(name="NicheCorrelationOrder"))
 */
class NicheCorrelationOrder extends Model
{

    /**
     * 订单ID
     * @SWG\Property(example = "1,2,3")
     * @var integer
     */
    public $order_ids;

    public $creator_name;

    /** @var $currentAdministrator */
    public $currentAdministrator;

    /**
     * 商机ID
     * @SWG\Property(example = "1")
     * @var integer
     */
    public $niche_id;



    public function rules()
    {
        return [
            [['niche_id','order_ids'], 'required'],
            [['order_ids'], 'string'],
            [['niche_id'], 'validateNicheId'],
            [['order_ids'], 'validateOrderIds'],
        ];
    }

    public function load($data, $formName = '')
    {
        return parent::load($data, $formName);
    }

    public function validateNicheId()
    {
        $niche_one = Niche::find()->where(['id'=>$this->niche_id])->one();
        if (empty($niche_one))
        {
            return $this->addError('niche_id','商机ID不存在');
        }
        return true;
    }

    public function validateOrderIds()
    {
        $ids = explode(',', $this->order_ids);
        $count = Order::find()->where(['in','id',$ids])->count();
        if ((int)$count != count($ids))
        {
            return $this->addError('order_ids','订单ID不存在');
        }
        return true;
    }
    public function save()
    {
        $ids = explode(',', $this->order_ids);
        foreach ($ids as $order_id)
        {
            /** @var Order $order */
            $order = Order::find()->where(['id'=>$order_id])->one();
            $model = new NicheOrder();
            $model->order_id = $order_id;
            $model->niche_id = $this->niche_id;
            $model->save(false);
            //添加操作记录
            NicheOperationRecord::create($this->niche_id,'关联订单','关联了订单，虚拟订单号为：'.$order->virtualOrder->sn);
        }
        return true;
    }
}
