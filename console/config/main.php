<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
		'administratorAuthManager' => [
			'class' => 'yii\rbac\DbManager',
			'itemTable' => '{{%administrator_auth_item}}',
			'itemChildTable' => '{{%administrator_auth_item_child}}',
			'assignmentTable' => '{{%administrator_auth_assignment}}',
			'ruleTable' => '{{%administrator_auth_rule}}',
		]
    ],
    'controllerMap' => [
        'batch' => [
            'class' => 'schmunk42\giiant\commands\BatchController',
            'overwrite' => true,
            'modelNamespace' => 'common\\models\\base',
            'crudTidyOutput' => true,
        ]
    ],
    'params' => $params,
];
