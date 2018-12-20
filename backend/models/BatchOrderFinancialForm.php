<?php
namespace backend\models;
use common\models\Administrator;
use common\models\Order;
use common\models\OrderRecord;
use Yii;
use yii\base\Model;


class BatchOrderFinancialForm extends Model
{
    public $financial_code;
    public $order_id;

    /**
     * @var Order[]
     */
    public $orders;

    public function rules()
    {
        return [
            [['financial_code', 'order_id'], 'trim'],
            [['order_id'], 'required','message' => '请至少选择一个子订单！'],
            [['financial_code'], 'required'],
            ['order_id', 'validateOrderId'],
            [['financial_code'], 'string', 'max' => 6],
            [['financial_code'], 'match', 'pattern'=>'/^[a-zA-Z][a-zA-Z]*\d{1,6}$/i', 'message'=>'财务明细编号格式不正确！'],
        ];
    }

    public function validateOrderId()
    {
        $order_ids = explode(',',rtrim($this->order_id,','));
        $this->orders = Order::find()->where(['in','id',$order_ids])->all();
        if(count($order_ids) != count($this->orders))
        {
            $this->addError('order_id','多选中有无效的订单id存在');
        }
//        else
//        {
//            foreach($this->orders as $order)
//            {
//                if(!$order->virtualOrder->isPendingPayment())
//                {
//                    $this->addError('order_id', '订单必须是未付款状态。');
//                }
//                if(empty($order->salesman_aid))
//                {
//                    $this->addError('order_id', '该订单无负责人不可修改价格。');
//                }
//                // 如果存在正在申请中的 或者 审核通过的 则不允许保存
//                if($order->isAdjustStatusPass() || $order->isAdjustStatusPending())
//                {
//                    $this->addError('order_id', '一个订单只能申请一次修改价格');
//                }
//            }
//        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'financial_code' => '财务明细编号',
        ];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if(!$this->validate()) return false;
        /** @var Administrator $admin */
        $admin = Yii::$app->user->identity;
        $t = Yii::$app->db->beginTransaction();
        try
        {
            foreach($this->orders as $order)
            {
                $order->financial_code = $this->financial_code;
                $order->save(false);
                OrderRecord::create($order->id, '批量编辑财务明细编号', '财务明细编号更新为：'.$this->financial_code, $admin,0, OrderRecord::INTERNAL_ACTIVE,0);
            }
            $t->commit();
            return true;
        }
        catch (\Exception $e)
        {
            $t->rollBack();
            throw $e;
        }
    }
}
