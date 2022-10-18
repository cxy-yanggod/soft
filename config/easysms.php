<?php
return  [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => ['aliyun',
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => '/tmp/easy-sms.log',
        ],
        'aliyun' => [
            'access_key_id' => 'LTAI4FikovDYrSdkrvLSHfPL',
            'access_key_secret' => 'Tq0w3IeqYI4YNpRBCIZIgf403oQj91',
            'sign_name' => '成都善恶云科技有限公司',
        ],
    ],
];
