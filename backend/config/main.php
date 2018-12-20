<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'niche' => [
            'class' => 'backend\modules\niche\Module',
        ],
    ],

    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
         'i18n'=>[
            'translations'=>[
                '*'=>[
                    'class'=>'yii\i18n\PhpMessageSource',
                    'fileMap'=>[
                    'common'=>'common.php',
                    ],
                ],
            ],
         ],
        'user' => [
            'class' => 'backend\models\AdministratorUser',
            'identityClass' => 'common\models\Administrator',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/app.log',
                    'except' => ['yii\web\HttpException:404', 'yii\web\HttpException:403'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/404.log',
                    'categories' => ['yii\web\HttpException:404'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'itemTable' => '{{%administrator_auth_item}}',
            'itemChildTable' => '{{%administrator_auth_item_child}}',
            'assignmentTable' => '{{%administrator_auth_assignment}}',
            'ruleTable' => '{{%administrator_auth_rule}}',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && is_array($response->data) && $response->statusCode != 200) {
                    $response->data = [
                        'code' => isset($response->data['status']) ? $response->data['status'] : $response->statusCode,
                        'message' => isset($response->data['message']) ? $response->data['message'] : $response->statusCode,
                        'data' => $response->data
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'take-coupon/<id:[\d]+>.html' => 'take-coupon/show',
                'topic/<key:[\w-]+>.html' => 'topic/show',
                '<controller:[\w-]+>/<action:[\w-]+>'=>'<controller>/<action>',
                //'<module:[\w-]>/<controller:[\w-]+>/<action:[\w-]+>'=>'<module>/<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];
