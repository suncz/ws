<?php
return [
    'adminEmail' => 'admin@example.com',
    'whenSocket' => [ //业务配置 何时可建立socket连接
        'tourist' => [
            'outer' => true,
            'room' => true,
        ],
        'loginUser' => [
            'outer' => true,
            'room' => true,
        ]],
    'mQMsgConsume' => [
        'queueName' => 'sunny_queue_msg_{ip}',
        'broadcastExchange' => 'sunny_im_broadcast',
        'pointToPointExchange' => 'sunny_im_point_to_point',
        'relationExchange' => [
            [
                'exchangeName' => 'sunny_im_broadcast',
                'exchangeType' => 'fanout',
                'durable' => false,
                'routingKey' => ''
            ],
            [
                'exchangeName' => 'sunny_im_point_to_point',
                'exchangeType' => 'direct',
                'durable' => false,
                'routingKey' => 'ip'
            ],
        ],
    ]

];
