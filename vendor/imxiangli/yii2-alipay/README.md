Yii2支付宝
=======
支付宝即时到账接口扩展

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist imxiangli/yii2-alipay "*"
```

or add

```
"imxiangli/yii2-alipay": "*"
```

to the require section of your `composer.json` file.

Config
------
```php
'components' => [
    // ...
    'alipay' => [
        'class' => \imxiangli\alipay\Alipay::className(),
        'partner' => '',
        'seller_id' => '',
        'seller_user_id' => '',
        'private_key' => '',
        'alipay_public_key' => '',
        'notify_url' => '',
        'return_url' => '',
        'refund_notify_url' => '',
        'sign_type' => '',
        'input_charset' => '',
        'cacert' => '',
        'transport' => '',
        'anti_phishing_key' => '',
        'exter_invoke_ip' => '',
    ],
    // ...
],
```


Usage
-----
```php
// pay request
Yii::$app->alipay->buildPayRequestForm($out_trade_no, $subject, $total_fee, $body);

// refund request
Yii::$app->alipay->buildRefundRequestForm($refund_date, $batch_no, $detail_data);
```