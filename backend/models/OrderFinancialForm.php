<?php
namespace backend\models;
use common\models\Order;
use yii\base\Model;


class OrderFinancialForm extends Model
{
    public $financial_code;
    public $order_id;

    public function rules()
    {
        return [
            [['financial_code', 'order_id'], 'trim'],
            [['financial_code', 'order_id'], 'required'],
            [['order_id'], 'integer'],
            [['financial_code'], 'string', 'max' => 6],
            ['financial_code', 'match', 'pattern'=>'/^[a-zA-Z][a-zA-Z]*\d{1,6}$/i', 'message'=>'财务明细编号格式不正确！'],
        ];
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
     * @param Order $order
     * @return bool
     */
    public function update($order)
    {
        if(!$this->validate()) return false;
        $order->financial_code = $this->financial_code;
        if(!$order->save(false)) return false;
        return true;
    }
}
