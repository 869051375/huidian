<?php
namespace common\wxpay;
use common\models\PayRecord;
use common\utils\BC;
use imxiangli\wxpay\WxPay;
use Yii;


/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/3/22
 * Time: 下午1:43
 */
class NativeNotifyCallBack extends \WxPayNotify
{
    /**
     * @return WxPay
     */
    private function getWxPay()
    {
        /** @var WxPay $wxpay */
        $wxpay = Yii::$app->get('wxpay');
        return $wxpay;
    }

    /**
     * @param $openId
     * @param PayRecord $payRecord
     * @return mixed
     */
    public function unifiedorder($openId, $payRecord)
    {
        $productName = $payRecord->virtualOrder->orders[0]->product_name;
        if(count($payRecord->virtualOrder->orders) > 1)
        {
            $productName .= '等多个服务';
        }
        $productName = empty($productName) ? '企业服务' : $productName;

        //统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($productName);
        // $input->SetAttach("");
        $input->SetOut_trade_no($payRecord->pay_sn);
        $input->SetTotal_fee(BC::mul($payRecord->payment_amount, 100, 0));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //$input->SetGoods_tag('');
        $input->SetTrade_type("NATIVE");
        $input->SetOpenid($openId);
        $input->SetProduct_id($payRecord->pay_sn);
        $result = $this->getWxPay()->unifiedOrder($input);
        return $result;
    }

    public function NotifyProcess($data, &$msg)
    {
        //echo "处理回调";
        if(!array_key_exists("openid", $data) ||
            !array_key_exists("product_id", $data))
        {
            $msg = "回调数据异常";
            return false;
        }

        $openid = $data["openid"];
        $pay_sn = $data["product_id"];

        /** @var PayRecord $payRecord */
        $payRecord = PayRecord::find()->where(['pay_sn' => $pay_sn])->one();
        if(null == $payRecord)
        {
            $msg = "单号不存在";
            return false;
        }

        //统一下单
        $result = $this->unifiedorder($openid, $payRecord);
        if(!array_key_exists("appid", $result) ||
            !array_key_exists("mch_id", $result) ||
            !array_key_exists("prepay_id", $result))
        {
            $msg = "统一下单失败";
            return false;
        }

        $this->SetData("appid", $result["appid"]);
        $this->SetData("mch_id", $result["mch_id"]);
        $this->SetData("nonce_str", $this->getWxPay()->getNonceStr());
        $this->SetData("prepay_id", $result["prepay_id"]);
        $this->SetData("result_code", "SUCCESS");
        $this->SetData("err_code_des", "OK");
        return true;
    }
}