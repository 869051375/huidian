<?php
namespace common\wxpay;
use common\models\PayRecord;
use imxiangli\wxpay\WxPay;
use Yii;

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/22
 * Time: 下午1:43
 */
class PayNotifyCallBack extends \WxPayNotify
{
    //查询订单
    public function Queryorder($transaction_id)
    {
        /** @var WxPay $wxpay */
        $wxpay = Yii::$app->get('wxpay');
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = $wxpay->orderQuery($input);
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {
        $notfiyOutput = array();

        if(!array_key_exists("transaction_id", $data) ||
            !array_key_exists("out_trade_no", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        PayRecord::paySuccess($data["total_fee"], $data["out_trade_no"],
            PayRecord::PAY_PLATFORM_WX, strtotime($data['time_end']), $data["transaction_id"]);
        return true;
    }
}