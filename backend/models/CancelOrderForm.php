<?php
namespace backend\models;

use common\models\AdministratorLog;
use common\models\VirtualOrder;
use yii\base\Model;

/**
 * Class CancelOrder
 * @package backend\models
 */
class CancelOrderForm extends Model
{
    public $virtual_order_id;

    /**
     * @var VirtualOrder
     */
    private $vo;

    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['virtual_order_id', 'validateVirtualOrderId'],
        ];
    }

    public function validateVirtualOrderId()
    {
        $this->vo = VirtualOrder::findOne($this->virtual_order_id);
        if(!$this->vo)
        {
            $this->addError('virtual_order_id', '找不到订单。');
        }
        else
        {
            if($this->vo->isAlreadyPayment())
            {
                $this->addError('virtual_order_id', '该订单已经完成付款，请根据单个订单退款。');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'virtual_order_id' => '订单',
        ];
    }

    /**
     * 需要验证取消订单的一系列状态，服务终止，两种情况，1.已经付款的，需要生成退款记录，并且虚拟订单需要取消.2.未付款的，直接取消
     * @return bool
     */
    public function save()
    {
        if(!$this->validate()) return false;
        $this->vo->cancel();
        $this->vo->refund();
        //新增后台操作日志
        AdministratorLog::logCancelVirtualOrder($this->vo);
        return true;
    }
}