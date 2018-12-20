<?php
return [
	'language' => 'zh-CN',
	'timeZone' => 'Asia/Shanghai',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'formatter' => [
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'dateFormat' => 'yyyy-MM-dd',
            'decimalSeparator' => '.',
            'thousandSeparator' => ',',
            'nullDisplay' => '',
            'currencyCode' => 'CNY',
        ],
    ],
];
