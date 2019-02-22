<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
        'w-s' => [
            'class' => 'console\controllers\WSController',
            'server' => 'console\controllers\WSServer', // 可替换为自己的业务类继承该类即可
            'host' => '0.0.0.0',// 监听地址
            'port' => 9501,// 监听端口
            'type' => 'ws', // 默认为ws连接，可修改为wss
            'config' => [// 标准的swoole配置项都可以再此加入
                'serverName'=>'ws',
                // 'ssl_cert_file' => '',
                // 'ssl_key_file' => '',
                'pid_file' => __DIR__ . '/../../console/runtime/logs/server.pid',
                'log_file' => __DIR__ . '/../../console/runtime/logs/swoole.log',
                'log_level' => 0,
                'setting' => array(
                    'daemonize' => false,// 守护进程执行
                    'worker_num' => 1,
                    'task_worker_num' => 1,
                    'max_request' => 500,
                    'task_max_request' => 500,
                    'backlog' => 128,
                    'heartbeat_idle_time' => 300,
                    'heartbeat_check_interval' => 60,
                    'dispatch_mode' => 1,
                ),
            ],
        ],
        'scriptManageWebsocket' => [
            'class' => 'scriptmanage\websocket\console\WebSocketController',
            'server' => 'scriptmanage\websocket\server\WebSocketServer', // 可替换为自己的业务类继承该类即可
            'host' => '0.0.0.0',// 监听地址
            'port' => 9508,// 监听端口
            'type' => 'ws', // 默认为ws连接，可修改为wss
            'config' => [// 标准的swoole配置项都可以再此加入
                'daemonize' => false,// 守护进程执行
                'task_worker_num' => 1,//task进程的数量
                'worker_num' => 1,//worker进程的数量
                // 'ssl_cert_file' => '',
                // 'ssl_key_file' => '',
                'pid_file' => __DIR__ . '/../../backend/runtime/logs/serverScriptManage.pid',
                'log_file' => __DIR__ . '/../../backend/runtime/logs/swooleScriptManage.log',
                'log_level' => 0,
            ],
        ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
