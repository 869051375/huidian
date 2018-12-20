<?php

namespace imxiangli\alipay;

use yii\base\Component;


class Alipay extends Component
{
    public $partner = ''; //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
    public $seller_id = ''; //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
    public $seller_user_id = ''; //卖家支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
    public $key = ''; //MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
    public $private_key = ''; //商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
    public $alipay_public_key = ''; //支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
    public $notify_url = ''; // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
    public $return_url = ''; // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
    public $refund_notify_url = ''; // 服务器异步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数,必须外网可以正常访问
    public $sign_type = 'MD5'; //签名方式
    public $input_charset = 'utf-8'; //字符编码格式 目前支持 gbk 或 utf-8
    public $cacert = '@vendor/imxiangli/yii2-alipay/cacert.pem'; //ca证书路径地址，用于curl中ssl校验
    public $transport = 'http'; //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    public $anti_phishing_key = ''; // 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
    public $exter_invoke_ip = ''; // 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1

    public function buildPayRequestForm($out_trade_no, $subject, $total_fee, $body = '', $method = 'get', $button_name = '确认')
    {
        $parameter = [
            'service' => 'create_direct_pay_by_user',
            'partner' => $this->partner,
            'seller_id' => $this->seller_id,
            'payment_type' => '1',
            'notify_url' => $this->notify_url,
            'return_url' => $this->return_url,
            'anti_phishing_key' => $this->anti_phishing_key,
            'exter_invoke_ip' => $this->exter_invoke_ip,
            'out_trade_no' => $out_trade_no,
            'subject' => $subject,
            'total_fee' => $total_fee,
            'body' => $body,
            '_input_charset' => $this->input_charset,
        ];
        $alipaySubmit = new AlipaySubmit($this->getConfig());
        $html_text = $alipaySubmit->buildRequestForm($parameter, $method, $button_name);
        return $html_text;
    }

    public function buildRefundRequestForm($refund_date, $batch_no, $detail_data, $method = 'get', $button_name = '确认')
    {
        $parameter = array(
            'service' => 'refund_fastpay_by_platform_pwd',
            'partner' => $this->partner,
            'notify_url' => $this->refund_notify_url,
            'seller_user_id' => $this->seller_user_id,
            'refund_date' => $refund_date,
            'batch_no' => $batch_no,
            'batch_num' => count(explode('#', $detail_data)),
            'detail_data' => $detail_data,
            '_input_charset' => $this->input_charset,
        );

        $alipaySubmit = new AlipaySubmit($this->getConfig());
        $html_text = $alipaySubmit->buildRequestForm($parameter, $method, $button_name);
        return $html_text;
    }

    private function getConfig()
    {
        return [
            'partner' => $this->partner,
            'seller_id' => $this->seller_id,
            'seller_user_id' => $this->seller_user_id,
            'key' => $this->key,
            'private_key' => $this->private_key,
            'alipay_public_key' => $this->alipay_public_key,
            'notify_url' => $this->notify_url,
            'return_url' => $this->return_url,
            'refund_notify_url' => $this->refund_notify_url,
            'sign_type' => $this->sign_type,
            'input_charset' => $this->input_charset,
            'cacert' => \Yii::getAlias($this->cacert),
            'transport' => $this->transport,
            'anti_phishing_key' => $this->anti_phishing_key,
            'exter_invoke_ip' => $this->exter_invoke_ip,
        ];
    }

    public function getVerifyReturn()
    {
        $alipayNotify = new AlipayNotify($this->getConfig());
        return $alipayNotify->verifyReturn();
    }

    public function getVerifyNotify()
    {
        $alipayNotify = new AlipayNotify($this->getConfig());
        return $alipayNotify->verifyNotify();
    }
}
