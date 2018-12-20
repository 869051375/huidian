<?php
/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 2017/1/16
 * Time: 上午10:14
 */

namespace common\components;

use common\models\Property;
use Yii;
use yii\base\Component;
use yii\web\Cookie;

class WXJSSDK extends Component
{
    public $appId;
    public $appSecret;

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = [
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        ];
        return $signPackage;
    }

    public function oauth()
    {
        $mobile_url = Property::get('mobile_domain');
        $REDIRECT_URI = $mobile_url.'/wechat/oauth.html';//url中的特殊字符如&、/等需要转码
        $scope='snsapi_userinfo';//需要授权
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appId.'&redirect_uri='.$REDIRECT_URI.'&response_type=code&scope='.$scope.'&state=1#wechat_redirect';
        header("Location:".$url);
    }

    public function getOpenId($code)
    {
        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appId.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';
        //获取token的url
        $json = $this->httpGet($get_token_url);
        $result = json_decode($json);
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie([
            'name' => 'openId',
            'value' => $result->openid,
            'expire'=> 100*365*24*3600,
        ]));
        return $result;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++)
        {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $res = $this->getCache("jsapi_ticket");
        if (null == $res)
        {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $json = $this->httpGet($url);
            $res = json_decode($json);
            $ticket = $res->ticket;
            if ($ticket)
            {
                $this->setCache("jsapi_ticket", $res, $res->expires_in-200);
            }
        }
        else
        {
            $ticket = $res->ticket;
        }

        return $ticket;
    }

    private function getAccessToken()
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $res = $this->getCache("access_token");
        if (null == $res)
        {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $json = $this->httpGet($url);
            $res = json_decode($json);
            $access_token = $res->access_token;
            if ($access_token)
            {
                $this->setCache("access_token", $res, $res->expires_in-200);
            }
        }
        else
        {
            $access_token = $res->access_token;
        }
        return $access_token;
    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function getCache($key)
    {
        return \Yii::$app->cache->get('wx-js-sdk-'.$key);
    }

    private function setCache($key, $content, $duration)
    {
        \Yii::$app->cache->set('wx-js-sdk-'.$key, $content, $duration);
    }
}
