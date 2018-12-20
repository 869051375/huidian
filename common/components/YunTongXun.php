<?php

/**
 * Created by PhpStorm.
 * User: lixiang
 * Date: 16/3/11
 * Time: 10:29
 */

namespace common\components;

use yii\base\Component;
use yii\httpclient\Client;
use yii\log\Logger;

class YunTongXun extends Component
{

    public $AccountSid;
    public $AccountToken;
    public $AppId;
    public $ServerIP = 'app.cloopen.com';
    public $ServerPort = '8883';
    public $SoftVersion = '2013-12-26';
    private $Batch;  //时间戳
    private $BodyType = "json"; //包体格式，可填值：json 、xml

    /**
     * 发送模板短信
     * @param string $to 短信接收彿手机号码集合,用英文逗号分开
     * @param array|null $datas 内容数据
     * @param string $tempId 模板Id
     * @return bool
     */
    public function sendTemplateSMS($to, $datas, $tempId)
    {
        $this->Batch = date("YmdHis");
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth = $this->accAuth();
        if ($auth === true) {
            // 拼接请求包体
            if ($this->BodyType == "json") {
                $data = "";
                for ($i = 0; $i < count($datas); $i++) {
                    $data = $data . "'" . $datas[$i] . "',";
                }
                $body = "{'to':'$to','templateId':'$tempId','appId':'$this->AppId','datas':[" . $data . "]}";
            } else {
                $data = "";
                for ($i = 0; $i < count($datas); $i++) {
                    $data = $data . "<data>" . $datas[$i] . "</data>";
                }
                $body = "<TemplateSMS>
                    <to>$to</to>
                    <appId>$this->AppId</appId>
                    <templateId>$tempId</templateId>
                    <datas>" . $data . "</datas>
                  </TemplateSMS>";
            }
            // 大写的sig参数
            $sig = strtoupper(md5($this->AccountSid . $this->AccountToken . $this->Batch));
            // 生成请求URL
            $url = "https://$this->ServerIP:$this->ServerPort/$this->SoftVersion/Accounts/$this->AccountSid/SMS/TemplateSMS?sig=$sig";
            // 生成授权：主帐户Id + 英文冒号 + 时间戳。
            $authen = base64_encode($this->AccountSid . ":" . $this->Batch);
            // 生成包头
            $header = array(
                "Accept" => "application/$this->BodyType",
                "Content-Type" => "application/$this->BodyType;charset=utf-8",
                "Authorization" => "$authen"
            );
            // 发送请求
            $result = $this->curl_post($url, $body, $header);
            if ($this->BodyType == "json") {//JSON格式
                $datas = json_decode($result);
            } else { //xml格式
                $datas = simplexml_load_string(trim($result, " \t\n\r"));
            }
            //重新装填数据
            if ($datas->statusCode == 0) {
                if ($this->BodyType == "json") {
                    $datas->TemplateSMS = $datas->templateSMS;
                    unset($datas->templateSMS);
                }
            }
        } else {
            return false;
        }
        if ($datas == NULL) {
            \Yii::getLogger()->log('短信发送失败：未知错误', Logger::LEVEL_ERROR);
            return false;
        }
        if ($datas->statusCode != 0) {
            \Yii::getLogger()->log('短信发送失败：' . var_export($datas, true), Logger::LEVEL_ERROR);
            return false;
        } else {
            return true;
        }
    }

    /**
     * 主帐号鉴权
     */
    private function accAuth()
    {
        if ($this->ServerIP == "") {
            $data = new \stdClass();
            $data->statusCode = '172004';
            $data->statusMsg = 'IP为空';
            return $data;
        }
        if ($this->ServerPort <= 0) {
            $data = new \stdClass();
            $data->statusCode = '172005';
            $data->statusMsg = '端口错误（小于等于0）';
            return $data;
        }
        if ($this->SoftVersion == "") {
            $data = new \stdClass();
            $data->statusCode = '172013';
            $data->statusMsg = '版本号为空';
            return $data;
        }
        if ($this->AccountSid == "") {
            $data = new \stdClass();
            $data->statusCode = '172006';
            $data->statusMsg = '主帐号为空';
            return $data;
        }
        if ($this->AccountToken == "") {
            $data = new \stdClass();
            $data->statusCode = '172007';
            $data->statusMsg = '主帐号令牌为空';
            return $data;
        }
        if ($this->AppId == "") {
            $data = new \stdClass();
            $data->statusCode = '172012';
            $data->statusMsg = '应用ID为空';
            return $data;
        }
        return true;
    }

    private function curl_post($url, $data, $header, $post = 1)
    {
        $client = new Client();
        $request = null;
        if ($post) {
            $request = $client->post($url, $data, $header);
        } else {
            $request = $client->get($url, $data, $header);
        }
        $rs = $request->send();
        $result = $rs->getContent();
        //连接失败
        if (!$rs->isOk) {
            if ($this->BodyType == 'json') {
                $result = "{\"statusCode\":\"172001\",\"statusMsg\":\"网络错误\"}";
            } else {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
            }
        }
        return $result;
    }

}
