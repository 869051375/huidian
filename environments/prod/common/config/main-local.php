<?php
return [
    'components' => [
        // 数据库
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=database',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            'schemaCache' => 'cache',
            /*
            // 读写分离配置
            'slaveConfig' => [
                'username' => 'slave',
                'password' => '',
                'charset' => 'utf8mb4',
                'attributes' => [
                    // use a smaller connection timeout
                    PDO::ATTR_TIMEOUT => 10,
                ],
            ],
            // 从库配置
            'slaves' => [
                ['dsn' => 'mysql:host=localhost;dbname=database'],
            ],
            */
        ],
        // Session
        'session' => [
            'class' => \yii\redis\Session::className(),
            'keyPrefix' => 'php-sid',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 3,
                'password' => '',
            ]
        ],
        // Redis
        'redis' => [
            'class' => \yii\redis\Connection::className(),
            'database' => 1,
            'hostname' => 'localhost',
            'port' => 6379,
            'password' => '',
        ],
        // 缓存
        'cache' => [
            'class' => \yii\redis\Cache::className(),
            'defaultDuration' => 3600,
            'redis' => [
                'database' => 0,
                'hostname' => 'localhost',
                'port' => 6379,
                'password' => '',
            ]
        ],
        // 缓存（常用于身份校验的数据存储）
        'redisCache' => [
            'class' => \yii\redis\Cache::className(),
            'defaultDuration' => 3600,
            'redis' => [
                'database' => 4,
                'hostname' => 'localhost',
                'port' => 6379,
                'password' => '',
            ]
        ],
        // 队列
        'queue' => [
            'class' => 'shmilyzxt\queue\queues\RedisQueue',
            'jobEvent' => [
                'on beforeExecute' => ['shmilyzxt\queue\base\JobEventHandler','beforeExecute'],
                'on beforeDelete' => ['shmilyzxt\queue\base\JobEventHandler','beforeDelete'],
            ],
            'connector' => [ //需要安装 predis\predis 扩展来操作redis
                'class' => 'shmilyzxt\queue\connectors\PredisConnector',
                'parameters' => [
                    'scheme' => 'tcp',
                    'host' => 'localhost',
                    'port' => 6379,
                    'password' => '',
                    'db' => 2
                ],
                'options'=> [],
            ],
            'queue' => 'default',
            'expire' => 1200,
            'maxJob' => 0,
            'failed' => [
                'logFail' => false,
//                'provider' => [
//                    'class' => 'shmilyzxt\queue\failed\DatabaseFailedProvider',
//                    'db' => [
//                        'class' => 'yii\db\Connection',
//                        'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
//                        'username' => 'root',
//                        'password' => '',
//                        'charset' => 'utf8',
//                    ],
//                    'table' => 'failed_jobs'
//                ],
            ],
        ],
        // 邮件
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.mxhichina.com',  //每种邮箱的host配置不一样（163:smtp.163.com）使用时必须开启163邮件的smtp功能
                'username' => 'notify@juejinqifu.com',//邮箱地址
                'password' => '',//授权码（非密码）
                'port' => '25',//QQ邮箱（465）163邮箱（25）
                'encryption' => 'tls',//163类型（QQ：ssl）
            ],
            'messageConfig'=>[
                'charset'=>'utf-8',
                'from'=>['notify@juejinqifu.com'=>'掘金企服'],//显示名字
            ],
        ],
        // 短信平台（云通讯）
        'yunTongXun' => [
            'class' => \common\components\YunTongXun::className(),
            'AccountSid' => '',
            'AccountToken' => '',
            'AppId' => '',
        ],
        // 微信 JS SDK
        'WXJSSDK' => [
            'class' => \common\components\WXJSSDK::className(),
            'appId' => '',
            'appSecret' => '',
        ],

        // 阿里云OSS（订单文件）
        'oss' => [
            'class' => \common\components\OSS::className(),
            'endPoint' => 'oss-cn-beijing.aliyuncs.com',
            'internalEndPoint' => 'oss-cn-beijing-internal.aliyuncs.com',
            'accessKeyId' => '',
            'accessKeySecret' => '',
            'isCName' => false,
            'securityToken' => null,
            'defaultBucket' => 'order-file',
        ],

        // 阿里云OSS（落地页图片）
        'pageOss' => [
            'class' => \common\components\OSS::className(),
            'endPoint' => 'oss-cn-beijing.aliyuncs.com',
            'internalEndPoint' => 'oss-cn-beijing-internal.aliyuncs.com',
            'accessKeyId' => '',
            'accessKeySecret' => '',
            'isCName' => false,
            'securityToken' => null,
            'defaultBucket' => 'page-assets',
        ],

        // 阿里云OSS（图片）
        'imageStorage' => [
            'class' => \imxiangli\image\storage\Multiple::className(),
            'adapters' => [
                'oss' => [
                    'class' => \imxiangli\image\storage\OSS::className(),
                    'endPoint' => 'oss-cn-beijing.aliyuncs.com',
                    'internalEndPoint' => 'oss-cn-beijing-internal.aliyuncs.com',
                    'accessKeyId' => '',
                    'accessKeySecret' => '',
                    'isCName' => false,
                    'securityToken' => null,
                    'defaultBucket' => 'juejinqifu-images',
                    'imageDomain' => 'oss-cn-beijing.aliyuncs.com'
                ],
            ],
        ],

        // 支付宝接口
        'alipay' => [
            'class' => \imxiangli\alipay\Alipay::className(),
            'partner' => '',
            'seller_id' => '',
            'seller_user_id' => '',
            'key' => '',
            'private_key' => '',
            'alipay_public_key' => '',
            'notify_url' => 'http://test.juejinqifu.com/pay-notify/alipay',
            'return_url' => 'http://test.juejinqifu.com/pay-return/alipay',
            'refund_notify_url' => 'http://test.juejinqifu.com/refund-notify/alipay',
            'sign_type' => 'MD5',
            'input_charset' => 'utf-8',
            //'cacert' => '',
            'transport' => 'http',
            'anti_phishing_key' => '',
            'exter_invoke_ip' => '',
        ],

        // 微信支付接口
        'wxpay' => [
            'class' => \imxiangli\wxpay\WxPay::className(),
            'app_id' => '',
            'mch_id' => '',
            'key' => '',
            'app_secret' => '',
            'ssl_cert_path' => Yii::getAlias('@common/secret/apiclient_cert.pem'),
            'ssl_key_path' => Yii::getAlias('@common/secret/apiclient_key.pem'),
            'notify_url' => 'http://test.juejinqifu.com/pay-notify/wx',
        ],

        // 企查查接口
        'qcc' => [
            'class' => \common\components\QCC::className(),
            'key' => '',
        ],

        // Assets
        'assetManager' => [
            //'linkAssets' => true,
            'appendTimestamp' => true,
            'hashCallback' => function($path){ // 为了解决负载均衡时多个服务器的hash不一样的问题
                return sprintf('%x', crc32($path . Yii::getVersion()));
            },
        ],

        // 调试日志
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/trace.log',
                    'levels' => ['trace', 'profile'],
                    'categories' => ['application'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/profile.log',
                    'levels' => ['profile'],
                    'categories' => ['application'],
                ],
            ],
        ],
    ],
];